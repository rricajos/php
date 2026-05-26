<?php
// Ejemplo 1: Invertir un array simple
$array1 = [1, 2, 3, 4, 5];
$invertido1 = array_reverse($array1);
echo "Ejemplo 1 (Array simple):\n";
print_r($invertido1);

// Ejemplo 2: Invertir preservando las claves
$array2 = ["a" => "manzana", "b" => "banana", "c" => "cereza"];
$invertido2 = array_reverse($array2, true);
echo "\nEjemplo 2 (Preservando claves):\n";
print_r($invertido2);

// Ejemplo 3: Invertir sin preservar claves numéricas
$array3 = [10 => "uno", 20 => "dos", 30 => "tres"];
$invertido3_sin = array_reverse($array3);
$invertido3_con = array_reverse($array3, true);
echo "\nEjemplo 3 (Sin preservar claves numéricas):\n";
print_r($invertido3_sin);
echo "Con preservar claves numéricas:\n";
print_r($invertido3_con);

// Ejemplo 4: Invertir un array con tipos mixtos
$array4 = ["texto", 42, 3.14, true, null];
$invertido4 = array_reverse($array4);
echo "\nEjemplo 4 (Tipos mixtos):\n";
print_r($invertido4);

// Ejemplo 5: Invertir un array anidado
$array5 = [[1, 2], [3, 4], [5, 6]];
$invertido5 = array_reverse($array5);
echo "\nEjemplo 5 (Array anidado - solo invierte el nivel superior):\n";
print_r($invertido5);
?>
