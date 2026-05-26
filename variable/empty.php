<?php
// Ejemplo 1: Valores que empty considera "vacíos"
echo "Ejemplo 1 (Valores 'vacíos'):\n";
$vacios = ["", 0, 0.0, "0", null, false, []];
foreach ($vacios as $val) {
    echo "  empty(" . var_export($val, true) . "): " . (empty($val) ? "true" : "false") . "\n";
}

// Ejemplo 2: Valores que NO son vacíos
echo "\nEjemplo 2 (Valores NO vacíos):\n";
$no_vacios = [1, "0.0", " ", "false", [0], new stdClass()];
foreach ($no_vacios as $val) {
    echo "  empty(" . var_export($val, true) . "): " . (empty($val) ? "true" : "false") . "\n";
}

// Ejemplo 3: empty con variables no definidas (no genera warning)
echo "\nEjemplo 3 (Variable no definida):\n";
echo "empty(\$no_existe): " . (empty($no_existe) ? "true" : "false") . "\n"; // true, sin warning

// Ejemplo 4: empty con arrays
$datos = ["nombre" => "Ana", "email" => "", "tel" => null];
echo "\nEjemplo 4 (Validar campos de formulario):\n";
foreach ($datos as $campo => $valor) {
    if (empty($valor)) {
        echo "  '$campo' está vacío\n";
    } else {
        echo "  '$campo': $valor\n";
    }
}

// Ejemplo 5: Comparar isset, empty, is_null
echo "\nEjemplo 5 (Tabla comparativa):\n";
echo str_pad("Valor", 12) . str_pad("isset", 8) . str_pad("empty", 8) . "is_null\n";
$tests = [["var" => "", "label" => '""'], ["var" => 0, "label" => "0"], ["var" => null, "label" => "null"], ["var" => false, "label" => "false"], ["var" => "hola", "label" => '"hola"']];
foreach ($tests as $t) {
    $v = $t["var"];
    echo str_pad($t["label"], 12) . str_pad(isset($v) ? "true" : "false", 8) . str_pad(empty($v) ? "true" : "false", 8) . (is_null($v) ? "true" : "false") . "\n";
}
?>
