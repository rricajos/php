<?php
// Ejemplo 1: Resolver ruta absoluta
echo "Ejemplo 1 (Ruta absoluta):\n";
echo "realpath('.'): " . realpath('.') . "\n";
echo "realpath(__DIR__): " . realpath(__DIR__) . "\n";

// Ejemplo 2: Resolver rutas relativas
echo "\nEjemplo 2 (Ruta relativa con ..):\n";
echo "realpath(__DIR__ . '/..'): " . realpath(__DIR__ . '/..') . "\n";

// Ejemplo 3: Ruta inexistente devuelve false
echo "\nEjemplo 3 (Ruta inexistente):\n";
var_dump(realpath('/ruta/que/no/existe')); // false

// Ejemplo 4: Resolver enlaces simbólicos
echo "\nEjemplo 4 (Resuelve symlinks):\n";
echo "realpath resuelve enlaces simbólicos a la ruta real del archivo\n";

// Ejemplo 5: Uso práctico - seguridad (prevenir path traversal)
function rutaSegura(string $base, string $ruta): string|false {
    $real = realpath($base . '/' . $ruta);
    if ($real === false || !str_starts_with($real, realpath($base))) {
        return false; // Intento de path traversal
    }
    return $real;
}
echo "\nEjemplo 5 (Prevenir path traversal):\n";
$base = __DIR__;
echo "Ruta válida: " . var_export(rutaSegura($base, basename(__FILE__)), true) . "\n";
echo "Path traversal: " . var_export(rutaSegura($base, '../../etc/passwd'), true) . "\n";
?>
