<?php
// Ejemplo 1: var_dump muestra tipo y valor
echo "Ejemplo 1 (Tipos básicos):\n";
var_dump(42);        // int(42)
var_dump(3.14);      // float(3.14)
var_dump("hola");    // string(4) "hola"
var_dump(true);      // bool(true)
var_dump(null);      // NULL

// Ejemplo 2: var_dump con arrays
echo "\nEjemplo 2 (Array):\n";
var_dump(["a" => 1, "b" => "dos", "c" => true]);

// Ejemplo 3: var_dump con objetos
echo "\nEjemplo 3 (Objeto):\n";
$obj = new stdClass();
$obj->nombre = "Ana";
$obj->edad = 30;
var_dump($obj);

// Ejemplo 4: Múltiples argumentos
echo "\nEjemplo 4 (Múltiples argumentos):\n";
var_dump("texto", 42, true);

// Ejemplo 5: Comparar var_dump, print_r, var_export
$datos = ["nombre" => "Ana", "activo" => true, "nota" => 9.5];
echo "\nEjemplo 5 (var_dump vs print_r vs var_export):\n";
echo "--- var_dump ---\n";
var_dump($datos);
echo "\n--- print_r ---\n";
print_r($datos);
echo "\n\n--- var_export ---\n";
var_export($datos);
echo "\n";
?>
