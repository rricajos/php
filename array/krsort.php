<?php
// Ejemplo 1: Ordenar un array asociativo por claves en orden descendente
$array1 = ["a" => 1, "c" => 3, "b" => 2];
krsort($array1);
echo "Ejemplo 1 (Claves en orden descendente):\n";
print_r($array1);

// Ejemplo 2: Ordenar con claves numéricas en orden descendente
$array2 = [1 => "uno", 3 => "tres", 2 => "dos", 0 => "cero"];
krsort($array2);
echo "\nEjemplo 2 (Claves numéricas descendentes):\n";
print_r($array2);

// Ejemplo 3: Ordenar versiones en orden descendente
$versiones = [
    "v1.0" => "Primera versión",
    "v3.0" => "Tercera versión",
    "v2.0" => "Segunda versión",
    "v1.5" => "Versión intermedia"
];
krsort($versiones, SORT_NATURAL);
echo "\nEjemplo 3 (Versiones en orden descendente):\n";
foreach ($versiones as $ver => $desc) {
    echo "  $ver: $desc\n";
}

// Ejemplo 4: Comparar ksort vs krsort
$datos = ["b" => 2, "a" => 1, "d" => 4, "c" => 3];
$asc = $datos;
$desc = $datos;
ksort($asc);
krsort($desc);
echo "\nEjemplo 4 (ksort vs krsort):\n";
echo "ksort (ascendente):\n";
print_r($asc);
echo "krsort (descendente):\n";
print_r($desc);
?>
