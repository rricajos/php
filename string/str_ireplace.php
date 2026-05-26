<?php
// Ejemplo 1: Reemplazo insensible a mayúsculas
$texto = "PHP es genial, php es potente, Php es moderno";
echo "Ejemplo 1 (Reemplazo sin case):\n";
echo str_ireplace("php", "Python", $texto) . "\n";

// Ejemplo 2: Comparar str_replace vs str_ireplace
$texto2 = "Hola HOLA hola HoLa";
echo "\nEjemplo 2 (str_replace vs str_ireplace):\n";
echo "str_replace('hola', '*'):  " . str_replace("hola", "*", $texto2) . "\n";
echo "str_ireplace('hola', '*'): " . str_ireplace("hola", "*", $texto2) . "\n";

// Ejemplo 3: Reemplazo con arrays
$texto3 = "Rojo AZUL verde AMARILLO";
$buscar = ["rojo", "azul", "verde", "amarillo"];
$reemplazar = ["RED", "BLUE", "GREEN", "YELLOW"];
echo "\nEjemplo 3 (Arrays, insensible a case):\n";
echo str_ireplace($buscar, $reemplazar, $texto3) . "\n";

// Ejemplo 4: Contar reemplazos
$texto4 = "PHP php Php pHp";
$resultado = str_ireplace("php", "***", $texto4, $count);
echo "\nEjemplo 4 (Contar reemplazos):\n";
echo "Resultado: $resultado\n";
echo "Reemplazos: $count\n"; // 4

// Ejemplo 5: Uso práctico - censurar palabras sin importar case
$comentario = "Esto es MALO y malo y Malo";
echo "\nEjemplo 5 (Censurar palabras):\n";
echo str_ireplace("malo", "***", $comentario) . "\n";
?>
