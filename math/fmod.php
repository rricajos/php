<?php
// Ejemplo 1: Resto de división con floats
echo "Ejemplo 1 (Resto float):\n";
echo "fmod(10.5, 3.2): " . fmod(10.5, 3.2) . "\n"; // 0.9
echo "fmod(5, 2): " . fmod(5, 2) . "\n";             // 1

// Ejemplo 2: Comparar fmod con operador %
echo "\nEjemplo 2 (fmod vs %):\n";
echo "10 % 3: " . (10 % 3) . "\n";           // 1 (solo enteros)
echo "fmod(10, 3): " . fmod(10, 3) . "\n";   // 1 (funciona con floats)
echo "fmod(10.5, 3): " . fmod(10.5, 3) . "\n"; // 1.5

// Ejemplo 3: Con números negativos
echo "\nEjemplo 3 (Negativos):\n";
echo "fmod(7, 3): " . fmod(7, 3) . "\n";     // 1
echo "fmod(-7, 3): " . fmod(-7, 3) . "\n";   // -1
echo "fmod(7, -3): " . fmod(7, -3) . "\n";   // 1

// Ejemplo 4: Uso práctico - verificar si es múltiplo
function esMultiplo(float $n, float $de): bool {
    return fmod($n, $de) == 0;
}
echo "\nEjemplo 4 (Verificar múltiplos):\n";
echo "10.5 es múltiplo de 3.5: " . (esMultiplo(10.5, 3.5) ? "Sí" : "No") . "\n"; // Sí
echo "10.5 es múltiplo de 3.0: " . (esMultiplo(10.5, 3.0) ? "Sí" : "No") . "\n"; // No
?>
