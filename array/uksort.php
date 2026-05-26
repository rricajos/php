<?php
// Ejemplo 1: Ordenar por claves con función de comparación personalizada
$array1 = ["banana" => 2, "manzana" => 5, "cereza" => 3];
uksort($array1, function($a, $b) {
    return $a <=> $b; // Orden alfabético por clave
});
echo "Ejemplo 1 (Orden alfabético por clave):\n";
print_r($array1);

// Ejemplo 2: Ordenar claves por longitud
$array2 = ["php" => 1, "javascript" => 2, "go" => 3, "python" => 4];
uksort($array2, function($a, $b) {
    return strlen($a) - strlen($b);
});
echo "\nEjemplo 2 (Claves ordenadas por longitud):\n";
print_r($array2);

// Ejemplo 3: Ordenar claves numéricas como strings vs como números
$array3 = ["10" => "diez", "9" => "nueve", "100" => "cien", "1" => "uno"];
$como_string = $array3;
$como_numero = $array3;
uksort($como_string, "strcmp");
uksort($como_numero, function($a, $b) {
    return (int)$a - (int)$b;
});
echo "\nEjemplo 3 (Comparar como string vs como número):\n";
echo "Como string (strcmp):\n";
print_r($como_string);
echo "Como número:\n";
print_r($como_numero);

// Ejemplo 4: Ordenar claves de forma descendente
$array4 = ["a" => 1, "c" => 3, "b" => 2, "d" => 4];
uksort($array4, function($a, $b) {
    return $b <=> $a; // Descendente
});
echo "\nEjemplo 4 (Claves en orden descendente):\n";
print_r($array4);
?>
