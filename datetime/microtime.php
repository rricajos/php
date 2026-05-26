<?php
// Ejemplo 1: microtime como string
echo "Ejemplo 1 (Como string):\n";
echo "microtime(): " . microtime() . "\n"; // "0.12345678 1234567890"

// Ejemplo 2: microtime como float
echo "\nEjemplo 2 (Como float):\n";
echo "microtime(true): " . microtime(true) . "\n";

// Ejemplo 3: Uso principal - medir tiempo de ejecución con precisión
$inicio = microtime(true);
// Simular trabajo
$resultado = 0;
for ($i = 0; $i < 1000000; $i++) {
    $resultado += $i;
}
$fin = microtime(true);
$tiempo = ($fin - $inicio) * 1000; // Convertir a milisegundos
echo "\nEjemplo 3 (Medir ejecución):\n";
echo "Tiempo: " . round($tiempo, 4) . " ms\n";

// Ejemplo 4: Comparar rendimiento de dos métodos
$array = range(1, 10000);

$inicio1 = microtime(true);
$suma1 = array_sum($array);
$tiempo1 = microtime(true) - $inicio1;

$inicio2 = microtime(true);
$suma2 = 0;
foreach ($array as $val) {
    $suma2 += $val;
}
$tiempo2 = microtime(true) - $inicio2;

echo "\nEjemplo 4 (Benchmark):\n";
echo "array_sum: " . round($tiempo1 * 1000000, 2) . " μs\n";
echo "foreach:   " . round($tiempo2 * 1000000, 2) . " μs\n";

// Ejemplo 5: Generar ID único basado en microsegundos
echo "\nEjemplo 5 (ID basado en microsegundos):\n";
$id = str_replace(".", "", microtime(true));
echo "ID: $id\n";
?>
