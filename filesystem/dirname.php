<?php
// Ejemplo 1: Obtener el directorio padre de una ruta
echo "Ejemplo 1 (Directorio padre):\n";
echo dirname('/var/www/html/index.php') . "\n"; // /var/www/html
echo dirname('/home/user/docs/file.txt') . "\n"; // /home/user/docs

// Ejemplo 2: Subir múltiples niveles (PHP 7+)
echo "\nEjemplo 2 (Subir varios niveles):\n";
echo dirname('/var/www/html/index.php', 1) . "\n"; // /var/www/html
echo dirname('/var/www/html/index.php', 2) . "\n"; // /var/www
echo dirname('/var/www/html/index.php', 3) . "\n"; // /var

// Ejemplo 3: Constante __DIR__ equivale a dirname(__FILE__)
echo "\nEjemplo 3 (__DIR__ vs dirname(__FILE__)):\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "dirname(__FILE__): " . dirname(__FILE__) . "\n";

// Ejemplo 4: Uso práctico - incluir archivos relativos al proyecto
echo "\nEjemplo 4 (Ruta base del proyecto):\n";
$project_root = dirname(__DIR__); // Subir un nivel desde filesystem/
echo "Raíz del proyecto: $project_root\n";

// Ejemplo 5: dirname de archivo sin ruta
echo "\nEjemplo 5 (Sin ruta de directorio):\n";
echo dirname('archivo.php') . "\n"; // . (directorio actual)
?>
