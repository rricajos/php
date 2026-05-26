<?php
// Ejemplo 1: Ordenar un array numérico
$array1 = [3, 1, 4, 1, 5, 9, 2, 6];
sort($array1);
echo "Ejemplo 1 (Array numérico):\n";
print_r($array1);

// Ejemplo 2: Ordenar un array de strings
$array2 = ["banana", "manzana", "cereza", "arándano"];
sort($array2);
echo "\nEjemplo 2 (Array de strings):\n";
print_r($array2);

// Ejemplo 3: Ordenar con SORT_NUMERIC
$array3 = ["10", "9", "100", "1"];
sort($array3, SORT_NUMERIC);
echo "\nEjemplo 3 (SORT_NUMERIC):\n";
print_r($array3);

// Ejemplo 4: Ordenar con SORT_STRING
$array4 = ["10", "9", "100", "1"];
sort($array4, SORT_STRING);
echo "\nEjemplo 4 (SORT_STRING - orden lexicográfico):\n";
print_r($array4);

// Ejemplo 5: Ordenar con SORT_NATURAL (orden natural)
$array5 = ["img12", "img2", "img1", "img10"];
sort($array5, SORT_NATURAL);
echo "\nEjemplo 5 (SORT_NATURAL):\n";
print_r($array5);

// Ejemplo 6: Ordenar con SORT_FLAG_CASE (insensible a mayúsculas)
$array6 = ["Banana", "arándano", "Cereza", "almendra"];
sort($array6, SORT_STRING | SORT_FLAG_CASE);
echo "\nEjemplo 6 (SORT_STRING | SORT_FLAG_CASE):\n";
print_r($array6);
?>
