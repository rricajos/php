<?php
// Ejemplo 1: Verificar si es numérico
echo "Ejemplo 1 (Verificar valores numéricos):\n";
var_dump(is_numeric(42));       // true
var_dump(is_numeric(3.14));     // true
var_dump(is_numeric("42"));     // true
var_dump(is_numeric("3.14"));   // true
var_dump(is_numeric("1e5"));    // true (notación científica)
var_dump(is_numeric("hola"));   // false

// Ejemplo 2: Casos especiales
echo "\nEjemplo 2 (Casos especiales):\n";
var_dump(is_numeric("0x1A"));   // false (hex string no es numérico desde PHP 7)
var_dump(is_numeric("+42"));    // true
var_dump(is_numeric("-3.14"));  // true
var_dump(is_numeric("  42  ")); // false (espacios)
var_dump(is_numeric(""));       // false

// Ejemplo 3: is_numeric vs is_int vs ctype_digit
$valores = [42, "42", "3.14", "1e5", "-5", "abc"];
echo "\nEjemplo 3 (Comparar funciones):\n";
echo str_pad("Valor", 10) . str_pad("is_numeric", 12) . str_pad("is_int", 8) . "ctype_digit\n";
foreach ($valores as $val) {
    $display = str_pad(var_export($val, true), 10);
    $numeric = str_pad(is_numeric($val) ? "Sí" : "No", 12);
    $int = str_pad(is_int($val) ? "Sí" : "No", 8);
    $digit = is_string($val) && ctype_digit($val) ? "Sí" : "No";
    echo "$display$numeric$int$digit\n";
}

// Ejemplo 4: Uso práctico - validar input
$edad_input = "25";
echo "\nEjemplo 4 (Validar input):\n";
if (is_numeric($edad_input)) {
    $edad = (int)$edad_input;
    echo "Edad válida: $edad\n";
} else {
    echo "Edad no válida\n";
}
?>
