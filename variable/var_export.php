<?php
// Ejemplo 1: Exportar como código PHP válido
$datos = ["nombre" => "Ana", "edad" => 30, "activo" => true];
echo "Ejemplo 1 (Exportar array):\n";
var_export($datos);
echo "\n";

// Ejemplo 2: Devolver como string (return = true)
$string = var_export($datos, true);
echo "\nEjemplo 2 (Como string):\n";
echo $string . "\n";

// Ejemplo 3: Diferentes tipos de datos
echo "\nEjemplo 3 (Tipos de datos):\n";
var_export(42); echo "\n";        // 42
var_export(3.14); echo "\n";      // 3.14
var_export("hola"); echo "\n";    // 'hola'
var_export(true); echo "\n";      // true
var_export(null); echo "\n";      // NULL

// Ejemplo 4: El output es código PHP ejecutable
$original = ["x" => 1, "y" => [2, 3]];
$codigo = var_export($original, true);
echo "\nEjemplo 4 (Código ejecutable):\n";
echo "Código: $codigo\n";
eval("\$restaurado = $codigo;");
echo "Restaurado:\n";
print_r($restaurado);

// Ejemplo 5: Comparar var_dump, print_r, var_export
$val = ["clave" => "valor", "num" => 42];
echo "\nEjemplo 5 (Comparación de funciones de debug):\n";
echo "var_dump: muestra tipo + valor (para debug)\n";
echo "print_r: muestra estructura legible (para debug)\n";
echo "var_export: muestra código PHP válido (para serialización)\n";
?>
