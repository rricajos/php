<?php
// Ejemplo 1: Comparar los primeros N caracteres
echo "Ejemplo 1 (Primeros 3 caracteres):\n";
echo "strncmp('Hola', 'Hombre', 3): " . strncmp("Hola", "Hombre", 3) . "\n"; // != 0
echo "strncmp('Hola', 'Hombre', 2): " . strncmp("Hola", "Hombre", 2) . "\n"; // 0 (ambos empiezan por "Ho")

// Ejemplo 2: Verificar prefijo de strings
$archivos = ["test_login.php", "test_api.php", "utils.php", "test_db.php"];
echo "\nEjemplo 2 (Filtrar por prefijo 'test_'):\n";
foreach ($archivos as $archivo) {
    if (strncmp($archivo, "test_", 5) === 0) {
        echo "  Test: $archivo\n";
    }
}

// Ejemplo 3: Comparar versiones parciales
echo "\nEjemplo 3 (Comparar versión mayor):\n";
$version1 = "8.3.11";
$version2 = "8.3.5";
if (strncmp($version1, $version2, 3) === 0) {
    echo "Misma versión mayor: " . substr($version1, 0, 3) . "\n";
}

// Ejemplo 4: strncasecmp - versión insensible a mayúsculas
echo "\nEjemplo 4 (strncasecmp):\n";
echo "strncmp('PHP', 'php', 3): " . strncmp("PHP", "php", 3) . "\n";       // != 0
echo "strncasecmp('PHP', 'php', 3): " . strncasecmp("PHP", "php", 3) . "\n"; // 0

// Ejemplo 5: Comparar con strcmp (toda la cadena)
echo "\nEjemplo 5 (strncmp vs strcmp):\n";
echo "strcmp('Hola mundo', 'Hola PHP'): " . strcmp("Hola mundo", "Hola PHP") . "\n";
echo "strncmp('Hola mundo', 'Hola PHP', 5): " . strncmp("Hola mundo", "Hola PHP", 5) . "\n"; // 0
?>
