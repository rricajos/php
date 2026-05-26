<?php
// Ejemplo 1: Número aleatorio con Mersenne Twister
echo "Ejemplo 1 (mt_rand básico):\n";
echo "mt_rand(): " . mt_rand() . "\n";

// Ejemplo 2: Con rango
echo "\nEjemplo 2 (Rango 1-100):\n";
echo "mt_rand(1, 100): " . mt_rand(1, 100) . "\n";

// Ejemplo 3: mt_rand es más rápido y mejor que rand
echo "\nEjemplo 3 (mt_rand vs rand):\n";
echo "mt_getrandmax(): " . mt_getrandmax() . "\n";
echo "getrandmax():    " . getrandmax() . "\n";

// Ejemplo 4: Uso práctico - generar color hexadecimal aleatorio
$color = sprintf("#%06x", mt_rand(0, 0xFFFFFF));
echo "\nEjemplo 4 (Color aleatorio):\n";
echo "Color: $color\n";

// Ejemplo 5: Uso práctico - generar string aleatorio
$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
$longitud = 10;
$aleatorio = "";
for ($i = 0; $i < $longitud; $i++) {
    $aleatorio .= $chars[mt_rand(0, strlen($chars) - 1)];
}
echo "\nEjemplo 5 (String aleatorio de $longitud caracteres):\n";
echo "Resultado: $aleatorio\n";

// Ejemplo 6: Nota - desde PHP 8.1, rand() usa Mersenne Twister internamente
echo "\nEjemplo 6 (Nota):\n";
echo "Desde PHP 8.1, rand() usa el mismo algoritmo que mt_rand().\n";
echo "Para seguridad criptográfica, usa random_int().\n";
?>
