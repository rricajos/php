<?php
// Ejemplo 1: Verificar si es un string
echo "Ejemplo 1 (Verificar strings):\n";
var_dump(is_string("hola"));   // true
var_dump(is_string(""));       // true (string vacío)
var_dump(is_string("42"));     // true (número como string)
var_dump(is_string(42));       // false
var_dump(is_string(null));     // false

// Ejemplo 2: Objetos con __toString no son strings
$obj = new class { public function __toString() { return "objeto"; } };
echo "\nEjemplo 2 (Objeto con __toString):\n";
echo "is_string: " . (is_string($obj) ? "true" : "false") . "\n"; // false
echo "Pero se puede usar como string: $obj\n"; // "objeto"

// Ejemplo 3: Uso práctico - validar input
function saludar(mixed $nombre): string {
    if (!is_string($nombre)) {
        return "Error: se esperaba un string";
    }
    return "Hola, $nombre!";
}
echo "\nEjemplo 3 (Validar input):\n";
echo saludar("Ana") . "\n";
echo saludar(42) . "\n";

// Ejemplo 4: Verificar varios tipos de dato
$valores = ["texto", 42, 3.14, true, null, [], new stdClass()];
echo "\nEjemplo 4 (Verificar varios valores):\n";
foreach ($valores as $val) {
    $tipo = gettype($val);
    $esString = is_string($val) ? "Sí" : "No";
    echo "  $tipo -> is_string: $esString\n";
}
?>
