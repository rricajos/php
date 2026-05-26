<?php
// Ejemplo 1: Verificar si es un array
echo "Ejemplo 1 (Verificar arrays):\n";
var_dump(is_array([1, 2, 3]));    // true
var_dump(is_array(["a" => 1]));   // true
var_dump(is_array("texto"));       // false
var_dump(is_array(42));            // false

// Ejemplo 2: Array vacío sigue siendo un array
echo "\nEjemplo 2 (Array vacío):\n";
var_dump(is_array([])); // true

// Ejemplo 3: Uso práctico - validar parámetro
function sumarTodos($datos): int|float {
    if (!is_array($datos)) {
        echo "  Error: Se esperaba un array\n";
        return 0;
    }
    return array_sum($datos);
}
echo "\nEjemplo 3 (Validar parámetro):\n";
echo "  sumarTodos([1,2,3]): " . sumarTodos([1, 2, 3]) . "\n";
echo "  sumarTodos('hola'): " . sumarTodos("hola") . "\n";

// Ejemplo 4: Diferencia entre array y objeto iterable
$array = [1, 2, 3];
$objeto = new ArrayObject([1, 2, 3]);
echo "\nEjemplo 4 (Array vs ArrayObject):\n";
echo "is_array(\$array): " . (is_array($array) ? "true" : "false") . "\n";       // true
echo "is_array(\$objeto): " . (is_array($objeto) ? "true" : "false") . "\n";     // false
echo "is_iterable(\$objeto): " . (is_iterable($objeto) ? "true" : "false") . "\n"; // true
?>
