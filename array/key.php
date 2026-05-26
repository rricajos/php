<?php
// Ejemplo 1: Obtener la clave del elemento actual
$frutas = ["a" => "manzana", "b" => "banana", "c" => "cereza"];
echo "Ejemplo 1 (Clave actual):\n";
echo "Clave: " . key($frutas) . "\n"; // a
echo "Valor: " . current($frutas) . "\n"; // manzana

// Ejemplo 2: key después de mover el puntero
$array2 = ["nombre" => "Ana", "edad" => 30, "ciudad" => "Madrid"];
echo "\nEjemplo 2 (key + next):\n";
echo "Clave: " . key($array2) . "\n"; // nombre
next($array2);
echo "Después de next: " . key($array2) . "\n"; // edad
next($array2);
echo "Después de otro next: " . key($array2) . "\n"; // ciudad

// Ejemplo 3: key con array indexado numéricamente
$colores = ["rojo", "verde", "azul"];
echo "\nEjemplo 3 (Array numérico):\n";
echo "Clave: " . key($colores) . "\n"; // 0

// Ejemplo 4: Recorrer mostrando claves y valores
$datos = ["php" => 8.3, "python" => 3.12, "node" => 20];
echo "\nEjemplo 4 (Recorrer con key + current + next):\n";
reset($datos);
while (key($datos) !== null) {
    echo "  " . key($datos) . " => " . current($datos) . "\n";
    next($datos);
}

// Ejemplo 5: key cuando el puntero está más allá del final
$array5 = [1, 2];
next($array5);
next($array5); // Más allá del final
echo "\nEjemplo 5 (Puntero más allá del final):\n";
var_dump(key($array5)); // NULL
?>
