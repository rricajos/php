<?php
// Ejemplo 1: Imprimir array de forma legible
$frutas = ["manzana", "banana", "cereza"];
echo "Ejemplo 1 (Array simple):\n";
print_r($frutas);

// Ejemplo 2: Array asociativo
$persona = ["nombre" => "Ana", "edad" => 30, "ciudad" => "Madrid"];
echo "\nEjemplo 2 (Array asociativo):\n";
print_r($persona);

// Ejemplo 3: Array multidimensional
$empresa = [
    "nombre" => "TechCorp",
    "empleados" => [
        ["nombre" => "Ana", "rol" => "Dev"],
        ["nombre" => "Carlos", "rol" => "QA"]
    ]
];
echo "\nEjemplo 3 (Multidimensional):\n";
print_r($empresa);

// Ejemplo 4: Devolver como string en vez de imprimir (return = true)
$datos = ["a" => 1, "b" => 2];
$string = print_r($datos, true);
echo "\nEjemplo 4 (Devolver como string):\n";
echo "Longitud del string: " . strlen($string) . "\n";
echo $string;

// Ejemplo 5: print_r con objetos
$obj = new stdClass();
$obj->nombre = "Ana";
$obj->items = [1, 2, 3];
echo "\nEjemplo 5 (Objeto):\n";
print_r($obj);

// Ejemplo 6: Uso práctico - debug rápido
echo "\nEjemplo 6 (Debug rápido):\n";
$config = ["debug" => true, "version" => "8.3", "módulos" => ["pdo", "json"]];
echo "<pre>";
print_r($config);
echo "</pre>";
?>
