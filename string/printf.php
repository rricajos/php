<?php
// Ejemplo 1: printf imprime directamente (no devuelve string)
echo "Ejemplo 1 (printf vs sprintf):\n";
printf("Hola, %s!\n", "Ana"); // Imprime directamente

// Ejemplo 2: Formatear números
echo "\nEjemplo 2 (Formatear números):\n";
printf("Entero: %d\n", 42);
printf("Float: %.2f\n", 3.14159);
printf("Con signo: %+d\n", 42);
printf("Con signo: %+d\n", -42);

// Ejemplo 3: Tabla formateada con printf
echo "\nEjemplo 3 (Tabla formateada):\n";
$productos = [
    ["Pan", 1.50, 10],
    ["Leche", 0.99, 25],
    ["Queso curado", 3.25, 5]
];
printf("%-15s %8s %5s\n", "Producto", "Precio", "Stock");
printf("%s\n", str_repeat("-", 30));
foreach ($productos as [$nombre, $precio, $stock]) {
    printf("%-15s %7.2f€ %5d\n", $nombre, $precio, $stock);
}

// Ejemplo 4: printf devuelve la longitud del string impreso
echo "\nEjemplo 4 (Valor de retorno):\n";
$longitud = printf("PHP %d.%d\n", 8, 3);
echo "Caracteres impresos: $longitud\n";

// Ejemplo 5: Diferencia entre echo, print, printf, sprintf
echo "\nEjemplo 5 (Comparación):\n";
echo "echo: no formatea\n";
printf("printf: formatea e imprime (%d)\n", 42);
$str = sprintf("sprintf: formatea y devuelve (%d)\n", 42);
echo $str;
?>
