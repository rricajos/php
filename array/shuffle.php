<?php
// Ejemplo 1: Mezclar un array numérico
$numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
echo "Ejemplo 1 (Mezclar números):\n";
echo "Original: ";
print_r($numeros);
shuffle($numeros);
echo "Mezclado: ";
print_r($numeros);

// Ejemplo 2: Mezclar un array de strings
$frutas = ["manzana", "banana", "cereza", "naranja", "uva"];
shuffle($frutas);
echo "\nEjemplo 2 (Frutas mezcladas):\n";
print_r($frutas);

// Ejemplo 3: shuffle pierde las claves originales
$asociativo = ["a" => 1, "b" => 2, "c" => 3, "d" => 4];
echo "\nEjemplo 3 (Las claves se reindezan):\n";
echo "Original:\n";
print_r($asociativo);
shuffle($asociativo);
echo "Después de shuffle (claves perdidas):\n";
print_r($asociativo);

// Ejemplo 4: Uso práctico - seleccionar N elementos aleatorios
$colores = ["rojo", "azul", "verde", "amarillo", "morado", "naranja"];
shuffle($colores);
$seleccion = array_slice($colores, 0, 3);
echo "\nEjemplo 4 (Seleccionar 3 colores aleatorios):\n";
print_r($seleccion);

// Ejemplo 5: Uso práctico - barajar un mazo de cartas
$palos = ["♠", "♥", "♦", "♣"];
$valores = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];
$mazo = [];
foreach ($palos as $palo) {
    foreach ($valores as $valor) {
        $mazo[] = "$valor$palo";
    }
}
shuffle($mazo);
echo "\nEjemplo 5 (Primeras 5 cartas de un mazo barajado):\n";
$mano = array_slice($mazo, 0, 5);
echo implode(", ", $mano) . "\n";
?>
