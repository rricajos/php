<?php
// str_contains() - Disponible desde PHP 8.0

// Ejemplo 1: Verificar si un string contiene un substring
$texto = "Hola mundo PHP";
echo "Ejemplo 1 (Contiene 'mundo'):\n";
var_dump(str_contains($texto, "mundo")); // true

// Ejemplo 2: No contiene
echo "\nEjemplo 2 (No contiene 'Python'):\n";
var_dump(str_contains($texto, "Python")); // false

// Ejemplo 3: Es sensible a mayúsculas
echo "\nEjemplo 3 (Sensible a case):\n";
var_dump(str_contains($texto, "php")); // false
var_dump(str_contains($texto, "PHP")); // true

// Ejemplo 4: String vacío siempre está contenido
echo "\nEjemplo 4 (String vacío):\n";
var_dump(str_contains($texto, "")); // true

// Ejemplo 5: Comparar con strpos (antes de PHP 8)
echo "\nEjemplo 5 (str_contains vs strpos):\n";
// PHP 8+
$resultado_nuevo = str_contains($texto, "mundo");
// Antes de PHP 8
$resultado_viejo = strpos($texto, "mundo") !== false;
echo "str_contains: " . ($resultado_nuevo ? "true" : "false") . "\n";
echo "strpos !== false: " . ($resultado_viejo ? "true" : "false") . "\n";

// Ejemplo 6: Uso práctico - filtrar resultados
$lenguajes = ["PHP es backend", "JavaScript es fullstack", "Python es versátil", "PHP es rápido"];
echo "\nEjemplo 6 (Filtrar con str_contains):\n";
$php_items = array_filter($lenguajes, fn($item) => str_contains($item, "PHP"));
print_r(array_values($php_items));
?>
