<?php
// Ejemplo 1: Formato básico con %s (string)
$nombre = "Ana";
$resultado = sprintf("Hola, %s!", $nombre);
echo "Ejemplo 1 (String %s):\n";
echo $resultado . "\n";

// Ejemplo 2: Formato con %d (entero)
$edad = 30;
echo "\nEjemplo 2 (Entero %d):\n";
echo sprintf("%s tiene %d años", $nombre, $edad) . "\n";

// Ejemplo 3: Formato con %f (float) y precisión
$precio = 19.99;
echo "\nEjemplo 3 (Float %f):\n";
echo sprintf("Precio: %.2f€", $precio) . "\n"; // 19.99€
echo sprintf("Precio: %.0f€", $precio) . "\n"; // 20€

// Ejemplo 4: Rellenar con ceros (%05d)
echo "\nEjemplo 4 (Rellenar con ceros):\n";
echo sprintf("ID: %05d", 42) . "\n"; // ID: 00042
echo sprintf("ID: %05d", 12345) . "\n"; // ID: 12345

// Ejemplo 5: Alineación y ancho
echo "\nEjemplo 5 (Alineación):\n";
echo sprintf("|%-20s|%10d|", "Producto", 100) . "\n"; // Izquierda y derecha
echo sprintf("|%-20s|%10d|", "Pan", 2) . "\n";
echo sprintf("|%-20s|%10d|", "Queso", 350) . "\n";

// Ejemplo 6: Formato hexadecimal y octal
$num = 255;
echo "\nEjemplo 6 (Bases numéricas):\n";
echo sprintf("Decimal: %d", $num) . "\n";
echo sprintf("Hexadecimal: %x", $num) . "\n";
echo sprintf("Hexadecimal (mayús): %X", $num) . "\n";
echo sprintf("Octal: %o", $num) . "\n";
echo sprintf("Binario: %b", $num) . "\n";

// Ejemplo 7: Argumentos posicionales
echo "\nEjemplo 7 (Argumentos posicionales):\n";
echo sprintf('%2$s tiene %1$d años', 30, 'Ana') . "\n";
?>
