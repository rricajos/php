<?php
// pos() es un alias de current()

// Ejemplo 1: Uso básico de pos (idéntico a current)
$frutas = ["manzana", "banana", "cereza"];
echo "Ejemplo 1 (pos es alias de current):\n";
echo "pos(): " . pos($frutas) . "\n";
echo "current(): " . current($frutas) . "\n";

// Ejemplo 2: pos después de mover el puntero
$numeros = [10, 20, 30];
echo "\nEjemplo 2 (pos después de next):\n";
echo "pos(): " . pos($numeros) . "\n"; // 10
next($numeros);
echo "Después de next, pos(): " . pos($numeros) . "\n"; // 20

// Ejemplo 3: Verificar que pos y current siempre devuelven lo mismo
$array3 = ["a" => 1, "b" => 2, "c" => 3];
echo "\nEjemplo 3 (Comparar pos y current en cada paso):\n";
reset($array3);
do {
    echo "  pos(): " . pos($array3) . " | current(): " . current($array3) . "\n";
} while (next($array3));

// Ejemplo 4: pos con array vacío
$vacio = [];
echo "\nEjemplo 4 (Array vacío):\n";
var_dump(pos($vacio)); // false
?>
