<?php
/**
 * PDO CONNECTION - Conexiones a bases de datos con PDO
 *
 * PDO (PHP Data Objects) proporciona una interfaz uniforme para acceder
 * a diferentes bases de datos. Soporta MySQL, PostgreSQL, SQLite, y mas.
 *
 * Temas cubiertos:
 * - Constructor PDO y cadenas DSN
 * - Opciones de conexion (ATTR_ERRMODE, ATTR_DEFAULT_FETCH_MODE, etc.)
 * - Conexiones persistentes
 * - Configuracion de charset
 * - Clase Singleton reutilizable (patron Database)
 */

// ============================================================
// Ejemplo 1: Cadenas DSN para diferentes bases de datos
// ============================================================
// DSN = Data Source Name, define el tipo de base de datos y como conectarse

echo "=== Ejemplo 1: Cadenas DSN para MySQL, SQLite y PostgreSQL ===\n\n";

// --- MySQL DSN ---
// Formato: mysql:host=SERVIDOR;dbname=NOMBRE_BD;port=PUERTO;charset=CHARSET
$dsnMySQL = 'mysql:host=localhost;dbname=mi_aplicacion;port=3306;charset=utf8mb4';

// --- PostgreSQL DSN ---
// Formato: pgsql:host=SERVIDOR;dbname=NOMBRE_BD;port=PUERTO
$dsnPostgreSQL = 'pgsql:host=localhost;dbname=mi_aplicacion;port=5432';

// --- SQLite DSN ---
// Formato: sqlite:RUTA_AL_ARCHIVO (no necesita servidor)
$dsnSQLiteArchivo = 'sqlite:' . __DIR__ . '/mi_base_datos.db';

// SQLite en memoria (se pierde al cerrar la conexion)
$dsnSQLiteMemoria = 'sqlite::memory:';

// Intentar conexion con SQLite (funciona sin servidor externo)
try {
    $dbSQLite = new PDO($dsnSQLiteMemoria);
    echo "Conexion SQLite en memoria: EXITOSA\n";
    echo "Driver usado: " . $dbSQLite->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "Version del servidor: " . $dbSQLite->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
} catch (PDOException $e) {
    echo "Error SQLite: " . $e->getMessage() . "\n";
}

// Ejemplo de conexion MySQL (requiere servidor MySQL corriendo)
// Descomentarlo solo si tienes MySQL instalado y configurado
/*
try {
    $dbMySQL = new PDO($dsnMySQL, 'usuario', 'contrasena');
    echo "Conexion MySQL: EXITOSA\n";
} catch (PDOException $e) {
    echo "Error MySQL: " . $e->getMessage() . "\n";
}
*/

// Ejemplo de conexion PostgreSQL (requiere servidor PostgreSQL corriendo)
/*
try {
    $dbPgSQL = new PDO($dsnPostgreSQL, 'usuario', 'contrasena');
    echo "Conexion PostgreSQL: EXITOSA\n";
} catch (PDOException $e) {
    echo "Error PostgreSQL: " . $e->getMessage() . "\n";
}
*/

echo "\n";

// ============================================================
// Ejemplo 2: Opciones de conexion importantes
// ============================================================
// Las opciones controlan el comportamiento de PDO

echo "=== Ejemplo 2: Opciones de conexion PDO ===\n\n";

$opciones = [
    // Modo de errores: lanzar excepciones en vez de silenciar errores
    // ERRMODE_SILENT (default) - no muestra errores
    // ERRMODE_WARNING - genera E_WARNING
    // ERRMODE_EXCEPTION - lanza PDOException (RECOMENDADO)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // Modo de fetch por defecto: devolver arrays asociativos
    // FETCH_BOTH (default) - array con indices numericos y asociativos
    // FETCH_ASSOC - solo indices asociativos (RECOMENDADO)
    // FETCH_OBJ - objetos stdClass
    // FETCH_NUM - solo indices numericos
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Desactivar emulacion de prepared statements
    // false = usa prepared statements nativos del driver (MAS SEGURO)
    // true = PDO emula los prepared statements en PHP
    PDO::ATTR_EMULATE_PREPARES   => false,

    // Convertir valores NULL de cadenas vacias
    PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,

    // Convertir nombres de columnas a minusculas
    PDO::ATTR_CASE               => PDO::CASE_NATURAL,
];

try {
    $db = new PDO('sqlite::memory:', null, null, $opciones);
    echo "Conexion con opciones: EXITOSA\n";

    // Verificar las opciones configuradas
    $modoError = $db->getAttribute(PDO::ATTR_ERRMODE);
    echo "Modo de error: " . match($modoError) {
        PDO::ERRMODE_SILENT    => 'SILENT',
        PDO::ERRMODE_WARNING   => 'WARNING',
        PDO::ERRMODE_EXCEPTION => 'EXCEPTION',
    } . "\n";

    $modoFetch = $db->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
    echo "Modo fetch: " . match($modoFetch) {
        PDO::FETCH_BOTH  => 'BOTH',
        PDO::FETCH_ASSOC => 'ASSOC',
        PDO::FETCH_OBJ   => 'OBJ',
        PDO::FETCH_NUM   => 'NUM',
    } . "\n";

    // Nota: ATTR_EMULATE_PREPARES no siempre es legible por getAttribute
    // en todos los drivers, pero su efecto esta activo
    echo "Emular prepares: desactivado (mas seguro)\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================
// Ejemplo 3: Configuracion de charset (crucial para seguridad)
// ============================================================
// El charset debe configurarse en el DSN, NO con SET NAMES

echo "=== Ejemplo 3: Configuracion de charset ===\n\n";

// CORRECTO: charset en el DSN (MySQL)
// Esto configura el charset a nivel de conexion nativa
$dsnConCharset = 'mysql:host=localhost;dbname=mi_app;charset=utf8mb4';

// INCORRECTO (pero comun): usar SET NAMES despues de conectar
// Esto NO protege contra ciertos ataques de inyeccion SQL
// $db->exec("SET NAMES 'utf8mb4'"); // NO HACER ESTO

echo "CORRECTO - charset en DSN:\n";
echo "  'mysql:host=localhost;dbname=mi_app;charset=utf8mb4'\n\n";

echo "INCORRECTO - SET NAMES despues:\n";
echo "  \$db->exec(\"SET NAMES 'utf8mb4'\"); // Vulnerable a ataques\n\n";

// Para SQLite, el charset se maneja con PRAGMA
try {
    $dbCharset = new PDO('sqlite::memory:', null, null, $opciones);
    $dbCharset->exec("PRAGMA encoding = 'UTF-8'");
    $encoding = $dbCharset->query("PRAGMA encoding")->fetchColumn();
    echo "Encoding SQLite: $encoding\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Para PostgreSQL, charset en DSN:
// 'pgsql:host=localhost;dbname=mi_app;options=\'--client_encoding=UTF8\''

echo "\n";

// ============================================================
// Ejemplo 4: Conexiones persistentes
// ============================================================
// Las conexiones persistentes se reutilizan entre peticiones PHP

echo "=== Ejemplo 4: Conexiones persistentes ===\n\n";

// Conexion NORMAL: se abre y cierra en cada peticion PHP
$opcionesNormal = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

// Conexion PERSISTENTE: se mantiene abierta entre peticiones
$opcionesPersistente = [
    PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => true,  // Activar persistencia
];

try {
    // Conexion normal
    $dbNormal = new PDO('sqlite::memory:', null, null, $opcionesNormal);
    echo "Conexion normal creada\n";

    // Conexion persistente
    // Nota: en SQLite en memoria, la persistencia no tiene mucho sentido,
    // pero la sintaxis es la misma para MySQL/PostgreSQL
    $dbPersistente = new PDO('sqlite::memory:', null, null, $opcionesPersistente);
    echo "Conexion persistente creada\n";

    echo "\nVentajas de conexiones persistentes:\n";
    echo "  + Menor latencia (no reconecta cada peticion)\n";
    echo "  + Menor carga en el servidor de BD\n";
    echo "\nDesventajas:\n";
    echo "  - Mantiene conexiones abiertas (consume recursos)\n";
    echo "  - Puede alcanzar limites de conexion del servidor\n";
    echo "  - Las transacciones incompletas pueden persistir\n";
    echo "  - Tablas temporales y variables de sesion se comparten\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================
// Ejemplo 5: Funcion reutilizable para crear conexiones
// ============================================================

echo "=== Ejemplo 5: Funcion reutilizable de conexion ===\n\n";

/**
 * Crea una conexion PDO con las opciones recomendadas
 *
 * @param string $driver  Tipo de base de datos (mysql, pgsql, sqlite)
 * @param array  $config  Configuracion de la conexion
 * @return PDO Conexion configurada
 * @throws PDOException Si la conexion falla
 */
function crearConexion(string $driver, array $config = []): PDO
{
    // Opciones por defecto recomendadas para produccion
    $opcionesDefault = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Construir DSN segun el driver
    $dsn = match ($driver) {
        'mysql' => sprintf(
            'mysql:host=%s;dbname=%s;port=%s;charset=%s',
            $config['host'] ?? 'localhost',
            $config['database'] ?? '',
            $config['port'] ?? '3306',
            $config['charset'] ?? 'utf8mb4'
        ),
        'pgsql' => sprintf(
            'pgsql:host=%s;dbname=%s;port=%s',
            $config['host'] ?? 'localhost',
            $config['database'] ?? '',
            $config['port'] ?? '5432'
        ),
        'sqlite' => 'sqlite:' . ($config['path'] ?? ':memory:'),
        default => throw new InvalidArgumentException("Driver no soportado: $driver"),
    };

    // Usuario y contrasena (SQLite no los necesita)
    $usuario = $config['username'] ?? null;
    $contrasena = $config['password'] ?? null;

    // Combinar opciones del usuario con las por defecto
    $opciones = ($config['options'] ?? []) + $opcionesDefault;

    // Crear y retornar la conexion
    return new PDO($dsn, $usuario, $contrasena, $opciones);
}

// Usar la funcion con SQLite
try {
    $db = crearConexion('sqlite', ['path' => ':memory:']);
    echo "Conexion via funcion (SQLite): EXITOSA\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Ejemplo de como se usaria con MySQL (requiere servidor)
// $db = crearConexion('mysql', [
//     'host'     => 'localhost',
//     'database' => 'mi_aplicacion',
//     'username' => 'root',
//     'password' => 'secreto',
//     'charset'  => 'utf8mb4',
// ]);

echo "\n";

// ============================================================
// Ejemplo 6: Patron Singleton - Clase Database reutilizable
// ============================================================
// El Singleton garantiza una sola instancia de conexion en toda la app

echo "=== Ejemplo 6: Clase Database Singleton ===\n\n";

/**
 * Clase Database implementando el patron Singleton
 * Garantiza una unica conexion PDO en toda la aplicacion
 *
 * Uso:
 *   $db = Database::getInstance();
 *   $stmt = $db->getConnection()->prepare("SELECT * FROM usuarios");
 */
class Database
{
    // La unica instancia de esta clase
    private static ?Database $instancia = null;

    // La conexion PDO
    private PDO $conexion;

    // Configuracion por defecto (puede sobreescribirse)
    private static array $config = [
        'driver'   => 'sqlite',
        'path'     => ':memory:',  // Para SQLite
        'host'     => 'localhost',
        'database' => '',
        'username' => null,
        'password' => null,
        'port'     => '3306',
        'charset'  => 'utf8mb4',
        'options'  => [],
    ];

    /**
     * Constructor privado - previene new Database() desde fuera
     */
    private function __construct()
    {
        $cfg = self::$config;
        $opcionesDefault = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = match ($cfg['driver']) {
            'mysql' => sprintf(
                'mysql:host=%s;dbname=%s;port=%s;charset=%s',
                $cfg['host'], $cfg['database'], $cfg['port'], $cfg['charset']
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;dbname=%s;port=%s',
                $cfg['host'], $cfg['database'], $cfg['port']
            ),
            'sqlite' => 'sqlite:' . $cfg['path'],
            default => throw new InvalidArgumentException("Driver no soportado: {$cfg['driver']}"),
        };

        $opciones = $cfg['options'] + $opcionesDefault;
        $this->conexion = new PDO($dsn, $cfg['username'], $cfg['password'], $opciones);
    }

    /**
     * Prevenir clonacion del Singleton
     */
    private function __clone() {}

    /**
     * Configurar antes de obtener la instancia
     */
    public static function configurar(array $config): void
    {
        // Solo permitir configurar si no hay instancia creada
        if (self::$instancia !== null) {
            throw new RuntimeException(
                'No se puede reconfigurar Database despues de obtener la instancia'
            );
        }
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Obtener la instancia unica (crearla si no existe)
     */
    public static function getInstance(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Obtener la conexion PDO directamente
     */
    public function getConnection(): PDO
    {
        return $this->conexion;
    }

    /**
     * Resetear la instancia (util para testing)
     */
    public static function resetear(): void
    {
        self::$instancia = null;
        self::$config = [
            'driver'   => 'sqlite',
            'path'     => ':memory:',
            'host'     => 'localhost',
            'database' => '',
            'username' => null,
            'password' => null,
            'port'     => '3306',
            'charset'  => 'utf8mb4',
            'options'  => [],
        ];
    }
}

// Usar el Singleton
try {
    // Configurar (opcional, por defecto usa SQLite en memoria)
    Database::configurar([
        'driver' => 'sqlite',
        'path'   => ':memory:',
    ]);

    // Obtener instancia
    $db1 = Database::getInstance();
    $db2 = Database::getInstance();

    // Verificar que son la misma instancia
    echo "Misma instancia? " . ($db1 === $db2 ? 'SI' : 'NO') . "\n";

    // Usar la conexion
    $pdo = $db1->getConnection();
    $pdo->exec("CREATE TABLE test (id INTEGER PRIMARY KEY, nombre TEXT)");
    $pdo->exec("INSERT INTO test (nombre) VALUES ('Sandra')");

    $resultado = $pdo->query("SELECT * FROM test")->fetch();
    echo "Dato insertado: " . $resultado['nombre'] . "\n";

    // Intentar reconfigurar despues de instanciar (lanza error)
    try {
        Database::configurar(['driver' => 'mysql']);
    } catch (RuntimeException $e) {
        echo "Error esperado: " . $e->getMessage() . "\n";
    }

    // Resetear para permitir reconfiguracion (solo en testing)
    Database::resetear();
    echo "Singleton reseteado correctamente\n";

} catch (PDOException $e) {
    echo "Error de conexion: " . $e->getMessage() . "\n";
}

echo "\n=== Resumen de buenas practicas de conexion ===\n";
echo "1. Siempre usar ERRMODE_EXCEPTION\n";
echo "2. Siempre usar FETCH_ASSOC como modo por defecto\n";
echo "3. Desactivar EMULATE_PREPARES para mayor seguridad\n";
echo "4. Configurar charset en el DSN, no con SET NAMES\n";
echo "5. Usar Singleton o inyeccion de dependencias para reutilizar conexiones\n";
echo "6. Siempre envolver conexiones en try/catch\n";
?>
