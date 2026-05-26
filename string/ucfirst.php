<?php
// Ejemplo 1: Convertir el primer carácter a mayúscula
$texto = "hola mundo";
echo "Ejemplo 1 (Primer carácter a mayúscula):\n";
echo ucfirst($texto) . "\n"; // Hola mundo

// Ejemplo 2: Ya empieza en mayúscula (sin cambios)
$mayuscula = "PHP";
echo "\nEjemplo 2 (Ya en mayúscula):\n";
echo ucfirst($mayuscula) . "\n"; // PHP

// Ejemplo 3: String todo en minúsculas
$frase = "el gato negro duerme";
echo "\nEjemplo 3 (Solo capitaliza el primer carácter):\n";
echo ucfirst($frase) . "\n"; // El gato negro duerme

// Ejemplo 4: Uso práctico - capitalizar la primera letra de una oración
$oraciones = ["hola.", "esto es PHP.", "me gusta programar."];
echo "\nEjemplo 4 (Capitalizar oraciones):\n";
foreach ($oraciones as $oracion) {
    echo ucfirst($oracion) . " ";
}
echo "\n";

// Ejemplo 5: Combinación con strtolower para normalizar
$desordenado = "hOLA mUNDO";
echo "\nEjemplo 5 (Normalizar: strtolower + ucfirst):\n";
echo ucfirst(strtolower($desordenado)) . "\n"; // Hola mundo
?>
