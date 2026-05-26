<?php
// Ejemplo 1: Calcular diferencia entre dos fechas
$fecha1 = new DateTime('2026-01-01');
$fecha2 = new DateTime('2026-12-31');
$diff = date_diff($fecha1, $fecha2);
echo "Ejemplo 1 (Diferencia entre fechas):\n";
echo "Días: " . $diff->days . "\n"; // 364
echo "Formato: " . $diff->format('%y años, %m meses, %d días') . "\n";

// Ejemplo 2: Calcular edad
$nacimiento = new DateTime('1995-08-15');
$hoy = new DateTime();
$edad = date_diff($nacimiento, $hoy);
echo "\nEjemplo 2 (Calcular edad):\n";
echo "Edad: " . $edad->y . " años, " . $edad->m . " meses, " . $edad->d . " días\n";

// Ejemplo 3: Diferencia con horas, minutos, segundos
$inicio = new DateTime('2026-05-26 08:00:00');
$fin = new DateTime('2026-05-26 17:30:45');
$diff3 = date_diff($inicio, $fin);
echo "\nEjemplo 3 (Con horas):\n";
echo $diff3->format('%h horas, %i minutos, %s segundos') . "\n";

// Ejemplo 4: Diferencia negativa (fecha2 anterior a fecha1)
$pasado = new DateTime('2020-01-01');
$futuro = new DateTime('2026-12-31');
$diff4 = date_diff($futuro, $pasado);
echo "\nEjemplo 4 (Diferencia negativa):\n";
echo "Invertido (invert): " . $diff4->invert . "\n"; // 1 = negativo
echo "Días: " . $diff4->days . "\n";

// Ejemplo 5: Estilo OOP (método ->diff)
echo "\nEjemplo 5 (Estilo OOP):\n";
$d1 = new DateTime('2026-03-01');
$d2 = new DateTime('2026-06-15');
$diff5 = $d1->diff($d2);
echo $diff5->format('De %m meses y %d días') . "\n";

// Ejemplo 6: Uso práctico - cuenta atrás
$objetivo = new DateTime('2027-01-01');
$ahora = new DateTime();
$falta = date_diff($ahora, $objetivo);
echo "\nEjemplo 6 (Cuenta atrás para Año Nuevo 2027):\n";
echo "Faltan: " . $falta->format('%m meses y %d días') . "\n";
?>
