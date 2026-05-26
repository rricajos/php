<?php
// Ejemplo 1: Comparar dos strings iguales
echo "Ejemplo 1 (Strings iguales):\n";
echo "strcmp('abc', 'abc'): " . strcmp("abc", "abc") . "\n"; // 0

// Ejemplo 2: Primer string es mayor
echo "\nEjemplo 2 (Primer string mayor):\n";
echo "strcmp('b', 'a'): " . strcmp("b", "a") . "\n"; // > 0

// Ejemplo 3: Primer string es menor
echo "\nEjemplo 3 (Primer string menor):\n";
echo "strcmp('a', 'b'): " . strcmp("a", "b") . "\n"; // < 0

// Ejemplo 4: Es sensible a mayúsculas
echo "\nEjemplo 4 (Sensible a case):\n";
echo "strcmp('ABC', 'abc'): " . strcmp("ABC", "abc") . "\n"; // < 0 (mayúsculas < minúsculas)

// Ejemplo 5: Uso práctico - ordenar con usort
$nombres = ["Carlos", "Ana", "Zoe", "Marta"];
usort($nombres, "strcmp");
echo "\nEjemplo 5 (Ordenar con strcmp):\n";
print_r($nombres);

// Ejemplo 6: Comparar resultados
echo "\nEjemplo 6 (Interpretar resultado):\n";
$resultado = strcmp("PHP", "Python");
if ($resultado === 0) {
    echo "Son iguales\n";
} elseif ($resultado < 0) {
    echo "'PHP' viene antes que 'Python'\n";
} else {
    echo "'PHP' viene después que 'Python'\n";
}
?>
