<?php
// Ejemplo 1: Ordenar por valores manteniendo claves con comparación personalizada
$array1 = ["b" => 3, "a" => 1, "c" => 2];
uasort($array1, function($a, $b) {
    return $a <=> $b;
});
echo "Ejemplo 1 (Ordenar valores manteniendo claves):\n";
print_r($array1);

// Ejemplo 2: Ordenar productos por precio manteniendo el nombre como clave
$productos = ["pan" => 1.50, "queso" => 3.25, "leche" => 0.99, "huevos" => 2.10];
uasort($productos, function($a, $b) {
    return $a <=> $b;
});
echo "\nEjemplo 2 (Productos ordenados por precio):\n";
foreach ($productos as $nombre => $precio) {
    echo "  $nombre: {$precio}€\n";
}

// Ejemplo 3: Ordenar por longitud del valor
$datos = ["x" => "elefante", "y" => "sol", "z" => "gato", "w" => "mariposa"];
uasort($datos, function($a, $b) {
    return strlen($a) - strlen($b);
});
echo "\nEjemplo 3 (Ordenar por longitud del valor):\n";
print_r($datos);

// Ejemplo 4: Diferencia entre usort y uasort
$original = ["c" => 30, "a" => 10, "b" => 20];
$con_usort = $original;
$con_uasort = $original;
usort($con_usort, function($a, $b) { return $a <=> $b; });
uasort($con_uasort, function($a, $b) { return $a <=> $b; });
echo "\nEjemplo 4 (usort vs uasort):\n";
echo "usort (pierde claves):\n";
print_r($con_usort);
echo "uasort (mantiene claves):\n";
print_r($con_uasort);
?>
