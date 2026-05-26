<?php
// Ejemplo 1: Obtener el valor de PI
echo "Ejemplo 1 (Valor de PI):\n";
echo "pi(): " . pi() . "\n";
echo "M_PI: " . M_PI . "\n"; // Constante equivalente

// Ejemplo 2: Área de un círculo (π * r²)
$radio = 5;
$area = pi() * pow($radio, 2);
echo "\nEjemplo 2 (Área del círculo, radio=$radio):\n";
echo "Área: " . round($area, 2) . "\n";

// Ejemplo 3: Circunferencia (2 * π * r)
$circunferencia = 2 * pi() * $radio;
echo "\nEjemplo 3 (Circunferencia, radio=$radio):\n";
echo "Circunferencia: " . round($circunferencia, 2) . "\n";

// Ejemplo 4: Conversión de grados a radianes
echo "\nEjemplo 4 (Grados a radianes):\n";
$grados = [0, 45, 90, 180, 360];
foreach ($grados as $g) {
    $radianes = $g * pi() / 180;
    echo "  {$g}° = " . round($radianes, 4) . " rad\n";
}

// Ejemplo 5: Otras constantes matemáticas de PHP
echo "\nEjemplo 5 (Constantes matemáticas):\n";
echo "M_PI:      " . M_PI . "\n";      // π
echo "M_E:       " . M_E . "\n";       // e (Euler)
echo "M_SQRT2:   " . M_SQRT2 . "\n";   // √2
echo "M_LN2:     " . M_LN2 . "\n";     // ln(2)
echo "M_LOG2E:   " . M_LOG2E . "\n";   // log₂(e)
?>
