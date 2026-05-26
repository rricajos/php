<?php
// Ejemplo 1: Reemplazar parte de un string
$texto = "Hola mundo";
echo "Ejemplo 1 (Reemplazar desde posición 5):\n";
echo substr_replace($texto, "PHP", 5) . "\n"; // Hola PHP

// Ejemplo 2: Reemplazar con longitud específica
echo "\nEjemplo 2 (Reemplazar 'mundo' por 'PHP'):\n";
echo substr_replace($texto, "PHP", 5, 5) . "\n"; // Hola PHP

// Ejemplo 3: Insertar sin eliminar (longitud 0)
echo "\nEjemplo 3 (Insertar en posición 5):\n";
echo substr_replace($texto, "bonito ", 5, 0) . "\n"; // Hola bonito mundo

// Ejemplo 4: Eliminar sin insertar (reemplazo vacío)
echo "\nEjemplo 4 (Eliminar desde posición 4):\n";
echo substr_replace($texto, "", 4) . "\n"; // Hola

// Ejemplo 5: Posición negativa (desde el final)
echo "\nEjemplo 5 (Reemplazar últimos 5 caracteres):\n";
echo substr_replace($texto, "PHP", -5) . "\n"; // Hola PHP

// Ejemplo 6: Uso práctico - censurar parte de un email
$email = "usuario@ejemplo.com";
$arroba = strpos($email, "@");
$censurado = substr_replace($email, str_repeat("*", $arroba - 2), 2, $arroba - 2);
echo "\nEjemplo 6 (Censurar email):\n";
echo "Original: $email\n";
echo "Censurado: $censurado\n";

// Ejemplo 7: Aplicar a un array de strings
$nombres = ["Ana", "Carlos", "Marta"];
$resultado = substr_replace($nombres, "...", 1);
echo "\nEjemplo 7 (Aplicar a array):\n";
print_r($resultado);
?>
