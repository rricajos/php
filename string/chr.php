<?php
// Ejemplo 1: Obtener el carácter de un código ASCII
echo "Ejemplo 1 (Código ASCII a carácter):\n";
echo "chr(65): " . chr(65) . "\n"; // A
echo "chr(97): " . chr(97) . "\n"; // a
echo "chr(48): " . chr(48) . "\n"; // 0

// Ejemplo 2: Generar el alfabeto
echo "\nEjemplo 2 (Generar alfabeto):\n";
for ($i = 65; $i <= 90; $i++) {
    echo chr($i);
}
echo "\n";

// Ejemplo 3: Caracteres especiales
echo "\nEjemplo 3 (Caracteres especiales):\n";
echo "Tab: 'A" . chr(9) . "B'\n";      // Tab
echo "Newline: 'A" . chr(10) . "B'\n";  // Newline
echo "Espacio: 'A" . chr(32) . "B'\n";  // Espacio

// Ejemplo 4: chr + ord son funciones inversas
$letra = "Z";
echo "\nEjemplo 4 (chr + ord son inversas):\n";
echo "chr(ord('$letra')): " . chr(ord($letra)) . "\n"; // Z
echo "ord(chr(90)): " . ord(chr(90)) . "\n"; // 90

// Ejemplo 5: Tabla ASCII parcial
echo "\nEjemplo 5 (Tabla ASCII 32-126):\n";
for ($i = 32; $i <= 126; $i++) {
    printf("  %3d: %s", $i, chr($i));
    if (($i - 31) % 10 === 0) echo "\n";
}
echo "\n";
?>
