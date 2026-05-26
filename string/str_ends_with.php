<?php
// str_ends_with() - Disponible desde PHP 8.0

// Ejemplo 1: Verificar si termina con un substring
$texto = "Hola mundo PHP";
echo "Ejemplo 1 (Termina con 'PHP'):\n";
var_dump(str_ends_with($texto, "PHP")); // true

// Ejemplo 2: No termina con
echo "\nEjemplo 2 (No termina con 'mundo'):\n";
var_dump(str_ends_with($texto, "mundo")); // false

// Ejemplo 3: Es sensible a mayúsculas
echo "\nEjemplo 3 (Sensible a case):\n";
var_dump(str_ends_with($texto, "php")); // false

// Ejemplo 4: Uso práctico - verificar extensión de archivo
$archivos = ["foto.jpg", "documento.pdf", "script.php", "estilo.css"];
echo "\nEjemplo 4 (Filtrar archivos PHP):\n";
foreach ($archivos as $archivo) {
    if (str_ends_with($archivo, ".php")) {
        echo "  $archivo -> Es PHP\n";
    }
}

// Ejemplo 5: Comparar con substr (antes de PHP 8)
$sufijo = "PHP";
$nuevo = str_ends_with($texto, $sufijo);
$viejo = substr($texto, -strlen($sufijo)) === $sufijo;
echo "\nEjemplo 5 (str_ends_with vs substr):\n";
echo "str_ends_with: " . ($nuevo ? "true" : "false") . "\n";
echo "substr: " . ($viejo ? "true" : "false") . "\n";

// Ejemplo 6: Uso práctico - asegurar que una URL termina con /
$urls = ["https://ejemplo.com", "https://ejemplo.com/"];
echo "\nEjemplo 6 (Normalizar URLs con /):\n";
foreach ($urls as $url) {
    $normalizada = str_ends_with($url, "/") ? $url : $url . "/";
    echo "  $normalizada\n";
}
?>
