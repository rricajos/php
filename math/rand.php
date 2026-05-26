<?php
// Ejemplo 1: Número aleatorio sin rango
echo "Ejemplo 1 (Aleatorio sin rango):\n";
echo "rand(): " . rand() . "\n";

// Ejemplo 2: Número aleatorio con rango
echo "\nEjemplo 2 (Rango 1-10):\n";
echo "rand(1, 10): " . rand(1, 10) . "\n";

// Ejemplo 3: Simular un dado
echo "\nEjemplo 3 (Lanzar un dado 5 veces):\n";
for ($i = 0; $i < 5; $i++) {
    echo "  Lanzamiento " . ($i + 1) . ": " . rand(1, 6) . "\n";
}

// Ejemplo 4: Comparar rand, mt_rand, random_int
echo "\nEjemplo 4 (Comparar funciones aleatorias):\n";
echo "rand(1, 100):       " . rand(1, 100) . "\n";
echo "mt_rand(1, 100):    " . mt_rand(1, 100) . "\n";
echo "random_int(1, 100): " . random_int(1, 100) . "\n";

// Ejemplo 5: getrandmax - valor máximo
echo "\nEjemplo 5 (Valor máximo):\n";
echo "getrandmax(): " . getrandmax() . "\n";

// Ejemplo 6: Nota de seguridad
echo "\nEjemplo 6 (Nota):\n";
echo "rand() y mt_rand() NO son criptográficamente seguros.\n";
echo "Para seguridad, usa random_int() o random_bytes().\n";
?>
