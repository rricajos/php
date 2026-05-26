<?php
// Ejemplo 1: Buscar un patrón simple
$texto = "Hola mundo PHP";
echo "Ejemplo 1 (Buscar patrón):\n";
if (preg_match("/PHP/", $texto)) {
    echo "Encontrado 'PHP'\n";
}

// Ejemplo 2: Capturar matches
$email = "usuario@ejemplo.com";
preg_match("/^(.+)@(.+)\.(.+)$/", $email, $matches);
echo "\nEjemplo 2 (Capturar grupos):\n";
echo "Match completo: $matches[0]\n";
echo "Usuario: $matches[1]\n";
echo "Dominio: $matches[2]\n";
echo "TLD: $matches[3]\n";

// Ejemplo 3: Grupos con nombre
$fecha = "2026-05-26";
preg_match("/(?P<anio>\d{4})-(?P<mes>\d{2})-(?P<dia>\d{2})/", $fecha, $matches);
echo "\nEjemplo 3 (Grupos con nombre):\n";
echo "Año: {$matches['anio']}, Mes: {$matches['mes']}, Día: {$matches['dia']}\n";

// Ejemplo 4: Retorno de preg_match
echo "\nEjemplo 4 (Valor de retorno):\n";
echo "Encontrado: " . preg_match("/\d+/", "abc123") . "\n"; // 1
echo "No encontrado: " . preg_match("/\d+/", "abc") . "\n"; // 0

// Ejemplo 5: Modificadores
$html = "<P>Hola</p>";
echo "\nEjemplo 5 (Modificador i - insensible a case):\n";
echo "Sin /i: " . preg_match("/<p>/", $html) . "\n";  // 0
echo "Con /i: " . preg_match("/<p>/i", $html) . "\n";  // 1

// Ejemplo 6: Uso práctico - validar formato
$telefono = "+34 612 345 678";
echo "\nEjemplo 6 (Validar teléfono español):\n";
if (preg_match("/^\+34\s?\d{3}\s?\d{3}\s?\d{3}$/", $telefono)) {
    echo "Teléfono válido: $telefono\n";
} else {
    echo "Teléfono inválido\n";
}
?>
