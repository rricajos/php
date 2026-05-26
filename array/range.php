<?php
// Ejemplo 1: Crear un rango numérico
$numeros = range(1, 10);
echo "Ejemplo 1 (Rango 1 a 10):\n";
print_r($numeros);

// Ejemplo 2: Rango con paso personalizado
$pares = range(2, 20, 2);
echo "\nEjemplo 2 (Números pares del 2 al 20):\n";
print_r($pares);

// Ejemplo 3: Rango descendente
$cuenta_atras = range(10, 1);
echo "\nEjemplo 3 (Cuenta atrás del 10 al 1):\n";
print_r($cuenta_atras);

// Ejemplo 4: Rango de letras
$letras = range('a', 'z');
echo "\nEjemplo 4 (Letras a-z):\n";
echo implode(", ", $letras) . "\n";

// Ejemplo 5: Rango de letras mayúsculas
$mayusculas = range('A', 'Z');
echo "\nEjemplo 5 (Letras A-Z):\n";
echo implode(", ", $mayusculas) . "\n";

// Ejemplo 6: Rango con decimales
$decimales = range(0.0, 1.0, 0.1);
echo "\nEjemplo 6 (Rango decimal 0.0 a 1.0):\n";
print_r($decimales);

// Ejemplo 7: Uso práctico - generar opciones de un select
echo "\nEjemplo 7 (Generar años para un formulario):\n";
$anios = range(2020, 2026);
foreach ($anios as $anio) {
    echo "  <option value=\"$anio\">$anio</option>\n";
}
?>
