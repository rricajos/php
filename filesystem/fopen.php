<?php
// Ejemplo 1: Abrir un archivo para lectura
$archivo = tempnam(sys_get_temp_dir(), 'php');
file_put_contents($archivo, "Línea 1\nLínea 2\nLínea 3\n");

$fp = fopen($archivo, 'r');
echo "Ejemplo 1 (Abrir para lectura 'r'):\n";
echo fread($fp, filesize($archivo));
fclose($fp);

// Ejemplo 2: Abrir para escritura (trunca el archivo)
$fp = fopen($archivo, 'w');
fwrite($fp, "Nuevo contenido\n");
fclose($fp);
echo "\nEjemplo 2 (Escritura 'w' - trunca):\n";
echo file_get_contents($archivo);

// Ejemplo 3: Abrir para añadir
$fp = fopen($archivo, 'a');
fwrite($fp, "Contenido añadido\n");
fclose($fp);
echo "\nEjemplo 3 (Append 'a'):\n";
echo file_get_contents($archivo);

// Ejemplo 4: Modos de apertura
echo "\nEjemplo 4 (Modos de apertura):\n";
echo "'r'  - Solo lectura (puntero al inicio)\n";
echo "'r+' - Lectura/escritura (puntero al inicio)\n";
echo "'w'  - Solo escritura (trunca o crea)\n";
echo "'w+' - Lectura/escritura (trunca o crea)\n";
echo "'a'  - Solo escritura (puntero al final, crea si no existe)\n";
echo "'a+' - Lectura/escritura (puntero al final, crea si no existe)\n";
echo "'x'  - Solo escritura (crea nuevo, falla si existe)\n";
echo "'c'  - Solo escritura (no trunca, puntero al inicio)\n";

// Ejemplo 5: Leer línea por línea con fgets
file_put_contents($archivo, "Uno\nDos\nTres\n");
$fp = fopen($archivo, 'r');
echo "\nEjemplo 5 (Leer línea por línea):\n";
while (($linea = fgets($fp)) !== false) {
    echo "  > " . trim($linea) . "\n";
}
fclose($fp);

unlink($archivo);
?>
