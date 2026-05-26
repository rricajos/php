<?php
// Ejemplo 1: Convertir diferentes tipos a string
echo "Ejemplo 1 (Convertir a string):\n";
echo "strval(42): '" . strval(42) . "'\n";         // "42"
echo "strval(3.14): '" . strval(3.14) . "'\n";     // "3.14"
echo "strval(true): '" . strval(true) . "'\n";     // "1"
echo "strval(false): '" . strval(false) . "'\n";   // ""
echo "strval(null): '" . strval(null) . "'\n";     // ""

// Ejemplo 2: strval vs (string) cast
$numero = 42;
echo "\nEjemplo 2 (strval vs cast):\n";
echo "strval: '" . strval($numero) . "'\n";
echo "(string): '" . (string)$numero . "'\n";

// Ejemplo 3: Con objetos que implementan __toString
$obj = new class {
    public function __toString(): string {
        return "soy un objeto";
    }
};
echo "\nEjemplo 3 (Objeto con __toString):\n";
echo "strval: '" . strval($obj) . "'\n";

// Ejemplo 4: Uso práctico - concatenación segura
$datos = ["nombre" => "Ana", "edad" => 30, "activo" => true];
echo "\nEjemplo 4 (Concatenación segura):\n";
foreach ($datos as $clave => $valor) {
    echo "  $clave: " . strval($valor) . "\n";
}

// Ejemplo 5: Verificar tipo después de conversión
$original = 42;
$convertido = strval($original);
echo "\nEjemplo 5 (Tipo después de conversión):\n";
echo "Original: " . gettype($original) . " = $original\n";
echo "Convertido: " . gettype($convertido) . " = '$convertido'\n";
?>
