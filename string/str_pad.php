<?php
// Ejemplo 1: Rellenar por la derecha (por defecto)
$texto = "Hola";
echo "Ejemplo 1 (Relleno derecha):\n";
echo "'" . str_pad($texto, 10) . "'\n"; // 'Hola      '

// Ejemplo 2: Rellenar por la izquierda
echo "\nEjemplo 2 (Relleno izquierda):\n";
echo "'" . str_pad($texto, 10, " ", STR_PAD_LEFT) . "'\n"; // '      Hola'

// Ejemplo 3: Rellenar por ambos lados
echo "\nEjemplo 3 (Relleno ambos lados):\n";
echo "'" . str_pad($texto, 10, " ", STR_PAD_BOTH) . "'\n"; // '   Hola   '

// Ejemplo 4: Rellenar con un carácter personalizado
$numero = "42";
echo "\nEjemplo 4 (Rellenar con ceros):\n";
echo str_pad($numero, 5, "0", STR_PAD_LEFT) . "\n"; // 00042

// Ejemplo 5: Rellenar con un string multi-carácter
echo "\nEjemplo 5 (Relleno multi-carácter):\n";
echo str_pad("test", 15, ".-") . "\n"; // test.-.-.-.-.-

// Ejemplo 6: Uso práctico - formatear una tabla
$productos = [
    ["Pan", "1.50€"],
    ["Leche", "0.99€"],
    ["Queso curado", "3.25€"]
];
echo "\nEjemplo 6 (Tabla formateada):\n";
echo str_repeat("-", 30) . "\n";
foreach ($productos as [$nombre, $precio]) {
    echo str_pad($nombre, 20, ".") . str_pad($precio, 10, " ", STR_PAD_LEFT) . "\n";
}
echo str_repeat("-", 30) . "\n";
?>
