<?php
// Ejemplo 1: Convertir de decimal a hexadecimal
echo "Ejemplo 1 (Decimal a hexadecimal):\n";
echo "base_convert('255', 10, 16): " . base_convert('255', 10, 16) . "\n"; // ff

// Ejemplo 2: Hexadecimal a decimal
echo "\nEjemplo 2 (Hexadecimal a decimal):\n";
echo "base_convert('ff', 16, 10): " . base_convert('ff', 16, 10) . "\n"; // 255

// Ejemplo 3: Binario a decimal
echo "\nEjemplo 3 (Binario a decimal):\n";
echo "base_convert('1010', 2, 10): " . base_convert('1010', 2, 10) . "\n"; // 10

// Ejemplo 4: Decimal a binario
echo "\nEjemplo 4 (Decimal a binario):\n";
echo "base_convert('42', 10, 2): " . base_convert('42', 10, 2) . "\n"; // 101010

// Ejemplo 5: Octal a hexadecimal
echo "\nEjemplo 5 (Octal a hexadecimal):\n";
echo "base_convert('377', 8, 16): " . base_convert('377', 8, 16) . "\n"; // ff

// Ejemplo 6: Funciones especializadas equivalentes
echo "\nEjemplo 6 (Funciones especializadas):\n";
echo "hexdec('ff'): " . hexdec('ff') . "\n";     // 255
echo "dechex(255): " . dechex(255) . "\n";        // ff
echo "bindec('1010'): " . bindec('1010') . "\n";  // 10
echo "decbin(10): " . decbin(10) . "\n";          // 1010
echo "octdec('377'): " . octdec('377') . "\n";    // 255
echo "decoct(255): " . decoct(255) . "\n";        // 377

// Ejemplo 7: Soporta bases de 2 a 36
echo "\nEjemplo 7 (Base 36 - usa 0-9 y a-z):\n";
echo "base_convert('100', 10, 36): " . base_convert('100', 10, 36) . "\n"; // 2s
echo "base_convert('zz', 36, 10): " . base_convert('zz', 36, 10) . "\n";  // 1295
?>
