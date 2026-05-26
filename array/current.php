<?php
// Ejemplo 1: Obtener el elemento actual de un array
$frutas = ["manzana", "banana", "cereza"];
echo "Ejemplo 1 (Elemento actual):\n";
echo "Actual: " . current($frutas) . "\n"; // manzana (el puntero empieza al inicio)

// Ejemplo 2: current después de mover el puntero con next
$array2 = [10, 20, 30, 40];
echo "\nEjemplo 2 (current + next):\n";
echo "Actual: " . current($array2) . "\n"; // 10
next($array2);
echo "Después de next: " . current($array2) . "\n"; // 20
next($array2);
echo "Después de otro next: " . current($array2) . "\n"; // 30

// Ejemplo 3: current con array asociativo
$persona = ["nombre" => "Ana", "edad" => 30, "ciudad" => "Madrid"];
echo "\nEjemplo 3 (Array asociativo):\n";
echo "Actual: " . current($persona) . "\n"; // Ana

// Ejemplo 4: current en un array vacío
$vacio = [];
echo "\nEjemplo 4 (Array vacío):\n";
var_dump(current($vacio)); // false

// Ejemplo 5: Recorrer un array usando current y next
$colores = ["rojo", "verde", "azul"];
echo "\nEjemplo 5 (Recorrer con current + next):\n";
while ($color = current($colores)) {
    echo "  - $color\n";
    next($colores);
}
?>
