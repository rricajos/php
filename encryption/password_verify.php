<?php
/**
 * password_verify() y password_needs_rehash()
 *
 * Funciones complementarias para verificar contraseñas y
 * mantener los hashes actualizados con los mejores algoritmos.
 */

// ============================================
// Ejemplo 1: password_verify() básico
// ============================================

echo "=== Ejemplo 1: password_verify() básico ===\n";

$password = 'MiContraseñaSegura123!';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Contraseña: $password\n";
echo "Hash: $hash\n\n";

/**
 * password_verify() compara una contraseña en texto plano contra un hash.
 * Es resistente a ataques de timing (usa comparación en tiempo constante).
 */

// Verificación correcta
$esValida = password_verify($password, $hash);
echo "Verificar '$password': " . ($esValida ? 'CORRECTA' : 'INCORRECTA') . "\n";

// Verificación incorrecta
$esValida = password_verify('ContraseñaEquivocada', $hash);
echo "Verificar 'ContraseñaEquivocada': " . ($esValida ? 'CORRECTA' : 'INCORRECTA') . "\n";

// Verificación sensible a mayúsculas
$esValida = password_verify('micontraseñasegura123!', $hash);
echo "Verificar (minúsculas): " . ($esValida ? 'CORRECTA' : 'INCORRECTA') . "\n";

// Verificar contra diferentes hashes de la misma contraseña
$hash2 = password_hash($password, PASSWORD_BCRYPT);
echo "\nDos hashes diferentes, misma contraseña:\n";
echo "  Hash 1: " . substr($hash, 0, 30) . "...\n";
echo "  Hash 2: " . substr($hash2, 0, 30) . "...\n";
echo "  Verificar contra Hash 1: " . (password_verify($password, $hash) ? 'OK' : 'FALLO') . "\n";
echo "  Verificar contra Hash 2: " . (password_verify($password, $hash2) ? 'OK' : 'FALLO') . "\n";

// ============================================
// Ejemplo 2: password_needs_rehash()
// ============================================

echo "\n=== Ejemplo 2: password_needs_rehash() ===\n";

/**
 * password_needs_rehash() verifica si un hash fue generado con
 * el algoritmo y opciones actuales. Si no, debe regenerarse.
 * Esto permite migrar hashes cuando se cambia la política de seguridad.
 */

// Hash generado con costo bajo (antiguo)
$hashAntiguo = password_hash('password', PASSWORD_BCRYPT, ['cost' => 8]);
echo "Hash antiguo (costo 8): " . substr($hashAntiguo, 0, 30) . "...\n";

// Verificar si necesita rehash con política actual (costo 12)
$necesita = password_needs_rehash($hashAntiguo, PASSWORD_BCRYPT, ['cost' => 12]);
echo "¿Necesita rehash con costo 12? " . ($necesita ? 'SÍ' : 'NO') . "\n";

// Hash generado con costo actual
$hashActual = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
$necesita = password_needs_rehash($hashActual, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash actual (costo 12), ¿necesita rehash? " . ($necesita ? 'SÍ' : 'NO') . "\n";

// Verificar cambio de algoritmo
if (defined('PASSWORD_ARGON2ID')) {
    $hashBcrypt = password_hash('password', PASSWORD_BCRYPT);
    $necesitaArgon = password_needs_rehash($hashBcrypt, PASSWORD_ARGON2ID);
    echo "\nHash bcrypt, ¿necesita rehash a Argon2id? " . ($necesitaArgon ? 'SÍ' : 'NO') . "\n";
}

// ============================================
// Ejemplo 3: Flujo completo de login con rehash automático
// ============================================

echo "\n=== Ejemplo 3: Login con rehash automático ===\n";

/**
 * Simulamos una base de datos en memoria para demostrar
 * el flujo completo de login con actualización de hash.
 */
class BaseDatosSimulada
{
    private array $usuarios = [];

    public function guardarUsuario(string $email, string $hashPassword): void
    {
        $this->usuarios[$email] = [
            'email'         => $email,
            'password_hash' => $hashPassword,
            'actualizado'   => date('Y-m-d H:i:s'),
        ];
    }

    public function obtenerUsuario(string $email): ?array
    {
        return $this->usuarios[$email] ?? null;
    }

    public function actualizarHash(string $email, string $nuevoHash): void
    {
        if (isset($this->usuarios[$email])) {
            $this->usuarios[$email]['password_hash'] = $nuevoHash;
            $this->usuarios[$email]['actualizado'] = date('Y-m-d H:i:s');
        }
    }
}

class AuthService
{
    private BaseDatosSimulada $db;
    private string|int $algoritmo;
    private array $opciones;

    public function __construct(BaseDatosSimulada $db, string|int $algoritmo = PASSWORD_BCRYPT, array $opciones = [])
    {
        $this->db = $db;
        $this->algoritmo = $algoritmo;
        $this->opciones = $opciones;
    }

    /**
     * Registrar un nuevo usuario
     */
    public function registrar(string $email, string $password): bool
    {
        if ($this->db->obtenerUsuario($email)) {
            echo "  Error: El usuario ya existe.\n";
            return false;
        }

        $hash = password_hash($password, $this->algoritmo, $this->opciones);
        $this->db->guardarUsuario($email, $hash);
        echo "  Usuario registrado: $email\n";
        echo "  Hash: " . substr($hash, 0, 25) . "...\n";
        return true;
    }

    /**
     * Iniciar sesión con rehash automático
     */
    public function login(string $email, string $password): bool
    {
        $usuario = $this->db->obtenerUsuario($email);

        if ($usuario === null) {
            echo "  Login fallido: usuario no encontrado.\n";
            // Hacer hash igualmente para evitar ataques de timing
            password_hash($password, $this->algoritmo, $this->opciones);
            return false;
        }

        // Verificar la contraseña
        if (!password_verify($password, $usuario['password_hash'])) {
            echo "  Login fallido: contraseña incorrecta.\n";
            return false;
        }

        echo "  Login exitoso: $email\n";

        // Verificar si el hash necesita actualización
        if (password_needs_rehash($usuario['password_hash'], $this->algoritmo, $this->opciones)) {
            $nuevoHash = password_hash($password, $this->algoritmo, $this->opciones);
            $this->db->actualizarHash($email, $nuevoHash);
            echo "  [Auto-rehash] Hash actualizado al algoritmo/costo actual.\n";
            echo "  Nuevo hash: " . substr($nuevoHash, 0, 25) . "...\n";
        }

        return true;
    }
}

// Simular escenario de migración de costos
$db = new BaseDatosSimulada();

// Fase 1: Registrar con costo bajo (política antigua)
echo "Fase 1: Registro con costo 8 (política antigua)\n";
$authAntiguo = new AuthService($db, PASSWORD_BCRYPT, ['cost' => 8]);
$authAntiguo->registrar('sandra@mail.com', 'SuperSecreta123!');

// Fase 2: Login con política nueva (costo 12)
echo "\nFase 2: Login con costo 12 (política nueva)\n";
$authNuevo = new AuthService($db, PASSWORD_BCRYPT, ['cost' => 12]);
$authNuevo->login('sandra@mail.com', 'SuperSecreta123!');

// Fase 3: Siguiente login ya usa el hash actualizado
echo "\nFase 3: Login posterior (hash ya actualizado)\n";
$authNuevo->login('sandra@mail.com', 'SuperSecreta123!');

// ============================================
// Ejemplo 4: Migración desde MD5/SHA1 (legacy)
// ============================================

echo "\n=== Ejemplo 4: Migración desde hashes legacy ===\n";

/**
 * Muchas aplicaciones antiguas almacenan contraseñas con md5 o sha1.
 * Este ejemplo muestra cómo migrar gradualmente a password_hash.
 */
class MigradorPasswords
{
    /**
     * Detectar el formato del hash almacenado
     */
    public static function detectarFormato(string $hash): string
    {
        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2b$')) {
            return 'bcrypt';
        }
        if (str_starts_with($hash, '$argon2')) {
            return 'argon2';
        }
        if (strlen($hash) === 32 && ctype_xdigit($hash)) {
            return 'md5';
        }
        if (strlen($hash) === 40 && ctype_xdigit($hash)) {
            return 'sha1';
        }
        return 'desconocido';
    }

    /**
     * Verificar contraseña soportando múltiples formatos
     */
    public static function verificar(string $password, string $hash): bool
    {
        $formato = self::detectarFormato($hash);

        return match ($formato) {
            'bcrypt', 'argon2' => password_verify($password, $hash),
            'md5'              => md5($password) === $hash,
            'sha1'             => sha1($password) === $hash,
            default            => false,
        };
    }

    /**
     * Verificar y migrar si es necesario
     */
    public static function verificarYMigrar(string $password, string $hashActual): array
    {
        $formato = self::detectarFormato($hashActual);
        $valida = self::verificar($password, $hashActual);

        $resultado = [
            'valida'        => $valida,
            'formato'       => $formato,
            'necesitaMigrar' => false,
            'nuevoHash'     => null,
        ];

        // Si la contraseña es válida y el formato es legacy, migrar
        if ($valida && in_array($formato, ['md5', 'sha1'])) {
            $resultado['necesitaMigrar'] = true;
            $resultado['nuevoHash'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        return $resultado;
    }
}

// Simular hashes legacy
$hashMd5 = md5('password123');
$hashSha1 = sha1('password123');
$hashBcrypt = password_hash('password123', PASSWORD_BCRYPT);

$hashes = [
    'MD5'    => $hashMd5,
    'SHA1'   => $hashSha1,
    'Bcrypt' => $hashBcrypt,
];

foreach ($hashes as $tipo => $hash) {
    $resultado = MigradorPasswords::verificarYMigrar('password123', $hash);
    echo "  $tipo:\n";
    echo "    Hash: " . substr($hash, 0, 30) . "...\n";
    echo "    Formato: {$resultado['formato']}\n";
    echo "    Válida: " . ($resultado['valida'] ? 'Sí' : 'No') . "\n";
    echo "    Necesita migrar: " . ($resultado['necesitaMigrar'] ? 'SÍ' : 'No') . "\n";
    if ($resultado['nuevoHash']) {
        echo "    Nuevo hash: " . substr($resultado['nuevoHash'], 0, 25) . "...\n";
    }
    echo "\n";
}

// ============================================
// Ejemplo 5: Caso práctico - Flujo completo de autenticación
// ============================================

echo "=== Ejemplo 5: Flujo completo de autenticación ===\n";

/**
 * Flujo completo que incluye: registro, login, cambio de contraseña,
 * bloqueo por intentos fallidos y tokens de recuperación.
 */
class SistemaAuth
{
    private array $usuarios = [];
    private array $intentosFallidos = [];
    private int $maxIntentos = 3;
    private int $bloqueoSegundos = 300; // 5 minutos

    public function registrar(string $usuario, string $password): bool
    {
        if (isset($this->usuarios[$usuario])) {
            echo "  El usuario '$usuario' ya existe.\n";
            return false;
        }

        $this->usuarios[$usuario] = [
            'hash'           => password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]),
            'creado'         => time(),
            'ultimo_login'   => null,
            'cambio_password' => time(),
        ];

        echo "  Usuario '$usuario' registrado exitosamente.\n";
        return true;
    }

    public function login(string $usuario, string $password): bool
    {
        // Verificar bloqueo
        if ($this->estaBloqueado($usuario)) {
            $restante = $this->tiempoBloqueoRestante($usuario);
            echo "  Cuenta bloqueada. Espere $restante segundos.\n";
            return false;
        }

        if (!isset($this->usuarios[$usuario])) {
            // Hash para prevenir timing attack
            password_hash($password, PASSWORD_BCRYPT);
            $this->registrarIntentoFallido($usuario);
            echo "  Credenciales inválidas.\n";
            return false;
        }

        if (!password_verify($password, $this->usuarios[$usuario]['hash'])) {
            $this->registrarIntentoFallido($usuario);
            $restantes = $this->maxIntentos - ($this->intentosFallidos[$usuario]['count'] ?? 0);
            echo "  Credenciales inválidas. Intentos restantes: $restantes\n";
            return false;
        }

        // Login exitoso: resetear intentos fallidos
        unset($this->intentosFallidos[$usuario]);
        $this->usuarios[$usuario]['ultimo_login'] = time();

        echo "  Login exitoso para '$usuario'.\n";

        // Verificar si la contraseña necesita rehash
        if (password_needs_rehash($this->usuarios[$usuario]['hash'], PASSWORD_BCRYPT, ['cost' => 10])) {
            $this->usuarios[$usuario]['hash'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            echo "  [Info] Hash actualizado.\n";
        }

        // Verificar si la contraseña es antigua (90 días)
        $diasDesdeUltimoCambio = (time() - $this->usuarios[$usuario]['cambio_password']) / 86400;
        if ($diasDesdeUltimoCambio > 90) {
            echo "  [Aviso] Su contraseña tiene más de 90 días. Considere cambiarla.\n";
        }

        return true;
    }

    public function cambiarPassword(string $usuario, string $actual, string $nueva): bool
    {
        if (!isset($this->usuarios[$usuario])) {
            echo "  Usuario no encontrado.\n";
            return false;
        }

        if (!password_verify($actual, $this->usuarios[$usuario]['hash'])) {
            echo "  Contraseña actual incorrecta.\n";
            return false;
        }

        if (password_verify($nueva, $this->usuarios[$usuario]['hash'])) {
            echo "  La nueva contraseña no puede ser igual a la actual.\n";
            return false;
        }

        $this->usuarios[$usuario]['hash'] = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 10]);
        $this->usuarios[$usuario]['cambio_password'] = time();
        echo "  Contraseña cambiada exitosamente para '$usuario'.\n";
        return true;
    }

    private function registrarIntentoFallido(string $usuario): void
    {
        if (!isset($this->intentosFallidos[$usuario])) {
            $this->intentosFallidos[$usuario] = ['count' => 0, 'primer_intento' => time()];
        }
        $this->intentosFallidos[$usuario]['count']++;
        $this->intentosFallidos[$usuario]['ultimo_intento'] = time();
    }

    private function estaBloqueado(string $usuario): bool
    {
        if (!isset($this->intentosFallidos[$usuario])) return false;
        if ($this->intentosFallidos[$usuario]['count'] < $this->maxIntentos) return false;

        $transcurrido = time() - $this->intentosFallidos[$usuario]['ultimo_intento'];
        if ($transcurrido >= $this->bloqueoSegundos) {
            unset($this->intentosFallidos[$usuario]);
            return false;
        }

        return true;
    }

    private function tiempoBloqueoRestante(string $usuario): int
    {
        if (!isset($this->intentosFallidos[$usuario])) return 0;
        $transcurrido = time() - $this->intentosFallidos[$usuario]['ultimo_intento'];
        return max(0, $this->bloqueoSegundos - $transcurrido);
    }
}

$auth = new SistemaAuth();

// Registrar usuario
$auth->registrar('sandra', 'MiPassword123!');

// Login exitoso
echo "\n";
$auth->login('sandra', 'MiPassword123!');

// Login fallido
echo "\n";
$auth->login('sandra', 'PasswordIncorrecta');
$auth->login('sandra', 'OtraIncorrecta');
$auth->login('sandra', 'TerceraIncorrecta');

// Intentar después de bloqueo
echo "\n";
$auth->login('sandra', 'MiPassword123!');

// Cambiar contraseña (en un escenario real, después de desbloqueo)
echo "\n";
$auth->cambiarPassword('sandra', 'MiPassword123!', 'NuevaPassword456!');

?>
