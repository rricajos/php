<?php
// Ejemplo 1: Ordenar un array asociativo por valores en orden descendente
$array1 = ["b" => 3, "a" => 1, "c" => 2];
arsort($array1);
echo "Ejemplo 1 (Asociativo descendente por valores):\n";
print_r($array1);

// Ejemplo 2: Ordenar puntuaciones de jugadores (mayor a menor)
$puntuaciones = ["Ana" => 85, "Carlos" => 92, "Marta" => 78, "Luis" => 95];
arsort($puntuaciones);
echo "\nEjemplo 2 (Ranking de puntuaciones):\n";
$posicion = 1;
foreach ($puntuaciones as $nombre => $puntos) {
    echo "  $posicion. $nombre: $puntos puntos\n";
    $posicion++;
}

// Ejemplo 3: Ordenar strings en orden descendente manteniendo claves
$ciudades = ["es" => "Madrid", "fr" => "París", "de" => "Berlín", "it" => "Roma"];
arsort($ciudades);
echo "\nEjemplo 3 (Ciudades en orden descendente):\n";
print_r($ciudades);

// Ejemplo 4: Comparar asort vs arsort
$notas = ["mate" => 7, "lengua" => 9, "ciencias" => 5, "historia" => 8];
$asc = $notas;
$desc = $notas;
asort($asc);
arsort($desc);
echo "\nEjemplo 4 (asort vs arsort):\n";
echo "asort (ascendente):\n";
print_r($asc);
echo "arsort (descendente):\n";
print_r($desc);
?>
