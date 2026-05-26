<?php
/**
 * Funciones de hash - hash(), hash_hmac(), hash_file(), hash_algos()
 *
 * Los hashes son funciones unidireccionales que generan un resumen
 * de tamaño fijo a partir de datos de cualquier tamaño.
 * Se usan para verificar integridad, generar checksums, HMAC, etc.
 * NUNCA usar para contraseñas (usar password_hash() en su lugar).
 */

// ============================================
// Ejemplo 1: hash() - Algoritmos básicos
// ============================================

echo "=== Ejemplo 1: hash() con diferentes algoritmos ===\n";

$texto = 'PHP es un lenguaje de programación para la web.';

// Generar hashes con diferentes algoritmos
$algoritmos = ['md5', 'sha1', 'sha256', 'sha384', 'sha512', 'sha3-256', 'sha3-512'];

echo "Texto: \"$texto\"\n\n";

foreach ($algoritmos as $algo) {
    $hash = hash($algo, $texto);
    $bits = strlen(hash($algo, '', true)) * 8;
    echo sprintf("  %-10s (%3d bits): %s\n", strtoupper($algo), $bits, $hash);
}

// hash() con salida binaria (raw_output = true)
echo "\nHash SHA-256 (hexadecimal): " . hash('sha256', $texto) . "\n";
echo "Hash SHA-256 (binario hex): " . bin2hex(hash('sha256', $texto, true)) . "\n";
echo "Tamaño binario: " . strlen(hash('sha256', $texto, true)) . " bytes\n";

// ============================================
// Ejemplo 2: hash_algos() - Algoritmos disponibles
// ============================================

echo "\n=== Ejemplo 2: Algoritmos disponibles ===\n";

$todos = hash_algos();
echo "Total de algoritmos disponibles: " . count($todos) . "\n\n";

// Agrupar por familia
$familias = [
    'MD'     => array_filter($todos, fn($a) => str_starts_with($a, 'md')),
    'SHA-1'  => array_filter($todos, fn($a) => $a === 'sha1'),
    'SHA-2'  => array_filter($todos, fn($a) => preg_match('/^sha(224|256|384|512)/', $a)),
    'SHA-3'  => array_filter($todos, fn($a) => str_starts_with($a, 'sha3-')),
    'BLAKE2' => array_filter($todos, fn($a) => str_starts_with($a, 'blake2')),
    'CRC'    => array_filter($todos, fn($a) => str_starts_with($a, 'crc')),
    'RIPEMD' => array_filter($todos, fn($a) => str_starts_with($a, 'ripemd')),
];

foreach ($familias as $familia => $algos) {
    if (!empty($algos)) {
        echo "  $familia: " . implode(', ', $algos) . "\n";
    }
}

// Verificar si un algoritmo específico está disponible
$algosBuscados = ['sha256', 'sha3-256', 'blake2b', 'xxh128'];
echo "\nDisponibilidad de algoritmos:\n";
foreach ($algosBuscados as $algo) {
    $disponible = in_array($algo, $todos) ? 'SÍ' : 'NO';
    echo "  $algo: $disponible\n";
}

// ============================================
// Ejemplo 3: hash_hmac() - Hash con clave (autenticación de mensajes)
// ============================================

echo "\n=== Ejemplo 3: hash_hmac() ===\n";

/**
 * HMAC (Hash-based Message Authentication Code) combina un hash
 * con una clave secreta. Se usa para verificar autenticidad e integridad.
 * Común en APIs, tokens JWT, webhooks, etc.
 */

$mensaje = 'Transacción: transferir $500 a cuenta 1234';
$claveSecreta = 'mi_clave_secreta_api_2026';

// Generar HMAC
$hmac = hash_hmac('sha256', $mensaje, $claveSecreta);
echo "Mensaje: $mensaje\n";
echo "HMAC-SHA256: $hmac\n\n";

// Verificar HMAC (en el receptor)
$hmacRecibido = $hmac;
$hmacCalculado = hash_hmac('sha256', $mensaje, $claveSecreta);

// IMPORTANTE: usar hash_equals() para comparación segura (evita timing attacks)
$esValido = hash_equals($hmacCalculado, $hmacRecibido);
echo "Verificación HMAC: " . ($esValido ? 'VÁLIDO' : 'INVÁLIDO') . "\n";

// Intentar con mensaje modificado
$mensajeModificado = 'Transacción: transferir $5000 a cuenta 1234';
$hmacModificado = hash_hmac('sha256', $mensajeModificado, $claveSecreta);
$esValido = hash_equals($hmac, $hmacModificado);
echo "Verificación con mensaje modificado: " . ($esValido ? 'VÁLIDO' : 'INVÁLIDO (tampering detectado)') . "\n";

// Caso práctico: verificar webhook de Stripe/GitHub
echo "\nSimulación de verificación de webhook:\n";

function verificarWebhook(string $payload, string $firmaRecibida, string $secreto): bool
{
    $firmaCalculada = 'sha256=' . hash_hmac('sha256', $payload, $secreto);
    return hash_equals($firmaCalculada, $firmaRecibida);
}

$payload = '{"event":"payment.completed","amount":9999}';
$secretoWebhook = 'whsec_abc123';
$firma = 'sha256=' . hash_hmac('sha256', $payload, $secretoWebhook);

echo "  Payload: $payload\n";
echo "  Firma: $firma\n";
echo "  ¿Válido? " . (verificarWebhook($payload, $firma, $secretoWebhook) ? 'SÍ' : 'NO') . "\n";

// ============================================
// Ejemplo 4: hash_file() - Checksum de archivos
// ============================================

echo "\n=== Ejemplo 4: hash_file() ===\n";

/**
 * hash_file() calcula el hash de un archivo completo.
 * Útil para verificar integridad de descargas, detectar cambios, etc.
 */

// Crear archivos de prueba
$tmpDir = sys_get_temp_dir();
$archivo1 = $tmpDir . '/test_hash_1.txt';
$archivo2 = $tmpDir . '/test_hash_2.txt';
$archivo3 = $tmpDir . '/test_hash_3.txt';

file_put_contents($archivo1, 'Contenido del archivo de prueba número 1.');
file_put_contents($archivo2, 'Contenido del archivo de prueba número 2.');
file_put_contents($archivo3, 'Contenido del archivo de prueba número 1.'); // Mismo que archivo1

echo "Checksums de archivos:\n";
$archivos = [$archivo1, $archivo2, $archivo3];

foreach ($archivos as $archivo) {
    $md5 = hash_file('md5', $archivo);
    $sha256 = hash_file('sha256', $archivo);
    echo "  " . basename($archivo) . ":\n";
    echo "    MD5:    $md5\n";
    echo "    SHA256: $sha256\n";
}

// Comparar archivos por hash
$hash1 = hash_file('sha256', $archivo1);
$hash3 = hash_file('sha256', $archivo3);
echo "\n¿Archivo 1 = Archivo 3? " . ($hash1 === $hash3 ? 'SÍ (contenido idéntico)' : 'NO') . "\n";

// Hash incremental con hash_init/hash_update/hash_final
echo "\nHash incremental (para datos grandes):\n";
$ctx = hash_init('sha256');
hash_update($ctx, 'Primera parte del mensaje. ');
hash_update($ctx, 'Segunda parte del mensaje. ');
hash_update($ctx, 'Tercera parte del mensaje.');
$hashIncremental = hash_final($ctx);

$hashDirecto = hash('sha256', 'Primera parte del mensaje. Segunda parte del mensaje. Tercera parte del mensaje.');
echo "  Hash incremental: $hashIncremental\n";
echo "  Hash directo:     $hashDirecto\n";
echo "  ¿Iguales? " . ($hashIncremental === $hashDirecto ? 'SÍ' : 'NO') . "\n";

// Limpieza
unlink($archivo1);
unlink($archivo2);
unlink($archivo3);

// ============================================
// Ejemplo 5: Comparación de algoritmos y rendimiento
// ============================================

echo "\n=== Ejemplo 5: Comparación de rendimiento ===\n";

$datoPrueba = str_repeat('A', 1000000); // 1 MB de datos
$algoritmosPrueba = ['md5', 'sha1', 'sha256', 'sha512', 'sha3-256'];
$iteraciones = 10;

echo "Benchmark: $iteraciones iteraciones con 1 MB de datos\n\n";

$resultados = [];

foreach ($algoritmosPrueba as $algo) {
    $inicio = microtime(true);
    for ($i = 0; $i < $iteraciones; $i++) {
        hash($algo, $datoPrueba);
    }
    $tiempo = (microtime(true) - $inicio) * 1000;
    $hashEjemplo = hash($algo, $datoPrueba);

    $resultados[$algo] = $tiempo;

    echo sprintf(
        "  %-12s %6.1f ms total | %5.1f ms/iter | Longitud: %3d hex | %s...%s\n",
        strtoupper($algo),
        $tiempo,
        $tiempo / $iteraciones,
        strlen($hashEjemplo),
        substr($hashEjemplo, 0, 16),
        substr($hashEjemplo, -8)
    );
}

// Recomendaciones
echo "\nRecomendaciones de uso:\n";
echo "  Checksums rápidos (no seguridad): CRC32, MD5\n";
echo "  Integridad de datos: SHA-256\n";
echo "  HMAC para APIs: SHA-256 o SHA-512\n";
echo "  Contraseñas: NUNCA usar hash(), usar password_hash()\n";
echo "  Seguridad máxima: SHA-3 o BLAKE2\n";

// hash_equals() - comparación segura en tiempo constante
echo "\nhash_equals() vs === (comparación segura):\n";
$hash_a = hash('sha256', 'texto_a');
$hash_b = hash('sha256', 'texto_b');
$hash_c = hash('sha256', 'texto_a');

echo "  hash_equals(a, b): " . (hash_equals($hash_a, $hash_b) ? 'iguales' : 'diferentes') . "\n";
echo "  hash_equals(a, c): " . (hash_equals($hash_a, $hash_c) ? 'iguales' : 'diferentes') . "\n";
echo "  SIEMPRE usar hash_equals() para comparar hashes (previene timing attacks).\n";

?>
