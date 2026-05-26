<?php
// Ejemplo 1: Logaritmo natural (base e)
echo "Ejemplo 1 (Logaritmo natural):\n";
echo "log(1): " . log(1) . "\n";       // 0
echo "log(M_E): " . log(M_E) . "\n";   // 1
echo "log(10): " . log(10) . "\n";     // 2.302585...

// Ejemplo 2: Logaritmo con base personalizada
echo "\nEjemplo 2 (Base personalizada):\n";
echo "log(8, 2): " . log(8, 2) . "\n";     // 3 (2³ = 8)
echo "log(100, 10): " . log(100, 10) . "\n"; // 2 (10² = 100)
echo "log(27, 3): " . log(27, 3) . "\n";   // 3 (3³ = 27)

// Ejemplo 3: log10() - logaritmo en base 10
echo "\nEjemplo 3 (log10):\n";
echo "log10(1): " . log10(1) . "\n";       // 0
echo "log10(10): " . log10(10) . "\n";     // 1
echo "log10(100): " . log10(100) . "\n";   // 2
echo "log10(1000): " . log10(1000) . "\n"; // 3

// Ejemplo 4: log2() - logaritmo en base 2
echo "\nEjemplo 4 (log2):\n";
echo "log2(1): " . log2(1) . "\n";   // 0
echo "log2(2): " . log2(2) . "\n";   // 1
echo "log2(8): " . log2(8) . "\n";   // 3
echo "log2(256): " . log2(256) . "\n"; // 8

// Ejemplo 5: Uso práctico - calcular dígitos de un número
$numero = 123456;
$digitos = floor(log10($numero)) + 1;
echo "\nEjemplo 5 (Número de dígitos de $numero):\n";
echo "Dígitos: $digitos\n"; // 6
?>
