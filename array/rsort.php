<?php
// Ejemplo 1: Ordenar un array numérico en orden descendente
$array1 = [3, 1, 4, 1, 5, 9, 2, 6];
rsort($array1);
echo "Ejemplo 1 (Array numérico descendente):\n";
print_r($array1);

// Ejemplo 2: Ordenar un array de strings en orden descendente
$array2 = ["banana", "manzana", "cereza", "arándano"];
rsort($array2);
echo "\nEjemplo 2 (Strings en orden descendente):\n";
print_r($array2);

// Ejemplo 3: Ordenar con SORT_NUMERIC en orden descendente
$array3 = ["10", "9", "100", "1"];
rsort($array3, SORT_NUMERIC);
echo "\nEjemplo 3 (SORT_NUMERIC descendente):\n";
print_r($array3);

// Ejemplo 4: Ordenar con SORT_NATURAL en orden descendente
$array4 = ["img12", "img2", "img1", "img10"];
rsort($array4, SORT_NATURAL);
echo "\nEjemplo 4 (SORT_NATURAL descendente):\n";
print_r($array4);

// Ejemplo 5: Comparar sort vs rsort
$original = [5, 2, 8, 1, 9];
$asc = $original;
$desc = $original;
sort($asc);
rsort($desc);
echo "\nEjemplo 5 (Comparar sort vs rsort):\n";
echo "Original: ";
print_r($original);
echo "sort (ascendente): ";
print_r($asc);
echo "rsort (descendente): ";
print_r($desc);
?>
