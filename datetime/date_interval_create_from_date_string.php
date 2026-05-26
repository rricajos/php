<?php
// Ejemplo 1: Crear un intervalo desde un string legible
$intervalo = date_interval_create_from_date_string('1 year 2 months 3 days');
echo "Ejemplo 1 (Crear intervalo):\n";
echo $intervalo->format('%y años, %m meses, %d días') . "\n";

// Ejemplo 2: Sumar un intervalo a una fecha
$fecha = new DateTime('2026-01-01');
$intervalo2 = date_interval_create_from_date_string('6 months');
$fecha->add($intervalo2);
echo "\nEjemplo 2 (Sumar 6 meses a 01/01/2026):\n";
echo $fecha->format('d/m/Y') . "\n"; // 01/07/2026

// Ejemplo 3: Restar un intervalo
$fecha3 = new DateTime('2026-06-15');
$intervalo3 = date_interval_create_from_date_string('2 weeks 3 days');
$fecha3->sub($intervalo3);
echo "\nEjemplo 3 (Restar 2 semanas y 3 días):\n";
echo $fecha3->format('d/m/Y') . "\n";

// Ejemplo 4: Diferentes unidades de tiempo
echo "\nEjemplo 4 (Diferentes unidades):\n";
$unidades = ['1 year', '6 months', '2 weeks', '10 days', '5 hours', '30 minutes'];
$base = new DateTime('2026-01-01 00:00:00');
foreach ($unidades as $u) {
    $fecha = clone $base;
    $fecha->add(date_interval_create_from_date_string($u));
    echo "  +$u: " . $fecha->format('d/m/Y H:i') . "\n";
}

// Ejemplo 5: Comparar con new DateInterval (formato ISO 8601)
echo "\nEjemplo 5 (Comparar formas de crear intervalos):\n";
$legible = date_interval_create_from_date_string('1 year 6 months');
$iso = new DateInterval('P1Y6M'); // P=Period, 1Y=1 año, 6M=6 meses
echo "Legible: " . $legible->format('%y años, %m meses') . "\n";
echo "ISO 8601: " . $iso->format('%y años, %m meses') . "\n";
?>
