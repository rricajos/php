<?php
// Ejemplo 1: Parsear fechas en texto
echo "Ejemplo 1 (Parsear fechas):\n";
echo "strtotime('2026-06-15'): " . date('d/m/Y', strtotime('2026-06-15')) . "\n";
echo "strtotime('15 June 2026'): " . date('d/m/Y', strtotime('15 June 2026')) . "\n";
echo "strtotime('June 15, 2026'): " . date('d/m/Y', strtotime('June 15, 2026')) . "\n";

// Ejemplo 2: Expresiones relativas
echo "\nEjemplo 2 (Expresiones relativas):\n";
echo "now: " . date('d/m/Y H:i', strtotime('now')) . "\n";
echo "today: " . date('d/m/Y H:i', strtotime('today')) . "\n";
echo "tomorrow: " . date('d/m/Y', strtotime('tomorrow')) . "\n";
echo "yesterday: " . date('d/m/Y', strtotime('yesterday')) . "\n";

// Ejemplo 3: Sumar y restar tiempo
echo "\nEjemplo 3 (Sumar/restar tiempo):\n";
echo "+1 day: " . date('d/m/Y', strtotime('+1 day')) . "\n";
echo "+2 weeks: " . date('d/m/Y', strtotime('+2 weeks')) . "\n";
echo "+3 months: " . date('d/m/Y', strtotime('+3 months')) . "\n";
echo "+1 year: " . date('d/m/Y', strtotime('+1 year')) . "\n";
echo "-1 week: " . date('d/m/Y', strtotime('-1 week')) . "\n";

// Ejemplo 4: Expresiones de día de la semana
echo "\nEjemplo 4 (Días de la semana):\n";
echo "next Monday: " . date('d/m/Y', strtotime('next Monday')) . "\n";
echo "last Friday: " . date('d/m/Y', strtotime('last Friday')) . "\n";
echo "next Sunday: " . date('d/m/Y', strtotime('next Sunday')) . "\n";

// Ejemplo 5: Combinaciones
echo "\nEjemplo 5 (Combinaciones):\n";
echo "+1 month +2 days: " . date('d/m/Y', strtotime('+1 month +2 days')) . "\n";
echo "first day of next month: " . date('d/m/Y', strtotime('first day of next month')) . "\n";
echo "last day of this month: " . date('d/m/Y', strtotime('last day of this month')) . "\n";

// Ejemplo 6: Uso práctico - calcular edad
$nacimiento = '1995-08-15';
$edad = floor((time() - strtotime($nacimiento)) / (365.25 * 86400));
echo "\nEjemplo 6 (Calcular edad):\n";
echo "Nacimiento: $nacimiento\n";
echo "Edad: $edad años\n";

// Ejemplo 7: strtotime devuelve false si no puede parsear
echo "\nEjemplo 7 (Fecha inválida):\n";
$resultado = strtotime('no es una fecha');
var_dump($resultado); // false
?>
