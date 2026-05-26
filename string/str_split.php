<?php
// Ejemplo 1: Dividir un string en caracteres individuales
$texto = "Hola";
$caracteres = str_split($texto);
echo "Ejemplo 1 (Caracteres individuales):\n";
print_r($caracteres);

// Ejemplo 2: Dividir en trozos de longitud específica
$texto2 = "abcdefghij";
$trozos = str_split($texto2, 3);
echo "\nEjemplo 2 (Trozos de 3 caracteres):\n";
print_r($trozos);

// Ejemplo 3: Dividir un número de tarjeta para formatear
$tarjeta = "1234567890123456";
$grupos = str_split($tarjeta, 4);
echo "\nEjemplo 3 (Formatear número de tarjeta):\n";
echo implode(" ", $grupos) . "\n"; // 1234 5678 9012 3456

// Ejemplo 4: str_split con string de un solo carácter
$letra = "A";
$resultado = str_split($letra);
echo "\nEjemplo 4 (Un solo carácter):\n";
print_r($resultado);

// Ejemplo 5: Uso práctico - contar frecuencia de caracteres
$frase = "hola mundo";
$chars = str_split($frase);
$frecuencia = array_count_values($chars);
arsort($frecuencia);
echo "\nEjemplo 5 (Frecuencia de caracteres en '$frase'):\n";
foreach ($frecuencia as $char => $count) {
    $display = $char === " " ? "(espacio)" : $char;
    echo "  '$display': $count veces\n";
}
?>
