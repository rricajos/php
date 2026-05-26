<?php
// Ejemplo 1: Eliminar un archivo
$archivo = tempnam(sys_get_temp_dir(), 'php');
file_put_contents($archivo, "Contenido temporal");
echo "Ejemplo 1 (Eliminar archivo):\n";
echo "Existe antes: " . (file_exists($archivo) ? "Sí" : "No") . "\n";
unlink($archivo);
echo "Existe después: " . (file_exists($archivo) ? "Sí" : "No") . "\n";

// Ejemplo 2: unlink devuelve true/false
$archivo2 = tempnam(sys_get_temp_dir(), 'php');
file_put_contents($archivo2, "test");
echo "\nEjemplo 2 (Valor de retorno):\n";
$resultado = unlink($archivo2);
echo "Resultado: " . ($resultado ? "true" : "false") . "\n";

// Ejemplo 3: Uso seguro - verificar antes de eliminar
echo "\nEjemplo 3 (Verificar antes de eliminar):\n";
$archivo3 = sys_get_temp_dir() . '/test_unlink.txt';
file_put_contents($archivo3, "test");
if (file_exists($archivo3) && is_file($archivo3)) {
    unlink($archivo3);
    echo "Archivo eliminado de forma segura\n";
}

// Ejemplo 4: No se puede eliminar directorios con unlink
echo "\nEjemplo 4 (No funciona con directorios):\n";
echo "Para directorios usar rmdir() o para recursivo usar funciones personalizadas\n";

// Ejemplo 5: Uso práctico - limpiar archivos temporales
echo "\nEjemplo 5 (Limpiar temporales):\n";
$temps = [];
for ($i = 0; $i < 3; $i++) {
    $t = tempnam(sys_get_temp_dir(), 'php');
    file_put_contents($t, "temp $i");
    $temps[] = $t;
}
echo "Creados " . count($temps) . " archivos temporales\n";
foreach ($temps as $t) {
    unlink($t);
}
echo "Eliminados todos\n";
?>
