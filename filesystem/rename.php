<?php
// Ejemplo 1: Renombrar un archivo
$archivo = tempnam(sys_get_temp_dir(), 'php');
$nuevo_nombre = sys_get_temp_dir() . '/renombrado.txt';
file_put_contents($archivo, "Contenido");

echo "Ejemplo 1 (Renombrar archivo):\n";
echo "Antes: " . basename($archivo) . "\n";
rename($archivo, $nuevo_nombre);
echo "Después: " . basename($nuevo_nombre) . "\n";
echo "Contenido preservado: " . file_get_contents($nuevo_nombre) . "\n";

// Ejemplo 2: Mover archivo a otro directorio
$dir_destino = sys_get_temp_dir() . '/subdir_test';
mkdir($dir_destino);
$movido = $dir_destino . '/archivo.txt';
rename($nuevo_nombre, $movido);
echo "\nEjemplo 2 (Mover archivo):\n";
echo "Movido a: $movido\n";
echo "Existe en destino: " . (file_exists($movido) ? "Sí" : "No") . "\n";

// Ejemplo 3: Renombrar directorio
$dir_old = sys_get_temp_dir() . '/dir_viejo';
$dir_new = sys_get_temp_dir() . '/dir_nuevo';
mkdir($dir_old);
rename($dir_old, $dir_new);
echo "\nEjemplo 3 (Renombrar directorio):\n";
echo "Dir viejo existe: " . (is_dir($dir_old) ? "Sí" : "No") . "\n";
echo "Dir nuevo existe: " . (is_dir($dir_new) ? "Sí" : "No") . "\n";

// Limpiar
unlink($movido);
rmdir($dir_destino);
rmdir($dir_new);
?>
