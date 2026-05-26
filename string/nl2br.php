<?php
// Ejemplo 1: Convertir saltos de línea a <br>
$texto = "Línea 1\nLínea 2\nLínea 3";
echo "Ejemplo 1 (Saltos de línea a <br>):\n";
echo nl2br($texto) . "\n";

// Ejemplo 2: Con \r\n (Windows)
$windows = "Línea 1\r\nLínea 2\r\nLínea 3";
echo "\nEjemplo 2 (\\r\\n):\n";
echo nl2br($windows) . "\n";

// Ejemplo 3: Segundo parámetro - usar XHTML o no
$texto3 = "Hola\nMundo";
echo "\nEjemplo 3 (XHTML vs HTML):\n";
echo "XHTML (true): " . nl2br($texto3, true) . "\n";   // <br />
echo "HTML (false): " . nl2br($texto3, false) . "\n";   // <br>

// Ejemplo 4: Uso práctico - mostrar texto de usuario en HTML
$comentario = "Este es mi comentario.\nTiene varias líneas.\n\nY un párrafo separado.";
echo "\nEjemplo 4 (Comentario de usuario):\n";
echo "<p>" . nl2br(htmlspecialchars($comentario)) . "</p>\n";

// Ejemplo 5: nl2br no elimina los \n, solo añade <br>
$texto5 = "A\nB";
echo "\nEjemplo 5 (No elimina \\n):\n";
echo var_export(nl2br($texto5), true) . "\n";
?>
