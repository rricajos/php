<?php
// Ejemplo 1: Mínimo de dos valores
echo "Ejemplo 1 (Mínimo de dos valores):\n";
echo "min(3, 7): " . min(3, 7) . "\n"; // 3

// Ejemplo 2: Mínimo de múltiples valores
echo "\nEjemplo 2 (Múltiples valores):\n";
echo "min(10, 5, 3, 9, 2): " . min(10, 5, 3, 9, 2) . "\n"; // 2

// Ejemplo 3: Mínimo de un array
$precios = [19.99, 5.50, 12.75, 3.99, 8.00];
echo "\nEjemplo 3 (Precio más bajo):\n";
echo "Precio más bajo: " . min($precios) . "€\n";

// Ejemplo 4: Con valores negativos
echo "\nEjemplo 4 (Valores negativos):\n";
echo "min(-5, -1, -10): " . min(-5, -1, -10) . "\n"; // -10

// Ejemplo 5: Uso práctico - limitar un valor máximo
$porcentaje = 120;
$resultado = min(100, $porcentaje); // No permitir más de 100%
echo "\nEjemplo 5 (Limitar valor máximo a 100):\n";
echo "min(100, $porcentaje): $resultado\n";

// Ejemplo 6: Uso práctico - clamp (limitar entre min y max)
function clamp(int|float $valor, int|float $min, int|float $max): int|float {
    return max($min, min($max, $valor));
}
echo "\nEjemplo 6 (Clamp entre 0 y 100):\n";
echo "clamp(50, 0, 100): " . clamp(50, 0, 100) . "\n";   // 50
echo "clamp(-20, 0, 100): " . clamp(-20, 0, 100) . "\n"; // 0
echo "clamp(150, 0, 100): " . clamp(150, 0, 100) . "\n"; // 100
?>
