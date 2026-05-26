<?php
// Ejemplo 1: Eliminar espacios al inicio y al final
$texto = "  Hola mundo  ";
echo "Ejemplo 1 (Eliminar espacios):\n";
echo "Antes: '$texto'\n";
echo "Después: '" . trim($texto) . "'\n";

// Ejemplo 2: trim elimina espacios, tabs, newlines, etc.
$con_espacios = "\t\n  Hola  \n\t";
echo "\nEjemplo 2 (Elimina tabs y newlines):\n";
echo "Antes: " . var_export($con_espacios, true) . "\n";
echo "Después: '" . trim($con_espacios) . "'\n";

// Ejemplo 3: Eliminar caracteres específicos
$ruta = "/ruta/al/archivo/";
echo "\nEjemplo 3 (Eliminar caracteres específicos '/'):\n";
echo "Antes: $ruta\n";
echo "Después: " . trim($ruta, "/") . "\n";

// Ejemplo 4: Eliminar múltiples caracteres
$dato = "***Hola***";
echo "\nEjemplo 4 (Eliminar '*'):\n";
echo trim($dato, "*") . "\n"; // Hola

// Ejemplo 5: Uso práctico - limpiar input de usuario
$inputs = ["  usuario  ", "\temail@test.com\n", "  contraseña "];
echo "\nEjemplo 5 (Limpiar inputs):\n";
foreach ($inputs as $input) {
    echo "'" . trim($input) . "'\n";
}

// Ejemplo 6: Caracteres que trim elimina por defecto
echo "\nEjemplo 6 (Caracteres eliminados por defecto):\n";
echo "Espacio: ' '\n";
echo "Tab: \\t\n";
echo "Newline: \\n\n";
echo "Retorno de carro: \\r\n";
echo "NUL byte: \\0\n";
echo "Tab vertical: \\v\n";
?>
