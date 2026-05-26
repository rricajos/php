<?php
// Ejemplo 1: Buscar archivos por patrón
echo "Ejemplo 1 (Todos los .php del directorio actual):\n";
$archivos = glob(__DIR__ . '/*.php');
foreach (array_slice($archivos, 0, 5) as $archivo) {
    echo "  " . basename($archivo) . "\n";
}
echo "Total: " . count($archivos) . " archivos\n";

// Ejemplo 2: Buscar con comodín
echo "\nEjemplo 2 (Archivos que empiezan con 'file'):\n";
$archivos = glob(__DIR__ . '/file*.php');
foreach ($archivos as $archivo) {
    echo "  " . basename($archivo) . "\n";
}

// Ejemplo 3: Buscar múltiples extensiones
echo "\nEjemplo 3 (Múltiples extensiones con {}):\n";
$archivos = glob(__DIR__ . '/*.{php,txt,json}', GLOB_BRACE);
foreach (array_slice($archivos, 0, 5) as $archivo) {
    echo "  " . basename($archivo) . "\n";
}

// Ejemplo 4: Solo directorios
echo "\nEjemplo 4 (Solo directorios con GLOB_ONLYDIR):\n";
$dirs = glob(dirname(__DIR__) . '/*', GLOB_ONLYDIR);
foreach ($dirs as $dir) {
    echo "  [DIR] " . basename($dir) . "\n";
}

// Ejemplo 5: glob vs scandir
echo "\nEjemplo 5 (glob vs scandir):\n";
echo "glob: busca por patrón, devuelve rutas completas\n";
echo "scandir: lista todo, devuelve solo nombres\n";

// Ejemplo 6: Sin resultados devuelve array vacío
echo "\nEjemplo 6 (Sin resultados):\n";
$vacio = glob(__DIR__ . '/*.xyz');
echo "Resultados: " . count($vacio) . "\n"; // 0
?>
