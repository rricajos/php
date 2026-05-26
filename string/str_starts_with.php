<?php
// str_starts_with() - Disponible desde PHP 8.0

// Ejemplo 1: Verificar si empieza con un substring
$texto = "Hola mundo PHP";
echo "Ejemplo 1 (Empieza con 'Hola'):\n";
var_dump(str_starts_with($texto, "Hola")); // true

// Ejemplo 2: No empieza con
echo "\nEjemplo 2 (No empieza con 'mundo'):\n";
var_dump(str_starts_with($texto, "mundo")); // false

// Ejemplo 3: Es sensible a mayúsculas
echo "\nEjemplo 3 (Sensible a case):\n";
var_dump(str_starts_with($texto, "hola")); // false

// Ejemplo 4: Comparar con substr (antes de PHP 8)
echo "\nEjemplo 4 (str_starts_with vs substr):\n";
$prefijo = "Hola";
$nuevo = str_starts_with($texto, $prefijo);
$viejo = substr($texto, 0, strlen($prefijo)) === $prefijo;
echo "str_starts_with: " . ($nuevo ? "true" : "false") . "\n";
echo "substr: " . ($viejo ? "true" : "false") . "\n";

// Ejemplo 5: Uso práctico - detectar protocolo de URL
$urls = ["https://ejemplo.com", "http://test.com", "ftp://archivos.com"];
echo "\nEjemplo 5 (Detectar protocolo):\n";
foreach ($urls as $url) {
    if (str_starts_with($url, "https://")) {
        echo "  $url -> HTTPS (seguro)\n";
    } elseif (str_starts_with($url, "http://")) {
        echo "  $url -> HTTP (no seguro)\n";
    } else {
        echo "  $url -> Otro protocolo\n";
    }
}

// Ejemplo 6: Uso práctico - filtrar archivos por prefijo
$archivos = ["test_login.php", "test_api.php", "utils.php", "test_db.php"];
$tests = array_filter($archivos, fn($f) => str_starts_with($f, "test_"));
echo "\nEjemplo 6 (Filtrar archivos de test):\n";
print_r(array_values($tests));
?>
