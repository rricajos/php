<?php
// Ejemplo 1: Unir un array en un string
$colores = ["rojo", "verde", "azul"];
echo "Ejemplo 1 (Unir con coma):\n";
echo implode(", ", $colores) . "\n"; // rojo, verde, azul

// Ejemplo 2: Unir sin separador
$letras = ["H", "o", "l", "a"];
echo "\nEjemplo 2 (Sin separador):\n";
echo implode("", $letras) . "\n"; // Hola

// Ejemplo 3: Unir con salto de línea
$lineas = ["línea 1", "línea 2", "línea 3"];
echo "\nEjemplo 3 (Con salto de línea):\n";
echo implode("\n", $lineas) . "\n";

// Ejemplo 4: join() es un alias de implode()
$frutas = ["manzana", "banana", "cereza"];
echo "\nEjemplo 4 (join es alias de implode):\n";
echo "implode: " . implode(" | ", $frutas) . "\n";
echo "join:    " . join(" | ", $frutas) . "\n";

// Ejemplo 5: Uso práctico - generar SQL IN clause
$ids = [1, 5, 10, 15];
$in_clause = "WHERE id IN (" . implode(", ", $ids) . ")";
echo "\nEjemplo 5 (SQL IN clause):\n";
echo $in_clause . "\n";

// Ejemplo 6: Uso práctico - crear una ruta
$partes = ["var", "www", "html", "index.php"];
$ruta = "/" . implode("/", $partes);
echo "\nEjemplo 6 (Crear ruta):\n";
echo $ruta . "\n";

// Ejemplo 7: explode + implode (ida y vuelta)
$original = "Hola mundo PHP";
$reconstruido = implode(" ", explode(" ", $original));
echo "\nEjemplo 7 (explode + implode):\n";
echo "Original: $original\n";
echo "Reconstruido: $reconstruido\n";
?>
