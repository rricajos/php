<?php
// Ejemplo 1: Obtener el código ASCII de un carácter
echo "Ejemplo 1 (Carácter a código ASCII):\n";
echo "ord('A'): " . ord('A') . "\n"; // 65
echo "ord('a'): " . ord('a') . "\n"; // 97
echo "ord('0'): " . ord('0') . "\n"; // 48

// Ejemplo 2: Solo toma el primer carácter del string
echo "\nEjemplo 2 (Solo el primer carácter):\n";
echo "ord('Hola'): " . ord('Hola') . "\n"; // 72 (H)

// Ejemplo 3: Códigos de caracteres especiales
echo "\nEjemplo 3 (Caracteres especiales):\n";
echo "Espacio: " . ord(' ') . "\n";   // 32
echo "Tab: " . ord("\t") . "\n";      // 9
echo "Newline: " . ord("\n") . "\n";  // 10

// Ejemplo 4: Verificar si un carácter es mayúscula o minúscula
$char = 'G';
$code = ord($char);
echo "\nEjemplo 4 (Verificar tipo de carácter):\n";
if ($code >= 65 && $code <= 90) {
    echo "'$char' es mayúscula (código $code)\n";
} elseif ($code >= 97 && $code <= 122) {
    echo "'$char' es minúscula (código $code)\n";
} elseif ($code >= 48 && $code <= 57) {
    echo "'$char' es dígito (código $code)\n";
}

// Ejemplo 5: Uso práctico - cifrado César simple
$texto = "HOLA";
$desplazamiento = 3;
echo "\nEjemplo 5 (Cifrado César con desplazamiento $desplazamiento):\n";
$cifrado = "";
for ($i = 0; $i < strlen($texto); $i++) {
    $cifrado .= chr(((ord($texto[$i]) - 65 + $desplazamiento) % 26) + 65);
}
echo "Original: $texto\n";
echo "Cifrado: $cifrado\n";
?>
