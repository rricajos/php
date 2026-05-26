<?php
// Ejemplo 1: Reemplazo con función callback
$texto = "tengo 3 gatos y 5 perros";
echo "Ejemplo 1 (Duplicar números con callback):\n";
$resultado = preg_replace_callback("/\d+/", function($matches) {
    return $matches[0] * 2;
}, $texto);
echo "$resultado\n"; // tengo 6 gatos y 10 perros

// Ejemplo 2: Convertir camelCase a snake_case
$camelCase = "getUserNameFromDatabase";
echo "\nEjemplo 2 (camelCase a snake_case):\n";
$snake_case = strtolower(preg_replace_callback("/[A-Z]/", function($matches) {
    return "_" . strtolower($matches[0]);
}, $camelCase));
echo "$camelCase -> $snake_case\n";

// Ejemplo 3: Censurar parcialmente palabras
$texto3 = "La contraseña es: secreto123";
echo "\nEjemplo 3 (Censurar parcialmente):\n";
$censurado = preg_replace_callback("/\b\w{5,}\b/", function($matches) {
    $palabra = $matches[0];
    return $palabra[0] . str_repeat("*", strlen($palabra) - 2) . $palabra[strlen($palabra) - 1];
}, $texto3);
echo "$censurado\n";

// Ejemplo 4: Convertir unidades de temperatura en un texto
$texto4 = "Hoy hace 32°C y mañana hará 28°C";
echo "\nEjemplo 4 (Celsius a Fahrenheit):\n";
$resultado = preg_replace_callback("/(\d+)°C/", function($matches) {
    $f = round($matches[1] * 9/5 + 32, 1);
    return "{$matches[1]}°C ({$f}°F)";
}, $texto4);
echo "$resultado\n";

// Ejemplo 5: Resaltar palabras clave
$codigo = "Usar function para declarar y return para devolver";
$keywords = ["function", "return", "class", "if", "else"];
echo "\nEjemplo 5 (Resaltar keywords):\n";
$patron = "/\b(" . implode("|", $keywords) . ")\b/";
$resaltado = preg_replace_callback($patron, function($matches) {
    return strtoupper($matches[0]);
}, $codigo);
echo "$resaltado\n";
?>
