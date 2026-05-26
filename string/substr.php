<?php
// Ejemplo 1: Extraer una subcadena desde una posición
$texto = "Hola mundo PHP";
echo "Ejemplo 1 (Desde posición 5):\n";
echo substr($texto, 5) . "\n"; // mundo PHP

// Ejemplo 2: Extraer con longitud específica
echo "\nEjemplo 2 (Desde posición 5, longitud 5):\n";
echo substr($texto, 5, 5) . "\n"; // mundo

// Ejemplo 3: Posición negativa (desde el final)
echo "\nEjemplo 3 (Últimos 3 caracteres):\n";
echo substr($texto, -3) . "\n"; // PHP

// Ejemplo 4: Longitud negativa (hasta N caracteres antes del final)
echo "\nEjemplo 4 (Quitar últimos 4 caracteres):\n";
echo substr($texto, 0, -4) . "\n"; // Hola mundo

// Ejemplo 5: Extraer un solo carácter
echo "\nEjemplo 5 (Carácter en posición 0):\n";
echo substr($texto, 0, 1) . "\n"; // H

// Ejemplo 6: Uso práctico - obtener extensión de archivo
$archivo = "documento.pdf";
$extension = substr($archivo, strrpos($archivo, ".") + 1);
echo "\nEjemplo 6 (Extensión de archivo):\n";
echo "Archivo: $archivo\n";
echo "Extensión: $extension\n";

// Ejemplo 7: Truncar texto con puntos suspensivos
$largo = "Este es un texto muy largo que necesita ser truncado";
$max = 20;
$truncado = strlen($largo) > $max ? substr($largo, 0, $max) . "..." : $largo;
echo "\nEjemplo 7 (Truncar texto):\n";
echo "$truncado\n";
?>
