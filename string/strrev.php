<?php
// Ejemplo 1: Invertir un string
$texto = "Hola mundo";
echo "Ejemplo 1 (Invertir string):\n";
echo "Original: $texto\n";
echo "Invertido: " . strrev($texto) . "\n";

// Ejemplo 2: Verificar si es palíndromo
$palabra1 = "reconocer";
$palabra2 = "hola";
echo "\nEjemplo 2 (Palíndromos):\n";
echo "'$palabra1' es palíndromo: " . (strtolower($palabra1) === strtolower(strrev($palabra1)) ? "Sí" : "No") . "\n";
echo "'$palabra2' es palíndromo: " . (strtolower($palabra2) === strtolower(strrev($palabra2)) ? "Sí" : "No") . "\n";

// Ejemplo 3: Invertir un número como string
$numero = "12345";
echo "\nEjemplo 3 (Invertir número):\n";
echo "Original: $numero\n";
echo "Invertido: " . strrev($numero) . "\n";

// Ejemplo 4: Invertir string vacío
echo "\nEjemplo 4 (String vacío):\n";
echo "Invertido: '" . strrev("") . "'\n";

// Ejemplo 5: strrev con caracteres especiales (cuidado con UTF-8)
$ascii = "Hello!";
echo "\nEjemplo 5 (ASCII):\n";
echo "Original: $ascii\n";
echo "Invertido: " . strrev($ascii) . "\n";
?>
