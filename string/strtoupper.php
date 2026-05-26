<?php
// Ejemplo 1: Convertir a mayúsculas
$texto = "hola mundo";
echo "Ejemplo 1 (Convertir a mayúsculas):\n";
echo strtoupper($texto) . "\n"; // HOLA MUNDO

// Ejemplo 2: String mixto
$mixto = "HoLa MuNdO";
echo "\nEjemplo 2 (String mixto):\n";
echo strtoupper($mixto) . "\n"; // HOLA MUNDO

// Ejemplo 3: Uso práctico - normalizar códigos
$codigos = ["abc", "DEF", "gHi"];
echo "\nEjemplo 3 (Normalizar códigos):\n";
foreach ($codigos as $codigo) {
    echo strtoupper($codigo) . "\n";
}

// Ejemplo 4: Solo afecta a caracteres alfabéticos
$con_simbolos = "hola@mundo.com";
echo "\nEjemplo 4 (Con símbolos):\n";
echo strtoupper($con_simbolos) . "\n"; // HOLA@MUNDO.COM

// Ejemplo 5: Para UTF-8 usar mb_strtoupper
$utf8 = "café";
echo "\nEjemplo 5 (UTF-8):\n";
echo "strtoupper: " . strtoupper($utf8) . "\n";
echo "mb_strtoupper: " . mb_strtoupper($utf8) . "\n";
?>
