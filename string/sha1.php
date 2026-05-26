<?php
// Ejemplo 1: Generar hash SHA-1 de un string
$texto = "Hola mundo";
echo "Ejemplo 1 (Hash SHA-1):\n";
echo "sha1('$texto'): " . sha1($texto) . "\n";

// Ejemplo 2: Comparar md5 vs sha1
echo "\nEjemplo 2 (md5 vs sha1):\n";
echo "md5 (32 chars):  " . md5("test") . "\n";
echo "sha1 (40 chars): " . sha1("test") . "\n";

// Ejemplo 3: Hash en formato binario
echo "\nEjemplo 3 (Raw output):\n";
$raw = sha1("test", true);
echo "Longitud raw: " . strlen($raw) . " bytes\n"; // 20

// Ejemplo 4: Verificar integridad de datos
$datos_originales = "Datos importantes";
$hash_original = sha1($datos_originales);
$datos_recibidos = "Datos importantes";
echo "\nEjemplo 4 (Verificar integridad):\n";
if (sha1($datos_recibidos) === $hash_original) {
    echo "Datos íntegros\n";
} else {
    echo "Datos modificados\n";
}

// Ejemplo 5: Algoritmos de hash más seguros con hash()
echo "\nEjemplo 5 (Otros algoritmos con hash()):\n";
echo "sha256: " . hash('sha256', 'test') . "\n";
echo "sha512: " . substr(hash('sha512', 'test'), 0, 40) . "...\n";

// Ejemplo 6: ADVERTENCIA - no usar para contraseñas
echo "\nEjemplo 6 (Nota de seguridad):\n";
echo "Al igual que md5(), sha1() NO es seguro para contraseñas.\n";
echo "Usa password_hash() con PASSWORD_BCRYPT o PASSWORD_ARGON2ID.\n";
?>
