<?php
// Ejemplo 1: Dividir un string por un delimitador
$texto = "rojo,verde,azul";
$colores = explode(",", $texto);
echo "Ejemplo 1 (Dividir por coma):\n";
print_r($colores);

// Ejemplo 2: Dividir con límite
$csv = "Ana,30,Madrid,España";
$datos = explode(",", $csv, 3);
echo "\nEjemplo 2 (Con límite 3):\n";
print_r($datos); // El tercer elemento contiene el resto

// Ejemplo 3: Dividir por espacio
$frase = "PHP es un lenguaje genial";
$palabras = explode(" ", $frase);
echo "\nEjemplo 3 (Dividir por espacio):\n";
print_r($palabras);

// Ejemplo 4: Dividir líneas de texto
$multilínea = "línea 1\nlínea 2\nlínea 3";
$lineas = explode("\n", $multilínea);
echo "\nEjemplo 4 (Dividir por salto de línea):\n";
print_r($lineas);

// Ejemplo 5: Delimitador multi-carácter
$texto5 = "uno::dos::tres::cuatro";
$partes = explode("::", $texto5);
echo "\nEjemplo 5 (Delimitador '::'):\n";
print_r($partes);

// Ejemplo 6: Uso práctico con list
$fecha = "2026-05-26";
[$anio, $mes, $dia] = explode("-", $fecha);
echo "\nEjemplo 6 (Parsear fecha con list):\n";
echo "Año: $anio, Mes: $mes, Día: $dia\n";
?>
