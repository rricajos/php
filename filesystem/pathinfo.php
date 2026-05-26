<?php
// Ejemplo 1: Obtener información de una ruta
$ruta = '/var/www/html/index.php';
$info = pathinfo($ruta);
echo "Ejemplo 1 (Información de ruta):\n";
print_r($info);

// Ejemplo 2: Obtener componentes individuales
echo "\nEjemplo 2 (Componentes individuales):\n";
echo "dirname:   " . pathinfo($ruta, PATHINFO_DIRNAME) . "\n";
echo "basename:  " . pathinfo($ruta, PATHINFO_BASENAME) . "\n";
echo "extension: " . pathinfo($ruta, PATHINFO_EXTENSION) . "\n";
echo "filename:  " . pathinfo($ruta, PATHINFO_FILENAME) . "\n";

// Ejemplo 3: Archivo sin extensión
$sin_ext = '/home/user/README';
echo "\nEjemplo 3 (Sin extensión):\n";
print_r(pathinfo($sin_ext));

// Ejemplo 4: Múltiples extensiones
$tar_gz = '/backup/archivo.tar.gz';
echo "\nEjemplo 4 (Múltiples extensiones):\n";
echo "extension: " . pathinfo($tar_gz, PATHINFO_EXTENSION) . "\n"; // gz
echo "filename:  " . pathinfo($tar_gz, PATHINFO_FILENAME) . "\n";  // archivo.tar

// Ejemplo 5: Uso práctico - validar extensión de upload
$uploads = ["foto.jpg", "documento.pdf", "script.php", "imagen.png"];
$permitidas = ["jpg", "png", "gif", "pdf"];
echo "\nEjemplo 5 (Validar extensiones):\n";
foreach ($uploads as $archivo) {
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    $estado = in_array($ext, $permitidas) ? "Permitido" : "Rechazado";
    echo "  $archivo -> $estado\n";
}
?>
