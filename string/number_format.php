<?php
// Ejemplo 1: Formatear con separador de miles
$numero = 1234567.891;
echo "Ejemplo 1 (Formato por defecto):\n";
echo number_format($numero) . "\n"; // 1,234,568

// Ejemplo 2: Con decimales
echo "\nEjemplo 2 (Con 2 decimales):\n";
echo number_format($numero, 2) . "\n"; // 1,234,567.89

// Ejemplo 3: Formato europeo (coma decimal, punto miles)
echo "\nEjemplo 3 (Formato europeo):\n";
echo number_format($numero, 2, ",", ".") . "\n"; // 1.234.567,89

// Ejemplo 4: Sin separador de miles
echo "\nEjemplo 4 (Sin separador de miles):\n";
echo number_format($numero, 2, ".", "") . "\n"; // 1234567.89

// Ejemplo 5: Formatear precios
$precios = [9.9, 100, 1499.5, 0.5];
echo "\nEjemplo 5 (Formatear precios):\n";
foreach ($precios as $precio) {
    echo "  " . number_format($precio, 2, ",", ".") . " €\n";
}

// Ejemplo 6: Redondeo automático
echo "\nEjemplo 6 (Redondeo):\n";
echo number_format(1.555, 2) . "\n"; // 1.56
echo number_format(1.545, 2) . "\n"; // 1.54
echo number_format(1.5, 0) . "\n";   // 2
?>
