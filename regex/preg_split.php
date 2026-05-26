<?php
// Ejemplo 1: Dividir por uno o más espacios
$texto = "Hola   mundo   PHP";
$palabras = preg_split("/\s+/", $texto);
echo "Ejemplo 1 (Dividir por espacios múltiples):\n";
print_r($palabras);

// Ejemplo 2: Dividir por múltiples delimitadores
$datos = "uno,dos;tres|cuatro";
$partes = preg_split("/[,;|]/", $datos);
echo "\nEjemplo 2 (Múltiples delimitadores):\n";
print_r($partes);

// Ejemplo 3: Dividir manteniendo los delimitadores
$texto3 = "Hola. Mundo! Cómo? Estás";
$partes = preg_split("/([.!?])/", $texto3, -1, PREG_SPLIT_DELIM_CAPTURE);
echo "\nEjemplo 3 (Mantener delimitadores):\n";
print_r($partes);

// Ejemplo 4: Dividir en caracteres (alternativa a str_split)
$palabra = "Hola";
$chars = preg_split("//u", $palabra, -1, PREG_SPLIT_NO_EMPTY);
echo "\nEjemplo 4 (Dividir en caracteres UTF-8):\n";
print_r($chars);

// Ejemplo 5: Limitar número de divisiones
$csv = "uno,dos,tres,cuatro,cinco";
$partes = preg_split("/,/", $csv, 3);
echo "\nEjemplo 5 (Límite de 3 divisiones):\n";
print_r($partes);

// Ejemplo 6: Dividir por camelCase
$camelCase = "getUserNameFromDB";
$partes = preg_split("/(?=[A-Z])/", $camelCase);
echo "\nEjemplo 6 (Dividir camelCase):\n";
print_r(array_filter($partes));
echo "snake_case: " . strtolower(implode("_", array_filter($partes))) . "\n";
?>
