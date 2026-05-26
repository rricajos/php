<?php
// Ejemplo 1: Potencia básica
echo "Ejemplo 1 (Potencias):\n";
echo "pow(2, 3): " . pow(2, 3) . "\n";   // 8 (2³)
echo "pow(5, 2): " . pow(5, 2) . "\n";   // 25 (5²)
echo "pow(10, 0): " . pow(10, 0) . "\n"; // 1 (cualquier número^0 = 1)

// Ejemplo 2: Operador ** (alternativa desde PHP 5.6)
echo "\nEjemplo 2 (Operador **):\n";
echo "2 ** 3: " . (2 ** 3) . "\n";   // 8
echo "5 ** 2: " . (5 ** 2) . "\n";   // 25

// Ejemplo 3: Exponente negativo
echo "\nEjemplo 3 (Exponente negativo):\n";
echo "pow(2, -1): " . pow(2, -1) . "\n"; // 0.5
echo "pow(2, -2): " . pow(2, -2) . "\n"; // 0.25

// Ejemplo 4: Raíz cuadrada como potencia
echo "\nEjemplo 4 (Raíz como potencia):\n";
echo "pow(16, 0.5): " . pow(16, 0.5) . "\n"; // 4 (raíz cuadrada)
echo "pow(27, 1/3): " . pow(27, 1/3) . "\n"; // 3 (raíz cúbica)

// Ejemplo 5: Uso práctico - conversión de unidades de almacenamiento
echo "\nEjemplo 5 (Unidades de almacenamiento):\n";
echo "1 KB = " . pow(2, 10) . " bytes\n";   // 1024
echo "1 MB = " . pow(2, 20) . " bytes\n";   // 1048576
echo "1 GB = " . pow(2, 30) . " bytes\n";   // 1073741824
?>
