<?php
// Ejemplo 1: Encontrar la última ocurrencia
$texto = "el gato y el perro y el pájaro";
echo "Ejemplo 1 (Última posición de 'el'):\n";
echo "Última posición: " . strrpos($texto, "el") . "\n"; // 21

// Ejemplo 2: Comparar strpos vs strrpos
echo "\nEjemplo 2 (strpos vs strrpos):\n";
echo "strpos (primera): " . strpos($texto, "el") . "\n";   // 0
echo "strrpos (última): " . strrpos($texto, "el") . "\n";   // 21

// Ejemplo 3: Uso práctico - obtener extensión de archivo
$archivo = "documento.backup.tar.gz";
$ultima_punto = strrpos($archivo, ".");
$extension = substr($archivo, $ultima_punto + 1);
echo "\nEjemplo 3 (Extensión de archivo):\n";
echo "Archivo: $archivo\n";
echo "Extensión: $extension\n";

// Ejemplo 4: Uso práctico - obtener el último segmento de una ruta
$ruta = "/var/www/html/index.php";
$ultimo_slash = strrpos($ruta, "/");
$nombre = substr($ruta, $ultimo_slash + 1);
echo "\nEjemplo 4 (Último segmento de ruta):\n";
echo "Ruta: $ruta\n";
echo "Nombre: $nombre\n";

// Ejemplo 5: No encontrado
echo "\nEjemplo 5 (No encontrado):\n";
var_dump(strrpos($texto, "xyz")); // false
?>
