<?php
// Ejemplo 1: Verificar si es un archivo regular
echo "Ejemplo 1 (is_file):\n";
echo __FILE__ . ": " . (is_file(__FILE__) ? "Es archivo" : "No es archivo") . "\n";
echo __DIR__ . ": " . (is_file(__DIR__) ? "Es archivo" : "No es archivo") . "\n";

// Ejemplo 2: is_file vs is_dir
echo "\nEjemplo 2 (is_file vs is_dir):\n";
echo "is_file(__FILE__): " . (is_file(__FILE__) ? "true" : "false") . "\n";
echo "is_dir(__FILE__): " . (is_dir(__FILE__) ? "true" : "false") . "\n";
echo "is_file(__DIR__): " . (is_file(__DIR__) ? "true" : "false") . "\n";
echo "is_dir(__DIR__): " . (is_dir(__DIR__) ? "true" : "false") . "\n";

// Ejemplo 3: is_file devuelve false para inexistentes
echo "\nEjemplo 3 (Archivo inexistente):\n";
echo "is_file('no_existe.txt'): " . (is_file('no_existe.txt') ? "true" : "false") . "\n";

// Ejemplo 4: Uso práctico - filtrar solo archivos de un directorio
echo "\nEjemplo 4 (Listar solo archivos del directorio actual):\n";
$items = scandir(__DIR__);
$archivos = array_filter($items, function($item) {
    return is_file(__DIR__ . '/' . $item);
});
foreach (array_slice($archivos, 0, 5) as $archivo) {
    echo "  - $archivo\n";
}
if (count($archivos) > 5) echo "  ... y " . (count($archivos) - 5) . " más\n";
?>
