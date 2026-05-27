<?php
// Script de validación de sintaxis para todos los archivos PHP del proyecto
// Uso: php lint.php
// Recorre recursivamente todos los .php y ejecuta php -l en cada uno

echo "\033[32m=== PHP Lint - Validación de sintaxis ===\033[0m\n\n";

$errores = 0;
$total = 0;
$directorio = __DIR__;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directorio, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $archivo) {
    if ($archivo->getExtension() !== 'php') continue;

    $ruta = $archivo->getPathname();

    // Excluir este mismo archivo, el runner, y el mini-proyecto app
    $relativa = str_replace($directorio . DIRECTORY_SEPARATOR, '', $ruta);

    // Excluir .git
    if (str_contains($relativa, '.git')) continue;

    $total++;
    $output = [];
    $codigo = 0;
    exec("php -l " . escapeshellarg($ruta) . " 2>&1", $output, $codigo);

    if ($codigo !== 0) {
        $errores++;
        echo "\033[31m  ✗ $relativa\033[0m\n";
        foreach ($output as $linea) {
            echo "    $linea\n";
        }
    }
}

echo "\n\033[32m=== Resultado ===\033[0m\n";
echo "Archivos analizados: $total\n";

if ($errores === 0) {
    echo "\033[32m  ✓ Todos los archivos tienen sintaxis válida\033[0m\n";
} else {
    echo "\033[31m  ✗ $errores archivo(s) con errores de sintaxis\033[0m\n";
}
?>
