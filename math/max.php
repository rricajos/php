<?php
// Ejemplo 1: Máximo de dos valores
echo "Ejemplo 1 (Máximo de dos valores):\n";
echo "max(3, 7): " . max(3, 7) . "\n"; // 7

// Ejemplo 2: Máximo de múltiples valores
echo "\nEjemplo 2 (Múltiples valores):\n";
echo "max(1, 5, 3, 9, 2): " . max(1, 5, 3, 9, 2) . "\n"; // 9

// Ejemplo 3: Máximo de un array
$notas = [7, 9, 5, 8, 10, 6];
echo "\nEjemplo 3 (Máximo de un array):\n";
echo "Nota más alta: " . max($notas) . "\n"; // 10

// Ejemplo 4: Con valores negativos
echo "\nEjemplo 4 (Valores negativos):\n";
echo "max(-5, -1, -10): " . max(-5, -1, -10) . "\n"; // -1

// Ejemplo 5: Uso práctico - limitar un valor mínimo
$cantidad = -5;
$resultado = max(0, $cantidad); // No permitir negativos
echo "\nEjemplo 5 (Limitar valor mínimo a 0):\n";
echo "max(0, $cantidad): $resultado\n";

// Ejemplo 6: Comparar max con min
$valores = [15, 3, 42, 8, 23];
echo "\nEjemplo 6 (max vs min):\n";
echo "max: " . max($valores) . "\n";
echo "min: " . min($valores) . "\n";
?>
