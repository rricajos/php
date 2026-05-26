<?php
// Ejemplo 1: Convertir string a float
echo "Ejemplo 1 (String a float):\n";
echo "floatval('3.14'): " . floatval('3.14') . "\n";     // 3.14
echo "floatval('42'): " . floatval('42') . "\n";           // 42
echo "floatval('1.2e3'): " . floatval('1.2e3') . "\n";   // 1200
echo "floatval('abc'): " . floatval('abc') . "\n";         // 0

// Ejemplo 2: Strings que empiezan con número
echo "\nEjemplo 2 (Strings mixtos):\n";
echo "floatval('3.14abc'): " . floatval('3.14abc') . "\n"; // 3.14
echo "floatval('abc3.14'): " . floatval('abc3.14') . "\n"; // 0

// Ejemplo 3: floatval vs (float) cast
$valor = "99.99€";
echo "\nEjemplo 3 (floatval vs cast):\n";
echo "floatval: " . floatval($valor) . "\n"; // 99.99
echo "(float): " . (float)$valor . "\n";     // 99.99

// Ejemplo 4: doubleval es un alias de floatval
echo "\nEjemplo 4 (doubleval es alias):\n";
echo "floatval('3.14'): " . floatval('3.14') . "\n";
echo "doubleval('3.14'): " . doubleval('3.14') . "\n";

// Ejemplo 5: Uso práctico - limpiar precios de un string
$precios = ["19.99€", "5,50€", "100.00 USD"];
echo "\nEjemplo 5 (Limpiar precios):\n";
foreach ($precios as $precio) {
    $limpio = floatval(str_replace(",", ".", $precio));
    echo "  '$precio' -> $limpio\n";
}
?>
