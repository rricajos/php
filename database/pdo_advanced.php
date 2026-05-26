<?php
/**
 * PDO ADVANCED - Funcionalidades avanzadas de PDO
 *
 * Este archivo cubre funcionalidades avanzadas que son menos comunes
 * pero muy utiles en aplicaciones de produccion.
 *
 * Temas cubiertos:
 * - Multiples conjuntos de resultados
 * - Manejo de BLOBs (datos binarios)
 * - Procedimientos almacenados (stored procedures)
 * - PDO::quote() vs prepared statements
 * - getAvailableDrivers()
 * - Conceptos de connection pooling
 */

// Configurar base de datos SQLite en memoria
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

echo "=== PDO AVANZADO ===\n\n";

// ============================================================
// Ejemplo 1: Multiples conjuntos de resultados
// ============================================================
// Algunos drivers permiten ejecutar varias queries y recorrer sus resultados

echo "=== Ejemplo 1: Multiples conjuntos de resultados ===\n\n";

// Crear tablas de ejemplo
$db->exec("
    CREATE TABLE departamentos (
        id INTEGER PRIMARY KEY,
        nombre TEXT NOT NULL
    )
");
$db->exec("
    CREATE TABLE empleados_avz (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        depto_id INTEGER,
        salario REAL,
        FOREIGN KEY (depto_id) REFERENCES departamentos(id)
    )
");
$db->exec("
    INSERT INTO departamentos VALUES (1, 'Ingenieria'), (2, 'Marketing'), (3, 'Ventas')
");
$db->exec("
    INSERT INTO empleados_avz (nombre, depto_id, salario) VALUES
    ('Ana', 1, 85000), ('Carlos', 2, 62000), ('Diana', 1, 92000),
    ('Eduardo', 3, 58000), ('Fernanda', 2, 67000)
");

// SQLite NO soporta multiples queries en una sola llamada a query().
// Pero MySQL SI lo soporta con PDO::ATTR_EMULATE_PREPARES = true
// Aqui simulamos el concepto ejecutando queries separadas:

echo "--- Simulacion de multiples result sets ---\n\n";

// En MySQL, esto funcionaria con una sola llamada:
// $stmt = $db->query("SELECT * FROM departamentos; SELECT * FROM empleados");
// $resultSet1 = $stmt->fetchAll();
// $stmt->nextRowset();
// $resultSet2 = $stmt->fetchAll();

// Simulacion con queries individuales (funciona en todos los drivers)
$queries = [
    'departamentos' => "SELECT * FROM departamentos",
    'empleados'     => "SELECT * FROM empleados_avz ORDER BY salario DESC",
    'estadisticas'  => "SELECT depto_id, COUNT(*) as total, AVG(salario) as salario_promedio
                        FROM empleados_avz GROUP BY depto_id",
];

$resultados = [];
foreach ($queries as $nombre => $sql) {
    $resultados[$nombre] = $db->query($sql)->fetchAll();
}

echo "Resultado 1 - Departamentos:\n";
foreach ($resultados['departamentos'] as $d) {
    echo "  [{$d['id']}] {$d['nombre']}\n";
}

echo "\nResultado 2 - Empleados (por salario):\n";
foreach ($resultados['empleados'] as $e) {
    echo "  {$e['nombre']} - \${$e['salario']}\n";
}

echo "\nResultado 3 - Estadisticas por departamento:\n";
foreach ($resultados['estadisticas'] as $est) {
    echo "  Depto {$est['depto_id']}: {$est['total']} empleados, ";
    echo "promedio \$" . number_format($est['salario_promedio'], 2) . "\n";
}

// Ejemplo de nextRowset() para MySQL (comentado porque SQLite no lo soporta)
/*
// En MySQL con EMULATE_PREPARES activado:
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
$stmt = $db->query("
    SELECT * FROM departamentos;
    SELECT nombre, salario FROM empleados ORDER BY salario DESC LIMIT 3;
    SELECT COUNT(*) as total FROM empleados
");

// Primer conjunto de resultados
echo "Departamentos:\n";
while ($fila = $stmt->fetch()) {
    echo "  {$fila['nombre']}\n";
}

// Avanzar al siguiente conjunto
$stmt->nextRowset();
echo "\nTop 3 salarios:\n";
while ($fila = $stmt->fetch()) {
    echo "  {$fila['nombre']}: \${$fila['salario']}\n";
}

// Avanzar al tercer conjunto
$stmt->nextRowset();
$fila = $stmt->fetch();
echo "\nTotal empleados: {$fila['total']}\n";
*/

echo "\nNota: nextRowset() funciona en MySQL/PostgreSQL con stored procedures\n";
echo "SQLite no soporta multiples result sets en una sola query.\n";

echo "\n";

// ============================================================
// Ejemplo 2: Manejo de BLOBs (datos binarios)
// ============================================================
// Almacenar y recuperar datos binarios como imagenes, archivos, etc.

echo "=== Ejemplo 2: Manejo de BLOBs (datos binarios) ===\n\n";

// Crear tabla para archivos
$db->exec("
    CREATE TABLE archivos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        tipo_mime TEXT NOT NULL,
        tamano INTEGER NOT NULL,
        contenido BLOB NOT NULL,
        hash_md5 TEXT NOT NULL,
        creado_en TEXT DEFAULT (datetime('now'))
    )
");

/**
 * Repositorio para almacenar archivos binarios en la BD
 */
class ArchivoRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Guardar datos binarios en la BD
     * En produccion, normalmente se guardan archivos en disco/S3
     * y solo la referencia en la BD, pero BLOBs son utiles para:
     * - Archivos pequenos (iconos, thumbnails)
     * - Datos que DEBEN estar en la BD (firmas digitales, certificados)
     */
    public function guardar(string $nombre, string $tipoMime, string $contenido): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO archivos (nombre, tipo_mime, tamano, contenido, hash_md5)
            VALUES (:nombre, :tipo, :tamano, :contenido, :hash)
        ");

        // Para BLOBs, usar PDO::PARAM_LOB
        $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindValue(':tipo', $tipoMime, PDO::PARAM_STR);
        $stmt->bindValue(':tamano', strlen($contenido), PDO::PARAM_INT);
        $stmt->bindValue(':contenido', $contenido, PDO::PARAM_LOB);
        $stmt->bindValue(':hash', md5($contenido), PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Recuperar un archivo por ID
     */
    public function obtener(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM archivos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Listar archivos sin el contenido (para mostrar en lista)
     */
    public function listar(): array
    {
        return $this->db->query("
            SELECT id, nombre, tipo_mime, tamano, hash_md5, creado_en
            FROM archivos
            ORDER BY creado_en DESC
        ")->fetchAll();
    }

    /**
     * Guardar archivo con stream (mas eficiente para archivos grandes)
     * Usando parametro por referencia con bindParam y PARAM_LOB
     */
    public function guardarConStream(string $nombre, string $tipoMime, $recursoArchivo): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO archivos (nombre, tipo_mime, tamano, contenido, hash_md5)
            VALUES (:nombre, :tipo, 0, :contenido, 'stream')
        ");

        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':tipo', $tipoMime);
        // Con PARAM_LOB y un recurso de stream, PDO puede leer directamente
        $stmt->bindParam(':contenido', $recursoArchivo, PDO::PARAM_LOB);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }
}

$repoArchivos = new ArchivoRepository($db);

// Simular almacenamiento de diferentes tipos de datos binarios
// (en produccion, estos serian archivos reales)

// Simular un archivo de texto
$textoContenido = "Este es el contenido de un documento de ejemplo.\nLinea 2 del documento.";
$idTexto = $repoArchivos->guardar('documento.txt', 'text/plain', $textoContenido);

// Simular datos binarios (imagen ficticia)
$imagenSimulada = random_bytes(256);  // 256 bytes de datos aleatorios
$idImagen = $repoArchivos->guardar('foto.jpg', 'image/jpeg', $imagenSimulada);

// Simular un JSON grande
$jsonGrande = json_encode(array_fill(0, 100, ['clave' => 'valor', 'numero' => rand(1, 1000)]));
$idJson = $repoArchivos->guardar('datos.json', 'application/json', $jsonGrande);

// Listar archivos guardados
echo "Archivos almacenados:\n";
foreach ($repoArchivos->listar() as $archivo) {
    $tamano = $archivo['tamano'] > 1024
        ? number_format($archivo['tamano'] / 1024, 1) . ' KB'
        : $archivo['tamano'] . ' bytes';
    echo "  [{$archivo['id']}] {$archivo['nombre']} ({$archivo['tipo_mime']}) - $tamano\n";
    echo "       MD5: {$archivo['hash_md5']}\n";
}

// Recuperar y verificar un archivo
$archivo = $repoArchivos->obtener($idTexto);
echo "\nRecuperar documento.txt:\n";
echo "  Contenido: " . $archivo['contenido'] . "\n";
echo "  Hash coincide: " . (md5($archivo['contenido']) === $archivo['hash_md5'] ? 'SI' : 'NO') . "\n";

// Verificar integridad de la imagen
$archivoImg = $repoArchivos->obtener($idImagen);
echo "\nVerificar foto.jpg:\n";
echo "  Tamano recuperado: " . strlen($archivoImg['contenido']) . " bytes\n";
echo "  Hash coincide: " . (md5($archivoImg['contenido']) === $archivoImg['hash_md5'] ? 'SI' : 'NO') . "\n";

echo "\n";

// ============================================================
// Ejemplo 3: Procedimientos almacenados (Stored Procedures)
// ============================================================
// Los stored procedures se ejecutan en el servidor de BD

echo "=== Ejemplo 3: Procedimientos almacenados ===\n\n";

// SQLite no soporta stored procedures nativos, pero podemos
// crear funciones personalizadas con sqliteCreateFunction
// Para MySQL/PostgreSQL, mostramos la sintaxis correcta

echo "--- SQLite: Funciones personalizadas ---\n\n";

// Registrar una funcion personalizada en SQLite
// Esto es similar conceptualmente a un stored procedure
$db->sqliteCreateFunction('calcular_impuesto', function ($precio, $tasa = 16.0) {
    return round($precio * (1 + $tasa / 100), 2);
}, 2);

$db->sqliteCreateFunction('clasificar_salario', function ($salario) {
    return match (true) {
        $salario >= 90000 => 'Senior',
        $salario >= 70000 => 'Mid-level',
        $salario >= 50000 => 'Junior',
        default           => 'Trainee',
    };
}, 1);

// Usar las funciones en queries
$resultados = $db->query("
    SELECT nombre, salario,
           calcular_impuesto(salario, 16) as salario_con_impuesto,
           clasificar_salario(salario) as nivel
    FROM empleados_avz
    ORDER BY salario DESC
")->fetchAll();

echo "Empleados con funciones personalizadas:\n";
foreach ($resultados as $e) {
    echo "  {$e['nombre']}: \${$e['salario']} -> \${$e['salario_con_impuesto']} ";
    echo "(con ISR) [{$e['nivel']}]\n";
}

// Para MySQL, los stored procedures se llamarian asi:
echo "\n--- MySQL Stored Procedures (sintaxis, no ejecutable aqui) ---\n\n";
echo "Crear procedimiento en MySQL:\n";
echo <<<'SQL'
  DELIMITER //
  CREATE PROCEDURE obtener_empleados_depto(IN p_depto_id INT)
  BEGIN
      SELECT * FROM empleados WHERE depto_id = p_depto_id ORDER BY nombre;
      SELECT COUNT(*) as total, AVG(salario) as promedio
      FROM empleados WHERE depto_id = p_depto_id;
  END //
  DELIMITER ;
SQL;

echo "\n\nLlamar desde PHP:\n";
echo <<<'PHP'
  // Llamar stored procedure con parametros de entrada
  $stmt = $db->prepare("CALL obtener_empleados_depto(:depto_id)");
  $stmt->execute([':depto_id' => 1]);

  // Primer result set: lista de empleados
  $empleados = $stmt->fetchAll();

  // Segundo result set: estadisticas
  $stmt->nextRowset();
  $estadisticas = $stmt->fetch();

  // Stored procedure con parametro de salida (OUT)
  // $db->prepare("CALL calcular_total(:id, @total)");
  // $total = $db->query("SELECT @total")->fetchColumn();
PHP;

echo "\n\n";

// Para PostgreSQL:
echo "--- PostgreSQL Functions (sintaxis, no ejecutable aqui) ---\n\n";
echo <<<'SQL'
  CREATE FUNCTION obtener_salario_promedio(p_depto TEXT)
  RETURNS NUMERIC AS $$
  BEGIN
      RETURN (SELECT AVG(salario) FROM empleados WHERE departamento = p_depto);
  END;
  $$ LANGUAGE plpgsql;
SQL;

echo "\n\nLlamar desde PHP:\n";
echo <<<'PHP'
  $stmt = $db->prepare("SELECT obtener_salario_promedio(:depto)");
  $stmt->execute([':depto' => 'Ingenieria']);
  $promedio = $stmt->fetchColumn();
PHP;

echo "\n\n";

// ============================================================
// Ejemplo 4: PDO::quote() vs Prepared Statements
// ============================================================
// SIEMPRE preferir prepared statements sobre quote()

echo "=== Ejemplo 4: PDO::quote() vs Prepared Statements ===\n\n";

$textoMalicioso = "Robert'; DROP TABLE usuarios; --";

// --- PDO::quote() ---
echo "--- PDO::quote() ---\n";
// quote() escapa y entrecomilla una cadena para uso SEGURO en SQL
$textoEscapado = $db->quote($textoMalicioso);
echo "Original:  $textoMalicioso\n";
echo "Escapado:  $textoEscapado\n";

// Usar quote() en una query (funciona pero NO ES RECOMENDADO)
$sql = "SELECT * FROM empleados_avz WHERE nombre = $textoEscapado";
echo "Query:     $sql\n\n";

// --- Prepared Statement (RECOMENDADO) ---
echo "--- Prepared Statement (RECOMENDADO) ---\n";
$stmt = $db->prepare("SELECT * FROM empleados_avz WHERE nombre = :nombre");
$stmt->execute([':nombre' => $textoMalicioso]);
echo "Query preparada: nombre = :nombre (el valor se pasa separado)\n\n";

// Comparacion detallada
echo "--- Comparacion ---\n\n";

echo "PDO::quote():\n";
echo "  + Util para queries dinamicas donde no se puede usar prepare\n";
echo "  + Rapido para un solo valor\n";
echo "  - NO todos los drivers lo soportan (ej: ODBC)\n";
echo "  - Puede fallar con ciertos charsets exoticos\n";
echo "  - Requiere recordar llamarlo SIEMPRE (facil de olvidar)\n";
echo "  - No aprovecha cache de query plan del servidor\n\n";

echo "Prepared Statements:\n";
echo "  + Separacion total entre SQL y datos (mas seguro)\n";
echo "  + Funciona en TODOS los drivers PDO\n";
echo "  + El servidor cachea el query plan (mas rapido en repeticion)\n";
echo "  + Imposible inyeccion SQL si se usa correctamente\n";
echo "  + Mejor legibilidad del codigo\n";
echo "  - Ligeramente mas codigo para queries simples\n\n";

// Caso especial donde quote() puede ser util: nombres de tablas/columnas
// Los prepared statements NO pueden parametrizar nombres de tablas/columnas
echo "Caso especial - Nombre de tabla dinamico (NO usar prepare):\n";
$tablaPermitida = 'empleados_avz';
$tablasValidas = ['empleados_avz', 'departamentos'];

if (in_array($tablaPermitida, $tablasValidas, true)) {
    // Whitelist validado, seguro usar directamente
    $resultado = $db->query("SELECT COUNT(*) FROM $tablaPermitida")->fetchColumn();
    echo "  SELECT COUNT(*) FROM $tablaPermitida = $resultado\n";
    echo "  (Seguro porque usamos whitelist de tablas permitidas)\n";
} else {
    echo "  Tabla no permitida!\n";
}

echo "\n";

// ============================================================
// Ejemplo 5: getAvailableDrivers() e informacion del sistema
// ============================================================
// Consultar que drivers PDO estan disponibles en el sistema

echo "=== Ejemplo 5: Drivers disponibles e informacion del sistema ===\n\n";

// Listar todos los drivers PDO instalados
$drivers = PDO::getAvailableDrivers();
echo "Drivers PDO disponibles en este sistema:\n";
foreach ($drivers as $driver) {
    $descripcion = match ($driver) {
        'sqlite'  => 'SQLite 3 (archivo local / memoria)',
        'mysql'   => 'MySQL / MariaDB',
        'pgsql'   => 'PostgreSQL',
        'sqlsrv'  => 'Microsoft SQL Server',
        'oci'     => 'Oracle Database',
        'odbc'    => 'ODBC (Open Database Connectivity)',
        'firebird'=> 'Firebird / InterBase',
        'dblib'   => 'FreeTDS / Sybase / MS SQL',
        default   => 'Driver adicional',
    };
    echo "  - $driver: $descripcion\n";
}

echo "\nTotal drivers: " . count($drivers) . "\n\n";

// Verificar si un driver especifico esta disponible antes de usar
function verificarDriver(string $driver): bool
{
    return in_array($driver, PDO::getAvailableDrivers(), true);
}

$driversRequeridos = ['sqlite', 'mysql', 'pgsql'];
echo "Verificar drivers requeridos:\n";
foreach ($driversRequeridos as $driver) {
    $disponible = verificarDriver($driver);
    $estado = $disponible ? 'DISPONIBLE' : 'NO INSTALADO';
    echo "  $driver: $estado\n";
}

// Informacion detallada de la conexion actual
echo "\nInformacion de la conexion actual (SQLite):\n";
$atributos = [
    'ATTR_DRIVER_NAME'     => PDO::ATTR_DRIVER_NAME,
    'ATTR_SERVER_VERSION'  => PDO::ATTR_SERVER_VERSION,
    'ATTR_CLIENT_VERSION'  => PDO::ATTR_CLIENT_VERSION,
    'ATTR_CONNECTION_STATUS' => PDO::ATTR_CONNECTION_STATUS,
];

foreach ($atributos as $nombre => $constante) {
    try {
        $valor = $db->getAttribute($constante);
        echo "  $nombre: $valor\n";
    } catch (PDOException $e) {
        echo "  $nombre: No disponible para este driver\n";
    }
}

echo "\n";

// ============================================================
// Ejemplo 6: Conceptos de Connection Pooling
// ============================================================
// Connection pooling reutiliza conexiones para mejorar rendimiento

echo "=== Ejemplo 6: Connection Pooling (conceptos y patron) ===\n\n";

/**
 * Simulacion de un connection pool basico
 *
 * En PHP, el connection pooling real se maneja a nivel de:
 * 1. PDO::ATTR_PERSISTENT - Conexiones persistentes entre peticiones
 * 2. Software externo como PgBouncer (PostgreSQL) o ProxySQL (MySQL)
 * 3. Frameworks como Laravel usan un pool interno
 *
 * Esta clase demuestra el concepto para fines educativos
 */
class ConnectionPool
{
    /** @var PDO[] Conexiones disponibles */
    private array $disponibles = [];

    /** @var PDO[] Conexiones en uso */
    private array $enUso = [];

    private string $dsn;
    private ?string $usuario;
    private ?string $contrasena;
    private array $opciones;

    private int $maxConexiones;
    private int $totalCreadas = 0;

    public function __construct(
        string $dsn,
        ?string $usuario = null,
        ?string $contrasena = null,
        array $opciones = [],
        int $maxConexiones = 10
    ) {
        $this->dsn = $dsn;
        $this->usuario = $usuario;
        $this->contrasena = $contrasena;
        $this->opciones = $opciones + [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $this->maxConexiones = $maxConexiones;
    }

    /**
     * Obtener una conexion del pool
     * Reutiliza una existente o crea una nueva si es posible
     */
    public function obtener(): PDO
    {
        // Si hay conexiones disponibles, reutilizar una
        if (!empty($this->disponibles)) {
            $conexion = array_pop($this->disponibles);
            $id = spl_object_id($conexion);
            $this->enUso[$id] = $conexion;
            echo "    [Pool] Reutilizando conexion (ID: $id)\n";
            return $conexion;
        }

        // Si no hemos alcanzado el maximo, crear una nueva
        if ($this->totalCreadas < $this->maxConexiones) {
            $conexion = new PDO(
                $this->dsn,
                $this->usuario,
                $this->contrasena,
                $this->opciones
            );
            $this->totalCreadas++;
            $id = spl_object_id($conexion);
            $this->enUso[$id] = $conexion;
            echo "    [Pool] Nueva conexion creada (ID: $id, total: {$this->totalCreadas})\n";
            return $conexion;
        }

        // Pool agotado
        throw new RuntimeException(
            "Pool agotado: {$this->maxConexiones} conexiones en uso. " .
            "Aumente el maximo o libere conexiones."
        );
    }

    /**
     * Devolver una conexion al pool para reutilizacion
     */
    public function liberar(PDO $conexion): void
    {
        $id = spl_object_id($conexion);

        if (isset($this->enUso[$id])) {
            unset($this->enUso[$id]);
            $this->disponibles[] = $conexion;
            echo "    [Pool] Conexion liberada (ID: $id)\n";
        }
    }

    /**
     * Obtener estadisticas del pool
     */
    public function estadisticas(): array
    {
        return [
            'total_creadas' => $this->totalCreadas,
            'en_uso'        => count($this->enUso),
            'disponibles'   => count($this->disponibles),
            'maximo'        => $this->maxConexiones,
        ];
    }

    /**
     * Cerrar todas las conexiones del pool
     */
    public function cerrarTodas(): void
    {
        $this->disponibles = [];
        $this->enUso = [];
        $this->totalCreadas = 0;
        echo "    [Pool] Todas las conexiones cerradas\n";
    }
}

// Crear un pool con maximo 3 conexiones
$pool = new ConnectionPool('sqlite::memory:', null, null, [], 3);

echo "--- Simular uso del connection pool ---\n\n";

// Simular multiples "peticiones" usando el pool
echo "Peticion 1:\n";
$conn1 = $pool->obtener();
$conn1->exec("CREATE TABLE pool_test (id INTEGER PRIMARY KEY, dato TEXT)");
$conn1->exec("INSERT INTO pool_test VALUES (1, 'desde conexion 1')");

echo "\nPeticion 2:\n";
$conn2 = $pool->obtener();

echo "\nPeticion 3:\n";
$conn3 = $pool->obtener();

echo "\nEstadisticas del pool:\n";
$stats = $pool->estadisticas();
echo "  Creadas: {$stats['total_creadas']}, En uso: {$stats['en_uso']}, ";
echo "Disponibles: {$stats['disponibles']}\n";

// Intentar obtener una 4ta (pool agotado)
echo "\nPeticion 4 (pool lleno):\n";
try {
    $conn4 = $pool->obtener();
} catch (RuntimeException $e) {
    echo "    Error: {$e->getMessage()}\n";
}

// Liberar una conexion y reutilizarla
echo "\nLiberar conexion 1:\n";
$pool->liberar($conn1);

echo "\nPeticion 4 (ahora hay disponibles):\n";
$conn4 = $pool->obtener();  // Reutiliza la conexion 1

$stats = $pool->estadisticas();
echo "\nEstadisticas finales:\n";
echo "  Creadas: {$stats['total_creadas']}, En uso: {$stats['en_uso']}, ";
echo "Disponibles: {$stats['disponibles']}\n";

// Limpiar
$pool->cerrarTodas();

echo "\n--- Soluciones reales de connection pooling ---\n\n";
echo "PHP nativo:\n";
echo "  PDO::ATTR_PERSISTENT => true (conexiones persistentes entre peticiones)\n\n";

echo "MySQL:\n";
echo "  ProxySQL - Proxy de alto rendimiento con pool integrado\n";
echo "  MySQL Router - Parte oficial de MySQL para balanceo y pool\n\n";

echo "PostgreSQL:\n";
echo "  PgBouncer - El mas popular, ligero y eficiente\n";
echo "  PgPool-II - Mas funciones (replicacion, balanceo)\n\n";

echo "Frameworks PHP:\n";
echo "  Laravel - Pool integrado via database.php config\n";
echo "  Symfony - Doctrine DBAL con pool configurable\n";
echo "  Swoole/OpenSwoole - Pool nativo para PHP async\n";

echo "\n=== Resumen de PDO avanzado ===\n";
echo "1. Multiples result sets: util con stored procedures en MySQL/PostgreSQL\n";
echo "2. BLOBs: usar PDO::PARAM_LOB, preferir almacenamiento externo para archivos grandes\n";
echo "3. Stored procedures: CALL en MySQL, SELECT funcion() en PostgreSQL\n";
echo "4. quote() vs prepare: SIEMPRE preferir prepared statements\n";
echo "5. getAvailableDrivers(): verificar drivers antes de usar\n";
echo "6. Connection pooling: usar persistent connections o herramientas externas\n";
?>
