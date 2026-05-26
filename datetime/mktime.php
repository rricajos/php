<?php
// Ejemplo 1: Crear un timestamp para una fecha específica
$timestamp = mktime(10, 30, 0, 6, 15, 2026);
echo "Ejemplo 1 (Crear timestamp):\n";
echo "15/06/2026 10:30:00 -> timestamp: $timestamp\n";
echo "Verificar: " . date('d/m/Y H:i:s', $timestamp) . "\n";

// Ejemplo 2: mktime con parámetros por defecto
echo "\nEjemplo 2 (Parámetros: hora, minuto, segundo, mes, día, año):\n";
echo "mktime(0, 0, 0, 1, 1, 2026): " . date('d/m/Y', mktime(0, 0, 0, 1, 1, 2026)) . "\n";
echo "mktime(23, 59, 59, 12, 31, 2026): " . date('d/m/Y H:i:s', mktime(23, 59, 59, 12, 31, 2026)) . "\n";

// Ejemplo 3: mktime maneja desbordamientos
echo "\nEjemplo 3 (Desbordamiento automático):\n";
echo "Mes 13 de 2025 = " . date('d/m/Y', mktime(0, 0, 0, 13, 1, 2025)) . "\n"; // Enero 2026
echo "Día 32 de enero = " . date('d/m/Y', mktime(0, 0, 0, 1, 32, 2026)) . "\n"; // 1 Feb 2026
echo "Hora 25 = " . date('H:i d/m/Y', mktime(25, 0, 0, 1, 1, 2026)) . "\n"; // 01:00 02/01/2026

// Ejemplo 4: Calcular el último día de un mes
$anio = 2026;
$mes = 2;
$ultimo_dia = mktime(0, 0, 0, $mes + 1, 0, $anio); // Día 0 del mes siguiente
echo "\nEjemplo 4 (Último día de febrero $anio):\n";
echo date('d/m/Y', $ultimo_dia) . " (día " . date('d', $ultimo_dia) . ")\n";

// Ejemplo 5: Uso práctico - generar fechas de un rango
echo "\nEjemplo 5 (Primer día de cada mes de 2026):\n";
for ($m = 1; $m <= 12; $m++) {
    echo "  " . date('d/m/Y (l)', mktime(0, 0, 0, $m, 1, 2026)) . "\n";
}
?>
