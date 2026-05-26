<?php
// Ejemplo 1: Formatear un objeto DateTime (estilo procedural)
$fecha = date_create('2026-06-15 14:30:00');
echo "Ejemplo 1 (date_format procedural):\n";
echo date_format($fecha, 'd/m/Y') . "\n";
echo date_format($fecha, 'H:i:s') . "\n";
echo date_format($fecha, 'l, d F Y') . "\n";

// Ejemplo 2: Estilo OOP ($fecha->format)
$fecha2 = new DateTime('2026-12-25 10:00:00');
echo "\nEjemplo 2 (Estilo OOP):\n";
echo $fecha2->format('d/m/Y H:i') . "\n";
echo $fecha2->format('D, d M Y') . "\n";

// Ejemplo 3: Diferentes formatos de salida
$ahora = new DateTime();
echo "\nEjemplo 3 (Formatos comunes):\n";
echo "ISO 8601:  " . $ahora->format('c') . "\n";
echo "RFC 2822:  " . $ahora->format('r') . "\n";
echo "MySQL:     " . $ahora->format('Y-m-d H:i:s') . "\n";
echo "Español:   " . $ahora->format('d/m/Y') . "\n";
echo "US:        " . $ahora->format('m/d/Y') . "\n";
echo "Timestamp: " . $ahora->format('U') . "\n";

// Ejemplo 4: Formatear con día de la semana y mes
$navidad = new DateTime('2026-12-25');
echo "\nEjemplo 4 (Navidad 2026):\n";
echo $navidad->format('l') . "\n";   // Día de la semana
echo $navidad->format('F') . "\n";   // Mes completo

// Ejemplo 5: Uso práctico - generar fechas formateadas
$fechas = ['2026-01-01', '2026-06-15', '2026-12-31'];
echo "\nEjemplo 5 (Formatear varias fechas):\n";
foreach ($fechas as $f) {
    $dt = new DateTime($f);
    echo "  " . $dt->format('d M Y (D)') . "\n";
}

// Ejemplo 6: Constantes de formato predefinidas
echo "\nEjemplo 6 (Constantes predefinidas):\n";
echo "ATOM: " . $ahora->format(DateTime::ATOM) . "\n";
echo "W3C:  " . $ahora->format(DateTime::W3C) . "\n";
echo "RSS:  " . $ahora->format(DateTime::RSS) . "\n";
?>
