<?php
// Ejemplo 1: Verificar si es un float
echo "Ejemplo 1 (Verificar floats):\n";
var_dump(is_float(3.14));    // true
var_dump(is_float(0.0));     // true
var_dump(is_float(42));      // false (es integer)
var_dump(is_float("3.14")); // false (es string)

// Ejemplo 2: is_float, is_double son lo mismo
echo "\nEjemplo 2 (Aliases):\n";
$num = 3.14;
echo "is_float: " . (is_float($num) ? "true" : "false") . "\n";
echo "is_double: " . (is_double($num) ? "true" : "false") . "\n";

// Ejemplo 3: Resultado de operaciones
echo "\nEjemplo 3 (Resultados de operaciones):\n";
echo "10 / 3 es " . gettype(10 / 3) . "\n";   // double
echo "10 / 2 es " . gettype(10 / 2) . "\n";   // integer
echo "10 / 5 es " . gettype(10 / 5) . "\n";   // integer

// Ejemplo 4: Valores especiales de float
echo "\nEjemplo 4 (Valores especiales):\n";
echo "INF: ";
var_dump(is_float(INF));   // true
echo "NAN: ";
var_dump(is_float(NAN));   // true
echo "is_infinite(INF): " . (is_infinite(INF) ? "true" : "false") . "\n";
echo "is_nan(NAN): " . (is_nan(NAN) ? "true" : "false") . "\n";
echo "is_finite(3.14): " . (is_finite(3.14) ? "true" : "false") . "\n";

// Ejemplo 5: Precisión de floats
echo "\nEjemplo 5 (Precisión):\n";
echo "0.1 + 0.2 == 0.3: " . ((0.1 + 0.2 == 0.3) ? "true" : "false") . "\n"; // false!
echo "0.1 + 0.2 = " . (0.1 + 0.2) . "\n";
echo "Usa abs() para comparar: " . (abs(0.1 + 0.2 - 0.3) < PHP_FLOAT_EPSILON ? "true" : "false") . "\n";
?>
