<?php
/**
 * random_bytes() y random_int() - Generación criptográficamente segura
 *
 * PHP provee funciones para generar datos aleatorios seguros que son
 * adecuados para uso criptográfico (tokens, claves, IDs únicos).
 * NUNCA usar rand() o mt_rand() para seguridad.
 */

// ============================================
// Ejemplo 1: random_bytes() y bin2hex()
// ============================================

echo "=== Ejemplo 1: random_bytes() básico ===\n";

/**
 * random_bytes() genera bytes aleatorios criptográficamente seguros.
 * Usa la fuente de aleatoriedad del sistema operativo.
 */

// Generar diferentes cantidades de bytes aleatorios
$longitudes = [8, 16, 32, 64];

foreach ($longitudes as $len) {
    $bytes = random_bytes($len);
    $hex = bin2hex($bytes);
    $base64 = base64_encode($bytes);

    echo "  $len bytes:\n";
    echo "    Hex:    $hex\n";
    echo "    Base64: $base64\n";
    echo "    Longitud hex: " . strlen($hex) . " chars\n\n";
}

// Cada llamada genera valores diferentes
echo "Tres llamadas con 8 bytes:\n";
for ($i = 1; $i <= 3; $i++) {
    echo "  Llamada $i: " . bin2hex(random_bytes(8)) . "\n";
}

// random_int() para enteros aleatorios seguros
echo "\nrandom_int() - Enteros seguros:\n";
echo "  Entre 1 y 100: " . random_int(1, 100) . "\n";
echo "  Entre 0 y PHP_INT_MAX: " . random_int(0, PHP_INT_MAX) . "\n";
echo "  Entre -1000 y 1000: " . random_int(-1000, 1000) . "\n";

// ============================================
// Ejemplo 2: Generar tokens seguros
// ============================================

echo "\n=== Ejemplo 2: Generar tokens seguros ===\n";

/**
 * Funciones para generar diferentes tipos de tokens
 * criptográficamente seguros.
 */

// Token hexadecimal (para URLs, APIs)
function generarTokenHex(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

// Token URL-safe base64 (sin caracteres problemáticos para URLs)
function generarTokenUrlSafe(int $bytes = 32): string
{
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

// Token numérico (para verificación SMS, 2FA)
function generarCodigoNumerico(int $digitos = 6): string
{
    $min = (int) str_pad('1', $digitos, '0');
    $max = (int) str_pad('9', $digitos, '9');
    return (string) random_int($min, $max);
}

// Token alfanumérico
function generarTokenAlfanumerico(int $longitud = 32): string
{
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $max = strlen($caracteres) - 1;
    $token = '';

    for ($i = 0; $i < $longitud; $i++) {
        $token .= $caracteres[random_int(0, $max)];
    }

    return $token;
}

echo "Token hex (32 bytes):      " . generarTokenHex(32) . "\n";
echo "Token URL-safe (32 bytes): " . generarTokenUrlSafe(32) . "\n";
echo "Código numérico (6 dígitos): " . generarCodigoNumerico(6) . "\n";
echo "Código numérico (8 dígitos): " . generarCodigoNumerico(8) . "\n";
echo "Token alfanumérico (24):   " . generarTokenAlfanumerico(24) . "\n";
echo "Token alfanumérico (48):   " . generarTokenAlfanumerico(48) . "\n";

// Tokens para diferentes propósitos
echo "\nTokens específicos:\n";
echo "  API Key:          " . 'sk_live_' . generarTokenHex(24) . "\n";
echo "  Token de sesión:  " . generarTokenUrlSafe(48) . "\n";
echo "  Código de verif.: " . generarCodigoNumerico(6) . "\n";
echo "  Reset password:   " . generarTokenUrlSafe(32) . "\n";
echo "  CSRF token:       " . generarTokenHex(32) . "\n";

// ============================================
// Ejemplo 3: Generar UUIDs (v4)
// ============================================

echo "\n=== Ejemplo 3: Generar UUIDs v4 ===\n";

/**
 * UUID v4 es un identificador único universal generado aleatoriamente.
 * Formato: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
 * donde 4 indica versión 4 y 'y' es 8, 9, a, o b.
 */
function generarUUIDv4(): string
{
    $bytes = random_bytes(16);

    // Establecer la versión 4 (bits 48-51)
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);

    // Establecer la variante (bits 64-65)
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

    return sprintf(
        '%s-%s-%s-%s-%s',
        bin2hex(substr($bytes, 0, 4)),
        bin2hex(substr($bytes, 4, 2)),
        bin2hex(substr($bytes, 6, 2)),
        bin2hex(substr($bytes, 8, 2)),
        bin2hex(substr($bytes, 10, 6))
    );
}

echo "UUIDs v4 generados:\n";
for ($i = 1; $i <= 5; $i++) {
    $uuid = generarUUIDv4();
    echo "  $i. $uuid\n";
}

// Validar formato UUID v4
function esUUIDv4Valido(string $uuid): bool
{
    return (bool) preg_match(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
        $uuid
    );
}

echo "\nValidación de UUIDs:\n";
$uuids = [
    generarUUIDv4(),
    '550e8400-e29b-41d4-a716-446655440000',  // v4 válido
    'no-es-un-uuid-valido',
    '12345678-1234-5678-1234-567812345678',   // No es v4 (versión 5)
];

foreach ($uuids as $uuid) {
    $valido = esUUIDv4Valido($uuid) ? 'VÁLIDO' : 'INVÁLIDO';
    echo "  $uuid => $valido\n";
}

// ============================================
// Ejemplo 4: Generación de contraseñas aleatorias
// ============================================

echo "\n=== Ejemplo 4: Generación de contraseñas ===\n";

/**
 * Generador de contraseñas aleatorias seguras con requisitos
 * configurables de complejidad.
 */
class GeneradorPasswords
{
    private const MINUSCULAS = 'abcdefghijklmnopqrstuvwxyz';
    private const MAYUSCULAS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const NUMEROS = '0123456789';
    private const ESPECIALES = '!@#$%^&*()-_=+[]{}|;:,.<>?';

    /**
     * Generar contraseña con requisitos mínimos garantizados
     */
    public static function generar(
        int $longitud = 16,
        bool $conMinusculas = true,
        bool $conMayusculas = true,
        bool $conNumeros = true,
        bool $conEspeciales = true
    ): string {
        if ($longitud < 4) {
            throw new InvalidArgumentException('La longitud mínima es 4.');
        }

        $password = [];
        $todosCaracteres = '';

        // Garantizar al menos un carácter de cada tipo requerido
        if ($conMinusculas) {
            $password[] = self::MINUSCULAS[random_int(0, strlen(self::MINUSCULAS) - 1)];
            $todosCaracteres .= self::MINUSCULAS;
        }
        if ($conMayusculas) {
            $password[] = self::MAYUSCULAS[random_int(0, strlen(self::MAYUSCULAS) - 1)];
            $todosCaracteres .= self::MAYUSCULAS;
        }
        if ($conNumeros) {
            $password[] = self::NUMEROS[random_int(0, strlen(self::NUMEROS) - 1)];
            $todosCaracteres .= self::NUMEROS;
        }
        if ($conEspeciales) {
            $password[] = self::ESPECIALES[random_int(0, strlen(self::ESPECIALES) - 1)];
            $todosCaracteres .= self::ESPECIALES;
        }

        // Llenar el resto con caracteres aleatorios de todos los tipos
        $restante = $longitud - count($password);
        $maxIndex = strlen($todosCaracteres) - 1;

        for ($i = 0; $i < $restante; $i++) {
            $password[] = $todosCaracteres[random_int(0, $maxIndex)];
        }

        // Mezclar para que los caracteres obligatorios no estén siempre al inicio
        $resultado = $password;
        for ($i = count($resultado) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$resultado[$i], $resultado[$j]] = [$resultado[$j], $resultado[$i]];
        }

        return implode('', $resultado);
    }

    /**
     * Generar passphrase (frase de contraseña) usando palabras
     */
    public static function generarPassphrase(int $numPalabras = 4, string $separador = '-'): string
    {
        // Lista de palabras comunes en español (simplificada)
        $palabras = [
            'casa', 'perro', 'gato', 'luna', 'sol', 'mar', 'rio', 'arbol',
            'flor', 'cielo', 'nube', 'viento', 'fuego', 'tierra', 'agua',
            'mesa', 'libro', 'pluma', 'reloj', 'silla', 'puerta', 'calle',
            'noche', 'campo', 'monte', 'playa', 'bosque', 'piedra', 'estrella',
            'camino', 'barco', 'avion', 'tren', 'coche', 'bici', 'zapato',
            'verde', 'rojo', 'azul', 'blanco', 'negro', 'dorado', 'plata',
        ];

        $seleccionadas = [];
        for ($i = 0; $i < $numPalabras; $i++) {
            $indice = random_int(0, count($palabras) - 1);
            $seleccionadas[] = $palabras[$indice];
        }

        // Agregar número aleatorio para más entropía
        $seleccionadas[] = (string) random_int(10, 99);

        return implode($separador, $seleccionadas);
    }

    /**
     * Calcular entropía aproximada de una contraseña
     */
    public static function calcularEntropia(string $password): float
    {
        $poolSize = 0;
        if (preg_match('/[a-z]/', $password)) $poolSize += 26;
        if (preg_match('/[A-Z]/', $password)) $poolSize += 26;
        if (preg_match('/[0-9]/', $password)) $poolSize += 10;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $poolSize += 32;

        return strlen($password) * log2($poolSize);
    }
}

echo "Contraseñas generadas:\n";
for ($i = 1; $i <= 5; $i++) {
    $pwd = GeneradorPasswords::generar(16);
    $entropia = GeneradorPasswords::calcularEntropia($pwd);
    echo "  $i. $pwd (entropía: " . number_format($entropia, 1) . " bits)\n";
}

echo "\nSolo alfanuméricas:\n";
for ($i = 1; $i <= 3; $i++) {
    echo "  $i. " . GeneradorPasswords::generar(20, true, true, true, false) . "\n";
}

echo "\nPassphrases:\n";
for ($i = 1; $i <= 3; $i++) {
    $passphrase = GeneradorPasswords::generarPassphrase(4, '-');
    $entropia = GeneradorPasswords::calcularEntropia($passphrase);
    echo "  $i. $passphrase (entropía: " . number_format($entropia, 1) . " bits)\n";
}

// ============================================
// Ejemplo 5: Caso práctico - Sistema de tokens de acceso
// ============================================

echo "\n=== Ejemplo 5: Sistema de tokens de acceso ===\n";

/**
 * Sistema completo de generación y gestión de tokens de acceso
 * para una API, con expiración y revocación.
 */
class TokenManager
{
    private array $tokens = []; // En producción: base de datos o Redis

    /**
     * Generar un token de acceso
     */
    public function generarToken(string $usuario, int $expiracionSegundos = 3600): array
    {
        // Token aleatorio URL-safe
        $tokenPlano = bin2hex(random_bytes(32));

        // Hash del token para almacenar (no guardamos el token en plano)
        $tokenHash = hash('sha256', $tokenPlano);

        $datosToken = [
            'hash'     => $tokenHash,
            'usuario'  => $usuario,
            'creado'   => time(),
            'expira'   => time() + $expiracionSegundos,
            'revocado' => false,
        ];

        $this->tokens[$tokenHash] = $datosToken;

        return [
            'token'   => $tokenPlano, // Este se envía al cliente
            'expira'  => $datosToken['expira'],
            'tipo'    => 'Bearer',
        ];
    }

    /**
     * Validar un token
     */
    public function validarToken(string $tokenPlano): array
    {
        $tokenHash = hash('sha256', $tokenPlano);

        if (!isset($this->tokens[$tokenHash])) {
            return ['valido' => false, 'razon' => 'Token no encontrado'];
        }

        $datos = $this->tokens[$tokenHash];

        if ($datos['revocado']) {
            return ['valido' => false, 'razon' => 'Token revocado'];
        }

        if (time() > $datos['expira']) {
            return ['valido' => false, 'razon' => 'Token expirado'];
        }

        return [
            'valido'  => true,
            'usuario' => $datos['usuario'],
            'expira'  => $datos['expira'],
        ];
    }

    /**
     * Revocar un token
     */
    public function revocarToken(string $tokenPlano): bool
    {
        $tokenHash = hash('sha256', $tokenPlano);

        if (!isset($this->tokens[$tokenHash])) {
            return false;
        }

        $this->tokens[$tokenHash]['revocado'] = true;
        return true;
    }

    /**
     * Generar token de refresh (mayor duración)
     */
    public function generarRefreshToken(string $usuario): array
    {
        return $this->generarToken($usuario, 86400 * 30); // 30 días
    }
}

$tm = new TokenManager();

// Generar token de acceso
echo "Generando token de acceso:\n";
$tokenAcceso = $tm->generarToken('sandra@mail.com', 3600);
echo "  Token: " . substr($tokenAcceso['token'], 0, 20) . "...\n";
echo "  Tipo: {$tokenAcceso['tipo']}\n";
echo "  Expira: " . date('Y-m-d H:i:s', $tokenAcceso['expira']) . "\n";

// Validar token
echo "\nValidando token:\n";
$resultado = $tm->validarToken($tokenAcceso['token']);
echo "  Válido: " . ($resultado['valido'] ? 'SÍ' : 'NO') . "\n";
echo "  Usuario: " . ($resultado['usuario'] ?? '-') . "\n";

// Validar token inexistente
echo "\nValidar token inexistente:\n";
$resultado = $tm->validarToken('token_falso_12345');
echo "  Válido: " . ($resultado['valido'] ? 'SÍ' : 'NO') . "\n";
echo "  Razón: " . ($resultado['razon'] ?? '-') . "\n";

// Revocar token
echo "\nRevocar y re-validar:\n";
$tm->revocarToken($tokenAcceso['token']);
$resultado = $tm->validarToken($tokenAcceso['token']);
echo "  Válido: " . ($resultado['valido'] ? 'SÍ' : 'NO') . "\n";
echo "  Razón: " . ($resultado['razon'] ?? '-') . "\n";

// Generar refresh token
echo "\nRefresh token:\n";
$refreshToken = $tm->generarRefreshToken('sandra@mail.com');
echo "  Token: " . substr($refreshToken['token'], 0, 20) . "...\n";
echo "  Expira: " . date('Y-m-d H:i:s', $refreshToken['expira']) . "\n";

?>
