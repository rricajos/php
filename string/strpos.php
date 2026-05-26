<?php
// Ejemplo 1: Encontrar la posición de un substring
$texto = "Hola mundo PHP";
echo "Ejemplo 1 (Posición de 'mundo'):\n";
echo "Posición: " . strpos($texto, "mundo") . "\n"; // 5

// Ejemplo 2: Substring no encontrado
echo "\nEjemplo 2 (No encontrado):\n";
$resultado = strpos($texto, "Python");
var_dump($resultado); // false

// Ejemplo 3: Buscar desde una posición específica
$texto3 = "abcabc";
echo "\nEjemplo 3 (Buscar desde posición 3):\n";
echo "Primera 'abc': " . strpos($texto3, "abc") . "\n"; // 0
echo "Segunda 'abc': " . strpos($texto3, "abc", 3) . "\n"; // 3

// Ejemplo 4: Cuidado con la comparación (usar ===)
$texto4 = "PHP es genial";
echo "\nEjemplo 4 (Comparación estricta):\n";
if (strpos($texto4, "PHP") !== false) {
    echo "'PHP' encontrado en posición " . strpos($texto4, "PHP") . "\n";
}
// Importante: usar !== false porque la posición 0 es falsy

// Ejemplo 5: Es sensible a mayúsculas
$texto5 = "Hola Mundo";
echo "\nEjemplo 5 (Sensible a case):\n";
var_dump(strpos($texto5, "hola")); // false
var_dump(strpos($texto5, "Hola")); // int(0)

// Ejemplo 6: Encontrar todas las ocurrencias
$texto6 = "el gato el perro el pájaro";
$buscar = "el";
$posiciones = [];
$offset = 0;
while (($pos = strpos($texto6, $buscar, $offset)) !== false) {
    $posiciones[] = $pos;
    $offset = $pos + 1;
}
echo "\nEjemplo 6 (Todas las posiciones de 'el'):\n";
print_r($posiciones);
?>
