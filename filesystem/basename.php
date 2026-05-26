<?php
// Ejemplo 1: Obtener el nombre del archivo de una ruta
echo "Ejemplo 1 (Nombre de archivo):\n";
echo basename('/var/www/html/index.php') . "\n"; // index.php
echo basename('/home/user/docs/readme.txt') . "\n"; // readme.txt

// Ejemplo 2: Quitar la extensión (segundo parámetro)
echo "\nEjemplo 2 (Sin extensión):\n";
echo basename('index.php', '.php') . "\n"; // index
echo basename('style.css', '.css') . "\n"; // style

// Ejemplo 3: Con rutas Windows
echo "\nEjemplo 3 (Rutas Windows):\n";
echo basename('C:\\Users\\user\\file.txt') . "\n"; // file.txt

// Ejemplo 4: Solo el directorio más profundo
echo "\nEjemplo 4 (Directorio):\n";
echo basename('/var/www/html/') . "\n"; // html

// Ejemplo 5: Uso práctico - log del archivo actual
echo "\nEjemplo 5 (Archivo actual):\n";
echo "[" . basename(__FILE__) . "] Este es un mensaje de log\n";
?>
