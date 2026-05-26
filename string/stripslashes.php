<?php
// Ejemplo 1: Eliminar backslashes de escape
$escapado = 'Ella dijo \"Hola\" y él respondió \'Adiós\'';
echo "Ejemplo 1 (Eliminar backslashes):\n";
echo "Escapado: $escapado\n";
echo "Limpio: " . stripslashes($escapado) . "\n";

// Ejemplo 2: Revertir addslashes
$original = "It's a \"test\" with \\ backslash";
$escapado2 = addslashes($original);
$revertido = stripslashes($escapado2);
echo "\nEjemplo 2 (Revertir addslashes):\n";
echo "Original: $original\n";
echo "addslashes: $escapado2\n";
echo "stripslashes: $revertido\n";

// Ejemplo 3: Solo elimina un nivel de backslashes
$doble = "doble\\\\escape";
echo "\nEjemplo 3 (Solo un nivel):\n";
echo "Antes: $doble\n";
echo "Después: " . stripslashes($doble) . "\n";

// Ejemplo 4: Uso práctico - limpiar datos de magic_quotes (legacy)
$datos = [
    "nombre" => "O\\'Brien",
    "ciudad" => "San Sebasti\\'an"
];
echo "\nEjemplo 4 (Limpiar datos legacy):\n";
foreach ($datos as $clave => $valor) {
    echo "  $clave: " . stripslashes($valor) . "\n";
}
?>
