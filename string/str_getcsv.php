<?php
// Ejemplo 1: Parsear una línea CSV
$csv = "Ana,30,Madrid";
$datos = str_getcsv($csv);
echo "Ejemplo 1 (Parsear CSV simple):\n";
print_r($datos);

// Ejemplo 2: CSV con comillas (campos con comas dentro)
$csv2 = '"García, Ana",30,"Madrid, España"';
$datos2 = str_getcsv($csv2);
echo "\nEjemplo 2 (Campos entrecomillados):\n";
print_r($datos2);

// Ejemplo 3: Separador personalizado
$tsv = "Ana\t30\tMadrid";
$datos3 = str_getcsv($tsv, "\t");
echo "\nEjemplo 3 (TSV con tab):\n";
print_r($datos3);

// Ejemplo 4: Separador punto y coma (formato europeo)
$csv4 = "Ana;30;Madrid";
$datos4 = str_getcsv($csv4, ";");
echo "\nEjemplo 4 (Separador ;):\n";
print_r($datos4);

// Ejemplo 5: Uso práctico - parsear múltiples líneas CSV
$csv_completo = "nombre,edad,ciudad\nAna,30,Madrid\nCarlos,25,Barcelona\nMarta,35,Sevilla";
$lineas = explode("\n", $csv_completo);
$cabeceras = str_getcsv(array_shift($lineas));
echo "\nEjemplo 5 (Parsear CSV completo):\n";
foreach ($lineas as $linea) {
    $fila = array_combine($cabeceras, str_getcsv($linea));
    print_r($fila);
}
?>
