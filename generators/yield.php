<?php
/**
 * ============================================================
 * Generadores y yield en PHP - Iteracion perezosa y eficiente
 * ============================================================
 * Un generador es una funcion que usa yield para producir
 * valores uno a uno, pausandose y reanudandose entre cada uno.
 * Esto permite trabajar con secuencias enormes (o infinitas)
 * sin cargar todo en memoria al mismo tiempo.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: Generador basico con yield
// ============================================================
// Una funcion generadora se parece a una funcion normal, pero
// en vez de return usa yield. Al llamarla, devuelve un objeto
// Generator que implementa Iterator.

echo "=== Ejemplo 1: Generador basico ===\n\n";

/**
 * Genera una secuencia de numeros desde $inicio hasta $fin.
 * Cada llamada a yield pausa la funcion y entrega un valor.
 */
function rango(int $inicio, int $fin, int $paso = 1): Generator {
    if ($paso <= 0) {
        throw new InvalidArgumentException("El paso debe ser positivo");
    }

    for ($i = $inicio; $i <= $fin; $i += $paso) {
        echo "  [generando {$i}...]\n"; // Para ver cuando se ejecuta
        yield $i;
    }
}

// El generador no ejecuta nada hasta que se empieza a iterar
$numeros = rango(1, 5);
echo "Generador creado: " . get_class($numeros) . "\n";
echo "Todavia no se ha ejecutado nada del cuerpo de la funcion.\n\n";

// Al iterar con foreach, se ejecuta bajo demanda
echo "Iterando con foreach:\n";
foreach ($numeros as $valor) {
    echo "  -> Recibido: {$valor}\n";
}

// Tambien se puede avanzar manualmente con el protocolo Iterator
echo "\nIteracion manual con current/next/valid:\n";
$gen = rango(10, 12);
$gen->rewind(); // Iniciar (opcional en foreach, obligatorio manual)
while ($gen->valid()) {
    echo "  Valor actual: {$gen->current()}\n";
    $gen->next();
}

// Con paso personalizado
echo "\nNumeros del 0 al 20, de 5 en 5:\n";
foreach (rango(0, 20, 5) as $n) {
    echo "  {$n}";
}
echo "\n";

// ============================================================
// Ejemplo 2: Generadores vs arrays - Comparacion de memoria
// ============================================================
// La diferencia clave: un array almacena TODOS los valores;
// un generador produce uno a la vez y descarta el anterior.

echo "\n=== Ejemplo 2: Generadores vs arrays (memoria) ===\n\n";

/**
 * Devuelve un array con $n numeros (consume mucha memoria).
 */
function rangoArray(int $n): array {
    $resultado = [];
    for ($i = 1; $i <= $n; $i++) {
        $resultado[] = $i;
    }
    return $resultado;
}

/**
 * Devuelve un generador con $n numeros (memoria constante).
 */
function rangoGenerador(int $n): Generator {
    for ($i = 1; $i <= $n; $i++) {
        yield $i;
    }
}

// Comparar con 100,000 elementos
$cantidad = 100000;

// Medicion con array
$memAntes = memory_get_usage();
$array = rangoArray($cantidad);
$memDespuesArray = memory_get_usage();
$usoArray = $memDespuesArray - $memAntes;

// Consumir el array para asegurar la comparacion justa
$sumaArray = 0;
foreach ($array as $v) {
    $sumaArray += $v;
}
unset($array); // Liberar

// Medicion con generador
$memAntes2 = memory_get_usage();
$generador = rangoGenerador($cantidad);
$memDespuesGen = memory_get_usage();
$usoGenerador = $memDespuesGen - $memAntes2;

// Consumir el generador
$sumaGen = 0;
foreach ($generador as $v) {
    $sumaGen += $v;
}

echo "Comparacion para {$cantidad} elementos:\n";
echo str_repeat("-", 45) . "\n";
echo sprintf("  %-20s %15s\n", "Enfoque", "Memoria usada");
echo str_repeat("-", 45) . "\n";
echo sprintf("  %-20s %12s KB\n", "Array completo", number_format($usoArray / 1024, 1));
echo sprintf("  %-20s %12s KB\n", "Generador", number_format($usoGenerador / 1024, 1));
echo str_repeat("-", 45) . "\n";

$factor = ($usoGenerador > 0) ? round($usoArray / $usoGenerador) : 'infinito';
echo "  Factor de ahorro: ~{$factor}x menos memoria con generador\n";
echo "  Resultados identicos: " . ($sumaArray === $sumaGen ? "SI" : "NO") . "\n";

// Nota: el generador usa memoria ~constante sin importar el tamano
// El array crece linealmente con el numero de elementos

// ============================================================
// Ejemplo 3: yield con clave => valor
// ============================================================
// Se puede usar yield $clave => $valor para generar pares
// clave-valor, igual que un array asociativo.

echo "\n=== Ejemplo 3: yield clave => valor ===\n\n";

/**
 * Genera pares clave-valor a partir de una cadena de configuracion.
 * Formato: "clave1=valor1;clave2=valor2;..."
 */
function parsearConfiguracion(string $cadena): Generator {
    $pares = explode(';', $cadena);
    foreach ($pares as $par) {
        $par = trim($par);
        if (empty($par)) continue;

        if (str_contains($par, '=')) {
            [$clave, $valor] = explode('=', $par, 2);
            yield trim($clave) => trim($valor);
        } else {
            // Clave sin valor: usar true como valor por defecto
            yield trim($par) => true;
        }
    }
}

$config = "host=localhost; port=3306; db=mi_app; debug; ssl=true; charset=utf8mb4";

echo "Configuracion parseada:\n";
foreach (parsearConfiguracion($config) as $clave => $valor) {
    $valorStr = is_bool($valor) ? 'true (flag)' : $valor;
    echo "  {$clave} => {$valorStr}\n";
}

/**
 * Genera los caracteres de una cadena con sus posiciones como claves.
 * Util para procesamiento de texto caracter por caracter.
 */
function caracteres(string $texto): Generator {
    $longitud = mb_strlen($texto);
    for ($i = 0; $i < $longitud; $i++) {
        yield $i => mb_substr($texto, $i, 1);
    }
}

echo "\nCaracteres con posicion:\n";
foreach (caracteres("Hola!") as $pos => $char) {
    echo "  [{$pos}] => '{$char}'\n";
}

/**
 * Genera meses del anio con numero como clave y nombre como valor.
 */
function mesesDelAnio(string $idioma = 'es'): Generator {
    $meses = [
        'es' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    ];

    $lista = $meses[$idioma] ?? $meses['es'];
    foreach ($lista as $indice => $nombre) {
        yield ($indice + 1) => $nombre;
    }
}

echo "\nMeses (clave numerica => nombre):\n";
foreach (mesesDelAnio() as $num => $nombre) {
    echo "  {$num} -> {$nombre}\n";
}

// Convertir generador a array (con claves preservadas)
$mesesArray = iterator_to_array(mesesDelAnio());
echo "\nConvertido a array: " . count($mesesArray) . " elementos\n";
echo "Mes 5: {$mesesArray[5]}\n";

// ============================================================
// Ejemplo 4: Valor de retorno con getReturn()
// ============================================================
// Ademas de yield, un generador puede terminar con return
// para entregar un valor final accesible con getReturn().

echo "\n=== Ejemplo 4: getReturn() - valor final del generador ===\n\n";

/**
 * Procesa una lista de ventas y genera cada venta individual.
 * Al terminar, retorna un resumen con totales.
 */
function procesarVentas(array $ventas): Generator {
    $totalImporte = 0;
    $contador = 0;
    $maxVenta = 0;
    $mejorCliente = '';

    foreach ($ventas as $venta) {
        $totalImporte += $venta['importe'];
        $contador++;

        if ($venta['importe'] > $maxVenta) {
            $maxVenta = $venta['importe'];
            $mejorCliente = $venta['cliente'];
        }

        // yield entrega cada venta individual al consumidor
        yield $venta;
    }

    // return entrega un resumen al final (accesible con getReturn)
    return [
        'total_ventas'  => $contador,
        'importe_total' => $totalImporte,
        'ticket_medio'  => $contador > 0 ? round($totalImporte / $contador, 2) : 0,
        'venta_maxima'  => $maxVenta,
        'mejor_cliente' => $mejorCliente,
    ];
}

$ventas = [
    ['cliente' => 'Ana Garcia',    'producto' => 'Portatil',    'importe' => 899.99],
    ['cliente' => 'Pedro Ruiz',    'producto' => 'Tablet',      'importe' => 449.50],
    ['cliente' => 'Laura Mendez',  'producto' => 'Servidor',    'importe' => 2500.00],
    ['cliente' => 'Carlos Diaz',   'producto' => 'Monitor',     'importe' => 350.00],
    ['cliente' => 'Sofia Martin',  'producto' => 'Impresora',   'importe' => 189.90],
];

$procesador = procesarVentas($ventas);

echo "Procesando ventas individualmente:\n";
foreach ($procesador as $venta) {
    echo "  {$venta['cliente']}: {$venta['producto']} -> {$venta['importe']} EUR\n";
}

// IMPORTANTE: getReturn() solo funciona DESPUES de consumir todo el generador
$resumen = $procesador->getReturn();
echo "\nResumen final (via getReturn):\n";
echo "  Total ventas: {$resumen['total_ventas']}\n";
echo "  Importe total: {$resumen['importe_total']} EUR\n";
echo "  Ticket medio: {$resumen['ticket_medio']} EUR\n";
echo "  Mayor venta: {$resumen['venta_maxima']} EUR ({$resumen['mejor_cliente']})\n";

// Caso borde: intentar getReturn() sin consumir todo el generador
$genIncompleto = procesarVentas($ventas);
$genIncompleto->current(); // Solo leemos el primero
try {
    $genIncompleto->getReturn(); // Esto lanzara excepcion
} catch (\Exception $e) {
    echo "\nError esperado al llamar getReturn() sin consumir todo:\n";
    echo "  " . get_class($e) . ": " . $e->getMessage() . "\n";
}

// Generador sin return explicito: getReturn() devuelve null
function generadorSinReturn(): Generator {
    yield 1;
    yield 2;
}

$gsr = generadorSinReturn();
foreach ($gsr as $_) {} // Consumir
echo "\ngetReturn() sin return explicito: " . var_export($gsr->getReturn(), true) . "\n";

// ============================================================
// Ejemplo 5: Lectura eficiente de archivo grande (practico)
// ============================================================
// Caso real: leer un archivo CSV de millones de lineas sin
// cargar todo en memoria. El generador entrega linea por linea.

echo "\n=== Ejemplo 5: Leer archivo grande linea por linea (practico) ===\n\n";

/**
 * Lee un archivo linea por linea usando un generador.
 * Memoria constante sin importar el tamano del archivo.
 *
 * @param string $rutaArchivo Ruta al archivo.
 * @param bool $omitirVacias No entregar lineas vacias.
 * @return Generator Cada iteracion entrega una linea del archivo.
 */
function leerLineas(string $rutaArchivo, bool $omitirVacias = true): Generator {
    $handle = fopen($rutaArchivo, 'r');
    if ($handle === false) {
        throw new RuntimeException("No se pudo abrir: {$rutaArchivo}");
    }

    $numLinea = 0;
    try {
        while (($linea = fgets($handle)) !== false) {
            $numLinea++;
            $linea = rtrim($linea, "\r\n");

            if ($omitirVacias && trim($linea) === '') {
                continue;
            }

            yield $numLinea => $linea;
        }
    } finally {
        // finally garantiza que el archivo se cierra aunque se rompa el foreach
        fclose($handle);
    }

    return $numLinea; // Total de lineas leidas
}

/**
 * Parsea un CSV usando generadores. Entrega cada fila como array asociativo.
 *
 * @param string $ruta Ruta al archivo CSV.
 * @param string $delimitador Separador de campos.
 * @return Generator Cada fila como array [columna => valor].
 */
function leerCSV(string $ruta, string $delimitador = ','): Generator {
    $encabezados = null;
    $filasValidas = 0;
    $filasError = 0;

    foreach (leerLineas($ruta) as $numLinea => $linea) {
        $campos = str_getcsv($linea, $delimitador);

        if ($encabezados === null) {
            // Primera linea: usar como nombres de columnas
            $encabezados = array_map('trim', $campos);
            continue;
        }

        // Validar que la fila tenga el numero correcto de columnas
        if (count($campos) !== count($encabezados)) {
            $filasError++;
            continue; // Saltar filas malformadas
        }

        $filasValidas++;
        yield array_combine($encabezados, $campos);
    }

    return [
        'filas_validas' => $filasValidas,
        'filas_error'   => $filasError,
        'columnas'      => $encabezados,
    ];
}

// Crear un archivo CSV de ejemplo para demostrar
$archivoCSV = tempnam(sys_get_temp_dir(), 'csv_');
$contenidoCSV = "id,nombre,email,departamento,salario\n";
$departamentos = ['Ingenieria', 'Marketing', 'Ventas', 'RRHH', 'Finanzas'];
$nombres = ['Garcia', 'Martinez', 'Lopez', 'Sanchez', 'Fernandez', 'Gonzalez',
            'Rodriguez', 'Perez', 'Gomez', 'Martin', 'Ruiz', 'Hernandez'];

for ($i = 1; $i <= 200; $i++) {
    $nombre = $nombres[array_rand($nombres)];
    $depto = $departamentos[array_rand($departamentos)];
    $salario = rand(25000, 75000);
    $contenidoCSV .= "{$i},{$nombre} {$i},{$nombre}{$i}@empresa.com,{$depto},{$salario}\n";
}
// Agregar una linea malformada para probar validacion
$contenidoCSV .= "esta,linea,tiene,columnas,extra,invalida\n";
file_put_contents($archivoCSV, $contenidoCSV);

echo "Archivo CSV creado con " . filesize($archivoCSV) . " bytes\n\n";

// Leer y procesar con el generador
$lector = leerCSV($archivoCSV);

// Solo mostrar los primeros 5 registros
echo "Primeros 5 empleados:\n";
echo str_repeat("-", 65) . "\n";
$contador = 0;
$salariosPorDepto = [];

foreach ($lector as $fila) {
    $contador++;

    // Acumular estadisticas mientras leemos
    $depto = $fila['departamento'];
    if (!isset($salariosPorDepto[$depto])) {
        $salariosPorDepto[$depto] = ['total' => 0, 'count' => 0];
    }
    $salariosPorDepto[$depto]['total'] += (int)$fila['salario'];
    $salariosPorDepto[$depto]['count']++;

    // Solo mostrar los primeros 5
    if ($contador <= 5) {
        echo sprintf("  [%03d] %-15s %-30s %s\n",
            $fila['id'], $fila['nombre'], $fila['email'], $fila['departamento']
        );
    }
}

// Obtener resumen del procesamiento
$info = $lector->getReturn();
echo "\n... y {$contador} registros mas procesados.\n";
echo "Resumen del procesamiento:\n";
echo "  Filas validas: {$info['filas_validas']}\n";
echo "  Filas con error: {$info['filas_error']}\n";
echo "  Columnas: " . implode(', ', $info['columnas']) . "\n";

// Estadisticas calculadas en una sola pasada
echo "\nSalario promedio por departamento:\n";
arsort($salariosPorDepto);
foreach ($salariosPorDepto as $depto => $datos) {
    $promedio = round($datos['total'] / $datos['count']);
    echo "  {$depto}: " . number_format($promedio) . " EUR ({$datos['count']} empleados)\n";
}

// Limpiar
unlink($archivoCSV);

echo "\n// Nota: Este patron permite procesar archivos CSV de varios GB\n";
echo "// usando solo unos pocos KB de memoria (una fila a la vez).\n";
echo "// Comparado con file() o file_get_contents(), es la unica\n";
echo "// opcion viable para archivos que no caben en memoria.\n";

?>
