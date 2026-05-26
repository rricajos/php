<?php
// Ejemplo 1: División entera
echo "Ejemplo 1 (División entera):\n";
echo "intdiv(7, 2): " . intdiv(7, 2) . "\n"; // 3
echo "intdiv(10, 3): " . intdiv(10, 3) . "\n"; // 3

// Ejemplo 2: Comparar con división normal
echo "\nEjemplo 2 (intdiv vs /):\n";
echo "7 / 2: " . (7 / 2) . "\n";           // 3.5
echo "intdiv(7, 2): " . intdiv(7, 2) . "\n"; // 3
echo "(int)(7/2): " . (int)(7/2) . "\n";     // 3

// Ejemplo 3: Con negativos
echo "\nEjemplo 3 (Negativos):\n";
echo "intdiv(7, -2): " . intdiv(7, -2) . "\n";   // -3
echo "intdiv(-7, 2): " . intdiv(-7, 2) . "\n";   // -3
echo "intdiv(-7, -2): " . intdiv(-7, -2) . "\n"; // 3

// Ejemplo 4: Uso práctico - convertir minutos a horas y minutos
$total_minutos = 145;
$horas = intdiv($total_minutos, 60);
$minutos = $total_minutos % 60;
echo "\nEjemplo 4 (Convertir minutos):\n";
echo "$total_minutos minutos = {$horas}h {$minutos}min\n";

// Ejemplo 5: Uso práctico - convertir segundos a formato legible
$total_seg = 3661;
$h = intdiv($total_seg, 3600);
$m = intdiv($total_seg % 3600, 60);
$s = $total_seg % 60;
echo "\nEjemplo 5 (Segundos a H:M:S):\n";
echo "$total_seg segundos = {$h}h {$m}m {$s}s\n";
?>
