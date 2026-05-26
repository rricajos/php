<?php
// Ejemplo 1: Valor absoluto de números negativos
echo "Ejemplo 1 (Valor absoluto):\n";
echo "abs(-5): " . abs(-5) . "\n";     // 5
echo "abs(5): " . abs(5) . "\n";       // 5
echo "abs(0): " . abs(0) . "\n";       // 0

// Ejemplo 2: Con floats
echo "\nEjemplo 2 (Floats):\n";
echo "abs(-3.14): " . abs(-3.14) . "\n"; // 3.14

// Ejemplo 3: Uso práctico - calcular distancia entre dos puntos
$punto1 = 10;
$punto2 = 3;
$distancia = abs($punto1 - $punto2);
echo "\nEjemplo 3 (Distancia entre $punto1 y $punto2):\n";
echo "Distancia: $distancia\n";

// Ejemplo 4: Uso práctico - calcular diferencia de temperaturas
$temp_max = 35;
$temp_min = -5;
echo "\nEjemplo 4 (Diferencia de temperatura):\n";
echo "Diferencia: " . abs($temp_max - $temp_min) . "°C\n";
?>
