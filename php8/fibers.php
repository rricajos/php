<?php
// ============================================
// FIBERS EN PHP 8.1
// Fiber class, Fiber::suspend, Fiber::resume, ejemplo asíncrono práctico
// ============================================

// --- Ejemplo 1: Fiber básica - suspender y reanudar ---
// Un Fiber es una función que puede pausarse y reanudarse

$fiber = new Fiber(function (): void {
    echo "1. Fiber iniciada\n";

    // Fiber::suspend() pausa la ejecución y devuelve el control al llamador
    $valor = Fiber::suspend("Primer valor suspendido");
    echo "3. Fiber reanudada con: {$valor}\n";

    $valor2 = Fiber::suspend("Segundo valor suspendido");
    echo "5. Fiber reanudada de nuevo con: {$valor2}\n";

    echo "6. Fiber finalizada\n";
});

// start() inicia la fiber y se ejecuta hasta el primer suspend()
$resultado1 = $fiber->start();
echo "2. Código principal recibió: {$resultado1}\n";

// resume() reanuda la fiber desde donde se pausó
$resultado2 = $fiber->resume("Datos del exterior");
echo "4. Código principal recibió: {$resultado2}\n";

// Última reanudación
$fiber->resume("Más datos");
// Salida:
// 1. Fiber iniciada
// 2. Código principal recibió: Primer valor suspendido
// 3. Fiber reanudada con: Datos del exterior
// 4. Código principal recibió: Segundo valor suspendido
// 5. Fiber reanudada de nuevo con: Más datos
// 6. Fiber finalizada

echo "¿Terminada? " . ($fiber->isTerminated() ? "Sí" : "No") . "\n\n"; // Sí


// --- Ejemplo 2: Generador de secuencia con Fibers ---
// Usar Fibers para producir valores bajo demanda (similar a generators pero más flexible)

function crearSecuenciaFibonacci(int $limite): Fiber {
    return new Fiber(function () use ($limite): void {
        $a = 0;
        $b = 1;
        $cuenta = 0;

        while ($cuenta < $limite) {
            // Suspendemos y enviamos el valor actual
            Fiber::suspend($a);
            [$a, $b] = [$b, $a + $b];
            $cuenta++;
        }
    });
}

$fib = crearSecuenciaFibonacci(10);
$numeros = [];

// Obtenemos el primer valor con start()
$valor = $fib->start();
$numeros[] = $valor;

// Los siguientes con resume()
while (!$fib->isTerminated()) {
    $valor = $fib->resume();
    if ($valor !== null) {
        $numeros[] = $valor;
    }
}

echo "Fibonacci: " . implode(', ', $numeros) . "\n\n";
// Fibonacci: 0, 1, 1, 2, 3, 5, 8, 13, 21, 34


// --- Ejemplo 3: Simulación de tareas asíncronas con un planificador ---
// Un planificador simple que ejecuta múltiples fibers de forma cooperativa

class Planificador {
    /** @var array<string, Fiber> */
    private array $tareas = [];
    private array $resultados = [];

    public function agregarTarea(string $nombre, callable $funcion): void {
        $this->tareas[$nombre] = new Fiber($funcion);
    }

    // Ejecuta todas las tareas de forma cooperativa (round-robin)
    public function ejecutar(): array {
        // Iniciamos todas las fibers
        foreach ($this->tareas as $nombre => $fiber) {
            echo "[Planificador] Iniciando tarea: {$nombre}\n";
            $valor = $fiber->start();
            if ($valor !== null) {
                $this->resultados[$nombre][] = $valor;
            }
        }

        // Seguimos reanudando hasta que todas terminen
        $activas = true;
        while ($activas) {
            $activas = false;
            foreach ($this->tareas as $nombre => $fiber) {
                if (!$fiber->isTerminated()) {
                    $activas = true;
                    $valor = $fiber->resume();
                    if ($valor !== null) {
                        $this->resultados[$nombre][] = $valor;
                    }
                }
            }
        }

        echo "[Planificador] Todas las tareas completadas\n";
        return $this->resultados;
    }
}

$planificador = new Planificador();

// Tarea que simula descarga de archivos
$planificador->agregarTarea('descarga', function (): void {
    $archivos = ['imagen.jpg', 'video.mp4', 'documento.pdf'];
    foreach ($archivos as $archivo) {
        echo "  [Descarga] Descargando {$archivo}...\n";
        Fiber::suspend("descargado: {$archivo}");
    }
    echo "  [Descarga] Todas las descargas completadas\n";
});

// Tarea que simula procesamiento de datos
$planificador->agregarTarea('procesamiento', function (): void {
    $lotes = ['Lote A', 'Lote B'];
    foreach ($lotes as $lote) {
        echo "  [Proceso] Procesando {$lote}...\n";
        Fiber::suspend("procesado: {$lote}");
    }
    echo "  [Proceso] Procesamiento completado\n";
});

$resultados = $planificador->ejecutar();
echo "\nResultados:\n";
foreach ($resultados as $tarea => $valores) {
    echo "  {$tarea}: " . implode(', ', $valores) . "\n";
}


// --- Ejemplo 4: Fiber como middleware / pipeline ---
// Usar Fibers para crear un pipeline donde cada paso puede pausarse

class Pipeline {
    /** @var Fiber[] */
    private array $pasos = [];

    public function tuberia(callable $paso): self {
        $this->pasos[] = new Fiber($paso);
        return $this;
    }

    public function procesar(mixed $entrada): mixed {
        $valor = $entrada;

        foreach ($this->pasos as $indice => $fiber) {
            echo "  Pipeline paso " . ($indice + 1) . ": procesando...\n";

            // Iniciamos la fiber pasándole el valor actual
            $valor = $fiber->start($valor);

            // Si la fiber se suspendió, la reanudamos hasta que termine
            while (!$fiber->isTerminated()) {
                $valor = $fiber->resume();
            }

            // Si la fiber retornó un valor final, lo usamos
            $retorno = $fiber->getReturn();
            if ($retorno !== null) {
                $valor = $retorno;
            }
        }

        return $valor;
    }
}

echo "\n=== Pipeline de procesamiento de texto ===\n";

$pipeline = new Pipeline();

$pipeline->tuberia(function (string $texto): string {
    // Paso 1: Limpiar espacios extra
    $limpio = preg_replace('/\s+/', ' ', trim($texto));
    Fiber::suspend($limpio);
    return $limpio;
});

$pipeline->tuberia(function (string $texto): string {
    // Paso 2: Convertir a minúsculas
    $minusculas = mb_strtolower($texto);
    Fiber::suspend($minusculas);
    return $minusculas;
});

$pipeline->tuberia(function (string $texto): string {
    // Paso 3: Eliminar caracteres especiales
    $limpio = preg_replace('/[^a-záéíóúüñ\s]/u', '', $texto);
    Fiber::suspend($limpio);
    return $limpio;
});

$resultado = $pipeline->procesar("  ¡Hola,   MUNDO!  ¿Cómo   Estás?  ");
echo "Resultado final: '{$resultado}'\n";
// Resultado final: 'hola mundo cómo estás'


// --- Ejemplo 5: Estado de una Fiber ---
// Verificación de los diferentes estados de una Fiber

echo "\n=== Estados de una Fiber ===\n";

$fiber = new Fiber(function (): string {
    Fiber::suspend();
    return "valor final";
});

echo "Antes de start():\n";
echo "  ¿Iniciada? " . ($fiber->isStarted() ? "Sí" : "No") . "\n";       // No
echo "  ¿Corriendo? " . ($fiber->isRunning() ? "Sí" : "No") . "\n";      // No
echo "  ¿Suspendida? " . ($fiber->isSuspended() ? "Sí" : "No") . "\n";   // No
echo "  ¿Terminada? " . ($fiber->isTerminated() ? "Sí" : "No") . "\n";   // No

$fiber->start();
echo "\nDespués de start() (suspendida):\n";
echo "  ¿Iniciada? " . ($fiber->isStarted() ? "Sí" : "No") . "\n";       // Sí
echo "  ¿Suspendida? " . ($fiber->isSuspended() ? "Sí" : "No") . "\n";   // Sí
echo "  ¿Terminada? " . ($fiber->isTerminated() ? "Sí" : "No") . "\n";   // No

$fiber->resume();
echo "\nDespués de resume() (terminada):\n";
echo "  ¿Iniciada? " . ($fiber->isStarted() ? "Sí" : "No") . "\n";       // Sí
echo "  ¿Suspendida? " . ($fiber->isSuspended() ? "Sí" : "No") . "\n";   // No
echo "  ¿Terminada? " . ($fiber->isTerminated() ? "Sí" : "No") . "\n";   // Sí
echo "  Valor retornado: " . $fiber->getReturn() . "\n";                   // valor final

?>
