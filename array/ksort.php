<?php
// Ejemplo 1: Ordenar un array asociativo por claves
$array1 = ["c" => 3, "a" => 1, "b" => 2];
ksort($array1);
echo "Ejemplo 1 (Ordenar por claves):\n";
print_r($array1);

// Ejemplo 2: Ordenar con claves numéricas desordenadas
$array2 = [3 => "tres", 1 => "uno", 2 => "dos", 0 => "cero"];
ksort($array2);
echo "\nEjemplo 2 (Claves numéricas):\n";
print_r($array2);

// Ejemplo 3: Ordenar un diccionario
$traducciones = [
    "orange" => "naranja",
    "apple" => "manzana",
    "banana" => "banana",
    "cherry" => "cereza"
];
ksort($traducciones);
echo "\nEjemplo 3 (Diccionario ordenado):\n";
foreach ($traducciones as $en => $es) {
    echo "  $en => $es\n";
}

// Ejemplo 4: Ordenar con SORT_NATURAL
$archivos = ["file10" => "dato10", "file2" => "dato2", "file1" => "dato1"];
ksort($archivos, SORT_NATURAL);
echo "\nEjemplo 4 (SORT_NATURAL por claves):\n";
print_r($archivos);

// Ejemplo 5: Diferencia entre ksort y asort
$array5 = ["z" => 1, "a" => 3, "m" => 2];
$por_clave = $array5;
$por_valor = $array5;
ksort($por_clave);
asort($por_valor);
echo "\nEjemplo 5 (ksort vs asort):\n";
echo "ksort (por clave):\n";
print_r($por_clave);
echo "asort (por valor):\n";
print_r($por_valor);
?>
