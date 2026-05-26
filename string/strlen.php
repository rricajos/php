<?php
// Ejemplo 1: Longitud de un string simple
$texto = "Hola mundo";
echo "Ejemplo 1 (String simple):\n";
echo "Longitud de '$texto': " . strlen($texto) . "\n"; // 10

// Ejemplo 2: String vacío
$vacio = "";
echo "\nEjemplo 2 (String vacío):\n";
echo "Longitud: " . strlen($vacio) . "\n"; // 0

// Ejemplo 3: String con espacios
$espacios = "  hola  ";
echo "\nEjemplo 3 (Con espacios):\n";
echo "Longitud de '$espacios': " . strlen($espacios) . "\n"; // 8

// Ejemplo 4: String con caracteres especiales (UTF-8)
$utf8 = "café";
echo "\nEjemplo 4 (UTF-8):\n";
echo "strlen: " . strlen($utf8) . "\n"; // 5 (strlen cuenta bytes, no caracteres)
echo "mb_strlen: " . mb_strlen($utf8) . "\n"; // 4 (mb_strlen cuenta caracteres)

// Ejemplo 5: Uso práctico - validar longitud mínima
$password = "abc";
$min = 8;
echo "\nEjemplo 5 (Validar longitud mínima):\n";
if (strlen($password) < $min) {
    echo "La contraseña debe tener al menos $min caracteres (tiene " . strlen($password) . ")\n";
}
?>
