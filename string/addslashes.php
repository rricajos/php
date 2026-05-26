<?php
// Ejemplo 1: Escapar comillas y backslash
$texto = 'Ella dijo "Hola" y él respondió \'Adiós\'';
echo "Ejemplo 1 (Escapar comillas):\n";
echo "Original: $texto\n";
echo "Escapado: " . addslashes($texto) . "\n";

// Ejemplo 2: Caracteres que addslashes escapa
echo "\nEjemplo 2 (Caracteres escapados):\n";
echo "Comilla simple: " . addslashes("It's") . "\n";       // It\'s
echo "Comilla doble: " . addslashes('"Hola"') . "\n";      // \"Hola\"
echo "Backslash: " . addslashes("C:\\ruta") . "\n";         // C:\\\\ruta
echo "NUL byte: " . addslashes("hola\0mundo") . "\n";      // hola\\0mundo

// Ejemplo 3: Uso práctico - escapar para JavaScript
$nombre = "O'Brien";
echo "\nEjemplo 3 (Escapar para JS):\n";
echo "<script>var nombre = '" . addslashes($nombre) . "';</script>\n";

// Ejemplo 4: stripslashes para revertir
$escapado = addslashes('Dijo "Hola" y \'Adiós\'');
echo "\nEjemplo 4 (Revertir con stripslashes):\n";
echo "Escapado: $escapado\n";
echo "Original: " . stripslashes($escapado) . "\n";

// Ejemplo 5: Nota sobre SQL - usar prepared statements en su lugar
echo "\nEjemplo 5 (Nota sobre SQL):\n";
echo "Para SQL, NO uses addslashes(). Usa prepared statements:\n";
echo '  $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");' . "\n";
echo '  $stmt->execute([$nombre]);' . "\n";
?>
