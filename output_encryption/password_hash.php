<?php
/**
 * password_hash() - Hashing seguro de contraseñas
 *
 * PHP provee funciones nativas para el hashing de contraseñas que
 * automáticamente manejan el salt, el algoritmo y el costo.
 * NUNCA usar md5() o sha1() para contraseñas.
 */

// ============================================
// Ejemplo 1: password_hash() con PASSWORD_DEFAULT
// ============================================

echo "=== Ejemplo 1: PASSWORD_DEFAULT ===\n";

$password = 'MiContraseña123!';

/**
 * PASSWORD_DEFAULT usa el mejor algoritmo disponible en la versión actual de PHP.
 * En PHP 8.x, actualmente usa bcrypt.
 * El hash incluye automáticamente: algoritmo, costo y salt.
 */
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña original: $password\n";
echo "Hash generado: $hash\n";
echo "Longitud del hash: " . strlen($hash) . " caracteres\n\n";

// Cada llamada genera un hash diferente (diferente salt)
$hash2 = password_hash($password, PASSWORD_DEFAULT);
echo "Segundo hash (misma contraseña): $hash2\n";
echo "¿Los hashes son iguales? " . ($hash === $hash2 ? 'Sí' : 'No') . "\n";
echo "  (Son diferentes porque cada uno tiene un salt único)\n";

// Obtener información del hash
$info = password_get_info($hash);
echo "\nInformación del hash:\n";
echo "  Algoritmo: " . ($info['algoName'] ?? $info['algo']) . "\n";
echo "  Opciones: " . json_encode($info['options']) . "\n";

// ============================================
// Ejemplo 2: PASSWORD_BCRYPT con factor de costo
// ============================================

echo "\n=== Ejemplo 2: PASSWORD_BCRYPT con costo ===\n";

/**
 * Bcrypt es el algoritmo estándar. El factor de costo controla
 * cuántas iteraciones se realizan (2^costo). Mayor costo = más seguro pero más lento.
 * El valor por defecto es 10. Se recomienda ajustarlo según el hardware.
 */

$password = 'ContraseñaSegura456!';

// Costo por defecto (10)
$hashDefault = password_hash($password, PASSWORD_BCRYPT);
echo "Bcrypt costo 10 (default): $hashDefault\n";

// Costo más alto (12) - más seguro, más lento
$hashAlto = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Bcrypt costo 12: $hashAlto\n";

// Medir el tiempo de cada factor de costo
echo "\nComparación de tiempos por factor de costo:\n";
$costos = [8, 10, 12, 13];

foreach ($costos as $costo) {
    $inicio = microtime(true);
    password_hash($password, PASSWORD_BCRYPT, ['cost' => $costo]);
    $tiempo = (microtime(true) - $inicio) * 1000;
    echo "  Costo $costo: " . number_format($tiempo, 1) . " ms\n";
}

// Encontrar el costo óptimo para el hardware actual
echo "\nBuscando costo óptimo (objetivo: ~100ms):\n";
$tiempoObjetivo = 0.1; // 100 milisegundos
$costo = 8;

do {
    $costo++;
    $inicio = microtime(true);
    password_hash($password, PASSWORD_BCRYPT, ['cost' => $costo]);
    $tiempo = microtime(true) - $inicio;
} while ($tiempo < $tiempoObjetivo && $costo < 16);

echo "  Costo recomendado para este servidor: $costo\n";
echo "  Tiempo: " . number_format($tiempo * 1000, 1) . " ms\n";

// ============================================
// Ejemplo 3: PASSWORD_ARGON2ID (PHP 7.3+)
// ============================================

echo "\n=== Ejemplo 3: PASSWORD_ARGON2ID ===\n";

/**
 * Argon2id es un algoritmo más moderno que es resistente tanto a
 * ataques de GPU como a ataques de canal lateral.
 * Requiere la extensión sodium o que PHP esté compilado con libargon2.
 *
 * Parámetros:
 * - memory_cost: memoria en KiB (default: 65536 = 64MB)
 * - time_cost: número de iteraciones (default: 4)
 * - threads: paralelismo (default: 1)
 */

if (defined('PASSWORD_ARGON2ID')) {
    $hashArgon = password_hash($password, PASSWORD_ARGON2ID);
    echo "Argon2id (default): $hashArgon\n\n";

    // Con parámetros personalizados
    $hashArgonCustom = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,  // 64 MB
        'time_cost'   => 4,      // 4 iteraciones
        'threads'     => 2,      // 2 hilos
    ]);
    echo "Argon2id (personalizado): $hashArgonCustom\n";

    $info = password_get_info($hashArgon);
    echo "\nInfo Argon2id:\n";
    echo "  Algoritmo: " . ($info['algoName'] ?? 'argon2id') . "\n";
    echo "  Opciones: " . json_encode($info['options']) . "\n";

    // Comparar tiempos
    echo "\nTiempos de generación:\n";
    $inicio = microtime(true);
    password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    echo "  Bcrypt (costo 10): " . number_format((microtime(true) - $inicio) * 1000, 1) . " ms\n";

    $inicio = microtime(true);
    password_hash($password, PASSWORD_ARGON2ID);
    echo "  Argon2id (default): " . number_format((microtime(true) - $inicio) * 1000, 1) . " ms\n";
} else {
    echo "PASSWORD_ARGON2ID no está disponible en esta instalación de PHP.\n";
    echo "Para habilitarlo, compilar PHP con --with-password-argon2 o instalar libargon2.\n";
}

// ============================================
// Ejemplo 4: Constantes y algoritmos disponibles
// ============================================

echo "\n=== Ejemplo 4: Algoritmos disponibles ===\n";

echo "Algoritmos de password_hash disponibles:\n";

$algoritmos = [
    'PASSWORD_DEFAULT'  => PASSWORD_DEFAULT,
    'PASSWORD_BCRYPT'   => PASSWORD_BCRYPT,
];

if (defined('PASSWORD_ARGON2I')) {
    $algoritmos['PASSWORD_ARGON2I'] = PASSWORD_ARGON2I;
}
if (defined('PASSWORD_ARGON2ID')) {
    $algoritmos['PASSWORD_ARGON2ID'] = PASSWORD_ARGON2ID;
}

foreach ($algoritmos as $nombre => $valor) {
    echo "  $nombre = $valor\n";
}

// Comparar formatos de hash
echo "\nFormatos de hash:\n";
$hashBcrypt = password_hash('test', PASSWORD_BCRYPT);
echo "  Bcrypt:    $hashBcrypt\n";
echo "  Prefijo:   \$2y\$ (identifica bcrypt)\n";
echo "  Estructura: \$2y\$costo\$salt(22 chars)hash(31 chars)\n\n";

if (defined('PASSWORD_ARGON2ID')) {
    $hashArgon2 = password_hash('test', PASSWORD_ARGON2ID);
    echo "  Argon2id:  $hashArgon2\n";
    echo "  Prefijo:   \$argon2id\$ (identifica argon2id)\n";
}

// ============================================
// Ejemplo 5: Caso práctico - Clase de gestión de contraseñas
// ============================================

echo "\n=== Ejemplo 5: Clase de gestión de contraseñas ===\n";

/**
 * Clase que centraliza la lógica de hashing de contraseñas
 * con políticas configurables y validación de fortaleza.
 */
class PasswordManager
{
    private string|int $algoritmo;
    private array $opciones;
    private int $longitudMinima;

    public function __construct(
        string|int $algoritmo = PASSWORD_DEFAULT,
        array $opciones = [],
        int $longitudMinima = 8
    ) {
        $this->algoritmo = $algoritmo;
        $this->opciones = $opciones;
        $this->longitudMinima = $longitudMinima;
    }

    /**
     * Verificar que la contraseña cumple con la política de seguridad
     */
    public function validarFortaleza(string $password): array
    {
        $errores = [];

        if (strlen($password) < $this->longitudMinima) {
            $errores[] = "Debe tener al menos {$this->longitudMinima} caracteres.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errores[] = "Debe contener al menos una mayúscula.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errores[] = "Debe contener al menos una minúscula.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errores[] = "Debe contener al menos un número.";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errores[] = "Debe contener al menos un carácter especial.";
        }

        return $errores;
    }

    /**
     * Generar hash de la contraseña (con validación previa)
     */
    public function hashear(string $password): string
    {
        $errores = $this->validarFortaleza($password);
        if (!empty($errores)) {
            throw new InvalidArgumentException(
                "Contraseña débil: " . implode(' ', $errores)
            );
        }

        return password_hash($password, $this->algoritmo, $this->opciones);
    }

    /**
     * Verificar contraseña contra hash
     */
    public function verificar(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Verificar si el hash necesita ser recalculado
     */
    public function necesitaRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algoritmo, $this->opciones);
    }

    /**
     * Obtener información del hash
     */
    public function infoHash(string $hash): array
    {
        return password_get_info($hash);
    }
}

$pm = new PasswordManager(
    PASSWORD_BCRYPT,
    ['cost' => 12],
    8
);

// Probar validación de fortaleza
echo "Validación de fortaleza:\n";
$passwordsTest = ['abc', 'abcdefgh', 'Abcdefgh1', 'Abcdefgh1!'];

foreach ($passwordsTest as $pwd) {
    $errores = $pm->validarFortaleza($pwd);
    $estado = empty($errores) ? 'FUERTE' : 'DEBIL';
    echo "  '$pwd' => [$estado]";
    if (!empty($errores)) {
        echo " " . implode(' ', $errores);
    }
    echo "\n";
}

// Hashear una contraseña válida
echo "\nHasheando contraseña válida:\n";
try {
    $hash = $pm->hashear('MiPassword123!');
    echo "  Hash: $hash\n";
    echo "  ¿Verificación correcta? " . ($pm->verificar('MiPassword123!', $hash) ? 'Sí' : 'No') . "\n";
    echo "  ¿Verificación incorrecta? " . ($pm->verificar('OtraPassword', $hash) ? 'Sí' : 'No') . "\n";
    echo "  ¿Necesita rehash? " . ($pm->necesitaRehash($hash) ? 'Sí' : 'No') . "\n";
} catch (InvalidArgumentException $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// Intentar hashear contraseña débil
echo "\nIntentando hashear contraseña débil:\n";
try {
    $pm->hashear('123');
} catch (InvalidArgumentException $e) {
    echo "  Rechazada: " . $e->getMessage() . "\n";
}

?>
