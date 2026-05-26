<?php
/**
 * ============================================================
 * yield from en PHP - Delegacion y composicion de generadores
 * ============================================================
 * yield from delega la generacion de valores a otro iterable
 * (array, generador u objeto Traversable). Permite componer
 * generadores como piezas modulares y reutilizables, creando
 * secuencias complejas a partir de partes simples.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: yield from con arrays
// ============================================================
// La forma mas basica: delegar a un array para que sus
// elementos se emitan como si fueran yields individuales.

echo "=== Ejemplo 1: yield from con arrays ===\n\n";

/**
 * Genera una baraja de cartas espanola combinando palos y valores.
 */
function barajaEspanola(): Generator {
    $palos = ['Oros', 'Copas', 'Espadas', 'Bastos'];

    foreach ($palos as $palo) {
        // yield from emite cada carta del array como un valor individual
        yield from generarCartasPalo($palo);
    }
}

function generarCartasPalo(string $palo): array {
    $nombres = [1 => 'As', 2 => 'Dos', 3 => 'Tres', 4 => 'Cuatro',
                5 => 'Cinco', 6 => 'Seis', 7 => 'Siete', 10 => 'Sota',
                11 => 'Caballo', 12 => 'Rey'];
    $cartas = [];
    foreach ($nombres as $valor => $nombre) {
        $cartas[] = "{$nombre} de {$palo}";
    }
    return $cartas;
}

// Sacar las primeras 8 cartas
echo "Primeras 8 cartas de la baraja espanola:\n";
$contador = 0;
foreach (barajaEspanola() as $carta) {
    $contador++;
    echo "  {$contador}. {$carta}\n";
    if ($contador >= 8) break;
}

// Contar total (sin almacenar en memoria)
$total = 0;
foreach (barajaEspanola() as $_) {
    $total++;
}
echo "\nTotal de cartas en la baraja: {$total}\n";

// yield from tambien funciona con arrays asociativos (clave => valor)
function configuracionPorDefecto(): Generator {
    yield from [
        'debug'    => false,
        'cache'    => true,
        'timeout'  => 30,
    ];

    yield from [
        'log_level' => 'warning',
        'locale'    => 'es_ES',
        'timezone'  => 'Europe/Madrid',
    ];
}

echo "\nConfiguracion combinada:\n";
foreach (configuracionPorDefecto() as $clave => $valor) {
    echo "  {$clave} => " . var_export($valor, true) . "\n";
}

// ============================================================
// Ejemplo 2: yield from con otros generadores (delegacion)
// ============================================================
// El caso mas potente: un generador delega a otro generador,
// creando cadenas de procesamiento modulares.

echo "\n=== Ejemplo 2: Delegacion entre generadores ===\n\n";

/**
 * Genera numeros pares en un rango.
 */
function pares(int $inicio, int $fin): Generator {
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i % 2 === 0) {
            yield $i;
        }
    }
    return "pares: {$inicio}-{$fin}"; // Valor de retorno
}

/**
 * Genera numeros impares en un rango.
 */
function impares(int $inicio, int $fin): Generator {
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i % 2 !== 0) {
            yield $i;
        }
    }
    return "impares: {$inicio}-{$fin}";
}

/**
 * Combina pares e impares en una secuencia intercalada por rangos.
 * yield from propaga los valores de cada sub-generador.
 */
function numerosOrdenados(int $max): Generator {
    echo "  [Delegando a pares...]\n";
    $retPares = yield from pares(1, $max);
    echo "  [Pares completado, retorno: '{$retPares}']\n";

    echo "  [Delegando a impares...]\n";
    $retImpares = yield from impares(1, $max);
    echo "  [Impares completado, retorno: '{$retImpares}']\n";

    // IMPORTANTE: yield from devuelve el valor de return del sub-generador
    return "Secuencia completa: {$retPares}, {$retImpares}";
}

echo "Todos los numeros (primero pares, luego impares) hasta 10:\n";
$gen = numerosOrdenados(10);
$valores = [];
foreach ($gen as $v) {
    $valores[] = $v;
}
echo "  Valores: " . implode(', ', $valores) . "\n";
echo "  Retorno final: " . $gen->getReturn() . "\n";

// ============================================================
// Ejemplo 3: Combinar multiples generadores
// ============================================================
// yield from permite encadenar generadores como bloques de
// construccion para crear secuencias complejas.

echo "\n=== Ejemplo 3: Combinacion de multiples generadores ===\n\n";

/**
 * Genera registros de log de diferentes fuentes.
 * Cada fuente es un generador independiente.
 */
function logsAplicacion(): Generator {
    yield from ['[APP] Inicio de aplicacion', '[APP] Cargando configuracion'];
}

function logsBaseDatos(): Generator {
    yield '[DB] Conexion establecida';
    yield '[DB] Pool de conexiones: 5 activas';
    yield '[DB] Migraciones aplicadas: 23';
}

function logsCache(): Generator {
    yield '[CACHE] Redis conectado en localhost:6379';
    yield '[CACHE] Precalentamiento completado (1,234 claves)';
}

function logsSeguridad(): Generator {
    yield '[SEC] Certificados SSL validados';
    yield '[SEC] Firewall activo: 12 reglas cargadas';
}

/**
 * Combina todos los logs de inicio del sistema.
 */
function logsSistemaCompleto(): Generator {
    yield '=== INICIO DEL SISTEMA ===';
    yield from logsAplicacion();
    yield from logsBaseDatos();
    yield from logsCache();
    yield from logsSeguridad();
    yield '=== SISTEMA LISTO ===';
}

echo "Secuencia de inicio:\n";
foreach (logsSistemaCompleto() as $linea) {
    echo "  {$linea}\n";
}

// Ejemplo con datos tabulares de diferentes origenes
function empleadosMadrid(): Generator {
    yield ['nombre' => 'Ana Lopez',     'ciudad' => 'Madrid',    'depto' => 'IT'];
    yield ['nombre' => 'Carlos Ruiz',   'ciudad' => 'Madrid',    'depto' => 'RRHH'];
}

function empleadosBarcelona(): Generator {
    yield ['nombre' => 'Laura Vidal',    'ciudad' => 'Barcelona', 'depto' => 'IT'];
    yield ['nombre' => 'Pedro Soler',    'ciudad' => 'Barcelona', 'depto' => 'Ventas'];
}

function empleadosValencia(): Generator {
    yield ['nombre' => 'Sofia Navarro',  'ciudad' => 'Valencia',  'depto' => 'Marketing'];
}

function todosLosEmpleados(): Generator {
    yield from empleadosMadrid();
    yield from empleadosBarcelona();
    yield from empleadosValencia();
}

echo "\nTodos los empleados (combinados de 3 oficinas):\n";
foreach (todosLosEmpleados() as $emp) {
    echo "  {$emp['nombre']} - {$emp['ciudad']} ({$emp['depto']})\n";
}

// ============================================================
// Ejemplo 4: Generadores recursivos (recorrido de arbol)
// ============================================================
// yield from es perfecto para algoritmos recursivos porque
// permite que cada nivel de recursion delegue sus valores
// hacia arriba de forma transparente.

echo "\n=== Ejemplo 4: Generadores recursivos ===\n\n";

/**
 * Recorre un arbol de directorios de forma recursiva.
 * yield from delega la recursion a sub-generadores.
 *
 * @param string $directorio Ruta base.
 * @param int $profundidad Nivel actual de profundidad.
 * @param int $maxProfundidad Limite de profundidad (-1 = sin limite).
 * @return Generator Cada archivo/directorio encontrado.
 */
function recorrerDirectorio(string $directorio, int $profundidad = 0, int $maxProfundidad = -1): Generator {
    if ($maxProfundidad >= 0 && $profundidad > $maxProfundidad) {
        return;
    }

    $entradas = @scandir($directorio);
    if ($entradas === false) {
        return; // No se puede leer el directorio
    }

    foreach ($entradas as $entrada) {
        if ($entrada === '.' || $entrada === '..') {
            continue;
        }

        $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $entrada;

        yield [
            'nombre'       => $entrada,
            'ruta'         => $rutaCompleta,
            'es_directorio' => is_dir($rutaCompleta),
            'profundidad'  => $profundidad,
            'tamano'       => is_file($rutaCompleta) ? filesize($rutaCompleta) : null,
        ];

        // Recursion: yield from delega al sub-arbol
        if (is_dir($rutaCompleta)) {
            yield from recorrerDirectorio($rutaCompleta, $profundidad + 1, $maxProfundidad);
        }
    }
}

// Recorrer el directorio temporal del sistema (limitado a profundidad 1)
$dirTemp = sys_get_temp_dir();
echo "Recorriendo {$dirTemp} (profundidad max: 1):\n";
$contadorArchivos = 0;
$contadorDirs = 0;
$tamanoTotal = 0;

foreach (recorrerDirectorio($dirTemp, 0, 1) as $item) {
    if ($contadorArchivos + $contadorDirs >= 15) {
        echo "  ... (mostrando solo los primeros 15)\n";
        break;
    }

    $indent = str_repeat('  ', $item['profundidad']);
    if ($item['es_directorio']) {
        echo "  {$indent}[DIR] {$item['nombre']}/\n";
        $contadorDirs++;
    } else {
        $tamano = $item['tamano'] !== null ? number_format($item['tamano']) . ' bytes' : 'N/A';
        echo "  {$indent}      {$item['nombre']} ({$tamano})\n";
        $contadorArchivos++;
        $tamanoTotal += $item['tamano'] ?? 0;
    }
}

// Ejemplo mas controlado: estructura de arbol en memoria
echo "\nRecorrido de arbol en memoria (estructura de datos):\n";

/**
 * Recorre un arbol representado como array anidado.
 * Generador recursivo con yield from.
 */
function recorrerArbol(array $nodo, int $nivel = 0): Generator {
    yield [
        'nombre' => $nodo['nombre'],
        'nivel'  => $nivel,
        'tipo'   => $nodo['tipo'] ?? 'nodo',
    ];

    if (isset($nodo['hijos'])) {
        foreach ($nodo['hijos'] as $hijo) {
            yield from recorrerArbol($hijo, $nivel + 1);
        }
    }
}

$arbolOrganizacion = [
    'nombre' => 'CEO - Maria Santos',
    'tipo'   => 'directivo',
    'hijos'  => [
        [
            'nombre' => 'CTO - Pedro Alvarez',
            'tipo'   => 'directivo',
            'hijos'  => [
                ['nombre' => 'Lead Backend - Ana Garcia', 'tipo' => 'lider',
                 'hijos' => [
                     ['nombre' => 'Dev Senior - Carlos Ruiz', 'tipo' => 'empleado'],
                     ['nombre' => 'Dev Junior - Laura Sanz', 'tipo' => 'empleado'],
                 ]],
                ['nombre' => 'Lead Frontend - Diego Torres', 'tipo' => 'lider',
                 'hijos' => [
                     ['nombre' => 'Dev Senior - Sofia Herrera', 'tipo' => 'empleado'],
                 ]],
            ],
        ],
        [
            'nombre' => 'CFO - Roberto Mendez',
            'tipo'   => 'directivo',
            'hijos'  => [
                ['nombre' => 'Contable - Isabel Moreno', 'tipo' => 'empleado'],
                ['nombre' => 'Analista - Javier Diaz', 'tipo' => 'empleado'],
            ],
        ],
    ],
];

foreach (recorrerArbol($arbolOrganizacion) as $persona) {
    $indent = str_repeat('    ', $persona['nivel']);
    $icono = match($persona['tipo']) {
        'directivo' => '[D]',
        'lider'     => '[L]',
        default     => '[-]',
    };
    echo "  {$indent}{$icono} {$persona['nombre']}\n";
}

// ============================================================
// Ejemplo 5: Aplanar estructuras anidadas (practico)
// ============================================================
// Caso real: datos JSON de APIs o bases de datos con estructura
// anidada que necesita ser aplanada para procesamiento.

echo "\n=== Ejemplo 5: Aplanar datos anidados (practico) ===\n\n";

/**
 * Aplana un array multidimensional de cualquier profundidad.
 * yield from maneja la recursion de forma elegante.
 *
 * @param iterable $datos Datos posiblemente anidados.
 * @param int $profundidad Niveles a aplanar (-1 = todos).
 * @return Generator Secuencia plana de valores.
 */
function aplanar(iterable $datos, int $profundidad = -1): Generator {
    foreach ($datos as $valor) {
        if ((is_array($valor) || $valor instanceof Traversable) && $profundidad !== 0) {
            yield from aplanar($valor, $profundidad > 0 ? $profundidad - 1 : -1);
        } else {
            yield $valor;
        }
    }
}

// Ejemplo: datos de categorias con subcategorias anidadas
$categorias = [
    'Electronica' => [
        'Moviles' => ['iPhone 15', 'Galaxy S24', 'Pixel 8'],
        'Portatiles' => ['MacBook Air', 'ThinkPad X1'],
        'Accesorios' => [
            'Audio' => ['AirPods Pro', 'Sony WH-1000XM5'],
            'Carga' => ['Cargador USB-C', 'Power Bank 20000mAh'],
        ],
    ],
    'Hogar' => [
        'Cocina' => ['Cafetera espresso', 'Robot de cocina'],
        'Limpieza' => ['Aspirador robot'],
    ],
];

echo "Todos los productos (estructura aplanada):\n";
$numProducto = 0;
foreach (aplanar($categorias) as $producto) {
    $numProducto++;
    echo "  {$numProducto}. {$producto}\n";
}

// Aplanar solo un nivel
echo "\nAplanado solo 1 nivel:\n";
$datos = [[1, 2], [3, [4, 5]], [6]];
echo "  Original: [[1,2], [3,[4,5]], [6]]\n";
echo "  1 nivel: ";
$resultados = iterator_to_array(aplanar($datos, 1), false);
echo implode(', ', array_map(fn($v) => is_array($v) ? '[' . implode(',', $v) . ']' : $v, $resultados)) . "\n";

echo "  Todos los niveles: ";
$resultados = iterator_to_array(aplanar($datos), false);
echo implode(', ', $resultados) . "\n";

/**
 * Aplana un array asociativo anidado, preservando la ruta como clave.
 * Formato de clave: "padre.hijo.nieto"
 */
function aplanarAsociativo(iterable $datos, string $prefijo = ''): Generator {
    foreach ($datos as $clave => $valor) {
        $claveCompleta = $prefijo === '' ? $clave : "{$prefijo}.{$clave}";

        if (is_array($valor) && !empty($valor) && !array_is_list($valor)) {
            // Array asociativo: recursion con prefijo
            yield from aplanarAsociativo($valor, $claveCompleta);
        } else {
            yield $claveCompleta => $valor;
        }
    }
}

// Caso practico: configuracion anidada -> aplanada
$configAnidada = [
    'app' => [
        'nombre'  => 'MiApp',
        'version' => '2.5.1',
        'debug'   => false,
    ],
    'base_datos' => [
        'principal' => [
            'host'   => 'db.ejemplo.com',
            'puerto' => 5432,
            'nombre' => 'produccion',
        ],
        'replica' => [
            'host'   => 'db-replica.ejemplo.com',
            'puerto' => 5432,
        ],
    ],
    'cache' => [
        'driver' => 'redis',
        'ttl'    => 3600,
    ],
];

echo "\nConfiguracion aplanada (clave con notacion de punto):\n";
foreach (aplanarAsociativo($configAnidada) as $clave => $valor) {
    $valorStr = is_bool($valor) ? ($valor ? 'true' : 'false') : $valor;
    echo "  {$clave} = {$valorStr}\n";
}

// Caso practico: respuesta JSON de API con datos anidados
echo "\nRespuesta de API aplanada:\n";
$respuestaAPI = [
    'status' => 'success',
    'data' => [
        'usuario' => [
            'id' => 12345,
            'perfil' => [
                'nombre' => 'Elena Rodriguez',
                'email'  => 'elena@ejemplo.com',
            ],
            'preferencias' => [
                'idioma' => 'es',
                'zona_horaria' => 'Europe/Madrid',
            ],
        ],
        'sesion' => [
            'token' => 'abc123...',
            'expira' => '2025-05-27T00:00:00Z',
        ],
    ],
];

foreach (aplanarAsociativo($respuestaAPI) as $ruta => $valor) {
    $valorStr = is_bool($valor) ? ($valor ? 'true' : 'false') : $valor;
    echo "  {$ruta} => {$valorStr}\n";
}

// ============================================================
// Ejemplo 6: Componer pipelines con yield from
// ============================================================
// yield from permite crear pipelines de procesamiento donde
// cada etapa es un generador que delega al siguiente.

echo "\n=== Ejemplo 6: Pipelines de procesamiento ===\n\n";

/**
 * Genera datos de ventas simuladas.
 */
function datosVentas(): Generator {
    $productos = ['Laptop', 'Monitor', 'Teclado', 'Mouse', 'Webcam', 'Auriculares'];
    $regiones = ['Norte', 'Sur', 'Este', 'Oeste', 'Centro'];

    for ($i = 1; $i <= 20; $i++) {
        yield [
            'id'       => $i,
            'producto' => $productos[array_rand($productos)],
            'region'   => $regiones[array_rand($regiones)],
            'importe'  => round(rand(1000, 50000) / 100, 2),
            'fecha'    => date('Y-m-d', strtotime("-" . rand(0, 30) . " days")),
        ];
    }
}

/**
 * Filtra ventas que superan un importe minimo.
 */
function filtrarPorImporte(Generator $origen, float $minimo): Generator {
    foreach ($origen as $venta) {
        if ($venta['importe'] >= $minimo) {
            yield $venta;
        }
    }
}

/**
 * Enriquece cada venta con datos calculados.
 */
function enriquecerVenta(Generator $origen): Generator {
    foreach ($origen as $venta) {
        $venta['iva'] = round($venta['importe'] * 0.21, 2);
        $venta['total'] = round($venta['importe'] * 1.21, 2);
        $venta['trimestre'] = ceil((int)date('n', strtotime($venta['fecha'])) / 3);
        yield $venta;
    }
}

/**
 * Agrupa resultados por campo.
 * Nota: Esto consume todo el generador y agrupa en memoria.
 */
function agruparPor(Generator $origen, string $campo): array {
    $grupos = [];
    foreach ($origen as $item) {
        $clave = $item[$campo] ?? 'sin_grupo';
        $grupos[$clave][] = $item;
    }
    return $grupos;
}

// Construir el pipeline
$pipeline = enriquecerVenta(
    filtrarPorImporte(
        datosVentas(),
        100.00 // Solo ventas >= 100 EUR
    )
);

// Consumir y agrupar por region
$porRegion = agruparPor($pipeline, 'region');

echo "Ventas >= 100 EUR agrupadas por region:\n";
echo str_repeat("-", 55) . "\n";

foreach ($porRegion as $region => $ventas) {
    $totalRegion = array_sum(array_column($ventas, 'total'));
    echo "\nRegion {$region} ({$totalRegion} EUR total con IVA):\n";
    foreach ($ventas as $v) {
        echo sprintf("  #%02d %-12s %8.2f EUR -> %8.2f EUR (IVA incl.)\n",
            $v['id'], $v['producto'], $v['importe'], $v['total']
        );
    }
}

?>
