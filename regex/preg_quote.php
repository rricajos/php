<?php
// Ejemplo 1: Escapar metacaracteres de regex
$texto = "Precio: $10.00 (USD)";
echo "Ejemplo 1 (Escapar metacaracteres):\n";
echo "Original: $texto\n";
echo "Escapado: " . preg_quote($texto) . "\n";

// Ejemplo 2: Caracteres que se escapan
echo "\nEjemplo 2 (Caracteres escapados):\n";
$metacaracteres = '. \\ + * ? [ ^ ] $ ( ) { } = ! < > | : - #';
echo "Metacaracteres: $metacaracteres\n";
echo "Escapados: " . preg_quote($metacaracteres) . "\n";

// Ejemplo 3: Especificar delimitador
echo "\nEjemplo 3 (Con delimitador /):\n";
$patron = "ruta/al/archivo";
echo "preg_quote sin delim: " . preg_quote($patron) . "\n";
echo "preg_quote con '/': " . preg_quote($patron, '/') . "\n";

// Ejemplo 4: Uso práctico - buscar texto literal del usuario
$busqueda = "precio (USD)";
$texto4 = "El precio (USD) es 10.00 y el precio (EUR) es 9.50";
$patron_seguro = "/" . preg_quote($busqueda, "/") . "/i";
echo "\nEjemplo 4 (Buscar texto literal):\n";
if (preg_match($patron_seguro, $texto4)) {
    echo "Encontrado '$busqueda'\n";
}

// Ejemplo 5: Resaltar término de búsqueda en un texto
$termino = "C++";
$texto5 = "Lenguajes: PHP, C++, Java, C#";
$escapado = preg_quote($termino, "/");
$resaltado = preg_replace("/$escapado/", "[$termino]", $texto5);
echo "\nEjemplo 5 (Resaltar '$termino'):\n";
echo "$resaltado\n";
?>
