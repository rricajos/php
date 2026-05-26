<?php
// Ejemplo 1: Raíz cuadrada
echo "Ejemplo 1 (Raíz cuadrada):\n";
echo "sqrt(4): " . sqrt(4) . "\n";     // 2
echo "sqrt(9): " . sqrt(9) . "\n";     // 3
echo "sqrt(16): " . sqrt(16) . "\n";   // 4
echo "sqrt(2): " . sqrt(2) . "\n";     // 1.4142135623731

// Ejemplo 2: Raíz cuadrada de 0 y 1
echo "\nEjemplo 2 (Casos especiales):\n";
echo "sqrt(0): " . sqrt(0) . "\n"; // 0
echo "sqrt(1): " . sqrt(1) . "\n"; // 1

// Ejemplo 3: Número negativo retorna NAN
echo "\nEjemplo 3 (Número negativo):\n";
$resultado = sqrt(-4);
echo "sqrt(-4): ";
var_dump($resultado); // float(NAN)
echo "¿Es NAN? " . (is_nan($resultado) ? "Sí" : "No") . "\n";

// Ejemplo 4: Uso práctico - distancia entre dos puntos (Pitágoras)
$x1 = 0; $y1 = 0;
$x2 = 3; $y2 = 4;
$distancia = sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
echo "\nEjemplo 4 (Distancia euclidiana):\n";
echo "Punto A: ($x1, $y1)\n";
echo "Punto B: ($x2, $y2)\n";
echo "Distancia: $distancia\n"; // 5

// Ejemplo 5: Verificar si un número es cuadrado perfecto
$numeros = [4, 7, 9, 15, 16, 25, 30];
echo "\nEjemplo 5 (Cuadrados perfectos):\n";
foreach ($numeros as $n) {
    $raiz = sqrt($n);
    $esPerfecto = $raiz == floor($raiz);
    echo "  $n: " . ($esPerfecto ? "Sí (√=$raiz)" : "No") . "\n";
}
?>
