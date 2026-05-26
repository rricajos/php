<?php
// Ejemplo 1: Obtener los valores de un array asociativo
$array1 = ["nombre" => "Ana", "edad" => 25, "ciudad" => "Madrid"];
$valores1 = array_values($array1);
echo "Ejemplo 1 (Array asociativo):\n";
print_r($valores1);

// Ejemplo 2: Reindexar un array con claves no consecutivas
$array2 = [3 => "manzana", 7 => "banana", 1 => "naranja"];
$valores2 = array_values($array2);
echo "\nEjemplo 2 (Reindexar claves no consecutivas):\n";
print_r($valores2);

// Ejemplo 3: Obtener valores de un array con tipos mixtos
$array3 = ["a" => 1, "b" => 3.14, "c" => true, "d" => "texto", "e" => null];
$valores3 = array_values($array3);
echo "\nEjemplo 3 (Tipos mixtos):\n";
print_r($valores3);

// Ejemplo 4: Reindexar después de filtrar
$array4 = [1, 2, 3, 4, 5, 6];
$filtrado = array_filter($array4, function($v) {
    return $v % 2 === 0;
});
echo "\nEjemplo 4 (Después de array_filter, claves desordenadas):\n";
print_r($filtrado);
$reindexado = array_values($filtrado);
echo "Reindexado con array_values:\n";
print_r($reindexado);

// Ejemplo 5: Array vacío
$array5 = [];
$valores5 = array_values($array5);
echo "\nEjemplo 5 (Array vacío):\n";
print_r($valores5);
?>
