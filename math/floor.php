<?php
// Ejemplo 1: Redondear hacia abajo
echo "Ejemplo 1 (Redondear abajo):\n";
echo "floor(4.9): " . floor(4.9) . "\n"; // 4
echo "floor(4.1): " . floor(4.1) . "\n"; // 4
echo "floor(4.0): " . floor(4.0) . "\n"; // 4

// Ejemplo 2: Con negativos
echo "\nEjemplo 2 (Negativos):\n";
echo "floor(-4.1): " . floor(-4.1) . "\n"; // -5
echo "floor(-4.9): " . floor(-4.9) . "\n"; // -5

// Ejemplo 3: Uso práctico - calcular edad a partir de la fecha
$nacimiento = new DateTime('1995-08-15');
$hoy = new DateTime();
$diferencia = $nacimiento->diff($hoy);
$edad = floor($diferencia->y);
echo "\nEjemplo 3 (Calcular edad):\n";
echo "Edad: $edad años\n";

// Ejemplo 4: Uso práctico - truncar a 2 decimales sin redondear
$precio = 19.999;
$truncado = floor($precio * 100) / 100;
echo "\nEjemplo 4 (Truncar decimales):\n";
echo "Original: $precio\n";
echo "Truncado a 2 dec: $truncado\n"; // 19.99

// Ejemplo 5: Comparar ceil vs floor
echo "\nEjemplo 5 (ceil vs floor):\n";
foreach ([1.2, 2.5, 3.8, -1.2, -2.5] as $n) {
    echo "  $n -> ceil: " . ceil($n) . ", floor: " . floor($n) . "\n";
}
?>
