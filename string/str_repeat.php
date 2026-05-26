<?php
// Ejemplo 1: Repetir un string
$texto = "ab";
echo "Ejemplo 1 (Repetir 'ab' 3 veces):\n";
echo str_repeat($texto, 3) . "\n"; // ababab

// Ejemplo 2: Crear una línea separadora
$separador = str_repeat("-", 40);
echo "\nEjemplo 2 (Línea separadora):\n";
echo $separador . "\n";

// Ejemplo 3: Crear indentación
$niveles = 3;
$indentacion = str_repeat("  ", $niveles);
echo "\nEjemplo 3 (Indentación):\n";
echo "Nivel 0\n";
echo str_repeat("  ", 1) . "Nivel 1\n";
echo str_repeat("  ", 2) . "Nivel 2\n";
echo str_repeat("  ", 3) . "Nivel 3\n";

// Ejemplo 4: Repetir 0 veces devuelve string vacío
echo "\nEjemplo 4 (Repetir 0 veces):\n";
echo "Resultado: '" . str_repeat("hola", 0) . "'\n";

// Ejemplo 5: Uso práctico - crear un patrón
$patron = str_repeat("*-", 10) . "*";
echo "\nEjemplo 5 (Patrón):\n";
echo $patron . "\n";
?>
