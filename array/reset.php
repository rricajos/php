<?php
// Ejemplo 1: Reiniciar el puntero al primer elemento
$frutas = ["manzana", "banana", "cereza"];
next($frutas);
next($frutas);
echo "Ejemplo 1 (Reset del puntero):\n";
echo "Antes de reset: " . current($frutas) . "\n"; // cereza
reset($frutas);
echo "Después de reset: " . current($frutas) . "\n"; // manzana

// Ejemplo 2: reset devuelve el primer elemento
$numeros = [10, 20, 30];
end($numeros);
$primero = reset($numeros);
echo "\nEjemplo 2 (reset devuelve el primer valor):\n";
echo "Primer elemento: $primero\n"; // 10

// Ejemplo 3: reset con array asociativo
$persona = ["nombre" => "Marta", "edad" => 35, "ciudad" => "Sevilla"];
end($persona);
echo "\nEjemplo 3 (Array asociativo):\n";
echo "Antes: " . key($persona) . " => " . current($persona) . "\n";
reset($persona);
echo "Después: " . key($persona) . " => " . current($persona) . "\n";

// Ejemplo 4: Recorrer un array dos veces usando reset
$colores = ["rojo", "verde", "azul"];
echo "\nEjemplo 4 (Recorrer dos veces):\n";
echo "Primera pasada:\n";
while ($color = current($colores)) {
    echo "  - $color\n";
    next($colores);
}
reset($colores);
echo "Segunda pasada (después de reset):\n";
while ($color = current($colores)) {
    echo "  - $color\n";
    next($colores);
}

// Ejemplo 5: reset con array vacío
$vacio = [];
echo "\nEjemplo 5 (Array vacío):\n";
var_dump(reset($vacio)); // false
?>
