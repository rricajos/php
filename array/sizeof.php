<?php
// sizeof() es un alias de count()

// Ejemplo 1: Uso básico de sizeof (idéntico a count)
$frutas = ["manzana", "banana", "cereza", "naranja"];
echo "Ejemplo 1 (sizeof es alias de count):\n";
echo "sizeof(): " . sizeof($frutas) . "\n";
echo "count():  " . count($frutas) . "\n";

// Ejemplo 2: sizeof con array multidimensional
$matriz = [[1, 2], [3, 4], [5, 6]];
echo "\nEjemplo 2 (Multidimensional):\n";
echo "sizeof normal: " . sizeof($matriz) . "\n"; // 3
echo "sizeof recursivo: " . sizeof($matriz, COUNT_RECURSIVE) . "\n"; // 9

// Ejemplo 3: sizeof con array asociativo
$datos = ["nombre" => "Luis", "edad" => 28];
echo "\nEjemplo 3 (Array asociativo):\n";
echo "sizeof(): " . sizeof($datos) . "\n"; // 2

// Ejemplo 4: sizeof con array vacío
$vacio = [];
echo "\nEjemplo 4 (Array vacío):\n";
echo "sizeof(): " . sizeof($vacio) . "\n"; // 0

// Nota: Se recomienda usar count() en lugar de sizeof()
// ya que count() es más reconocido y estándar en PHP.
// sizeof() existe principalmente por compatibilidad con C.
?>
