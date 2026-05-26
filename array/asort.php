<?php
// Ejemplo 1: Ordenar un array asociativo por valores manteniendo claves
$array1 = ["b" => 3, "a" => 1, "c" => 2];
asort($array1);
echo "Ejemplo 1 (Asociativo ordenado por valores):\n";
print_r($array1);

// Ejemplo 2: Ordenar frutas por nombre manteniendo la asociación
$frutas = ["x" => "naranja", "y" => "banana", "z" => "manzana", "w" => "cereza"];
asort($frutas);
echo "\nEjemplo 2 (Frutas ordenadas por valor):\n";
print_r($frutas);

// Ejemplo 3: Ordenar precios manteniendo el nombre del producto
$precios = ["pan" => 1.50, "leche" => 0.99, "queso" => 3.25, "huevos" => 2.10];
asort($precios);
echo "\nEjemplo 3 (Precios ordenados):\n";
foreach ($precios as $producto => $precio) {
    echo "  $producto: $precio€\n";
}

// Ejemplo 4: Diferencia entre sort y asort
$array_sort = ["c" => 3, "a" => 1, "b" => 2];
$array_asort = ["c" => 3, "a" => 1, "b" => 2];
sort($array_sort);
asort($array_asort);
echo "\nEjemplo 4 (sort vs asort):\n";
echo "sort (pierde claves):\n";
print_r($array_sort);
echo "asort (mantiene claves):\n";
print_r($array_asort);
?>
