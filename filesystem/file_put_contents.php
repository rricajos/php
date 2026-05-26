<?php
// Ejemplo 1: Escribir contenido en un archivo
$archivo = tempnam(sys_get_temp_dir(), 'php');
$bytes = file_put_contents($archivo, "Hola mundo PHP\n");
echo "Ejemplo 1 (Escribir archivo):\n";
echo "Bytes escritos: $bytes\n";
echo "Contenido: " . file_get_contents($archivo) . "\n";

// Ejemplo 2: Añadir contenido (FILE_APPEND)
file_put_contents($archivo, "Segunda línea\n", FILE_APPEND);
echo "Ejemplo 2 (Append):\n";
echo file_get_contents($archivo);

// Ejemplo 3: Escribir con bloqueo exclusivo (LOCK_EX)
file_put_contents($archivo, "Con bloqueo\n", LOCK_EX);
echo "\nEjemplo 3 (Con LOCK_EX):\n";
echo file_get_contents($archivo);

// Ejemplo 4: Combinar flags
file_put_contents($archivo, "Línea extra\n", FILE_APPEND | LOCK_EX);
echo "\nEjemplo 4 (APPEND + LOCK_EX):\n";
echo file_get_contents($archivo);

// Ejemplo 5: Escribir un array (cada elemento es una línea)
$lineas = ["línea 1\n", "línea 2\n", "línea 3\n"];
file_put_contents($archivo, $lineas);
echo "Ejemplo 5 (Escribir array):\n";
echo file_get_contents($archivo);

// Ejemplo 6: Guardar datos JSON
$datos = ["usuario" => "Ana", "rol" => "admin"];
file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT));
echo "\nEjemplo 6 (Guardar JSON):\n";
echo file_get_contents($archivo) . "\n";

// Ejemplo 7: Retorno de file_put_contents
echo "\nEjemplo 7 (Retorno):\n";
echo "Devuelve el número de bytes escritos, o false si falla\n";

unlink($archivo);
?>
