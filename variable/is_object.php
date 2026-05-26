<?php
// Ejemplo 1: Verificar si es un objeto
echo "Ejemplo 1 (Verificar objetos):\n";
var_dump(is_object(new stdClass()));   // true
var_dump(is_object([1, 2, 3]));        // false
var_dump(is_object("texto"));           // false
var_dump(is_object(null));              // false

// Ejemplo 2: Diferentes tipos de objetos
$obj1 = new stdClass();
$obj2 = new ArrayObject([1, 2]);
$obj3 = new DateTime();
echo "\nEjemplo 2 (Diferentes objetos):\n";
echo "stdClass: " . (is_object($obj1) ? "true" : "false") . "\n";
echo "ArrayObject: " . (is_object($obj2) ? "true" : "false") . "\n";
echo "DateTime: " . (is_object($obj3) ? "true" : "false") . "\n";

// Ejemplo 3: Clases anónimas también son objetos
$anonimo = new class {
    public function saludar(): string { return "Hola"; }
};
echo "\nEjemplo 3 (Clase anónima):\n";
echo "is_object: " . (is_object($anonimo) ? "true" : "false") . "\n";
echo "Método: " . $anonimo->saludar() . "\n";

// Ejemplo 4: Closures son objetos
$closure = function() { return 42; };
echo "\nEjemplo 4 (Closure):\n";
echo "is_object: " . (is_object($closure) ? "true" : "false") . "\n";
echo "get_class: " . get_class($closure) . "\n";

// Ejemplo 5: instanceof vs is_object
echo "\nEjemplo 5 (is_object vs instanceof):\n";
echo "is_object solo verifica que sea objeto\n";
echo "instanceof verifica la clase específica:\n";
echo "\$obj3 instanceof DateTime: " . ($obj3 instanceof DateTime ? "true" : "false") . "\n";
?>
