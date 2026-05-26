<?php
// Ejemplo 1: Verificar si es null
echo "Ejemplo 1 (Verificar null):\n";
var_dump(is_null(null));    // true
var_dump(is_null(0));       // false
var_dump(is_null(""));      // false
var_dump(is_null(false));   // false

// Ejemplo 2: Variable no definida
echo "\nEjemplo 2 (Variable no definida):\n";
var_dump(is_null($no_definida ?? null)); // true

// Ejemplo 3: is_null vs === null
$valor = null;
echo "\nEjemplo 3 (is_null vs === null):\n";
echo "is_null: " . (is_null($valor) ? "true" : "false") . "\n";
echo "=== null: " . ($valor === null ? "true" : "false") . "\n";
// Ambos son equivalentes, pero === null es más rápido

// Ejemplo 4: Operador ?? (null coalescing)
echo "\nEjemplo 4 (Null coalescing ??):\n";
$nombre = null;
$resultado = $nombre ?? "Anónimo";
echo "Resultado: $resultado\n"; // Anónimo

// Ejemplo 5: Uso práctico - verificar resultado de función
$array = ["a" => 1, "b" => 2];
$valor = $array["c"] ?? null;
echo "\nEjemplo 5 (Verificar resultado):\n";
if (is_null($valor)) {
    echo "La clave 'c' no existe\n";
}
?>
