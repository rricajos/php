<?php
// Ejemplo 1: Convertir a entero
$valor = "42";
echo "Ejemplo 1 (String a integer):\n";
echo "Antes: " . gettype($valor) . " = $valor\n";
settype($valor, "integer");
echo "Después: " . gettype($valor) . " = $valor\n";

// Ejemplo 2: Convertir a string
$numero = 3.14;
echo "\nEjemplo 2 (Float a string):\n";
echo "Antes: " . gettype($numero) . " = $numero\n";
settype($numero, "string");
echo "Después: " . gettype($numero) . " = $numero\n";

// Ejemplo 3: Convertir a boolean
$valores = [0, 1, "", "hola", null, [], [1]];
echo "\nEjemplo 3 (Conversión a boolean):\n";
foreach ($valores as $v) {
    $original = var_export($v, true);
    $copia = $v;
    settype($copia, "boolean");
    echo "  $original -> " . ($copia ? "true" : "false") . "\n";
}

// Ejemplo 4: Convertir a array
$obj = new stdClass();
$obj->nombre = "Ana";
$obj->edad = 30;
echo "\nEjemplo 4 (Object a array):\n";
settype($obj, "array");
print_r($obj);

// Ejemplo 5: Tipos soportados
echo "\nEjemplo 5 (Tipos soportados):\n";
echo "integer, float, string, boolean, array, object, null\n";

// Ejemplo 6: Alternativa - casting explícito
$val = "123";
echo "\nEjemplo 6 (Casting vs settype):\n";
echo "(int)\$val: " . (int)$val . "\n";
echo "(float)\$val: " . (float)$val . "\n";
echo "(bool)\$val: " . ((bool)$val ? "true" : "false") . "\n";
?>
