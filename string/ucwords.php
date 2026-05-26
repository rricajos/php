<?php
// Ejemplo 1: Capitalizar la primera letra de cada palabra
$texto = "hola mundo desde php";
echo "Ejemplo 1 (Capitalizar cada palabra):\n";
echo ucwords($texto) . "\n"; // Hola Mundo Desde Php

// Ejemplo 2: String todo en minúsculas primero
$desordenado = "hOLA mUNDO dESDE pHP";
echo "\nEjemplo 2 (strtolower + ucwords):\n";
echo ucwords(strtolower($desordenado)) . "\n"; // Hola Mundo Desde Php

// Ejemplo 3: Usar delimitadores personalizados
$guiones = "hola-mundo-php";
echo "\nEjemplo 3 (Delimitador personalizado '-'):\n";
echo ucwords($guiones, "-") . "\n"; // Hola-Mundo-Php

// Ejemplo 4: Múltiples delimitadores
$mixto = "hola-mundo_desde php";
echo "\nEjemplo 4 (Múltiples delimitadores '-_ '):\n";
echo ucwords($mixto, "-_ ") . "\n"; // Hola-Mundo_Desde Php

// Ejemplo 5: Uso práctico - formatear nombres
$nombres = ["ana garcía", "carlos lópez", "marta de la fuente"];
echo "\nEjemplo 5 (Formatear nombres):\n";
foreach ($nombres as $nombre) {
    echo ucwords($nombre) . "\n";
}

// Ejemplo 6: Diferencia entre ucfirst y ucwords
$frase = "el gato negro duerme";
echo "\nEjemplo 6 (ucfirst vs ucwords):\n";
echo "ucfirst: " . ucfirst($frase) . "\n";   // El gato negro duerme
echo "ucwords: " . ucwords($frase) . "\n";    // El Gato Negro Duerme
?>
