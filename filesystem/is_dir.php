<?php
// Ejemplo 1: Verificar si es un directorio
echo "Ejemplo 1 (is_dir):\n";
echo __DIR__ . ": " . (is_dir(__DIR__) ? "Es directorio" : "No") . "\n";
echo __FILE__ . ": " . (is_dir(__FILE__) ? "Es directorio" : "No") . "\n";

// Ejemplo 2: Directorio inexistente
echo "\nEjemplo 2 (Inexistente):\n";
echo "is_dir('/ruta/falsa'): " . (is_dir('/ruta/falsa') ? "true" : "false") . "\n";

// Ejemplo 3: Uso práctico - crear directorio si no existe
$dir_test = sys_get_temp_dir() . '/php_test_dir';
echo "\nEjemplo 3 (Crear si no existe):\n";
if (!is_dir($dir_test)) {
    mkdir($dir_test);
    echo "Directorio creado: $dir_test\n";
} else {
    echo "Ya existe: $dir_test\n";
}
rmdir($dir_test);

// Ejemplo 4: Listar solo subdirectorios
echo "\nEjemplo 4 (Subdirectorios del directorio padre):\n";
$parent = dirname(__DIR__);
$items = scandir($parent);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    if (is_dir($parent . '/' . $item)) {
        echo "  [DIR] $item\n";
    }
}
?>
