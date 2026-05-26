<?php
// Ejemplo 1: Verificar si un archivo existe
echo "Ejemplo 1 (Verificar archivo):\n";
echo __FILE__ . " existe: " . (file_exists(__FILE__) ? "Sí" : "No") . "\n";
echo "inexistente.txt existe: " . (file_exists('inexistente.txt') ? "Sí" : "No") . "\n";

// Ejemplo 2: También funciona con directorios
echo "\nEjemplo 2 (Verificar directorio):\n";
echo __DIR__ . " existe: " . (file_exists(__DIR__) ? "Sí" : "No") . "\n";

// Ejemplo 3: Diferencia entre file_exists, is_file, is_dir
echo "\nEjemplo 3 (file_exists vs is_file vs is_dir):\n";
echo str_pad("Función", 15) . str_pad(__FILE__, 10) . __DIR__ . "\n";
echo str_pad("file_exists", 15) . str_pad(file_exists(__FILE__) ? "true" : "false", 10) . (file_exists(__DIR__) ? "true" : "false") . "\n";
echo str_pad("is_file", 15) . str_pad(is_file(__FILE__) ? "true" : "false", 10) . (is_file(__DIR__) ? "true" : "false") . "\n";
echo str_pad("is_dir", 15) . str_pad(is_dir(__FILE__) ? "true" : "false", 10) . (is_dir(__DIR__) ? "true" : "false") . "\n";

// Ejemplo 4: Uso práctico - crear archivo solo si no existe
$archivo = tempnam(sys_get_temp_dir(), 'php');
unlink($archivo); // Eliminar para el test
echo "\nEjemplo 4 (Crear si no existe):\n";
if (!file_exists($archivo)) {
    file_put_contents($archivo, "Contenido inicial");
    echo "Archivo creado\n";
} else {
    echo "El archivo ya existía\n";
}
unlink($archivo);

// Ejemplo 5: Uso práctico - incluir archivo de configuración
echo "\nEjemplo 5 (Incluir config si existe):\n";
$config_file = __DIR__ . '/config.php';
if (file_exists($config_file)) {
    echo "Cargando configuración...\n";
} else {
    echo "Archivo de configuración no encontrado, usando valores por defecto\n";
}
?>
