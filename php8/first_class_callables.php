<?php
// ============================================
// CALLABLES DE PRIMERA CLASE EN PHP 8.1
// Closure::fromCallable, sintaxis strlen(...), pasar funciones como argumentos
// ============================================

// --- Ejemplo 1: Sintaxis nueva func(...) vs Closure::fromCallable ---
// PHP 8.1 introduce una sintaxis más limpia para crear closures desde funciones

// ANTES (PHP < 8.1): crear closure desde función existente
$longitudAntes = Closure::fromCallable('strlen');
echo "Longitud (antes): " . $longitudAntes("Hola") . "\n"; // 4

// AHORA (PHP 8.1): sintaxis limpia con ...
$longitud = strlen(...);
echo "Longitud (ahora): " . $longitud("Hola mundo") . "\n"; // 10

// Funciona con cualquier función nativa
$mayusculas = strtoupper(...);
$minusculas = strtolower(...);
$invertir = strrev(...);

echo $mayusculas("hola php") . "\n";   // HOLA PHP
echo $minusculas("HOLA PHP") . "\n";   // hola php
echo $invertir("PHP 8.1") . "\n";      // 1.8 PHP

// También con funciones de array
$ordenar = sort(...);
$numeros = [3, 1, 4, 1, 5, 9];
$ordenar($numeros);
echo "Ordenados: " . implode(', ', $numeros) . "\n"; // 1, 1, 3, 4, 5, 9


// --- Ejemplo 2: Pasar funciones como argumentos ---
// Las callables de primera clase son perfectas para funciones de orden superior

// Funciones personalizadas
function duplicar(int $n): int {
    return $n * 2;
}

function esPar(int $n): bool {
    return $n % 2 === 0;
}

function sumar(int $a, int $b): int {
    return $a + $b;
}

$numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Pasamos la función directamente como callable con la sintaxis (...)
$duplicados = array_map(duplicar(...), $numeros);
echo "Duplicados: " . implode(', ', $duplicados) . "\n";
// 2, 4, 6, 8, 10, 12, 14, 16, 18, 20

$pares = array_filter($numeros, esPar(...));
echo "Pares: " . implode(', ', $pares) . "\n";
// 2, 4, 6, 8, 10

$total = array_reduce($numeros, sumar(...), 0);
echo "Suma total: {$total}\n"; // 55

// Composición de funciones
function componer(callable ...$funciones): Closure {
    return function (mixed $valor) use ($funciones): mixed {
        foreach (array_reverse($funciones) as $fn) {
            $valor = $fn($valor);
        }
        return $valor;
    };
}

$procesarTexto = componer(
    strtoupper(...),      // Paso 3: mayúsculas
    trim(...),            // Paso 2: quitar espacios
    strrev(...)           // Paso 1: invertir
);

echo $procesarTexto("  Hola PHP  ") . "\n"; // PHP ALOH


// --- Ejemplo 3: Métodos de clase como callables ---
// Funciona con métodos estáticos y de instancia

class Calculadora {
    public static function cuadrado(int|float $n): int|float {
        return $n * $n;
    }

    public static function raizCuadrada(int|float $n): float {
        return sqrt($n);
    }

    public function factorial(int $n): int {
        if ($n <= 1) return 1;
        return $n * $this->factorial($n - 1);
    }
}

// Métodos estáticos como callables
$cuadrado = Calculadora::cuadrado(...);
$raiz = Calculadora::raizCuadrada(...);

$numeros = [1, 2, 3, 4, 5];
$cuadrados = array_map($cuadrado, $numeros);
echo "Cuadrados: " . implode(', ', $cuadrados) . "\n";
// 1, 4, 9, 16, 25

// Métodos de instancia como callables
$calc = new Calculadora();
$factorialFn = $calc->factorial(...);

$factoriales = array_map($factorialFn, [1, 2, 3, 4, 5, 6]);
echo "Factoriales: " . implode(', ', $factoriales) . "\n";
// 1, 2, 6, 24, 120, 720


// --- Ejemplo 4: Uso práctico en un sistema de eventos ---
// Las callables de primera clase simplifican el patrón Observer

class EmisiorEventos {
    /** @var array<string, Closure[]> */
    private array $escuchadores = [];

    public function escuchar(string $evento, Closure $callback): void {
        $this->escuchadores[$evento][] = $callback;
    }

    public function emitir(string $evento, mixed ...$datos): void {
        foreach ($this->escuchadores[$evento] ?? [] as $callback) {
            $callback(...$datos);
        }
    }
}

class ServicioUsuarios {
    public function alCrear(string $nombre, string $email): void {
        echo "[Servicio] Usuario creado: {$nombre} ({$email})\n";
    }

    public function alEliminar(string $nombre, string $email): void {
        echo "[Servicio] Usuario eliminado: {$nombre} ({$email})\n";
    }
}

class ServicioEmail {
    public function enviarBienvenida(string $nombre, string $email): void {
        echo "[Email] Enviando bienvenida a {$email}\n";
    }

    public static function registrarEnLog(string $nombre, string $email): void {
        echo "[Log] Acción registrada para {$nombre}\n";
    }
}

$emisor = new EmisiorEventos();
$servicioUsuarios = new ServicioUsuarios();
$servicioEmail = new ServicioEmail();

// Registramos escuchadores usando la sintaxis de callables de primera clase
$emisor->escuchar('usuario.creado', $servicioUsuarios->alCrear(...));
$emisor->escuchar('usuario.creado', $servicioEmail->enviarBienvenida(...));
$emisor->escuchar('usuario.creado', ServicioEmail::registrarEnLog(...));

echo "=== Emitiendo evento usuario.creado ===\n";
$emisor->emitir('usuario.creado', 'Sandra', 'sandra@ejemplo.com');
// [Servicio] Usuario creado: Sandra (sandra@ejemplo.com)
// [Email] Enviando bienvenida a sandra@ejemplo.com
// [Log] Acción registrada para Sandra


// --- Ejemplo 5: Pipeline funcional con callables de primera clase ---
// Construimos un pipeline que encadena transformaciones

class Pipeline {
    /** @var Closure[] */
    private array $etapas = [];

    // Aceptamos callables como etapas del pipeline
    public function tuberia(Closure $etapa): self {
        $clon = clone $this;
        $clon->etapas[] = $etapa;
        return $clon;
    }

    public function procesar(mixed $valor): mixed {
        foreach ($this->etapas as $etapa) {
            $valor = $etapa($valor);
        }
        return $valor;
    }
}

// Funciones de transformación para strings
function limpiarEspacios(string $texto): string {
    return preg_replace('/\s+/', ' ', trim($texto));
}

function capitalizarPalabras(string $texto): string {
    return mb_convert_case($texto, MB_CASE_TITLE);
}

function eliminarAcentos(string $texto): string {
    $mapa = ['á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
             'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'ñ'=>'n', 'Ñ'=>'N'];
    return strtr($texto, $mapa);
}

function generarSlug(string $texto): string {
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    $texto = preg_replace('/[\s-]+/', '-', $texto);
    return trim($texto, '-');
}

// Creamos el pipeline usando callables de primera clase
$pipelineSlug = (new Pipeline())
    ->tuberia(limpiarEspacios(...))
    ->tuberia(eliminarAcentos(...))
    ->tuberia(generarSlug(...));

$pipelineNombre = (new Pipeline())
    ->tuberia(limpiarEspacios(...))
    ->tuberia(capitalizarPalabras(...));

$titulo = "  programación   en   PHP:   guía   práctica  ";

echo "Slug: " . $pipelineSlug->procesar($titulo) . "\n";
// Slug: programacion-en-php-guia-practica

echo "Nombre: " . $pipelineNombre->procesar($titulo) . "\n";
// Nombre: Programación En Php: Guía Práctica

// Podemos reutilizar y extender pipelines
$pipelineCompleto = $pipelineNombre
    ->tuberia(strtoupper(...));

echo "Completo: " . $pipelineCompleto->procesar($titulo) . "\n";
// Completo: PROGRAMACIÓN EN PHP: GUÍA PRÁCTICA

?>
