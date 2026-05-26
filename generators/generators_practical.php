<?php
/**
 * ============================================================
 * Patrones practicos con generadores en PHP
 * ============================================================
 * Este archivo explora patrones avanzados del mundo real:
 * pipelines de datos, evaluacion perezosa, secuencias infinitas,
 * multitarea cooperativa, y cuando elegir generadores frente
 * a iteradores o arrays.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: Pipeline de procesamiento de datos
// ============================================================
// Patron: leer -> filtrar -> transformar -> escribir
// Cada etapa es un generador que consume del anterior.
// Los datos fluyen uno a uno, sin acumularse en memoria.

echo "=== Ejemplo 1: Pipeline (leer -> filtrar -> transformar -> salida) ===\n\n";

/**
 * Etapa 1: Generar registros de log simulados.
 * En produccion, esto leeria de un archivo o stream.
 */
function leerLogs(): Generator {
    $lineas = [
        '2025-05-26 08:01:12 INFO  Usuario admin inicio sesion desde 192.168.1.100',
        '2025-05-26 08:01:45 DEBUG Consulta SQL ejecutada en 0.003s',
        '2025-05-26 08:02:30 WARNING Uso de memoria al 80%',
        '2025-05-26 08:03:01 ERROR Conexion a Redis rechazada: timeout',
        '2025-05-26 08:03:15 INFO  Reintentando conexion a Redis (intento 1/3)',
        '2025-05-26 08:03:16 INFO  Conexion a Redis restaurada',
        '2025-05-26 08:04:00 DEBUG Cache hit para clave "productos_destacados"',
        '2025-05-26 08:05:22 ERROR Fallo al procesar pago: tarjeta rechazada',
        '2025-05-26 08:05:23 WARNING Se notifico al equipo de soporte',
        '2025-05-26 08:06:00 INFO  Backup diario iniciado',
        '2025-05-26 08:06:45 INFO  Backup completado (2.3 GB en 45s)',
        '2025-05-26 08:07:10 ERROR API externa no responde: servicio de envios',
        '2025-05-26 08:07:30 DEBUG Respuesta cacheada servida para /api/productos',
        '2025-05-26 08:08:00 CRITICAL Base de datos principal no accesible',
        '2025-05-26 08:08:01 INFO  Failover a base de datos secundaria activado',
    ];

    foreach ($lineas as $linea) {
        yield $linea;
    }
}

/**
 * Etapa 2: Parsear cada linea de log en un array estructurado.
 */
function parsearLog(Generator $entrada): Generator {
    foreach ($entrada as $linea) {
        if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\s+(DEBUG|INFO|WARNING|ERROR|CRITICAL)\s+(.+)$/', $linea, $matches)) {
            yield [
                'timestamp' => $matches[1],
                'nivel'     => $matches[2],
                'mensaje'   => $matches[3],
                'linea_raw' => $linea,
            ];
        }
    }
}

/**
 * Etapa 3: Filtrar por niveles de severidad.
 */
function filtrarPorNivel(Generator $entrada, array $niveles): Generator {
    foreach ($entrada as $registro) {
        if (in_array($registro['nivel'], $niveles, true)) {
            yield $registro;
        }
    }
}

/**
 * Etapa 4: Enriquecer con metadatos.
 */
function enriquecer(Generator $entrada): Generator {
    $prioridades = [
        'DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3, 'CRITICAL' => 4,
    ];

    foreach ($entrada as $registro) {
        $registro['prioridad'] = $prioridades[$registro['nivel']] ?? 0;
        $registro['requiere_accion'] = $registro['prioridad'] >= 3;
        $registro['hora'] = substr($registro['timestamp'], 11, 8);
        yield $registro;
    }
}

/**
 * Etapa 5: Formatear para salida.
 */
function formatearSalida(Generator $entrada): Generator {
    foreach ($entrada as $registro) {
        $icono = $registro['requiere_accion'] ? '(!!)' : '    ';
        $linea = sprintf(
            "%s [%-8s] %s %s",
            $registro['hora'],
            $registro['nivel'],
            $icono,
            $registro['mensaje']
        );
        yield $linea;
    }
}

// Construir el pipeline encadenando generadores
$pipeline = formatearSalida(
    enriquecer(
        filtrarPorNivel(
            parsearLog(
                leerLogs()
            ),
            ['WARNING', 'ERROR', 'CRITICAL'] // Solo alertas
        )
    )
);

echo "Alertas del sistema (WARNING+):\n";
echo str_repeat("-", 70) . "\n";
foreach ($pipeline as $linea) {
    echo "  {$linea}\n";
}

// Pipeline alternativo: solo errores criticos
echo "\nSolo errores criticos:\n";
$criticos = formatearSalida(
    enriquecer(
        filtrarPorNivel(parsearLog(leerLogs()), ['CRITICAL'])
    )
);
foreach ($criticos as $linea) {
    echo "  {$linea}\n";
}

// ============================================================
// Ejemplo 2: Evaluacion perezosa (operaciones tipo BD)
// ============================================================
// Implementar operaciones SELECT, WHERE, ORDER BY, LIMIT
// como generadores encadenables. Los datos se procesan solo
// cuando se consumen (evaluacion perezosa/lazy).

echo "\n=== Ejemplo 2: Evaluacion perezosa (query builder) ===\n\n";

/**
 * Fuente de datos: simula una tabla de base de datos.
 */
function tablaProductos(): Generator {
    $productos = [
        ['id' => 1,  'nombre' => 'Laptop Pro 15',       'categoria' => 'informatica', 'precio' => 1299.00, 'stock' => 15],
        ['id' => 2,  'nombre' => 'Monitor 4K 27"',      'categoria' => 'informatica', 'precio' => 449.99,  'stock' => 23],
        ['id' => 3,  'nombre' => 'Teclado mecanico',     'categoria' => 'perifericos', 'precio' => 89.90,   'stock' => 50],
        ['id' => 4,  'nombre' => 'Raton gaming',          'categoria' => 'perifericos', 'precio' => 59.99,   'stock' => 80],
        ['id' => 5,  'nombre' => 'Webcam 4K',             'categoria' => 'perifericos', 'precio' => 129.00,  'stock' => 0],
        ['id' => 6,  'nombre' => 'SSD NVMe 1TB',          'categoria' => 'almacenamiento', 'precio' => 89.99, 'stock' => 35],
        ['id' => 7,  'nombre' => 'HDD 4TB',               'categoria' => 'almacenamiento', 'precio' => 79.99, 'stock' => 12],
        ['id' => 8,  'nombre' => 'Router WiFi 6',         'categoria' => 'redes',      'precio' => 149.90,  'stock' => 18],
        ['id' => 9,  'nombre' => 'Switch 24 puertos',     'categoria' => 'redes',      'precio' => 199.00,  'stock' => 5],
        ['id' => 10, 'nombre' => 'Auriculares BT Pro',    'categoria' => 'audio',      'precio' => 179.99,  'stock' => 40],
        ['id' => 11, 'nombre' => 'Altavoces 2.1',         'categoria' => 'audio',      'precio' => 69.99,   'stock' => 25],
        ['id' => 12, 'nombre' => 'Impresora laser',       'categoria' => 'impresion',  'precio' => 249.00,  'stock' => 8],
    ];

    foreach ($productos as $p) {
        yield $p;
    }
}

// Operaciones perezosas reutilizables

function where(Generator $datos, callable $condicion): Generator {
    foreach ($datos as $fila) {
        if ($condicion($fila)) {
            yield $fila;
        }
    }
}

function select(Generator $datos, array $columnas): Generator {
    foreach ($datos as $fila) {
        $seleccion = [];
        foreach ($columnas as $col) {
            if (isset($fila[$col])) {
                $seleccion[$col] = $fila[$col];
            }
        }
        yield $seleccion;
    }
}

function limit(Generator $datos, int $cantidad): Generator {
    $contador = 0;
    foreach ($datos as $fila) {
        if ($contador >= $cantidad) {
            break;
        }
        yield $fila;
        $contador++;
    }
}

function offset(Generator $datos, int $saltar): Generator {
    $saltados = 0;
    foreach ($datos as $fila) {
        if ($saltados < $saltar) {
            $saltados++;
            continue;
        }
        yield $fila;
    }
}

function map(Generator $datos, callable $transformar): Generator {
    foreach ($datos as $fila) {
        yield $transformar($fila);
    }
}

// Consulta 1: Productos de informatica con precio > 500
echo "Productos de informatica > 500 EUR:\n";
$resultado1 = select(
    where(tablaProductos(), fn($p) => $p['categoria'] === 'informatica' && $p['precio'] > 500),
    ['nombre', 'precio']
);
foreach ($resultado1 as $p) {
    echo "  {$p['nombre']}: {$p['precio']} EUR\n";
}

// Consulta 2: Top 3 productos mas caros con stock
echo "\nTop 3 mas caros con stock (simulado):\n";
// Para ORDER BY necesitamos materializar (no se puede ordenar perezosamente)
$conStock = iterator_to_array(
    where(tablaProductos(), fn($p) => $p['stock'] > 0),
    false
);
usort($conStock, fn($a, $b) => $b['precio'] <=> $a['precio']);

// Pero el LIMIT si puede ser perezoso sobre el array ordenado
function desdeArray(array $datos): Generator {
    foreach ($datos as $item) {
        yield $item;
    }
}

$top3 = limit(desdeArray($conStock), 3);
foreach ($top3 as $p) {
    echo "  {$p['nombre']}: {$p['precio']} EUR (stock: {$p['stock']})\n";
}

// Consulta 3: Paginacion (pagina 2, 3 por pagina)
echo "\nPaginacion (pagina 2, 3 items por pagina):\n";
$pagina = limit(offset(tablaProductos(), 3), 3);
foreach ($pagina as $p) {
    echo "  [{$p['id']}] {$p['nombre']}\n";
}

// Consulta 4: Agregacion perezosa
echo "\nEstadisticas por categoria:\n";
$estadisticas = [];
foreach (tablaProductos() as $p) {
    $cat = $p['categoria'];
    if (!isset($estadisticas[$cat])) {
        $estadisticas[$cat] = ['count' => 0, 'total' => 0, 'stock_total' => 0];
    }
    $estadisticas[$cat]['count']++;
    $estadisticas[$cat]['total'] += $p['precio'];
    $estadisticas[$cat]['stock_total'] += $p['stock'];
}

echo sprintf("  %-15s %6s %12s %8s\n", 'Categoria', 'Items', 'Precio medio', 'Stock');
echo "  " . str_repeat("-", 50) . "\n";
foreach ($estadisticas as $cat => $s) {
    $media = round($s['total'] / $s['count'], 2);
    echo sprintf("  %-15s %6d %12.2f %8d\n", $cat, $s['count'], $media, $s['stock_total']);
}

// ============================================================
// Ejemplo 3: Secuencias infinitas
// ============================================================
// Los generadores pueden representar secuencias infinitas porque
// solo generan el siguiente valor cuando se les pide.

echo "\n=== Ejemplo 3: Secuencias infinitas ===\n\n";

/**
 * Genera la secuencia de Fibonacci de forma infinita.
 */
function fibonacci(): Generator {
    $a = 0;
    $b = 1;

    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

// Tomar solo los primeros 15 numeros de Fibonacci
echo "Fibonacci (primeros 15):\n  ";
$fib = fibonacci();
for ($i = 0; $i < 15; $i++) {
    echo $fib->current() . " ";
    $fib->next();
}
echo "\n";

// Funcion auxiliar: tomar N elementos de un generador infinito
function tomar(Generator $gen, int $n): array {
    $resultado = [];
    $contador = 0;
    foreach ($gen as $valor) {
        $resultado[] = $valor;
        $contador++;
        if ($contador >= $n) break;
    }
    return $resultado;
}

// Fibonacci mayores que 100 y menores que 10000
echo "\nFibonacci entre 100 y 10,000:\n  ";
foreach (fibonacci() as $num) {
    if ($num > 10000) break;
    if ($num >= 100) {
        echo "{$num} ";
    }
}
echo "\n";

/**
 * Genera numeros primos de forma infinita (Criba incremental).
 */
function primos(): Generator {
    yield 2;

    $compuestos = []; // Tabla de factores conocidos

    for ($n = 3; true; $n += 2) { // Solo impares
        if (isset($compuestos[$n])) {
            // $n es compuesto; mover sus factores hacia adelante
            $factores = $compuestos[$n];
            unset($compuestos[$n]);

            foreach ($factores as $factor) {
                // Buscar el siguiente multiplo impar del factor
                $siguiente = $n + 2 * $factor;
                while (isset($compuestos[$siguiente])) {
                    $siguiente += 2 * $factor;
                }
                $compuestos[$siguiente][] = $factor;
            }
        } else {
            // $n es primo
            yield $n;
            // Marcar n*n como compuesto (optimizacion: empezar desde n^2)
            $compuestos[$n * $n][] = $n;
        }
    }
}

echo "\nPrimos (primeros 25):\n  ";
echo implode(', ', tomar(primos(), 25)) . "\n";

// Contar primos menores de 1000
$contadorPrimos = 0;
foreach (primos() as $p) {
    if ($p >= 1000) break;
    $contadorPrimos++;
}
echo "Primos menores que 1000: {$contadorPrimos}\n";

/**
 * Genera una secuencia personalizada con regla definida.
 * Recuerde: el generador nunca termina (secuencia infinita).
 */
function secuenciaRecurrente(float $inicio, callable $regla): Generator {
    $valor = $inicio;
    while (true) {
        yield $valor;
        $valor = $regla($valor);
    }
}

// Secuencia geometrica: cada termino es el doble del anterior
echo "\nSecuencia geometrica (x2): ";
echo implode(', ', tomar(secuenciaRecurrente(1, fn($x) => $x * 2), 12)) . "\n";

// Secuencia de Collatz desde 27 (hasta llegar a 1)
echo "\nCollatz desde 27: ";
$collatzGen = secuenciaRecurrente(27, fn($n) => $n % 2 === 0 ? $n / 2 : 3 * $n + 1);
$pasos = 0;
foreach ($collatzGen as $valor) {
    echo "{$valor} ";
    $pasos++;
    if ($valor === 1.0 || $valor === 1) break;
    if ($pasos > 200) { echo "..."; break; } // Seguridad
}
echo "\n(Llego a 1 en {$pasos} pasos)\n";

// ============================================================
// Ejemplo 4: Multitarea cooperativa con generadores
// ============================================================
// Los generadores pueden simular concurrencia: un planificador
// (scheduler) alterna entre tareas usando yield como puntos
// de suspension.

echo "\n=== Ejemplo 4: Multitarea cooperativa ===\n\n";

/**
 * Tarea simulada que realiza trabajo en pasos.
 * Cada yield es un punto de suspension donde cede el control.
 */
function tarea(string $nombre, int $pasos): Generator {
    for ($i = 1; $i <= $pasos; $i++) {
        // Simular trabajo (en realidad seria I/O, espera, etc.)
        $progreso = round($i / $pasos * 100);
        yield "[{$nombre}] Paso {$i}/{$pasos} ({$progreso}%)";
    }
    return "[{$nombre}] Completada en {$pasos} pasos";
}

/**
 * Planificador round-robin simple.
 * Alterna entre las tareas, ejecutando un paso de cada una.
 */
function planificador(array $tareas): Generator {
    $cola = $tareas;
    $ciclo = 0;
    $completadas = [];

    while (!empty($cola)) {
        $ciclo++;
        $tareasActivas = [];

        foreach ($cola as $indice => $tarea) {
            if ($tarea->valid()) {
                $mensaje = $tarea->current();
                yield ['ciclo' => $ciclo, 'mensaje' => $mensaje];
                $tarea->next();
                $tareasActivas[] = $tarea;
            } else {
                // La tarea termino
                try {
                    $completadas[] = $tarea->getReturn();
                } catch (\Exception $e) {
                    $completadas[] = "(sin valor de retorno)";
                }
            }
        }

        $cola = $tareasActivas;
    }

    return [
        'ciclos'      => $ciclo,
        'completadas' => $completadas,
    ];
}

// Crear 4 tareas con diferente duracion
$tareas = [
    tarea('Backup DB', 3),
    tarea('Enviar emails', 5),
    tarea('Generar PDF', 4),
    tarea('Limpiar cache', 2),
];

$scheduler = planificador($tareas);

echo "Ejecucion cooperativa (round-robin):\n";
echo str_repeat("-", 55) . "\n";

$cicloAnterior = 0;
foreach ($scheduler as $evento) {
    if ($evento['ciclo'] !== $cicloAnterior) {
        if ($cicloAnterior > 0) echo "\n";
        echo "Ciclo {$evento['ciclo']}:\n";
        $cicloAnterior = $evento['ciclo'];
    }
    echo "  {$evento['mensaje']}\n";
}

$resumenScheduler = $scheduler->getReturn();
echo "\nResultados:\n";
echo "  Ciclos totales: {$resumenScheduler['ciclos']}\n";
echo "  Tareas completadas:\n";
foreach ($resumenScheduler['completadas'] as $resultado) {
    echo "    {$resultado}\n";
}

// ============================================================
// Ejemplo 5: Planificador con prioridades
// ============================================================
// Extension del patron anterior con prioridades: las tareas
// de alta prioridad se ejecutan mas a menudo.

echo "\n=== Ejemplo 5: Planificador con prioridades ===\n\n";

/**
 * Planificador con prioridades.
 * Prioridad alta (3) ejecuta 3 pasos por ciclo.
 * Prioridad media (2) ejecuta 2 pasos.
 * Prioridad baja (1) ejecuta 1 paso.
 */
function planificadorPrioridad(array $tareasConPrioridad): Generator {
    $ciclo = 0;

    while (!empty($tareasConPrioridad)) {
        $ciclo++;
        $siguientes = [];

        foreach ($tareasConPrioridad as [$generador, $prioridad, $nombre]) {
            $ejecutados = 0;

            // Ejecutar tantos pasos como indique la prioridad
            while ($ejecutados < $prioridad && $generador->valid()) {
                yield [
                    'ciclo'     => $ciclo,
                    'tarea'     => $nombre,
                    'prioridad' => $prioridad,
                    'paso'      => $generador->current(),
                ];
                $generador->next();
                $ejecutados++;
            }

            if ($generador->valid()) {
                $siguientes[] = [$generador, $prioridad, $nombre];
            }
        }

        $tareasConPrioridad = $siguientes;
    }

    return $ciclo;
}

// Tareas con diferente prioridad
$tareasP = [
    [tarea('URGENTE: Parche seguridad', 6), 3, 'Parche seguridad'],     // Alta
    [tarea('Migrar datos', 8), 2, 'Migrar datos'],                       // Media
    [tarea('Actualizar docs', 5), 1, 'Actualizar docs'],                  // Baja
];

$schedP = planificadorPrioridad($tareasP);

echo "Ejecucion con prioridades (alta=3 pasos, media=2, baja=1):\n";
echo str_repeat("-", 60) . "\n";

$cicloAnt = 0;
foreach ($schedP as $ev) {
    if ($ev['ciclo'] !== $cicloAnt) {
        if ($cicloAnt > 0) echo "\n";
        echo "Ciclo {$ev['ciclo']}:\n";
        $cicloAnt = $ev['ciclo'];
    }
    $prio = str_repeat('*', $ev['prioridad']);
    echo "  [{$prio}] {$ev['paso']}\n";
}
echo "\nCiclos necesarios: " . $schedP->getReturn() . "\n";

// ============================================================
// Ejemplo 6: Comparativa - Generadores vs Iteradores vs Arrays
// ============================================================
// Cada enfoque tiene su lugar. Esta seccion ayuda a elegir
// el correcto para cada situacion.

echo "\n=== Ejemplo 6: Comparativa de enfoques ===\n\n";

echo "GENERADORES vs ITERADORES (clase) vs ARRAYS\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Array: todos los datos en memoria
function obtenerDatosArray(int $n): array {
    $datos = [];
    for ($i = 0; $i < $n; $i++) {
        $datos[] = $i * $i;
    }
    return $datos;
}

// 2. Generador: datos bajo demanda
function obtenerDatosGenerador(int $n): Generator {
    for ($i = 0; $i < $n; $i++) {
        yield $i * $i;
    }
}

// 3. Iterator: clase completa con estado
class DatosIterator implements Iterator {
    private int $posicion = 0;

    public function __construct(private int $n) {}

    public function current(): int {
        return $this->posicion * $this->posicion;
    }

    public function key(): int {
        return $this->posicion;
    }

    public function next(): void {
        $this->posicion++;
    }

    public function rewind(): void {
        $this->posicion = 0;
    }

    public function valid(): bool {
        return $this->posicion < $this->n;
    }
}

// Comparar rendimiento con 50,000 elementos
$n = 50000;

// Array
$t1 = microtime(true);
$m1 = memory_get_usage();
$suma = 0;
foreach (obtenerDatosArray($n) as $v) {
    $suma += $v;
}
$tiempoArray = microtime(true) - $t1;
$memArray = memory_get_usage() - $m1;

// Generador
$t2 = microtime(true);
$m2 = memory_get_usage();
$suma = 0;
foreach (obtenerDatosGenerador($n) as $v) {
    $suma += $v;
}
$tiempoGen = microtime(true) - $t2;
$memGen = memory_get_usage() - $m2;

// Iterator
$t3 = microtime(true);
$m3 = memory_get_usage();
$suma = 0;
foreach (new DatosIterator($n) as $v) {
    $suma += $v;
}
$tiempoIter = microtime(true) - $t3;
$memIter = memory_get_usage() - $m3;

echo "Rendimiento con {$n} elementos:\n";
echo str_repeat("-", 55) . "\n";
echo sprintf("  %-15s %12s %15s\n", 'Enfoque', 'Tiempo (ms)', 'Memoria (KB)');
echo str_repeat("-", 55) . "\n";
echo sprintf("  %-15s %12.2f %15.1f\n", 'Array', $tiempoArray * 1000, max(0, $memArray / 1024));
echo sprintf("  %-15s %12.2f %15.1f\n", 'Generador', $tiempoGen * 1000, max(0, $memGen / 1024));
echo sprintf("  %-15s %12.2f %15.1f\n", 'Iterator', $tiempoIter * 1000, max(0, $memIter / 1024));
echo str_repeat("-", 55) . "\n";

echo "\nGUIA DE DECISION:\n";
echo str_repeat("-", 70) . "\n\n";

$guia = [
    [
        'situacion' => 'Datos pequenos (<1000 items), acceso aleatorio',
        'elegir'    => 'Array',
        'razon'     => 'Simple, rapido, soporta array_*, shuffle, sort',
    ],
    [
        'situacion' => 'Datos grandes, procesamiento secuencial',
        'elegir'    => 'Generador',
        'razon'     => 'Memoria constante, facil de escribir',
    ],
    [
        'situacion' => 'Logica de iteracion reutilizable y compleja',
        'elegir'    => 'Iterator (clase)',
        'razon'     => 'Encapsulacion, multiples recorridos, estado rico',
    ],
    [
        'situacion' => 'Pipeline de transformacion de datos',
        'elegir'    => 'Generador',
        'razon'     => 'Composicion natural, evaluacion perezosa',
    ],
    [
        'situacion' => 'Archivo de varios GB, procesamiento linea a linea',
        'elegir'    => 'Generador',
        'razon'     => 'Unica opcion viable (memoria constante)',
    ],
    [
        'situacion' => 'Necesitas array_map, array_filter, etc.',
        'elegir'    => 'Array',
        'razon'     => 'Funciones de array no aceptan generators',
    ],
    [
        'situacion' => 'Iterar multiples veces sobre los mismos datos',
        'elegir'    => 'Iterator o Array',
        'razon'     => 'Generadores NO se pueden rebobinar',
    ],
    [
        'situacion' => 'Secuencias infinitas o bajo demanda',
        'elegir'    => 'Generador',
        'razon'     => 'Array no puede ser infinito',
    ],
    [
        'situacion' => 'Necesitas serializar/cachear los datos',
        'elegir'    => 'Array',
        'razon'     => 'Generadores no son serializables',
    ],
    [
        'situacion' => 'Comunicacion bidireccional (send/throw)',
        'elegir'    => 'Generador',
        'razon'     => 'Unico que soporta corrutinas',
    ],
];

foreach ($guia as $i => $g) {
    $num = $i + 1;
    echo "  {$num}. {$g['situacion']}\n";
    echo "     -> Elegir: {$g['elegir']}\n";
    echo "     Razon: {$g['razon']}\n\n";
}

// Limitaciones importantes de los generadores
echo "LIMITACIONES DE LOS GENERADORES:\n";
echo str_repeat("-", 70) . "\n";
echo "  1. No se pueden rebobinar (rewind). Una vez consumidos, hay que\n";
echo "     crear un nuevo generador.\n";
echo "  2. No soportan acceso por indice (\$gen[5] no funciona).\n";
echo "  3. No se pueden serializar (json_encode, serialize).\n";
echo "  4. count() no funciona (no implementan Countable).\n";
echo "  5. array_map/array_filter no aceptan Generator.\n";
echo "     Alternativa: iterator_to_array() + funciones de array,\n";
echo "     o crear generadores wrapper (como los ejemplos de este archivo).\n";

// Demostrar la limitacion de rebobinado
echo "\nDemostracion: generadores no se pueden rebobinar:\n";
$gen = obtenerDatosGenerador(5);
echo "  Primera pasada: ";
foreach ($gen as $v) {
    echo "{$v} ";
}
echo "\n";

try {
    $gen->rewind();
    echo "  Segunda pasada: ";
    foreach ($gen as $v) {
        echo "{$v} ";
    }
} catch (\Exception $e) {
    echo "  Rebobinar fallo: " . $e->getMessage() . "\n";
}

// Solucion: envolver en una funcion que crea un nuevo generador
echo "\n  Solucion: recrear el generador para cada pasada.\n";
$crearGen = fn() => obtenerDatosGenerador(5);
echo "  Pasada 1: " . implode(', ', iterator_to_array($crearGen())) . "\n";
echo "  Pasada 2: " . implode(', ', iterator_to_array($crearGen())) . "\n";

?>
