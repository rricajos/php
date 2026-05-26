<?php
// Ejemplo 1: Eliminar espacios solo al inicio
$texto = "  Hola mundo  ";
echo "Ejemplo 1 (Eliminar espacios al inicio):\n";
echo "Antes: '$texto'\n";
echo "Después: '" . ltrim($texto) . "'\n"; // 'Hola mundo  '

// Ejemplo 2: Eliminar caracteres específicos al inicio
$ruta = "/ruta/al/archivo";
echo "\nEjemplo 2 (Eliminar '/' al inicio):\n";
echo ltrim($ruta, "/") . "\n"; // ruta/al/archivo

// Ejemplo 3: Eliminar ceros a la izquierda
$numero = "000042";
echo "\nEjemplo 3 (Eliminar ceros a la izquierda):\n";
echo ltrim($numero, "0") . "\n"; // 42

// Ejemplo 4: Comparar trim, ltrim y rtrim
$texto4 = "  PHP  ";
echo "\nEjemplo 4 (trim vs ltrim vs rtrim):\n";
echo "trim:  '" . trim($texto4) . "'\n";
echo "ltrim: '" . ltrim($texto4) . "'\n";
echo "rtrim: '" . rtrim($texto4) . "'\n";

// Ejemplo 5: Eliminar prefijo BOM de archivos UTF-8
$con_bom = "\xEF\xBB\xBFContenido del archivo";
echo "\nEjemplo 5 (Eliminar BOM UTF-8):\n";
echo "Con BOM: " . strlen($con_bom) . " bytes\n";
$sin_bom = ltrim($con_bom, "\xEF\xBB\xBF");
echo "Sin BOM: " . strlen($sin_bom) . " bytes\n";
?>
