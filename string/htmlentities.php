<?php
// Ejemplo 1: Convertir todos los caracteres aplicables a entidades HTML
$texto = '<p>Café & Résumé "hola"</p>';
echo "Ejemplo 1 (htmlentities):\n";
echo htmlentities($texto) . "\n";

// Ejemplo 2: Comparar htmlspecialchars vs htmlentities
$texto2 = "Café © 2026 — España™";
echo "\nEjemplo 2 (htmlspecialchars vs htmlentities):\n";
echo "htmlspecialchars: " . htmlspecialchars($texto2) . "\n";
echo "htmlentities:     " . htmlentities($texto2) . "\n";
// htmlentities convierte más caracteres (©, —, ™, etc.)

// Ejemplo 3: Con encoding UTF-8
echo "\nEjemplo 3 (UTF-8):\n";
echo htmlentities("Año Señor über", ENT_QUOTES, 'UTF-8') . "\n";

// Ejemplo 4: ENT_QUOTES para comillas
$html = "atributo='valor' y \"otro\"";
echo "\nEjemplo 4 (ENT_QUOTES):\n";
echo htmlentities($html, ENT_QUOTES) . "\n";

// Ejemplo 5: html_entity_decode para revertir
$entidades = "Caf&eacute; &amp; R&eacute;sum&eacute;";
echo "\nEjemplo 5 (Revertir con html_entity_decode):\n";
echo "Entidades: $entidades\n";
echo "Decodificado: " . html_entity_decode($entidades) . "\n";
?>
