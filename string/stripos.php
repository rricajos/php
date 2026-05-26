<?php
// Ejemplo 1: Buscar posición sin distinción de mayúsculas
$texto = "Hola Mundo PHP";
echo "Ejemplo 1 (Buscar 'hola' sin case):\n";
echo "Posición: " . stripos($texto, "hola") . "\n"; // 0

// Ejemplo 2: Comparar strpos vs stripos
$texto2 = "PHP es Genial";
echo "\nEjemplo 2 (strpos vs stripos):\n";
var_dump(strpos($texto2, "php"));   // false
var_dump(stripos($texto2, "php"));  // int(0)

// Ejemplo 3: Buscar desde una posición
$texto3 = "PHP php PHP";
echo "\nEjemplo 3 (Buscar desde posición 4):\n";
echo "Posición: " . stripos($texto3, "php", 4) . "\n"; // 4

// Ejemplo 4: Uso práctico - detectar navegador
$userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/120.0";
echo "\nEjemplo 4 (Detectar navegador):\n";
if (stripos($userAgent, "chrome") !== false) {
    echo "Navegador: Chrome\n";
} elseif (stripos($userAgent, "firefox") !== false) {
    echo "Navegador: Firefox\n";
}

// Ejemplo 5: Búsqueda de palabras clave
$contenido = "PHP es un Lenguaje de programación del lado del servidor";
$keywords = ["php", "javascript", "python"];
echo "\nEjemplo 5 (Buscar keywords):\n";
foreach ($keywords as $kw) {
    $encontrado = stripos($contenido, $kw) !== false ? "Sí" : "No";
    echo "  '$kw': $encontrado\n";
}
?>
