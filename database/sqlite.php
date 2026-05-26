<?php
/**
 * SQLite con PDO - Base de datos ligera sin servidor
 *
 * SQLite es una base de datos embebida que no requiere servidor.
 * Los datos se guardan en un solo archivo (o en memoria).
 * Ideal para: prototipos, aplicaciones pequenas, testing, cache local.
 *
 * Temas cubiertos:
 * - SQLite con PDO (sin necesidad de servidor)
 * - Base de datos en memoria
 * - Creacion de tablas con tipos de datos SQLite
 * - Operaciones CRUD completas
 * - Modo WAL (Write-Ahead Logging)
 * - Ejemplo practico: clase KeyValueStore
 */

echo "=== SQLite con PDO ===\n\n";

// ============================================================
// Ejemplo 1: Conexion a SQLite (archivo y memoria)
// ============================================================

echo "=== Ejemplo 1: Conexion a SQLite ===\n\n";

// --- Base de datos en MEMORIA (se pierde al terminar el script) ---
$dbMemoria = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
echo "Conexion en memoria: EXITOSA\n";

// --- Base de datos en ARCHIVO (persiste entre ejecuciones) ---
$rutaArchivo = sys_get_temp_dir() . '/ejemplo_sqlite.db';
$dbArchivo = new PDO("sqlite:$rutaArchivo", null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
echo "Conexion en archivo: EXITOSA ($rutaArchivo)\n";

// Informacion de la conexion
echo "Driver: " . $dbMemoria->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
echo "Version SQLite: " . $dbMemoria->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";

// Configuraciones recomendadas para SQLite via PRAGMA
$dbMemoria->exec("PRAGMA journal_mode = WAL");         // Modo WAL (mejor concurrencia)
$dbMemoria->exec("PRAGMA foreign_keys = ON");           // Activar foreign keys (desactivadas por defecto)
$dbMemoria->exec("PRAGMA synchronous = NORMAL");        // Balance entre rendimiento y seguridad
$dbMemoria->exec("PRAGMA cache_size = -64000");          // 64MB de cache
$dbMemoria->exec("PRAGMA temp_store = MEMORY");          // Tablas temporales en memoria

echo "\nPRAGMAs configurados:\n";
$pragmas = ['journal_mode', 'foreign_keys', 'synchronous', 'cache_size', 'encoding'];
foreach ($pragmas as $pragma) {
    $valor = $dbMemoria->query("PRAGMA $pragma")->fetchColumn();
    echo "  $pragma = $valor\n";
}

// Limpiar archivo de prueba
$dbArchivo = null;  // Cerrar conexion
@unlink($rutaArchivo);

echo "\n";

// Usar la DB en memoria para el resto de los ejemplos
$db = $dbMemoria;

// ============================================================
// Ejemplo 2: Creacion de tablas con tipos SQLite
// ============================================================
// SQLite usa "type affinity" - los tipos son flexibles

echo "=== Ejemplo 2: Creacion de tablas ===\n\n";

// SQLite tiene 5 tipos de almacenamiento: NULL, INTEGER, REAL, TEXT, BLOB
// Pero acepta cualquier nombre de tipo y lo mapea a una "afinidad"

$db->exec("
    CREATE TABLE IF NOT EXISTS articulos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        contenido TEXT,
        autor TEXT NOT NULL,
        categoria TEXT NOT NULL DEFAULT 'general',
        vistas INTEGER NOT NULL DEFAULT 0,
        calificacion REAL DEFAULT 0.0,
        publicado INTEGER NOT NULL DEFAULT 0,
        imagen BLOB,
        creado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
        actualizado_en TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
    )
");

// Crear tabla de categorias con relacion
$db->exec("
    CREATE TABLE IF NOT EXISTS categorias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL UNIQUE,
        descripcion TEXT,
        color TEXT DEFAULT '#333333'
    )
");

// Crear tabla de etiquetas (relacion muchos a muchos)
$db->exec("
    CREATE TABLE IF NOT EXISTS etiquetas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL UNIQUE
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS articulo_etiqueta (
        articulo_id INTEGER NOT NULL,
        etiqueta_id INTEGER NOT NULL,
        PRIMARY KEY (articulo_id, etiqueta_id),
        FOREIGN KEY (articulo_id) REFERENCES articulos(id) ON DELETE CASCADE,
        FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id) ON DELETE CASCADE
    )
");

// Crear indices para mejorar rendimiento
$db->exec("CREATE INDEX IF NOT EXISTS idx_articulos_autor ON articulos(autor)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_articulos_categoria ON articulos(categoria)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_articulos_publicado ON articulos(publicado)");

echo "Tablas creadas: articulos, categorias, etiquetas, articulo_etiqueta\n";
echo "Indices creados para: autor, categoria, publicado\n\n";

// Listar tablas existentes
$tablas = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
echo "Tablas en la base de datos: " . implode(', ', $tablas) . "\n";

// Ver estructura de una tabla
$estructura = $db->query("PRAGMA table_info(articulos)")->fetchAll();
echo "\nEstructura de 'articulos':\n";
foreach ($estructura as $col) {
    $nulo = $col['notnull'] ? 'NOT NULL' : 'NULL OK';
    $defecto = $col['dflt_value'] ? " DEFAULT {$col['dflt_value']}" : '';
    $pk = $col['pk'] ? ' [PK]' : '';
    echo "  {$col['name']} {$col['type']} $nulo$defecto$pk\n";
}

echo "\n";

// ============================================================
// Ejemplo 3: Operaciones CRUD completas
// ============================================================

echo "=== Ejemplo 3: CRUD con SQLite ===\n\n";

// --- CREATE ---
echo "--- CREATE (INSERT) ---\n";

$stmtInsert = $db->prepare("
    INSERT INTO articulos (titulo, contenido, autor, categoria, publicado)
    VALUES (:titulo, :contenido, :autor, :categoria, :publicado)
");

$articulos = [
    ['Introduccion a PHP',    'PHP es un lenguaje de programacion...',     'Ana',    'Programacion', 1],
    ['Guia de SQLite',        'SQLite es una base de datos embebida...',   'Carlos', 'Bases de datos', 1],
    ['CSS Flexbox Tutorial',  'Flexbox simplifica el layout en CSS...',    'Diana',  'Frontend', 1],
    ['APIs REST con PHP',     'Las APIs REST son fundamentales...',        'Ana',    'Programacion', 1],
    ['Docker para principiantes', 'Docker facilita el despliegue...',      'Eduardo','DevOps', 0],
    ['JavaScript Async/Await','Async/await simplifica el codigo async...', 'Diana',  'Frontend', 1],
];

$idsInsertados = [];
foreach ($articulos as [$titulo, $contenido, $autor, $categoria, $publicado]) {
    $stmtInsert->execute([
        ':titulo'    => $titulo,
        ':contenido' => $contenido,
        ':autor'     => $autor,
        ':categoria' => $categoria,
        ':publicado' => $publicado,
    ]);
    $idsInsertados[] = $db->lastInsertId();
}
echo "Insertados " . count($idsInsertados) . " articulos (IDs: " . implode(', ', $idsInsertados) . ")\n\n";

// --- READ ---
echo "--- READ (SELECT) ---\n";

// Listar todos los publicados
$publicados = $db->query("
    SELECT id, titulo, autor, categoria, vistas, creado_en
    FROM articulos
    WHERE publicado = 1
    ORDER BY creado_en DESC
")->fetchAll();

echo "Articulos publicados:\n";
foreach ($publicados as $a) {
    echo "  [{$a['id']}] {$a['titulo']} por {$a['autor']} ({$a['categoria']})\n";
}

// Buscar por autor
echo "\nArticulos de Ana:\n";
$stmt = $db->prepare("SELECT titulo, categoria FROM articulos WHERE autor = :autor");
$stmt->execute([':autor' => 'Ana']);
foreach ($stmt->fetchAll() as $a) {
    echo "  - {$a['titulo']} ({$a['categoria']})\n";
}

// Contar por categoria
echo "\nArticulos por categoria:\n";
$conteo = $db->query("
    SELECT categoria, COUNT(*) as total
    FROM articulos
    GROUP BY categoria
    ORDER BY total DESC
")->fetchAll();
foreach ($conteo as $c) {
    echo "  {$c['categoria']}: {$c['total']}\n";
}

// --- UPDATE ---
echo "\n--- UPDATE ---\n";

// Incrementar vistas
$stmt = $db->prepare("UPDATE articulos SET vistas = vistas + 1 WHERE id = :id");
$stmt->execute([':id' => 1]);
$stmt->execute([':id' => 1]);
$stmt->execute([':id' => 1]);  // 3 visitas al articulo 1

$vistas = $db->query("SELECT titulo, vistas FROM articulos WHERE id = 1")->fetch();
echo "Articulo '{$vistas['titulo']}': {$vistas['vistas']} vistas\n";

// Actualizar contenido
$stmt = $db->prepare("
    UPDATE articulos
    SET contenido = :contenido, actualizado_en = datetime('now', 'localtime')
    WHERE id = :id
");
$stmt->execute([':id' => 2, ':contenido' => 'SQLite es increiblemente util para desarrollo local y testing...']);
echo "Articulo 2 actualizado: {$stmt->rowCount()} fila(s)\n";

// --- DELETE ---
echo "\n--- DELETE ---\n";

$stmt = $db->prepare("DELETE FROM articulos WHERE id = :id");
$stmt->execute([':id' => 5]);  // Eliminar el borrador
echo "Articulo borrador eliminado: {$stmt->rowCount()} fila(s)\n";

$total = $db->query("SELECT COUNT(*) FROM articulos")->fetchColumn();
echo "Total articulos restantes: $total\n";

echo "\n";

// ============================================================
// Ejemplo 4: Funciones especiales de SQLite
// ============================================================

echo "=== Ejemplo 4: Funciones especiales de SQLite ===\n\n";

// --- Funciones de fecha/hora ---
echo "--- Funciones de fecha/hora ---\n";

$fechas = $db->query("
    SELECT
        date('now') as hoy,
        time('now') as hora_actual,
        datetime('now') as fecha_hora,
        datetime('now', '+7 days') as proxima_semana,
        datetime('now', '-1 month') as mes_pasado,
        strftime('%Y', 'now') as anio,
        strftime('%m', 'now') as mes,
        strftime('%d', 'now') as dia,
        julianday('now') - julianday('2026-01-01') as dias_desde_anio
")->fetch();

echo "  Hoy: {$fechas['hoy']}\n";
echo "  Hora: {$fechas['hora_actual']}\n";
echo "  Fecha completa: {$fechas['fecha_hora']}\n";
echo "  Proxima semana: {$fechas['proxima_semana']}\n";
echo "  Mes pasado: {$fechas['mes_pasado']}\n";
echo "  Anio/Mes/Dia: {$fechas['anio']}/{$fechas['mes']}/{$fechas['dia']}\n";
echo "  Dias desde inicio de anio: " . round($fechas['dias_desde_anio']) . "\n\n";

// --- Funciones de texto ---
echo "--- Funciones de texto ---\n";

$textos = $db->query("
    SELECT
        upper('hola mundo') as mayusculas,
        lower('HOLA MUNDO') as minusculas,
        length('SQLite') as longitud,
        substr('Hola Mundo', 1, 4) as subcadena,
        replace('Hola Mundo', 'Mundo', 'SQLite') as reemplazo,
        trim('  espacios  ') as recortado,
        instr('Hola Mundo', 'Mundo') as posicion,
        typeof(42) as tipo_entero,
        typeof(3.14) as tipo_real,
        typeof('texto') as tipo_texto,
        typeof(NULL) as tipo_nulo
")->fetch();

foreach ($textos as $nombre => $valor) {
    echo "  $nombre: '$valor'\n";
}

// --- Funciones de agregacion ---
echo "\n--- Funciones de agregacion ---\n";

$stats = $db->query("
    SELECT
        COUNT(*) as total,
        SUM(vistas) as total_vistas,
        AVG(vistas) as promedio_vistas,
        MIN(vistas) as min_vistas,
        MAX(vistas) as max_vistas,
        GROUP_CONCAT(DISTINCT autor, ', ') as autores
    FROM articulos
    WHERE publicado = 1
")->fetch();

echo "  Total articulos: {$stats['total']}\n";
echo "  Total vistas: {$stats['total_vistas']}\n";
echo "  Promedio vistas: {$stats['promedio_vistas']}\n";
echo "  Autores: {$stats['autores']}\n";

echo "\n";

// ============================================================
// Ejemplo 5: Modo WAL y optimizacion de rendimiento
// ============================================================

echo "=== Ejemplo 5: Modo WAL y optimizacion ===\n\n";

// --- WAL (Write-Ahead Logging) ---
echo "--- WAL (Write-Ahead Logging) ---\n\n";

// Verificar modo actual
$modoJournal = $db->query("PRAGMA journal_mode")->fetchColumn();
echo "Modo journal actual: $modoJournal\n\n";

echo "Modos disponibles:\n";
echo "  DELETE  (default): El mas compatible. Escribe en el archivo principal.\n";
echo "  WAL:    Escrituras no bloquean lecturas. RECOMENDADO para concurrencia.\n";
echo "  MEMORY: Journal en memoria (rapido pero arriesgado con crashes).\n";
echo "  OFF:    Sin journal (maximo rendimiento, riesgo de corrupcion).\n\n";

echo "Ventajas de WAL:\n";
echo "  + Lectores no bloquean escritores\n";
echo "  + Escritores no bloquean lectores\n";
echo "  + Mejor rendimiento en escrituras frecuentes\n";
echo "  + Se puede leer mientras se escribe\n\n";

echo "Desventajas de WAL:\n";
echo "  - Requiere acceso compartido al sistema de archivos (no NFS)\n";
echo "  - El archivo WAL puede crecer (hacer checkpoint peripdico)\n";
echo "  - Solo una escritura a la vez (pero lecturas concurrentes)\n\n";

// --- Benchmark: diferentes configuraciones ---
echo "--- Benchmark: Inserciones con diferentes configuraciones ---\n\n";

$configuraciones = [
    'Default (DELETE + FULL)' => [
        "PRAGMA journal_mode = DELETE",
        "PRAGMA synchronous = FULL",
    ],
    'WAL + NORMAL' => [
        "PRAGMA journal_mode = WAL",
        "PRAGMA synchronous = NORMAL",
    ],
    'WAL + OFF (maximo rendimiento)' => [
        "PRAGMA journal_mode = WAL",
        "PRAGMA synchronous = OFF",
    ],
];

$registros = 500;

foreach ($configuraciones as $nombre => $pragmas) {
    // Crear BD fresca para cada prueba
    $dbTest = new PDO('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    foreach ($pragmas as $pragma) {
        $dbTest->exec($pragma);
    }

    $dbTest->exec("CREATE TABLE bench (id INTEGER PRIMARY KEY, dato TEXT, valor REAL)");
    $stmt = $dbTest->prepare("INSERT INTO bench (dato, valor) VALUES (?, ?)");

    // Con transaccion
    $inicio = microtime(true);
    $dbTest->beginTransaction();
    for ($i = 0; $i < $registros; $i++) {
        $stmt->execute(["dato_$i", $i * 1.5]);
    }
    $dbTest->commit();
    $tiempo = (microtime(true) - $inicio) * 1000;

    echo "  $nombre: " . number_format($tiempo, 2) . " ms ($registros inserciones)\n";
    $dbTest = null;
}

// Restaurar configuracion optima
$db->exec("PRAGMA journal_mode = WAL");
$db->exec("PRAGMA synchronous = NORMAL");

echo "\n";

// ============================================================
// Ejemplo 6: Clase KeyValueStore (almacen clave-valor)
// ============================================================
// Ejemplo practico: usar SQLite como almacen clave-valor persistente

echo "=== Ejemplo 6: KeyValueStore (almacen clave-valor) ===\n\n";

/**
 * Almacen clave-valor basado en SQLite
 *
 * Uso practico: cache, configuracion, sesiones, feature flags.
 * Similar a Redis pero sin necesidad de servidor externo.
 */
class KeyValueStore
{
    private PDO $db;

    /**
     * @param string $ruta Ruta al archivo SQLite, o ':memory:' para RAM
     */
    public function __construct(string $ruta = ':memory:')
    {
        $this->db = new PDO("sqlite:$ruta", null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Optimizar para rendimiento
        $this->db->exec("PRAGMA journal_mode = WAL");
        $this->db->exec("PRAGMA synchronous = NORMAL");

        // Crear tabla si no existe
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS kv_store (
                clave TEXT PRIMARY KEY,
                valor TEXT NOT NULL,
                tipo TEXT NOT NULL DEFAULT 'string',
                expira_en TEXT,
                creado_en TEXT NOT NULL DEFAULT (datetime('now')),
                actualizado_en TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");

        // Indice para busqueda por expiracion
        $this->db->exec("
            CREATE INDEX IF NOT EXISTS idx_kv_expiracion ON kv_store(expira_en)
        ");
    }

    /**
     * Guardar un valor con clave
     *
     * @param string   $clave  Clave unica
     * @param mixed    $valor  Cualquier valor (se serializa automaticamente)
     * @param int|null $ttl    Tiempo de vida en segundos (null = sin expiracion)
     */
    public function set(string $clave, mixed $valor, ?int $ttl = null): void
    {
        $tipo = gettype($valor);
        $valorSerializado = $this->serializar($valor);
        $expira = $ttl ? date('Y-m-d H:i:s', time() + $ttl) : null;

        $stmt = $this->db->prepare("
            INSERT INTO kv_store (clave, valor, tipo, expira_en)
            VALUES (:clave, :valor, :tipo, :expira)
            ON CONFLICT(clave) DO UPDATE SET
                valor = excluded.valor,
                tipo = excluded.tipo,
                expira_en = excluded.expira_en,
                actualizado_en = datetime('now')
        ");

        $stmt->execute([
            ':clave'  => $clave,
            ':valor'  => $valorSerializado,
            ':tipo'   => $tipo,
            ':expira' => $expira,
        ]);
    }

    /**
     * Obtener un valor por clave
     *
     * @param string $clave   Clave a buscar
     * @param mixed  $default Valor por defecto si no existe o expiro
     * @return mixed El valor almacenado o el default
     */
    public function get(string $clave, mixed $default = null): mixed
    {
        // Limpiar expirados primero
        $this->limpiarExpirados();

        $stmt = $this->db->prepare("
            SELECT valor, tipo FROM kv_store
            WHERE clave = :clave
            AND (expira_en IS NULL OR expira_en > datetime('now'))
        ");
        $stmt->execute([':clave' => $clave]);
        $resultado = $stmt->fetch();

        if (!$resultado) {
            return $default;
        }

        return $this->deserializar($resultado['valor'], $resultado['tipo']);
    }

    /**
     * Verificar si una clave existe (y no ha expirado)
     */
    public function exists(string $clave): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM kv_store
            WHERE clave = :clave
            AND (expira_en IS NULL OR expira_en > datetime('now'))
        ");
        $stmt->execute([':clave' => $clave]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Eliminar una clave
     */
    public function delete(string $clave): bool
    {
        $stmt = $this->db->prepare("DELETE FROM kv_store WHERE clave = :clave");
        $stmt->execute([':clave' => $clave]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtener multiples claves de una vez
     *
     * @param array $claves Lista de claves a obtener
     * @return array Mapa clave => valor (solo claves encontradas)
     */
    public function getMultiple(array $claves): array
    {
        $this->limpiarExpirados();

        $placeholders = implode(',', array_fill(0, count($claves), '?'));
        $stmt = $this->db->prepare("
            SELECT clave, valor, tipo FROM kv_store
            WHERE clave IN ($placeholders)
            AND (expira_en IS NULL OR expira_en > datetime('now'))
        ");
        $stmt->execute($claves);

        $resultado = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resultado[$fila['clave']] = $this->deserializar($fila['valor'], $fila['tipo']);
        }
        return $resultado;
    }

    /**
     * Incrementar un valor numerico
     */
    public function increment(string $clave, int $cantidad = 1): int
    {
        $actual = $this->get($clave, 0);
        $nuevo = (int) $actual + $cantidad;
        $this->set($clave, $nuevo);
        return $nuevo;
    }

    /**
     * Buscar claves por patron (usa LIKE de SQL)
     */
    public function buscar(string $patron): array
    {
        $stmt = $this->db->prepare("
            SELECT clave, valor, tipo, expira_en FROM kv_store
            WHERE clave LIKE :patron
            AND (expira_en IS NULL OR expira_en > datetime('now'))
            ORDER BY clave
        ");
        $stmt->execute([':patron' => $patron]);

        $resultado = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resultado[$fila['clave']] = $this->deserializar($fila['valor'], $fila['tipo']);
        }
        return $resultado;
    }

    /**
     * Obtener estadisticas del almacen
     */
    public function estadisticas(): array
    {
        $stats = $this->db->query("
            SELECT
                COUNT(*) as total_claves,
                SUM(CASE WHEN expira_en IS NOT NULL THEN 1 ELSE 0 END) as con_expiracion,
                SUM(CASE WHEN expira_en IS NOT NULL AND expira_en <= datetime('now') THEN 1 ELSE 0 END) as expiradas,
                SUM(length(valor)) as tamano_total_bytes
            FROM kv_store
        ")->fetch();

        return $stats;
    }

    /**
     * Vaciar todo el almacen
     */
    public function flush(): int
    {
        $total = (int) $this->db->query("SELECT COUNT(*) FROM kv_store")->fetchColumn();
        $this->db->exec("DELETE FROM kv_store");
        return $total;
    }

    // ---- Metodos privados ----

    private function serializar(mixed $valor): string
    {
        if (is_string($valor) || is_int($valor) || is_float($valor)) {
            return (string) $valor;
        }
        return json_encode($valor, JSON_UNESCAPED_UNICODE);
    }

    private function deserializar(string $valor, string $tipo): mixed
    {
        return match ($tipo) {
            'integer' => (int) $valor,
            'double'  => (float) $valor,
            'boolean' => $valor === '1' || $valor === 'true',
            'array', 'object' => json_decode($valor, true),
            'NULL'    => null,
            default   => $valor,
        };
    }

    private function limpiarExpirados(): void
    {
        $this->db->exec("DELETE FROM kv_store WHERE expira_en IS NOT NULL AND expira_en <= datetime('now')");
    }
}

// ---- Usar el KeyValueStore ----

$store = new KeyValueStore();  // En memoria

// Guardar diferentes tipos de datos
echo "--- Guardar valores ---\n";
$store->set('nombre', 'Sandra');
$store->set('edad', 28);
$store->set('pi', 3.14159);
$store->set('activa', true);
$store->set('colores_favoritos', ['azul', 'verde', 'morado']);
$store->set('perfil', ['nombre' => 'Sandra', 'ciudad' => 'CDMX', 'rol' => 'developer']);

echo "Guardados 6 valores de diferentes tipos\n\n";

// Recuperar valores
echo "--- Recuperar valores ---\n";
echo "nombre: " . $store->get('nombre') . "\n";
echo "edad: " . $store->get('edad') . " (tipo: " . gettype($store->get('edad')) . ")\n";
echo "pi: " . $store->get('pi') . "\n";
echo "colores: " . json_encode($store->get('colores_favoritos')) . "\n";

$perfil = $store->get('perfil');
echo "perfil: {$perfil['nombre']} de {$perfil['ciudad']}\n\n";

// Valor por defecto
echo "no_existe: " . var_export($store->get('no_existe', 'default'), true) . "\n\n";

// Verificar existencia
echo "--- Verificar existencia ---\n";
echo "nombre existe: " . ($store->exists('nombre') ? 'SI' : 'NO') . "\n";
echo "fantasma existe: " . ($store->exists('fantasma') ? 'SI' : 'NO') . "\n\n";

// Incrementar
echo "--- Incrementar ---\n";
$store->set('contador', 0);
echo "Contador: " . $store->increment('contador') . "\n";     // 1
echo "Contador: " . $store->increment('contador') . "\n";     // 2
echo "Contador: " . $store->increment('contador', 5) . "\n";  // 7
echo "Contador: " . $store->increment('contador', -2) . "\n"; // 5

// Buscar por patron
echo "\n--- Buscar por patron ---\n";
$store->set('config:app_name', 'Mi App');
$store->set('config:version', '2.0');
$store->set('config:debug', false);

$configs = $store->buscar('config:%');
echo "Claves 'config:*':\n";
foreach ($configs as $clave => $valor) {
    echo "  $clave = " . var_export($valor, true) . "\n";
}

// Obtener multiples
echo "\n--- Obtener multiples ---\n";
$valores = $store->getMultiple(['nombre', 'edad', 'no_existe', 'pi']);
foreach ($valores as $clave => $valor) {
    echo "  $clave = $valor\n";
}

// Estadisticas
echo "\n--- Estadisticas ---\n";
$stats = $store->estadisticas();
echo "  Total claves: {$stats['total_claves']}\n";
echo "  Con expiracion: {$stats['con_expiracion']}\n";
echo "  Tamano total: {$stats['tamano_total_bytes']} bytes\n";

// Eliminar y flush
echo "\n--- Eliminar ---\n";
$eliminado = $store->delete('contador');
echo "Eliminar 'contador': " . ($eliminado ? 'SI' : 'NO') . "\n";

$total = $store->flush();
echo "Flush: $total claves eliminadas\n";

echo "\n=== Resumen de SQLite ===\n";
echo "1. No requiere servidor: ideal para desarrollo, testing y apps pequenas\n";
echo "2. Usar modo WAL para mejor concurrencia en lectura/escritura\n";
echo "3. Activar PRAGMA foreign_keys = ON (desactivado por defecto)\n";
echo "4. Los tipos son flexibles (type affinity), pero ser consistente\n";
echo "5. Perfecto como cache, almacen clave-valor, o BD embebida\n";
echo "6. Limitacion: una sola escritura a la vez (no apto para alta concurrencia)\n";
?>
