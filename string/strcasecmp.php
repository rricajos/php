<?php
// Ejemplo 1: Comparar sin distinción de mayúsculas
echo "Ejemplo 1 (Comparar sin case):\n";
echo "strcasecmp('Hola', 'hola'): " . strcasecmp("Hola", "hola") . "\n"; // 0

// Ejemplo 2: Comparar strcmp vs strcasecmp
echo "\nEjemplo 2 (strcmp vs strcasecmp):\n";
echo "strcmp('ABC', 'abc'):    " . strcmp("ABC", "abc") . "\n";    // != 0
echo "strcasecmp('ABC', 'abc'): " . strcasecmp("ABC", "abc") . "\n"; // 0

// Ejemplo 3: Uso práctico - verificar respuesta del usuario
$respuesta = "Si";
echo "\nEjemplo 3 (Verificar respuesta):\n";
if (strcasecmp($respuesta, "si") === 0) {
    echo "El usuario respondió sí\n";
}

// Ejemplo 4: Ordenar sin distinción de mayúsculas
$palabras = ["banana", "Cereza", "almendra", "Dátil"];
usort($palabras, "strcasecmp");
echo "\nEjemplo 4 (Ordenar sin case):\n";
print_r($palabras);

// Ejemplo 5: Comparar con diferentes cases
$tests = [
    ["PHP", "php"],
    ["abc", "ABC"],
    ["hello", "HELLO"],
    ["a", "B"]
];
echo "\nEjemplo 5 (Varias comparaciones):\n";
foreach ($tests as [$a, $b]) {
    $resultado = strcasecmp($a, $b) === 0 ? "iguales" : "diferentes";
    echo "  '$a' vs '$b': $resultado\n";
}
?>
