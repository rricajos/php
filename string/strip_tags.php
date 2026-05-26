<?php
// Ejemplo 1: Eliminar todas las etiquetas HTML
$html = "<p>Hola <b>mundo</b></p>";
echo "Ejemplo 1 (Eliminar todas las etiquetas):\n";
echo strip_tags($html) . "\n"; // Hola mundo

// Ejemplo 2: Permitir ciertas etiquetas
$html2 = "<p>Hola <b>mundo</b> <i>PHP</i> <script>alert(1)</script></p>";
echo "\nEjemplo 2 (Permitir <b> e <i>):\n";
echo strip_tags($html2, '<b><i>') . "\n";

// Ejemplo 3: PHP 7.4+ permite array de etiquetas permitidas
echo "\nEjemplo 3 (Array de etiquetas permitidas, PHP 7.4+):\n";
echo strip_tags($html2, ['b', 'i']) . "\n";

// Ejemplo 4: Etiquetas auto-cerradas
$html4 = "Texto<br>con<br/>saltos<hr>y líneas";
echo "\nEjemplo 4 (Etiquetas auto-cerradas):\n";
echo strip_tags($html4) . "\n";

// Ejemplo 5: Uso práctico - limpiar input de usuario
$comentario = '<div onclick="hack()">Hola <b>mundo</b></div><script>alert("xss")</script>';
echo "\nEjemplo 5 (Limpiar input de usuario):\n";
echo "Original: $comentario\n";
echo "Limpio: " . strip_tags($comentario, '<b>') . "\n";

// Ejemplo 6: strip_tags no valida HTML, simplemente elimina etiquetas
$malformado = "<<b>hola<</b>";
echo "\nEjemplo 6 (HTML malformado):\n";
echo strip_tags($malformado) . "\n";
?>
