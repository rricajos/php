<?php
// Ejemplo 1: Avanzar el puntero interno
$frutas = ["manzana", "banana", "cereza"];
echo "Ejemplo 1 (Avanzar puntero):\n";
echo "Actual: " . current($frutas) . "\n"; // manzana
$siguiente = next($frutas);
echo "next() devuelve: $siguiente\n"; // banana
echo "Actual ahora: " . current($frutas) . "\n"; // banana

// Ejemplo 2: Avanzar hasta el final
$numeros = [10, 20, 30];
echo "\nEjemplo 2 (Avanzar hasta el final):\n";
echo current($numeros) . "\n"; // 10
echo next($numeros) . "\n";    // 20
echo next($numeros) . "\n";    // 30
var_dump(next($numeros));       // false (no hay más elementos)

// Ejemplo 3: next con array asociativo
$persona = ["nombre" => "Luis", "edad" => 25, "ciudad" => "Valencia"];
echo "\nEjemplo 3 (Array asociativo):\n";
echo key($persona) . " => " . current($persona) . "\n";
next($persona);
echo key($persona) . " => " . current($persona) . "\n";
next($persona);
echo key($persona) . " => " . current($persona) . "\n";

// Ejemplo 4: Recorrer un array con next
$letras = ["a", "b", "c", "d", "e"];
echo "\nEjemplo 4 (Recorrer con do-while y next):\n";
reset($letras);
do {
    echo "  [" . key($letras) . "] => " . current($letras) . "\n";
} while (next($letras) !== false);
?>
