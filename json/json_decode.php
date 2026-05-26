<?php
// Ejemplo 1: Decodificar JSON a objeto (por defecto)
$json = '{"nombre":"Ana","edad":30,"ciudad":"Madrid"}';
$objeto = json_decode($json);
echo "Ejemplo 1 (JSON a objeto):\n";
echo "Nombre: $objeto->nombre\n";
echo "Edad: $objeto->edad\n";

// Ejemplo 2: Decodificar a array asociativo (associative = true)
$array = json_decode($json, true);
echo "\nEjemplo 2 (JSON a array asociativo):\n";
echo "Nombre: {$array['nombre']}\n";
echo "Edad: {$array['edad']}\n";

// Ejemplo 3: Decodificar array JSON
$json_array = '["manzana","banana","cereza"]';
$frutas = json_decode($json_array, true);
echo "\nEjemplo 3 (Array JSON):\n";
print_r($frutas);

// Ejemplo 4: JSON anidado
$json_anidado = '{"empresa":"TechCorp","empleados":[{"nombre":"Ana","rol":"Dev"},{"nombre":"Carlos","rol":"QA"}]}';
$datos = json_decode($json_anidado, true);
echo "\nEjemplo 4 (JSON anidado):\n";
echo "Empresa: {$datos['empresa']}\n";
foreach ($datos['empleados'] as $emp) {
    echo "  - {$emp['nombre']}: {$emp['rol']}\n";
}

// Ejemplo 5: Profundidad máxima
$profundo = '{"a":{"b":{"c":{"d":"valor"}}}}';
echo "\nEjemplo 5 (Profundidad):\n";
$resultado = json_decode($profundo, true, 2); // Solo 2 niveles
var_dump($resultado); // null (excede la profundidad)
$resultado = json_decode($profundo, true, 10);
print_r($resultado); // Funciona

// Ejemplo 6: JSON inválido devuelve null
$invalido = "esto no es JSON";
echo "\nEjemplo 6 (JSON inválido):\n";
$resultado = json_decode($invalido);
var_dump($resultado); // null

// Ejemplo 7: Tipos de datos JSON a PHP
$tipos = '{"entero":42,"float":3.14,"string":"hola","bool":true,"nulo":null}';
$datos = json_decode($tipos, true);
echo "\nEjemplo 7 (Tipos JSON a PHP):\n";
foreach ($datos as $clave => $valor) {
    echo "  $clave: " . gettype($valor) . " = " . var_export($valor, true) . "\n";
}
?>
