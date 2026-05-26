<?php
// Ejemplo 1: Orden natural insensible a mayúsculas
$array1 = ["Img12", "img2", "IMG1", "img10", "Img3"];
natcasesort($array1);
echo "Ejemplo 1 (Orden natural sin distinción de mayúsculas):\n";
print_r($array1);

// Ejemplo 2: Comparar natsort vs natcasesort
$array2 = ["Banana", "manzana", "Cereza", "arándano"];
$con_natsort = $array2;
$con_natcasesort = $array2;
natsort($con_natsort);
natcasesort($con_natcasesort);
echo "\nEjemplo 2 (natsort vs natcasesort):\n";
echo "natsort (sensible a mayúsculas):\n";
print_r($con_natsort);
echo "natcasesort (insensible a mayúsculas):\n";
print_r($con_natcasesort);

// Ejemplo 3: Ordenar archivos con mayúsculas mezcladas
$archivos = ["README.md", "archivo2.txt", "Archivo10.txt", "archivo1.txt"];
natcasesort($archivos);
echo "\nEjemplo 3 (Archivos con mayúsculas mezcladas):\n";
print_r($archivos);

// Ejemplo 4: natcasesort mantiene la asociación de claves
$datos = ["x" => "Item10", "y" => "item2", "z" => "ITEM1"];
natcasesort($datos);
echo "\nEjemplo 4 (Mantiene claves asociativas):\n";
print_r($datos);
?>
