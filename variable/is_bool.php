<?php
// Ejemplo 1: Verificar si es un booleano
echo "Ejemplo 1 (Verificar booleanos):\n";
var_dump(is_bool(true));    // true
var_dump(is_bool(false));   // true
var_dump(is_bool(1));       // false
var_dump(is_bool(0));       // false
var_dump(is_bool("true"));  // false
var_dump(is_bool(null));    // false

// Ejemplo 2: Valores que "parecen" bool pero no lo son
echo "\nEjemplo 2 (Falsos positivos comunes):\n";
$valores = [true, false, 1, 0, "", "0", null, "true", "false"];
foreach ($valores as $val) {
    $esBool = is_bool($val) ? "Sí" : "No";
    echo "  " . var_export($val, true) . " -> is_bool: $esBool\n";
}

// Ejemplo 3: Resultado de comparaciones es bool
echo "\nEjemplo 3 (Comparaciones devuelven bool):\n";
$resultado = (5 > 3);
echo "5 > 3 es " . gettype($resultado) . ": " . var_export($resultado, true) . "\n";

// Ejemplo 4: Uso práctico - validar flags
function configurar(mixed $debug): void {
    if (!is_bool($debug)) {
        echo "  Error: 'debug' debe ser true o false\n";
        return;
    }
    echo "  Debug: " . ($debug ? "activado" : "desactivado") . "\n";
}
echo "\nEjemplo 4 (Validar flag):\n";
configurar(true);
configurar(1); // Error
?>
