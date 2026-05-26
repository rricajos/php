<?php
// Ejemplo 1: Obtener el tipo de diferentes variables
echo "Ejemplo 1 (Tipos de datos):\n";
echo "gettype(42): " . gettype(42) . "\n";           // integer
echo "gettype(3.14): " . gettype(3.14) . "\n";       // double
echo "gettype('hola'): " . gettype('hola') . "\n";   // string
echo "gettype(true): " . gettype(true) . "\n";        // boolean
echo "gettype(null): " . gettype(null) . "\n";        // NULL
echo "gettype([]): " . gettype([]) . "\n";            // array

// Ejemplo 2: Objetos
$obj = new stdClass();
echo "\nEjemplo 2 (Objeto):\n";
echo "gettype(stdClass): " . gettype($obj) . "\n"; // object

// Ejemplo 3: Resource (legacy)
$file = fopen("php://temp", "r");
echo "\nEjemplo 3 (Resource):\n";
echo "gettype(fopen): " . gettype($file) . "\n"; // resource
fclose($file);

// Ejemplo 4: Comparar con funciones is_*
$valor = 42;
echo "\nEjemplo 4 (gettype vs is_*):\n";
echo "gettype: " . gettype($valor) . "\n";
echo "is_int: " . (is_int($valor) ? "true" : "false") . "\n";
echo "is_string: " . (is_string($valor) ? "true" : "false") . "\n";

// Ejemplo 5: Uso práctico - inspeccionar variables
$datos = ["nombre" => "Ana", "edad" => 30, "activo" => true, "datos" => null];
echo "\nEjemplo 5 (Inspeccionar variables):\n";
foreach ($datos as $clave => $valor) {
    echo "  $clave: " . gettype($valor) . " = " . var_export($valor, true) . "\n";
}
?>
