<?php
// Ejemplo 1: Convertir a minúsculas
$texto = "HOLA MUNDO";
echo "Ejemplo 1 (Convertir a minúsculas):\n";
echo strtolower($texto) . "\n"; // hola mundo

// Ejemplo 2: String mixto
$mixto = "HoLa MuNdO";
echo "\nEjemplo 2 (String mixto):\n";
echo strtolower($mixto) . "\n"; // hola mundo

// Ejemplo 3: Uso práctico - comparar sin distinción de mayúsculas
$input = "PHP";
$esperado = "php";
echo "\nEjemplo 3 (Comparar sin case):\n";
if (strtolower($input) === $esperado) {
    echo "'$input' coincide con '$esperado'\n";
}

// Ejemplo 4: Solo afecta a caracteres alfabéticos
$con_numeros = "ABC123DEF";
echo "\nEjemplo 4 (Con números):\n";
echo strtolower($con_numeros) . "\n"; // abc123def

// Ejemplo 5: Para UTF-8 usar mb_strtolower
$utf8 = "CAFÉ";
echo "\nEjemplo 5 (UTF-8):\n";
echo "strtolower: " . strtolower($utf8) . "\n";
echo "mb_strtolower: " . mb_strtolower($utf8) . "\n";
?>
