<?php
// Ejemplo 1: Orden natural vs orden estándar
$array1 = ["img12", "img2", "img1", "img10", "img3"];
$estandar = $array1;
$natural = $array1;
sort($estandar);
natsort($natural);
echo "Ejemplo 1 (sort vs natsort):\n";
echo "sort (estándar):\n";
print_r($estandar);
echo "natsort (natural):\n";
print_r($natural);

// Ejemplo 2: Ordenar archivos con nombres numéricos
$archivos = ["archivo20.txt", "archivo3.txt", "archivo1.txt", "archivo10.txt"];
natsort($archivos);
echo "\nEjemplo 2 (Archivos ordenados naturalmente):\n";
print_r($archivos);

// Ejemplo 3: Ordenar versiones de software
$versiones = ["v1.10", "v1.2", "v1.1", "v2.0", "v1.9"];
natsort($versiones);
echo "\nEjemplo 3 (Versiones de software):\n";
print_r($versiones);

// Ejemplo 4: natsort mantiene la asociación de claves
$array4 = ["b" => "item10", "a" => "item2", "c" => "item1"];
natsort($array4);
echo "\nEjemplo 4 (Mantiene claves asociativas):\n";
print_r($array4);

// Ejemplo 5: natsort es sensible a mayúsculas
$array5 = ["Img1", "img2", "IMG3", "img10"];
natsort($array5);
echo "\nEjemplo 5 (Sensible a mayúsculas):\n";
print_r($array5);
?>
