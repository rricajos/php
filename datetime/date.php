<?php
// Ejemplo 1: Formato básico de fecha
echo "Ejemplo 1 (Formato básico):\n";
echo "date('Y-m-d'): " . date('Y-m-d') . "\n";
echo "date('d/m/Y'): " . date('d/m/Y') . "\n";
echo "date('H:i:s'): " . date('H:i:s') . "\n";

// Ejemplo 2: Caracteres de formato comunes
echo "\nEjemplo 2 (Caracteres de formato):\n";
echo "Y (año 4 dígitos): " . date('Y') . "\n";
echo "y (año 2 dígitos): " . date('y') . "\n";
echo "m (mes con cero): " . date('m') . "\n";
echo "n (mes sin cero): " . date('n') . "\n";
echo "d (día con cero): " . date('d') . "\n";
echo "j (día sin cero): " . date('j') . "\n";
echo "H (hora 24h): " . date('H') . "\n";
echo "h (hora 12h): " . date('h') . "\n";
echo "i (minutos): " . date('i') . "\n";
echo "s (segundos): " . date('s') . "\n";
echo "A (AM/PM): " . date('A') . "\n";

// Ejemplo 3: Nombres de día y mes
echo "\nEjemplo 3 (Nombres):\n";
echo "l (día semana): " . date('l') . "\n";
echo "D (día abreviado): " . date('D') . "\n";
echo "F (mes completo): " . date('F') . "\n";
echo "M (mes abreviado): " . date('M') . "\n";

// Ejemplo 4: Formatear un timestamp específico
$timestamp = mktime(15, 30, 0, 12, 25, 2026);
echo "\nEjemplo 4 (Timestamp específico - Navidad 2026):\n";
echo date('d/m/Y H:i:s', $timestamp) . "\n";

// Ejemplo 5: Información adicional
echo "\nEjemplo 5 (Información adicional):\n";
echo "N (día semana 1-7): " . date('N') . "\n";
echo "W (semana del año): " . date('W') . "\n";
echo "z (día del año): " . date('z') . "\n";
echo "t (días en el mes): " . date('t') . "\n";
echo "L (año bisiesto): " . date('L') . "\n";
echo "U (timestamp Unix): " . date('U') . "\n";

// Ejemplo 6: Formato ISO 8601 y RFC 2822
echo "\nEjemplo 6 (Formatos estándar):\n";
echo "ISO 8601: " . date('c') . "\n";
echo "RFC 2822: " . date('r') . "\n";
?>
