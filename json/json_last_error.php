<?php
// Ejemplo 1: Sin error
$json_valido = '{"nombre":"Ana"}';
json_decode($json_valido);
echo "Ejemplo 1 (Sin error):\n";
echo "Código: " . json_last_error() . "\n"; // 0 (JSON_ERROR_NONE)
echo "Mensaje: " . json_last_error_msg() . "\n"; // "No error"

// Ejemplo 2: Error de sintaxis
$json_invalido = '{"nombre": "Ana",}'; // Coma extra
json_decode($json_invalido);
echo "\nEjemplo 2 (Error de sintaxis):\n";
echo "Código: " . json_last_error() . "\n"; // 4 (JSON_ERROR_SYNTAX)
echo "Mensaje: " . json_last_error_msg() . "\n";

// Ejemplo 3: Profundidad máxima excedida
$profundo = '{"a":{"b":{"c":"d"}}}';
json_decode($profundo, true, 2);
echo "\nEjemplo 3 (Profundidad excedida):\n";
echo "Código: " . json_last_error() . "\n"; // 1 (JSON_ERROR_DEPTH)
echo "Mensaje: " . json_last_error_msg() . "\n";

// Ejemplo 4: Constantes de error
echo "\nEjemplo 4 (Constantes de error):\n";
echo "JSON_ERROR_NONE: " . JSON_ERROR_NONE . "\n";
echo "JSON_ERROR_DEPTH: " . JSON_ERROR_DEPTH . "\n";
echo "JSON_ERROR_STATE_MISMATCH: " . JSON_ERROR_STATE_MISMATCH . "\n";
echo "JSON_ERROR_CTRL_CHAR: " . JSON_ERROR_CTRL_CHAR . "\n";
echo "JSON_ERROR_SYNTAX: " . JSON_ERROR_SYNTAX . "\n";
echo "JSON_ERROR_UTF8: " . JSON_ERROR_UTF8 . "\n";

// Ejemplo 5: Uso práctico - manejo de errores
function decodificarJSON(string $json): mixed {
    $datos = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "  Error JSON: " . json_last_error_msg() . "\n";
        return null;
    }
    return $datos;
}
echo "\nEjemplo 5 (Manejo de errores):\n";
echo "  Válido: ";
var_dump(decodificarJSON('{"ok":true}'));
echo "  Inválido: ";
var_dump(decodificarJSON('{mal json}'));

// Ejemplo 6: JSON_THROW_ON_ERROR (PHP 7.3+)
echo "\nEjemplo 6 (JSON_THROW_ON_ERROR):\n";
try {
    json_decode('{invalido}', true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo "JsonException: " . $e->getMessage() . "\n";
}
?>
