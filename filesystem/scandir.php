<?php
// Ejemplo 1: Listar contenido de un directorio
echo "Ejemplo 1 (Listar directorio actual):\n";
$items = scandir(__DIR__);
foreach ($items as $item) {
    echo "  $item\n";
}

// Ejemplo 2: Orden descendente
echo "\nEjemplo 2 (Orden descendente):\n";
$items = scandir(__DIR__, SCANDIR_SORT_DESCENDING);
foreach (array_slice($items, 0, 5) as $item) {
    echo "  $item\n";
}

// Ejemplo 3: Filtrar . y ..
echo "\nEjemplo 3 (Sin . y ..):\n";
$items = array_diff(scandir(__DIR__), ['.', '..']);
foreach (array_slice($items, 0, 5) as $item) {
    echo "  $item\n";
}

// Ejemplo 4: Separar archivos y directorios
echo "\nEjemplo 4 (Separar archivos y directorios):\n";
$parent = dirname(__DIR__);
$todos = array_diff(scandir($parent), ['.', '..']);
$dirs = $files = [];
foreach ($todos as $item) {
    if (is_dir($parent . '/' . $item)) {
        $dirs[] = $item;
    } else {
        $files[] = $item;
    }
}
echo "Directorios: " . implode(", ", $dirs) . "\n";
echo "Archivos: " . implode(", ", $files) . "\n";

// Ejemplo 5: Contar archivos PHP en el directorio
echo "\nEjemplo 5 (Archivos .php):\n";
$php_files = array_filter(scandir(__DIR__), function($f) {
    return str_ends_with($f, '.php');
});
echo "Archivos PHP: " . count($php_files) . "\n";
?>
