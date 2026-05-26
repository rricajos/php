<?php
// Ejemplo 1: Convertir string a entero
echo "Ejemplo 1 (String a entero):\n";
echo "intval('42'): " . intval('42') . "\n";     // 42
echo "intval('3.14'): " . intval('3.14') . "\n"; // 3
echo "intval('42abc'): " . intval('42abc') . "\n"; // 42
echo "intval('abc'): " . intval('abc') . "\n";   // 0

// Ejemplo 2: Conversión de bases
echo "\nEjemplo 2 (Conversión de bases):\n";
echo "intval('0xff', 16): " . intval('0xff', 16) . "\n";   // 255
echo "intval('1010', 2): " . intval('1010', 2) . "\n";     // 10
echo "intval('77', 8): " . intval('77', 8) . "\n";         // 63

// Ejemplo 3: Comparar con (int) cast
$valor = "42.99";
echo "\nEjemplo 3 (intval vs (int)):\n";
echo "intval: " . intval($valor) . "\n";   // 42
echo "(int): " . (int)$valor . "\n";       // 42

// Ejemplo 4: Otros tipos de casting
echo "\nEjemplo 4 (Familia de funciones *val):\n";
echo "intval('42.5'): " . intval('42.5') . "\n";       // 42
echo "floatval('42.5'): " . floatval('42.5') . "\n";   // 42.5
echo "boolval('42.5'): " . (boolval('42.5') ? "true" : "false") . "\n"; // true
echo "strval(42): " . strval(42) . "\n";               // "42"

// Ejemplo 5: Con booleanos
echo "\nEjemplo 5 (Booleanos):\n";
echo "intval(true): " . intval(true) . "\n";   // 1
echo "intval(false): " . intval(false) . "\n"; // 0
?>
