<?php
// Ejemplo 1: Crear un objeto DateTime con date_create (estilo procedural)
$fecha = date_create('2026-06-15');
echo "Ejemplo 1 (Crear DateTime):\n";
echo date_format($fecha, 'd/m/Y') . "\n";

// Ejemplo 2: Equivalente OOP con new DateTime
$fecha2 = new DateTime('2026-06-15');
echo "\nEjemplo 2 (Estilo OOP):\n";
echo $fecha2->format('d/m/Y') . "\n";

// Ejemplo 3: Crear con diferentes formatos de entrada
echo "\nEjemplo 3 (Diferentes formatos):\n";
echo date_format(date_create('now'), 'd/m/Y H:i:s') . "\n";
echo date_format(date_create('tomorrow'), 'd/m/Y') . "\n";
echo date_format(date_create('+1 week'), 'd/m/Y') . "\n";
echo date_format(date_create('last day of December 2026'), 'd/m/Y') . "\n";

// Ejemplo 4: date_create_from_format (parsear formato específico)
$fecha4 = date_create_from_format('d/m/Y', '15/06/2026');
echo "\nEjemplo 4 (Desde formato específico):\n";
echo date_format($fecha4, 'Y-m-d') . "\n";

// Ejemplo 5: Fecha con zona horaria
$madrid = date_create('now', new DateTimeZone('Europe/Madrid'));
$tokyo = date_create('now', new DateTimeZone('Asia/Tokyo'));
$ny = date_create('now', new DateTimeZone('America/New_York'));
echo "\nEjemplo 5 (Zonas horarias):\n";
echo "Madrid: " . date_format($madrid, 'H:i:s') . "\n";
echo "Tokyo:  " . date_format($tokyo, 'H:i:s') . "\n";
echo "New York: " . date_format($ny, 'H:i:s') . "\n";

// Ejemplo 6: date_create devuelve false si la fecha es inválida
echo "\nEjemplo 6 (Fecha inválida):\n";
$invalida = date_create('no es una fecha');
var_dump($invalida); // false en algunas versiones, o DateTime con warnings
?>
