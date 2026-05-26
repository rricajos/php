<?php
// Ejemplo 1: Mover el puntero al último elemento
$frutas = ["manzana", "banana", "cereza"];
$ultimo = end($frutas);
echo "Ejemplo 1 (Último elemento):\n";
echo "Último: $ultimo\n"; // cereza

// Ejemplo 2: end con array numérico
$numeros = [10, 20, 30, 40, 50];
echo "\nEjemplo 2 (Array numérico):\n";
echo "Primero (current): " . current($numeros) . "\n";
end($numeros);
echo "Último (después de end): " . current($numeros) . "\n";

// Ejemplo 3: end con array asociativo
$persona = ["nombre" => "Carlos", "edad" => 28, "ciudad" => "Barcelona"];
$ultimo_valor = end($persona);
$ultima_clave = key($persona);
echo "\nEjemplo 3 (Array asociativo):\n";
echo "Última clave: $ultima_clave\n";
echo "Último valor: $ultimo_valor\n";

// Ejemplo 4: Comparar end con array_key_last
$array4 = ["x" => 100, "y" => 200, "z" => 300];
echo "\nEjemplo 4 (end vs array_key_last):\n";
echo "end() devuelve el valor: " . end($array4) . "\n";
echo "array_key_last() devuelve la clave: " . array_key_last($array4) . "\n";

// Ejemplo 5: end con un solo elemento
$array5 = ["único"];
echo "\nEjemplo 5 (Un solo elemento):\n";
echo "end(): " . end($array5) . "\n";

// Ejemplo 6: end con array vacío
$vacio = [];
echo "\nEjemplo 6 (Array vacío):\n";
var_dump(end($vacio)); // false
?>
