<?php
// Ejemplo 1: Eliminar espacios solo al final
$texto = "  Hola mundo  ";
echo "Ejemplo 1 (Eliminar espacios al final):\n";
echo "Antes: '$texto'\n";
echo "Después: '" . rtrim($texto) . "'\n"; // '  Hola mundo'

// Ejemplo 2: Eliminar caracteres específicos al final
$ruta = "/ruta/al/archivo/";
echo "\nEjemplo 2 (Eliminar '/' al final):\n";
echo rtrim($ruta, "/") . "\n"; // /ruta/al/archivo

// Ejemplo 3: Eliminar saltos de línea al final (típico al leer archivos)
$linea = "Contenido de la línea\r\n";
echo "\nEjemplo 3 (Eliminar \\r\\n al final):\n";
echo "Antes: " . var_export($linea, true) . "\n";
echo "Después: '" . rtrim($linea, "\r\n") . "'\n";

// Ejemplo 4: chop() es un alias de rtrim()
$texto4 = "Hola!!!";
echo "\nEjemplo 4 (chop es alias de rtrim):\n";
echo "rtrim: " . rtrim($texto4, "!") . "\n";
echo "chop:  " . chop($texto4, "!") . "\n";

// Ejemplo 5: Uso práctico - limpiar coma final de una lista generada
$items = ["a", "b", "c"];
$lista = "";
foreach ($items as $item) {
    $lista .= "$item, ";
}
echo "\nEjemplo 5 (Eliminar coma final):\n";
echo "Antes: '$lista'\n";
echo "Después: '" . rtrim($lista, ", ") . "'\n";
?>
