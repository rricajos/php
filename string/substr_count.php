<?php
// Ejemplo 1: Contar ocurrencias de un substring
$texto = "el gato y el perro y el pájaro";
echo "Ejemplo 1 (Contar 'el'):\n";
echo "Ocurrencias de 'el': " . substr_count($texto, "el") . "\n"; // 3

// Ejemplo 2: Es sensible a mayúsculas
$texto2 = "PHP php Php";
echo "\nEjemplo 2 (Sensible a mayúsculas):\n";
echo "Ocurrencias de 'php': " . substr_count($texto2, "php") . "\n"; // 1
echo "Ocurrencias de 'PHP': " . substr_count($texto2, "PHP") . "\n"; // 1

// Ejemplo 3: Buscar desde una posición específica
$texto3 = "banana banana banana";
echo "\nEjemplo 3 (Desde posición 7):\n";
echo "Ocurrencias de 'banana' desde pos 7: " . substr_count($texto3, "banana", 7) . "\n"; // 2

// Ejemplo 4: Buscar con posición y longitud
echo "\nEjemplo 4 (Con posición y longitud):\n";
echo "Ocurrencias de 'banana' (pos 7, len 6): " . substr_count($texto3, "banana", 7, 6) . "\n"; // 1

// Ejemplo 5: Uso práctico - contar vocales
$frase = "Hola mundo";
$vocales = 0;
foreach (["a", "e", "i", "o", "u"] as $vocal) {
    $vocales += substr_count(strtolower($frase), $vocal);
}
echo "\nEjemplo 5 (Contar vocales en '$frase'):\n";
echo "Vocales: $vocales\n";
?>
