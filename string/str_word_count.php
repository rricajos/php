<?php
// Ejemplo 1: Contar palabras de un string
$texto = "Hola mundo desde PHP";
echo "Ejemplo 1 (Contar palabras):\n";
echo "Número de palabras: " . str_word_count($texto) . "\n"; // 4

// Ejemplo 2: Obtener un array de palabras (formato 1)
$frase = "El gato negro duerme";
$palabras = str_word_count($frase, 1);
echo "\nEjemplo 2 (Array de palabras):\n";
print_r($palabras);

// Ejemplo 3: Obtener posiciones de las palabras (formato 2)
$posiciones = str_word_count($frase, 2);
echo "\nEjemplo 3 (Posiciones de las palabras):\n";
print_r($posiciones);

// Ejemplo 4: String con números (no se cuentan como palabras)
$con_numeros = "Tengo 3 gatos y 2 perros";
echo "\nEjemplo 4 (Con números):\n";
echo "Palabras: " . str_word_count($con_numeros) . "\n";
print_r(str_word_count($con_numeros, 1));

// Ejemplo 5: Incluir caracteres adicionales como parte de palabras
$con_numeros2 = "Tengo 3 gatos y 2 perros";
$palabras_extra = str_word_count($con_numeros2, 1, "0123456789");
echo "\nEjemplo 5 (Incluir dígitos como caracteres de palabra):\n";
print_r($palabras_extra);

// Ejemplo 6: String vacío
echo "\nEjemplo 6 (String vacío):\n";
echo "Palabras: " . str_word_count("") . "\n"; // 0
?>
