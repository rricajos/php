<?php
// Ejemplo 1: Contar elementos de un array simple
$frutas = ["manzana", "banana", "cereza"];
echo "Ejemplo 1 (Array simple):\n";
echo "Cantidad: " . count($frutas) . "\n"; // 3

// Ejemplo 2: Contar elementos de un array asociativo
$persona = ["nombre" => "Ana", "edad" => 30, "ciudad" => "Madrid"];
echo "\nEjemplo 2 (Array asociativo):\n";
echo "Cantidad: " . count($persona) . "\n"; // 3

// Ejemplo 3: Contar un array multidimensional (modo normal)
$matriz = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
echo "\nEjemplo 3 (Multidimensional, COUNT_NORMAL):\n";
echo "count normal: " . count($matriz) . "\n"; // 3 (solo cuenta el primer nivel)

// Ejemplo 4: Contar recursivamente con COUNT_RECURSIVE
echo "\nEjemplo 4 (COUNT_RECURSIVE):\n";
echo "count recursivo: " . count($matriz, COUNT_RECURSIVE) . "\n"; // 12 (3 arrays + 9 elementos)

// Ejemplo 5: Contar un array vacío
$vacio = [];
echo "\nEjemplo 5 (Array vacío):\n";
echo "Cantidad: " . count($vacio) . "\n"; // 0

// Ejemplo 6: count con un string (no es array)
$texto = "Hola mundo";
echo "\nEjemplo 6 (Valor no-array):\n";
echo "count de un string: " . count($texto) . "\n"; // 1 (Warning en PHP 7.2+, pero devuelve 1)

// Ejemplo 7: Uso práctico - verificar si un array tiene elementos
$resultados = ["dato1", "dato2"];
echo "\nEjemplo 7 (Verificar si hay elementos):\n";
if (count($resultados) > 0) {
    echo "Hay " . count($resultados) . " resultados\n";
} else {
    echo "No hay resultados\n";
}
?>
