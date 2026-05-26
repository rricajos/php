<?php
// Ejemplo 1: Verificar si es un entero
echo "Ejemplo 1 (Verificar enteros):\n";
var_dump(is_int(42));      // true
var_dump(is_int(0));       // true
var_dump(is_int(-10));     // true
var_dump(is_int(3.14));    // false
var_dump(is_int("42"));    // false

// Ejemplo 2: is_int, is_integer y is_long son lo mismo
echo "\nEjemplo 2 (Aliases):\n";
$num = 42;
echo "is_int: " . (is_int($num) ? "true" : "false") . "\n";
echo "is_integer: " . (is_integer($num) ? "true" : "false") . "\n";
echo "is_long: " . (is_long($num) ? "true" : "false") . "\n";

// Ejemplo 3: Cuidado con los límites
echo "\nEjemplo 3 (Límites de entero):\n";
echo "PHP_INT_MAX: " . PHP_INT_MAX . "\n";
echo "PHP_INT_MIN: " . PHP_INT_MIN . "\n";
$grande = PHP_INT_MAX + 1;
echo "PHP_INT_MAX + 1 es " . gettype($grande) . "\n"; // double (overflow)

// Ejemplo 4: is_numeric vs is_int
echo "\nEjemplo 4 (is_int vs is_numeric):\n";
$valores = [42, "42", 3.14, "3.14", "0x1A", "1e5"];
foreach ($valores as $val) {
    $esInt = is_int($val) ? "true" : "false";
    $esNumeric = is_numeric($val) ? "true" : "false";
    echo "  " . var_export($val, true) . " -> is_int: $esInt, is_numeric: $esNumeric\n";
}
?>
