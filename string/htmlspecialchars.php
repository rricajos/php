<?php
// Ejemplo 1: Escapar caracteres especiales HTML
$html = '<script>alert("XSS")</script>';
echo "Ejemplo 1 (Escapar HTML):\n";
echo "Original: $html\n";
echo "Escapado: " . htmlspecialchars($html) . "\n";

// Ejemplo 2: Caracteres que se convierten
echo "\nEjemplo 2 (Caracteres convertidos):\n";
echo "& -> " . htmlspecialchars("&") . "\n";       // &amp;
echo "\" -> " . htmlspecialchars('"') . "\n";       // &quot;
echo "' -> " . htmlspecialchars("'", ENT_QUOTES) . "\n"; // &#039;
echo "< -> " . htmlspecialchars("<") . "\n";        // &lt;
echo "> -> " . htmlspecialchars(">") . "\n";        // &gt;

// Ejemplo 3: ENT_QUOTES (escapar comillas simples y dobles)
$atributo = "valor con 'comillas' y \"dobles\"";
echo "\nEjemplo 3 (ENT_QUOTES):\n";
echo htmlspecialchars($atributo, ENT_QUOTES) . "\n";

// Ejemplo 4: Especificar encoding
$utf8 = "Café & Résumé <tag>";
echo "\nEjemplo 4 (Con encoding UTF-8):\n";
echo htmlspecialchars($utf8, ENT_QUOTES, 'UTF-8') . "\n";

// Ejemplo 5: Uso práctico - prevenir XSS
$input_usuario = '<img src=x onerror="alert(1)">';
echo "\nEjemplo 5 (Prevenir XSS):\n";
echo "Seguro: " . htmlspecialchars($input_usuario, ENT_QUOTES, 'UTF-8') . "\n";

// Ejemplo 6: htmlspecialchars_decode para revertir
$escapado = "&lt;p&gt;Hola&lt;/p&gt;";
echo "\nEjemplo 6 (Revertir con htmlspecialchars_decode):\n";
echo "Escapado: $escapado\n";
echo "Original: " . htmlspecialchars_decode($escapado) . "\n";
?>
