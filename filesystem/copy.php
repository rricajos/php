<?php
// Ejemplo 1: Copiar un archivo
$origen = tempnam(sys_get_temp_dir(), 'php');
$destino = sys_get_temp_dir() . '/copia_test.txt';
file_put_contents($origen, "Contenido original");

echo "Ejemplo 1 (Copiar archivo):\n";
if (copy($origen, $destino)) {
    echo "Copiado exitosamente\n";
    echo "Original: " . file_get_contents($origen) . "\n";
    echo "Copia: " . file_get_contents($destino) . "\n";
}

// Ejemplo 2: Copiar sobrescribe si ya existe
file_put_contents($destino, "Contenido viejo");
copy($origen, $destino);
echo "\nEjemplo 2 (Sobrescribe destino existente):\n";
echo "Destino ahora: " . file_get_contents($destino) . "\n";

// Ejemplo 3: Verificar integridad de la copia
echo "\nEjemplo 3 (Verificar copia):\n";
echo "MD5 original: " . md5_file($origen) . "\n";
echo "MD5 copia:    " . md5_file($destino) . "\n";
echo "Iguales: " . (md5_file($origen) === md5_file($destino) ? "Sí" : "No") . "\n";

// Ejemplo 4: Uso práctico - backup de archivo
echo "\nEjemplo 4 (Crear backup):\n";
$backup = $origen . '.bak.' . date('Y-m-d');
copy($origen, $backup);
echo "Backup creado: $backup\n";

unlink($origen);
unlink($destino);
unlink($backup);
?>
