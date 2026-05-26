<?php
// Ejemplo 1: Ordenar números con función de comparación personalizada
$array1 = [3, 1, 4, 1, 5, 9, 2, 6];
usort($array1, function($a, $b) {
    return $a - $b; // Orden ascendente
});
echo "Ejemplo 1 (Ordenar números ascendente):\n";
print_r($array1);

// Ejemplo 2: Ordenar usando el operador spaceship (<=>)
$array2 = [3, 1, 4, 1, 5, 9, 2, 6];
usort($array2, function($a, $b) {
    return $b <=> $a; // Orden descendente
});
echo "\nEjemplo 2 (Operador spaceship, descendente):\n";
print_r($array2);

// Ejemplo 3: Ordenar un array de objetos por una propiedad
$personas = [
    ["nombre" => "Ana", "edad" => 30],
    ["nombre" => "Carlos", "edad" => 25],
    ["nombre" => "Marta", "edad" => 35],
    ["nombre" => "Luis", "edad" => 28]
];
usort($personas, function($a, $b) {
    return $a["edad"] <=> $b["edad"];
});
echo "\nEjemplo 3 (Ordenar personas por edad):\n";
foreach ($personas as $persona) {
    echo "  {$persona['nombre']}: {$persona['edad']} años\n";
}

// Ejemplo 4: Ordenar strings por longitud
$palabras = ["gato", "elefante", "sol", "mariposa", "río"];
usort($palabras, function($a, $b) {
    return strlen($a) - strlen($b);
});
echo "\nEjemplo 4 (Ordenar por longitud de string):\n";
print_r($palabras);

// Ejemplo 5: Ordenación múltiple (por edad, luego por nombre)
$empleados = [
    ["nombre" => "Ana", "edad" => 30],
    ["nombre" => "Zoe", "edad" => 25],
    ["nombre" => "Carlos", "edad" => 30],
    ["nombre" => "Ana", "edad" => 25]
];
usort($empleados, function($a, $b) {
    $cmp = $a["edad"] <=> $b["edad"];
    if ($cmp === 0) {
        return $a["nombre"] <=> $b["nombre"];
    }
    return $cmp;
});
echo "\nEjemplo 5 (Ordenar por edad, luego por nombre):\n";
foreach ($empleados as $e) {
    echo "  {$e['nombre']}: {$e['edad']} años\n";
}
?>
