<?php
// Ejemplo 1: Última posición insensible a mayúsculas
$texto = "PHP es genial, php es moderno, Php es potente";
echo "Ejemplo 1 (Última posición de 'php' sin case):\n";
echo "Posición: " . strripos($texto, "php") . "\n"; // 31

// Ejemplo 2: Comparar strrpos vs strripos
$texto2 = "ABC abc ABC abc";
echo "\nEjemplo 2 (strrpos vs strripos):\n";
echo "strrpos('abc'): " . strrpos($texto2, "abc") . "\n";    // 12
echo "strripos('abc'): " . strripos($texto2, "abc") . "\n";  // 12
echo "strrpos('ABC'): " . strrpos($texto2, "ABC") . "\n";    // 8
echo "strripos('ABC'): " . strripos($texto2, "ABC") . "\n";  // 12

// Ejemplo 3: Buscar desde un offset
echo "\nEjemplo 3 (Buscar con offset):\n";
echo "strripos desde pos 0: " . strripos($texto2, "abc", 0) . "\n";

// Ejemplo 4: Uso práctico - encontrar última etiqueta HTML
$html = "<DIV>Hola</DIV><div>Mundo</div><Div>PHP</Div>";
$pos = strripos($html, "<div>");
echo "\nEjemplo 4 (Última apertura de <div>):\n";
echo "Posición: $pos\n";
echo "Contenido desde ahí: " . substr($html, $pos) . "\n";
?>
