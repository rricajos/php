<?php
// Ejemplo 1: Asignar variables desde un array
$datos = ["Ana", 30, "Madrid"];
list($nombre, $edad, $ciudad) = $datos;
echo "Ejemplo 1 (Asignar desde array):\n";
echo "Nombre: $nombre, Edad: $edad, Ciudad: $ciudad\n";

// Ejemplo 2: Sintaxis corta (PHP 7.1+)
$coordenadas = [40.4168, -3.7038];
[$lat, $lng] = $coordenadas;
echo "\nEjemplo 2 (Sintaxis corta []):\n";
echo "Latitud: $lat, Longitud: $lng\n";

// Ejemplo 3: Omitir elementos con list
$datos3 = ["rojo", "verde", "azul", "amarillo"];
list(, $segundo, , $cuarto) = $datos3;
echo "\nEjemplo 3 (Omitir elementos):\n";
echo "Segundo: $segundo, Cuarto: $cuarto\n";

// Ejemplo 4: list con arrays anidados
$personas = [["Ana", 30], ["Carlos", 25], ["Marta", 35]];
echo "\nEjemplo 4 (Arrays anidados con list):\n";
foreach ($personas as [$nombre, $edad]) {
    echo "  $nombre tiene $edad años\n";
}

// Ejemplo 5: list con claves asociativas (PHP 7.1+)
$usuario = ["nombre" => "Luis", "email" => "luis@email.com", "edad" => 28];
["nombre" => $nombre, "email" => $email] = $usuario;
echo "\nEjemplo 5 (Claves asociativas, PHP 7.1+):\n";
echo "Nombre: $nombre, Email: $email\n";

// Ejemplo 6: Uso práctico - swap de variables
$a = "primero";
$b = "segundo";
[$a, $b] = [$b, $a];
echo "\nEjemplo 6 (Swap de variables):\n";
echo "a: $a, b: $b\n"; // a: segundo, b: primero

// Ejemplo 7: list con el resultado de una función
function obtenerMinMax(array $arr): array {
    return [min($arr), max($arr)];
}
[$minimo, $maximo] = obtenerMinMax([5, 2, 8, 1, 9]);
echo "\nEjemplo 7 (Resultado de función):\n";
echo "Mínimo: $minimo, Máximo: $maximo\n";
?>
