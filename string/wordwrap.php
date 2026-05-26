<?php
// Ejemplo 1: Envolver texto a 15 caracteres por línea
$texto = "El rápido zorro marrón salta sobre el perro perezoso";
echo "Ejemplo 1 (Envolver a 15 caracteres):\n";
echo wordwrap($texto, 15, "\n") . "\n";

// Ejemplo 2: Envolver con un separador personalizado
echo "\nEjemplo 2 (Separador <br>):\n";
echo wordwrap($texto, 20, "<br>\n") . "\n";

// Ejemplo 3: Cortar palabras largas (cut_long_words = true)
$largo = "Supercalifragilisticoespialidoso es una palabra larga";
echo "\nEjemplo 3 (Cortar palabras largas):\n";
echo "Sin cortar:\n";
echo wordwrap($largo, 10, "\n") . "\n";
echo "\nCon cortar (true):\n";
echo wordwrap($largo, 10, "\n", true) . "\n";

// Ejemplo 4: Texto ya corto (no necesita envolver)
$corto = "Hola";
echo "\nEjemplo 4 (Texto corto, sin cambios):\n";
echo wordwrap($corto, 20, "\n") . "\n";

// Ejemplo 5: Uso práctico - formatear email o texto de consola
$parrafo = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
echo "\nEjemplo 5 (Formatear párrafo a 40 columnas):\n";
echo str_repeat("-", 40) . "\n";
echo wordwrap($parrafo, 40, "\n") . "\n";
echo str_repeat("-", 40) . "\n";
?>
