<?php
// Ejemplo 1: Retroceder el puntero interno
$frutas = ["manzana", "banana", "cereza"];
end($frutas); // Mover al final
echo "Ejemplo 1 (Retroceder puntero):\n";
echo "Actual (end): " . current($frutas) . "\n"; // cereza
$anterior = prev($frutas);
echo "prev() devuelve: $anterior\n"; // banana
echo "Actual ahora: " . current($frutas) . "\n"; // banana

// Ejemplo 2: Retroceder hasta el inicio
$numeros = [10, 20, 30];
end($numeros);
echo "\nEjemplo 2 (Retroceder hasta el inicio):\n";
echo current($numeros) . "\n"; // 30
echo prev($numeros) . "\n";    // 20
echo prev($numeros) . "\n";    // 10
var_dump(prev($numeros));       // false (no hay más elementos antes)

// Ejemplo 3: Combinar next y prev para navegar
$colores = ["rojo", "verde", "azul", "amarillo"];
echo "\nEjemplo 3 (Navegar adelante y atrás):\n";
echo "Inicio: " . current($colores) . "\n";    // rojo
next($colores);
echo "next: " . current($colores) . "\n";       // verde
next($colores);
echo "next: " . current($colores) . "\n";       // azul
prev($colores);
echo "prev: " . current($colores) . "\n";       // verde

// Ejemplo 4: Recorrer un array en reversa
$letras = ["a", "b", "c", "d"];
echo "\nEjemplo 4 (Recorrer en reversa con prev):\n";
end($letras);
do {
    echo "  [" . key($letras) . "] => " . current($letras) . "\n";
} while (prev($letras) !== false);
?>
