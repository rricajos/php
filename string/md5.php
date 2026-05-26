<?php
// Ejemplo 1: Generar hash MD5 de un string
$texto = "Hola mundo";
echo "Ejemplo 1 (Hash MD5):\n";
echo "md5('$texto'): " . md5($texto) . "\n";

// Ejemplo 2: Mismo input siempre produce el mismo hash
echo "\nEjemplo 2 (Determinístico):\n";
echo md5("test") . "\n";
echo md5("test") . "\n"; // Idéntico

// Ejemplo 3: Cambio mínimo produce hash completamente diferente
echo "\nEjemplo 3 (Efecto avalancha):\n";
echo "md5('abc'):  " . md5("abc") . "\n";
echo "md5('abd'):  " . md5("abd") . "\n";

// Ejemplo 4: Hash en formato binario (raw_output = true)
echo "\nEjemplo 4 (Raw output):\n";
$raw = md5("test", true);
echo "Longitud raw: " . strlen($raw) . " bytes\n";   // 16
echo "Longitud hex: " . strlen(md5("test")) . " chars\n"; // 32

// Ejemplo 5: Uso práctico - generar identificadores únicos
$archivo = "foto.jpg";
$id = md5($archivo . time());
echo "\nEjemplo 5 (Identificador único):\n";
echo "ID: $id\n";

// Ejemplo 6: ADVERTENCIA - NO usar para contraseñas
echo "\nEjemplo 6 (Nota de seguridad):\n";
echo "NO uses md5() para contraseñas. Usa password_hash():\n";
$password = "mi_contraseña";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "password_hash: $hash\n";
echo "password_verify: " . (password_verify($password, $hash) ? "true" : "false") . "\n";
?>
