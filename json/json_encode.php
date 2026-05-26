<?php
// Ejemplo 1: Codificar un array asociativo a JSON
$datos = ["nombre" => "Ana", "edad" => 30, "ciudad" => "Madrid"];
echo "Ejemplo 1 (Array a JSON):\n";
echo json_encode($datos) . "\n";

// Ejemplo 2: JSON_PRETTY_PRINT (formato legible)
echo "\nEjemplo 2 (Pretty print):\n";
echo json_encode($datos, JSON_PRETTY_PRINT) . "\n";

// Ejemplo 3: Codificar array indexado
$frutas = ["manzana", "banana", "cereza"];
echo "\nEjemplo 3 (Array indexado):\n";
echo json_encode($frutas) . "\n"; // ["manzana","banana","cereza"]

// Ejemplo 4: JSON_UNESCAPED_UNICODE (mantener caracteres UTF-8)
$utf8 = ["ciudad" => "Málaga", "país" => "España"];
echo "\nEjemplo 4 (Unicode):\n";
echo "Sin flag:  " . json_encode($utf8) . "\n";
echo "Con flag:  " . json_encode($utf8, JSON_UNESCAPED_UNICODE) . "\n";

// Ejemplo 5: JSON_UNESCAPED_SLASHES
$url = ["web" => "https://ejemplo.com/ruta/pagina"];
echo "\nEjemplo 5 (Slashes):\n";
echo "Sin flag: " . json_encode($url) . "\n";
echo "Con flag: " . json_encode($url, JSON_UNESCAPED_SLASHES) . "\n";

// Ejemplo 6: Combinar flags
$complejo = ["nombre" => "José", "url" => "https://ejemplo.com/api"];
echo "\nEjemplo 6 (Combinar flags):\n";
echo json_encode($complejo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

// Ejemplo 7: Codificar tipos de datos PHP
echo "\nEjemplo 7 (Tipos de datos):\n";
echo "int:    " . json_encode(42) . "\n";
echo "float:  " . json_encode(3.14) . "\n";
echo "string: " . json_encode("hola") . "\n";
echo "bool:   " . json_encode(true) . "\n";
echo "null:   " . json_encode(null) . "\n";
echo "object: " . json_encode(new stdClass()) . "\n"; // {}

// Ejemplo 8: JSON_FORCE_OBJECT (array indexado como objeto)
$array = ["a", "b", "c"];
echo "\nEjemplo 8 (JSON_FORCE_OBJECT):\n";
echo "Normal: " . json_encode($array) . "\n";
echo "Object: " . json_encode($array, JSON_FORCE_OBJECT) . "\n";
?>
