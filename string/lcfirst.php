<?php
// Ejemplo 1: Convertir el primer carácter a minúscula
$texto = "Hola mundo";
echo "Ejemplo 1 (Primer carácter a minúscula):\n";
echo lcfirst($texto) . "\n"; // hola mundo

// Ejemplo 2: String todo en mayúsculas
$mayusculas = "PHP Es Genial";
echo "\nEjemplo 2 (Solo cambia el primer carácter):\n";
echo lcfirst($mayusculas) . "\n"; // pHP Es Genial

// Ejemplo 3: Ya empieza en minúscula (sin cambios)
$ya_minuscula = "ya está en minúscula";
echo "\nEjemplo 3 (Ya en minúscula):\n";
echo lcfirst($ya_minuscula) . "\n";

// Ejemplo 4: Uso práctico - convertir nombre de clase a variable
$className = "UserController";
$varName = lcfirst($className);
echo "\nEjemplo 4 (Clase a variable, camelCase):\n";
echo "Clase: $className\n";
echo "Variable: \$$varName\n";

// Ejemplo 5: Comparar lcfirst con ucfirst
$texto5 = "Ejemplo";
echo "\nEjemplo 5 (lcfirst vs ucfirst):\n";
echo "Original: $texto5\n";
echo "lcfirst: " . lcfirst($texto5) . "\n";
echo "ucfirst: " . ucfirst($texto5) . "\n";
?>
