<?php
// Ejemplo 1: Verificar si una variable está definida y no es null
$nombre = "Ana";
$nulo = null;
echo "Ejemplo 1 (isset básico):\n";
echo "isset(\$nombre): " . (isset($nombre) ? "true" : "false") . "\n"; // true
echo "isset(\$nulo): " . (isset($nulo) ? "true" : "false") . "\n";     // false
echo "isset(\$indefinida): " . (isset($indefinida) ? "true" : "false") . "\n"; // false

// Ejemplo 2: isset con arrays
$datos = ["nombre" => "Ana", "edad" => 30, "email" => null];
echo "\nEjemplo 2 (isset con arrays):\n";
echo "isset(\$datos['nombre']): " . (isset($datos['nombre']) ? "true" : "false") . "\n"; // true
echo "isset(\$datos['email']): " . (isset($datos['email']) ? "true" : "false") . "\n";   // false (es null)
echo "isset(\$datos['tel']): " . (isset($datos['tel']) ? "true" : "false") . "\n";       // false

// Ejemplo 3: isset con arrays multidimensionales
$config = ["db" => ["host" => "localhost", "port" => 3306]];
echo "\nEjemplo 3 (Arrays multidimensionales):\n";
echo "isset(\$config['db']['host']): " . (isset($config['db']['host']) ? "true" : "false") . "\n"; // true
echo "isset(\$config['db']['user']): " . (isset($config['db']['user']) ? "true" : "false") . "\n"; // false

// Ejemplo 4: isset con múltiples variables
$a = 1; $b = 2; $c = 3;
echo "\nEjemplo 4 (Múltiples variables):\n";
echo "isset(\$a, \$b, \$c): " . (isset($a, $b, $c) ? "true" : "false") . "\n"; // true (todas definidas)

// Ejemplo 5: isset vs empty vs is_null
$valor = 0;
echo "\nEjemplo 5 (isset vs empty vs is_null con 0):\n";
echo "isset: " . (isset($valor) ? "true" : "false") . "\n";     // true
echo "empty: " . (empty($valor) ? "true" : "false") . "\n";     // true
echo "is_null: " . (is_null($valor) ? "true" : "false") . "\n"; // false
?>
