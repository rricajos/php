<?php
// Ejemplo 1: Convertir diferentes valores a boolean
echo "Ejemplo 1 (Valores que son false):\n";
$falsy = [false, 0, 0.0, "", "0", null, []];
foreach ($falsy as $val) {
    echo "  boolval(" . var_export($val, true) . "): " . (boolval($val) ? "true" : "false") . "\n";
}

// Ejemplo 2: Valores que son true
echo "\nEjemplo 2 (Valores que son true):\n";
$truthy = [true, 1, -1, 3.14, "hola", "0.0", " ", [0], new stdClass()];
foreach ($truthy as $val) {
    $display = is_object($val) ? "stdClass" : var_export($val, true);
    echo "  boolval($display): " . (boolval($val) ? "true" : "false") . "\n";
}

// Ejemplo 3: boolval vs (bool) cast
$valor = "hola";
echo "\nEjemplo 3 (boolval vs cast):\n";
echo "boolval: " . (boolval($valor) ? "true" : "false") . "\n";
echo "(bool): " . ((bool)$valor ? "true" : "false") . "\n";

// Ejemplo 4: Uso práctico - normalizar flags de configuración
$config = [
    "debug" => "1",
    "cache" => "0",
    "verbose" => "",
    "log" => "true"
];
echo "\nEjemplo 4 (Normalizar flags):\n";
foreach ($config as $key => $val) {
    echo "  $key: " . (boolval($val) ? "activado" : "desactivado") . "\n";
}

// Ejemplo 5: Familia completa de funciones *val
echo "\nEjemplo 5 (Familia *val):\n";
$input = "42.5";
echo "intval('$input'): " . intval($input) . "\n";
echo "floatval('$input'): " . floatval($input) . "\n";
echo "boolval('$input'): " . (boolval($input) ? "true" : "false") . "\n";
echo "strval('$input'): '" . strval($input) . "'\n";
?>
