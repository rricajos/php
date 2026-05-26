<?php
// Ejemplo 1: Encontrar todas las ocurrencias
$texto = "PHP 8.3 es más rápido que PHP 7.4 y PHP 5.6";
$count = preg_match_all("/PHP \d\.\d/", $texto, $matches);
echo "Ejemplo 1 (Todas las ocurrencias):\n";
echo "Encontrados: $count\n";
print_r($matches[0]);

// Ejemplo 2: Capturar grupos de todas las ocurrencias
$html = '<a href="https://php.net">PHP</a> y <a href="https://mysql.com">MySQL</a>';
preg_match_all('/<a href="([^"]+)">([^<]+)<\/a>/', $html, $matches);
echo "\nEjemplo 2 (Grupos de cada match):\n";
echo "URLs:\n";
print_r($matches[1]);
echo "Textos:\n";
print_r($matches[2]);

// Ejemplo 3: PREG_SET_ORDER (agrupar por match)
preg_match_all('/<a href="([^"]+)">([^<]+)<\/a>/', $html, $matches, PREG_SET_ORDER);
echo "\nEjemplo 3 (PREG_SET_ORDER):\n";
foreach ($matches as $match) {
    echo "  URL: {$match[1]} -> Texto: {$match[2]}\n";
}

// Ejemplo 4: Encontrar todas las palabras
$frase = "El PHP es genial";
preg_match_all("/\b\w+\b/", $frase, $matches);
echo "\nEjemplo 4 (Todas las palabras):\n";
print_r($matches[0]);

// Ejemplo 5: Extraer todos los números de un texto
$texto5 = "Tiene 3 gatos, 2 perros y 15 peces";
preg_match_all("/\d+/", $texto5, $matches);
echo "\nEjemplo 5 (Extraer números):\n";
print_r($matches[0]);
echo "Suma: " . array_sum($matches[0]) . "\n";

// Ejemplo 6: Extraer todos los emails
$texto6 = "Contacta a ana@mail.com o carlos@empresa.es para más info";
preg_match_all("/[\w.+-]+@[\w-]+\.[\w.]+/", $texto6, $matches);
echo "\nEjemplo 6 (Extraer emails):\n";
print_r($matches[0]);
?>
