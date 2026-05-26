<?php
// Ejemplo 1: Obtener el timestamp actual
echo "Ejemplo 1 (Timestamp actual):\n";
echo "time(): " . time() . "\n";

// Ejemplo 2: Calcular timestamps futuros y pasados
$ahora = time();
$una_hora = $ahora + 3600;
$un_dia = $ahora + 86400;
$una_semana = $ahora + (7 * 86400);
echo "\nEjemplo 2 (Cálculos con timestamps):\n";
echo "Ahora: " . date('d/m/Y H:i', $ahora) . "\n";
echo "+1 hora: " . date('d/m/Y H:i', $una_hora) . "\n";
echo "+1 día: " . date('d/m/Y H:i', $un_dia) . "\n";
echo "+1 semana: " . date('d/m/Y H:i', $una_semana) . "\n";

// Ejemplo 3: Diferencia entre dos timestamps
$inicio = time();
$fin = $inicio + 3661; // 1 hora, 1 minuto, 1 segundo
$diff = $fin - $inicio;
echo "\nEjemplo 3 (Diferencia de timestamps):\n";
echo "Diferencia: $diff segundos\n";
echo "= " . intdiv($diff, 3600) . "h " . intdiv($diff % 3600, 60) . "m " . ($diff % 60) . "s\n";

// Ejemplo 4: Uso práctico - medir tiempo de ejecución
$inicio = microtime(true);
// Simular trabajo
$suma = 0;
for ($i = 0; $i < 100000; $i++) {
    $suma += $i;
}
$fin = microtime(true);
echo "\nEjemplo 4 (Medir tiempo de ejecución):\n";
echo "Tiempo: " . round(($fin - $inicio) * 1000, 2) . " ms\n";

// Ejemplo 5: Timestamp del inicio del día
$inicio_dia = strtotime('today');
$fin_dia = strtotime('tomorrow') - 1;
echo "\nEjemplo 5 (Timestamps del día):\n";
echo "Inicio del día: " . date('H:i:s', $inicio_dia) . "\n";
echo "Fin del día: " . date('H:i:s', $fin_dia) . "\n";
?>
