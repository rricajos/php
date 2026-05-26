<?php
// Ejemplo 1: Redondear al entero más cercano
echo "Ejemplo 1 (Redondear):\n";
echo "round(4.4): " . round(4.4) . "\n"; // 4
echo "round(4.5): " . round(4.5) . "\n"; // 5
echo "round(4.6): " . round(4.6) . "\n"; // 5

// Ejemplo 2: Redondear con precisión decimal
$pi = 3.14159265;
echo "\nEjemplo 2 (Precisión decimal):\n";
echo "round(π, 0): " . round($pi, 0) . "\n"; // 3
echo "round(π, 2): " . round($pi, 2) . "\n"; // 3.14
echo "round(π, 4): " . round($pi, 4) . "\n"; // 3.1416

// Ejemplo 3: Precisión negativa (redondear a decenas, centenas)
echo "\nEjemplo 3 (Precisión negativa):\n";
echo "round(1234, -1): " . round(1234, -1) . "\n"; // 1230
echo "round(1234, -2): " . round(1234, -2) . "\n"; // 1200
echo "round(1234, -3): " . round(1234, -3) . "\n"; // 1000

// Ejemplo 4: Modos de redondeo
echo "\nEjemplo 4 (Modos de redondeo para 2.5):\n";
echo "PHP_ROUND_HALF_UP:   " . round(2.5, 0, PHP_ROUND_HALF_UP) . "\n";   // 3
echo "PHP_ROUND_HALF_DOWN: " . round(2.5, 0, PHP_ROUND_HALF_DOWN) . "\n"; // 2
echo "PHP_ROUND_HALF_EVEN: " . round(2.5, 0, PHP_ROUND_HALF_EVEN) . "\n"; // 2
echo "PHP_ROUND_HALF_ODD:  " . round(2.5, 0, PHP_ROUND_HALF_ODD) . "\n";  // 3

// Ejemplo 5: Uso práctico - formatear precios
$precios = [9.995, 14.444, 29.999];
echo "\nEjemplo 5 (Precios redondeados a 2 decimales):\n";
foreach ($precios as $precio) {
    echo "  $precio -> " . round($precio, 2) . "\n";
}
?>
