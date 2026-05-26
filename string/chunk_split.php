<?php
// Ejemplo 1: Dividir un string insertando separador cada N caracteres
$texto = "abcdefghijklmnop";
echo "Ejemplo 1 (Separador cada 4 caracteres):\n";
echo chunk_split($texto, 4, "-") . "\n"; // abcd-efgh-ijkl-mnop-

// Ejemplo 2: Comportamiento por defecto (76 caracteres, \r\n)
$base64 = base64_encode("Este es un texto largo que se codifica en base64 para demostrar chunk_split");
echo "\nEjemplo 2 (Base64 formateado por defecto):\n";
echo chunk_split($base64) . "\n";

// Ejemplo 3: Separar cada carácter
$palabra = "HOLA";
echo "Ejemplo 3 (Separar cada carácter):\n";
echo chunk_split($palabra, 1, ".") . "\n"; // H.O.L.A.

// Ejemplo 4: Formatear un número largo
$numero = "1234567890";
echo "\nEjemplo 4 (Formatear número):\n";
echo chunk_split($numero, 3, " ") . "\n"; // 123 456 789 0

// Ejemplo 5: Diferencia entre chunk_split y wordwrap
$texto5 = "ABCDEFGHIJKLMNOP";
echo "\nEjemplo 5 (chunk_split vs str_split + implode):\n";
echo "chunk_split: " . chunk_split($texto5, 4, "-");
echo "str_split + implode: " . implode("-", str_split($texto5, 4)) . "\n";
// chunk_split añade el separador también al final
?>
