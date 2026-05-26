<?php
// Ejemplo 1: Reemplazar un string por otro
$texto = "Hola mundo";
echo "Ejemplo 1 (Reemplazo simple):\n";
echo str_replace("mundo", "PHP", $texto) . "\n"; // Hola PHP

// Ejemplo 2: Reemplazar múltiples ocurrencias
$texto2 = "el gato y el perro y el pájaro";
echo "\nEjemplo 2 (Múltiples ocurrencias):\n";
echo str_replace("el", "un", $texto2) . "\n";

// Ejemplo 3: Reemplazar con arrays (múltiples reemplazos)
$plantilla = "Hola {nombre}, bienvenido a {ciudad}";
$buscar = ["{nombre}", "{ciudad}"];
$reemplazar = ["Ana", "Madrid"];
echo "\nEjemplo 3 (Reemplazo con arrays):\n";
echo str_replace($buscar, $reemplazar, $plantilla) . "\n";

// Ejemplo 4: Contar los reemplazos realizados
$texto4 = "aaa bbb aaa ccc aaa";
$resultado = str_replace("aaa", "xxx", $texto4, $count);
echo "\nEjemplo 4 (Contar reemplazos):\n";
echo "Resultado: $resultado\n";
echo "Reemplazos: $count\n";

// Ejemplo 5: Es sensible a mayúsculas
$texto5 = "PHP php Php";
echo "\nEjemplo 5 (Sensible a mayúsculas):\n";
echo str_replace("PHP", "***", $texto5) . "\n"; // *** php Php

// Ejemplo 6: Reemplazar en un array de strings
$frutas = ["manzana verde", "manzana roja", "pera verde"];
$resultado = str_replace("verde", "amarilla", $frutas);
echo "\nEjemplo 6 (Reemplazar en array de strings):\n";
print_r($resultado);
?>
