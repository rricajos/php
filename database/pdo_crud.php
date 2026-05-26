<?php
/**
 * PDO CRUD - Operaciones Crear, Leer, Actualizar, Eliminar con PDO
 *
 * CRUD es el acronimo de Create, Read, Update, Delete.
 * Son las cuatro operaciones basicas de cualquier sistema de datos.
 *
 * Temas cubiertos:
 * - INSERT con lastInsertId()
 * - SELECT con WHERE y parametros
 * - UPDATE con conteo de filas afectadas
 * - DELETE con confirmacion
 * - Clase completa UsuarioRepository (patron Repository)
 */

// Configurar base de datos SQLite en memoria
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// Crear tabla de usuarios
$db->exec("
    CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        edad INTEGER,
        rol TEXT NOT NULL DEFAULT 'usuario',
        activo INTEGER NOT NULL DEFAULT 1,
        creado_en TEXT NOT NULL DEFAULT (datetime('now')),
        actualizado_en TEXT NOT NULL DEFAULT (datetime('now'))
    )
");

echo "Tabla 'usuarios' creada exitosamente\n\n";

// ============================================================
// Ejemplo 1: INSERT con lastInsertId()
// ============================================================
// Insertar registros y obtener el ID generado automaticamente

echo "=== Ejemplo 1: INSERT con lastInsertId() ===\n\n";

// Insercion simple con placeholders nombrados
$stmt = $db->prepare("
    INSERT INTO usuarios (nombre, email, edad, rol)
    VALUES (:nombre, :email, :edad, :rol)
");

$stmt->execute([
    ':nombre' => 'Ana Martinez',
    ':email'  => 'ana@ejemplo.com',
    ':edad'   => 28,
    ':rol'    => 'admin',
]);
$idAna = $db->lastInsertId();
echo "Insertado: Ana Martinez (ID: $idAna)\n";

// Insercion con la misma sentencia preparada (reutilizable)
$stmt->execute([
    ':nombre' => 'Carlos Lopez',
    ':email'  => 'carlos@ejemplo.com',
    ':edad'   => 35,
    ':rol'    => 'usuario',
]);
$idCarlos = $db->lastInsertId();
echo "Insertado: Carlos Lopez (ID: $idCarlos)\n";

// Insercion multiple en un bucle
$usuarios = [
    ['Diana Ramirez', 'diana@ejemplo.com', 42, 'moderador'],
    ['Eduardo Gomez', 'eduardo@ejemplo.com', 23, 'usuario'],
    ['Fernanda Silva', 'fernanda@ejemplo.com', 31, 'usuario'],
    ['Gabriel Torres', 'gabriel@ejemplo.com', 29, 'usuario'],
];

$idsInsertados = [];
foreach ($usuarios as [$nombre, $email, $edad, $rol]) {
    $stmt->execute([
        ':nombre' => $nombre,
        ':email'  => $email,
        ':edad'   => $edad,
        ':rol'    => $rol,
    ]);
    $idsInsertados[] = $db->lastInsertId();
}
echo "Insertados " . count($idsInsertados) . " usuarios mas (IDs: " . implode(', ', $idsInsertados) . ")\n";

// Intentar insertar email duplicado (viola UNIQUE)
try {
    $stmt->execute([
        ':nombre' => 'Ana Duplicada',
        ':email'  => 'ana@ejemplo.com',  // Email duplicado
        ':edad'   => 25,
        ':rol'    => 'usuario',
    ]);
} catch (PDOException $e) {
    echo "Error esperado (email duplicado): UNIQUE constraint failed\n";
}

// NOTA: lastInsertId() solo funciona con columnas AUTO_INCREMENT/AUTOINCREMENT
// En PostgreSQL, puedes usar RETURNING id en vez de lastInsertId()
// $stmt = $db->prepare("INSERT INTO usuarios (...) VALUES (...) RETURNING id");

echo "\n";

// ============================================================
// Ejemplo 2: SELECT con WHERE y diferentes filtros
// ============================================================
// Leer datos con condiciones, ordenamiento y limites

echo "=== Ejemplo 2: SELECT con WHERE ===\n\n";

// Buscar por ID exacto
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => 1]);
$usuario = $stmt->fetch();
echo "Buscar por ID 1:\n";
echo "  Nombre: {$usuario['nombre']}, Email: {$usuario['email']}, Rol: {$usuario['rol']}\n\n";

// Buscar por multiples condiciones
$stmt = $db->prepare("
    SELECT nombre, email, edad
    FROM usuarios
    WHERE rol = :rol AND edad >= :edad_min AND activo = 1
    ORDER BY edad ASC
");
$stmt->execute([':rol' => 'usuario', ':edad_min' => 25]);
$resultados = $stmt->fetchAll();

echo "Usuarios activos con edad >= 25:\n";
foreach ($resultados as $u) {
    echo "  - {$u['nombre']} ({$u['edad']} anos) - {$u['email']}\n";
}

// Buscar con IN (requiere construir placeholders dinamicamente)
echo "\nBuscar con IN (multiples valores):\n";
$rolesDeseados = ['admin', 'moderador'];
$placeholders = implode(',', array_fill(0, count($rolesDeseados), '?'));
$stmt = $db->prepare("SELECT nombre, rol FROM usuarios WHERE rol IN ($placeholders)");
$stmt->execute($rolesDeseados);

foreach ($stmt->fetchAll() as $u) {
    echo "  - {$u['nombre']} (rol: {$u['rol']})\n";
}

// Buscar con LIKE (busqueda parcial)
echo "\nBuscar con LIKE:\n";
$stmt = $db->prepare("SELECT nombre, email FROM usuarios WHERE nombre LIKE :busqueda");
$stmt->execute([':busqueda' => '%ez%']);  // Contiene 'ez'

foreach ($stmt->fetchAll() as $u) {
    echo "  - {$u['nombre']} ({$u['email']})\n";
}

// Contar registros por grupo
echo "\nContar por rol:\n";
$stmt = $db->query("
    SELECT rol, COUNT(*) as total
    FROM usuarios
    WHERE activo = 1
    GROUP BY rol
    ORDER BY total DESC
");
foreach ($stmt->fetchAll() as $grupo) {
    echo "  {$grupo['rol']}: {$grupo['total']} usuarios\n";
}

echo "\n";

// ============================================================
// Ejemplo 3: UPDATE con filas afectadas
// ============================================================
// Actualizar registros y verificar cuantos fueron modificados

echo "=== Ejemplo 3: UPDATE con filas afectadas ===\n\n";

// Actualizar un campo especifico
$stmt = $db->prepare("
    UPDATE usuarios
    SET rol = :nuevo_rol, actualizado_en = datetime('now')
    WHERE id = :id
");
$stmt->execute([':nuevo_rol' => 'admin', ':id' => 3]);
echo "Cambiar rol de Diana a admin: {$stmt->rowCount()} fila(s) afectada(s)\n";

// Actualizar multiples registros
$stmt = $db->prepare("
    UPDATE usuarios
    SET edad = edad + 1, actualizado_en = datetime('now')
    WHERE activo = :activo
");
$stmt->execute([':activo' => 1]);
echo "Incrementar edad de todos los activos: {$stmt->rowCount()} fila(s) afectada(s)\n";

// UPDATE condicional: solo actualizar si el valor cambio
$stmt = $db->prepare("
    UPDATE usuarios
    SET email = :nuevo_email, actualizado_en = datetime('now')
    WHERE id = :id AND email != :nuevo_email
");
$stmt->execute([
    ':id'          => 2,
    ':nuevo_email' => 'carlos.nuevo@ejemplo.com',
]);
$filasAfectadas = $stmt->rowCount();
echo "Actualizar email de Carlos: $filasAfectadas fila(s) afectada(s)\n";

// Ejecutar de nuevo (mismo valor, no deberia afectar filas)
$stmt->execute([
    ':id'          => 2,
    ':nuevo_email' => 'carlos.nuevo@ejemplo.com',
]);
$filasAfectadas = $stmt->rowCount();
echo "Actualizar mismo email: $filasAfectadas fila(s) afectada(s) (sin cambio)\n";

// UPDATE con CASE para actualizacion masiva diferenciada
echo "\nActualizacion masiva con CASE:\n";
$db->exec("
    UPDATE usuarios SET rol = CASE
        WHEN edad > 35 THEN 'senior'
        WHEN edad > 25 THEN 'regular'
        ELSE 'junior'
    END,
    actualizado_en = datetime('now')
    WHERE rol = 'usuario'
");

$stmt = $db->query("SELECT nombre, edad, rol FROM usuarios ORDER BY edad DESC");
foreach ($stmt->fetchAll() as $u) {
    echo "  {$u['nombre']} (edad: {$u['edad']}) -> rol: {$u['rol']}\n";
}

echo "\n";

// ============================================================
// Ejemplo 4: DELETE con confirmacion y soft delete
// ============================================================
// Eliminar registros de forma segura y patron soft delete

echo "=== Ejemplo 4: DELETE con confirmacion y soft delete ===\n\n";

// Verificar que el registro existe antes de eliminar
$idEliminar = 6;
$stmt = $db->prepare("SELECT id, nombre FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $idEliminar]);
$usuarioExistente = $stmt->fetch();

if ($usuarioExistente) {
    echo "Usuario encontrado: {$usuarioExistente['nombre']} (ID: {$usuarioExistente['id']})\n";

    // Eliminar el registro
    $stmtDelete = $db->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmtDelete->execute([':id' => $idEliminar]);
    echo "Eliminado: {$stmtDelete->rowCount()} fila(s)\n";
} else {
    echo "Usuario con ID $idEliminar no existe\n";
}

// Intentar eliminar un registro inexistente (no lanza error, rowCount = 0)
$stmtDelete = $db->prepare("DELETE FROM usuarios WHERE id = :id");
$stmtDelete->execute([':id' => 999]);
echo "Eliminar ID 999 (no existe): {$stmtDelete->rowCount()} fila(s)\n\n";

// --- Patron Soft Delete (marcar como inactivo en vez de borrar) ---
echo "--- Soft Delete: desactivar en vez de borrar ---\n";

// En vez de DELETE, marcar como inactivo
$stmt = $db->prepare("
    UPDATE usuarios
    SET activo = 0, actualizado_en = datetime('now')
    WHERE id = :id AND activo = 1
");
$stmt->execute([':id' => 5]);
echo "Soft delete ID 5: {$stmt->rowCount()} fila(s) desactivada(s)\n";

// Las queries normales deben filtrar por activo = 1
$activos = $db->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetchColumn();
$total = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
echo "Usuarios activos: $activos de $total total\n";

// Restaurar un usuario (revertir soft delete)
$stmt = $db->prepare("
    UPDATE usuarios
    SET activo = 1, actualizado_en = datetime('now')
    WHERE id = :id AND activo = 0
");
$stmt->execute([':id' => 5]);
echo "Restaurar ID 5: {$stmt->rowCount()} fila(s) restaurada(s)\n";

echo "\n";

// ============================================================
// Ejemplo 5: Clase UsuarioRepository completa
// ============================================================
// Patron Repository: encapsula todo el acceso a datos de una entidad

echo "=== Ejemplo 5: Clase UsuarioRepository (patron Repository) ===\n\n";

/**
 * Modelo de datos para un usuario
 * Representa la estructura de un registro de la tabla usuarios
 */
class Usuario
{
    public ?int $id;
    public string $nombre;
    public string $email;
    public ?int $edad;
    public string $rol;
    public bool $activo;
    public ?string $creado_en;
    public ?string $actualizado_en;

    public function __construct(
        string $nombre = '',
        string $email = '',
        ?int $edad = null,
        string $rol = 'usuario'
    ) {
        $this->id = null;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->edad = $edad;
        $this->rol = $rol;
        $this->activo = true;
        $this->creado_en = null;
        $this->actualizado_en = null;
    }

    /**
     * Crear un objeto Usuario desde un array de la BD
     */
    public static function desdeArray(array $datos): self
    {
        $usuario = new self();
        $usuario->id = (int) $datos['id'];
        $usuario->nombre = $datos['nombre'];
        $usuario->email = $datos['email'];
        $usuario->edad = $datos['edad'] !== null ? (int) $datos['edad'] : null;
        $usuario->rol = $datos['rol'];
        $usuario->activo = (bool) $datos['activo'];
        $usuario->creado_en = $datos['creado_en'];
        $usuario->actualizado_en = $datos['actualizado_en'];
        return $usuario;
    }
}

/**
 * Repositorio de usuarios - Maneja todas las operaciones CRUD
 * Patron Repository: abstrae la logica de acceso a datos
 */
class UsuarioRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ---- CREATE ----

    /**
     * Crear un nuevo usuario
     * @return int ID del usuario creado
     * @throws PDOException Si el email ya existe
     */
    public function crear(Usuario $usuario): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, edad, rol, activo)
            VALUES (:nombre, :email, :edad, :rol, :activo)
        ");

        $stmt->execute([
            ':nombre' => $usuario->nombre,
            ':email'  => $usuario->email,
            ':edad'   => $usuario->edad,
            ':rol'    => $usuario->rol,
            ':activo' => (int) $usuario->activo,
        ]);

        $usuario->id = (int) $this->db->lastInsertId();
        return $usuario->id;
    }

    // ---- READ ----

    /**
     * Buscar un usuario por ID
     * @return Usuario|null El usuario encontrado o null
     */
    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $datos = $stmt->fetch();

        return $datos ? Usuario::desdeArray($datos) : null;
    }

    /**
     * Buscar un usuario por email
     */
    public function buscarPorEmail(string $email): ?Usuario
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $datos = $stmt->fetch();

        return $datos ? Usuario::desdeArray($datos) : null;
    }

    /**
     * Listar todos los usuarios activos con paginacion
     * @return array{usuarios: Usuario[], total: int}
     */
    public function listarActivos(int $pagina = 1, int $porPagina = 10): array
    {
        $offset = ($pagina - 1) * $porPagina;

        // Contar total
        $total = (int) $this->db->query(
            "SELECT COUNT(*) FROM usuarios WHERE activo = 1"
        )->fetchColumn();

        // Obtener pagina
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios
            WHERE activo = 1
            ORDER BY nombre ASC
            LIMIT :limite OFFSET :offset
        ");
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $usuarios = array_map(
            fn(array $datos) => Usuario::desdeArray($datos),
            $stmt->fetchAll()
        );

        return ['usuarios' => $usuarios, 'total' => $total];
    }

    /**
     * Buscar usuarios por criterios flexibles
     */
    public function buscar(array $criterios): array
    {
        $condiciones = ['1 = 1'];  // Siempre verdadero (base)
        $params = [];

        if (isset($criterios['nombre'])) {
            $condiciones[] = 'nombre LIKE :nombre';
            $params[':nombre'] = "%{$criterios['nombre']}%";
        }

        if (isset($criterios['rol'])) {
            $condiciones[] = 'rol = :rol';
            $params[':rol'] = $criterios['rol'];
        }

        if (isset($criterios['edad_min'])) {
            $condiciones[] = 'edad >= :edad_min';
            $params[':edad_min'] = $criterios['edad_min'];
        }

        if (isset($criterios['edad_max'])) {
            $condiciones[] = 'edad <= :edad_max';
            $params[':edad_max'] = $criterios['edad_max'];
        }

        $where = implode(' AND ', $condiciones);
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE $where AND activo = 1 ORDER BY nombre");
        $stmt->execute($params);

        return array_map(
            fn(array $datos) => Usuario::desdeArray($datos),
            $stmt->fetchAll()
        );
    }

    // ---- UPDATE ----

    /**
     * Actualizar un usuario existente
     * @return bool true si se actualizo, false si no hubo cambios
     */
    public function actualizar(Usuario $usuario): bool
    {
        if ($usuario->id === null) {
            throw new InvalidArgumentException('El usuario debe tener un ID para actualizar');
        }

        $stmt = $this->db->prepare("
            UPDATE usuarios SET
                nombre = :nombre,
                email = :email,
                edad = :edad,
                rol = :rol,
                actualizado_en = datetime('now')
            WHERE id = :id
        ");

        $stmt->execute([
            ':nombre' => $usuario->nombre,
            ':email'  => $usuario->email,
            ':edad'   => $usuario->edad,
            ':rol'    => $usuario->rol,
            ':id'     => $usuario->id,
        ]);

        return $stmt->rowCount() > 0;
    }

    // ---- DELETE ----

    /**
     * Soft delete: desactivar un usuario
     */
    public function desactivar(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET activo = 0, actualizado_en = datetime('now')
            WHERE id = :id AND activo = 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reactivar un usuario desactivado
     */
    public function reactivar(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET activo = 1, actualizado_en = datetime('now')
            WHERE id = :id AND activo = 0
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Hard delete: eliminar permanentemente
     * CUIDADO: Accion irreversible
     */
    public function eliminarPermanente(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // ---- UTILIDADES ----

    /**
     * Verificar si un email ya esta en uso
     */
    public function emailExiste(string $email, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $params = [':email' => $email];

        if ($excluirId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Contar usuarios por rol
     * @return array<string, int>
     */
    public function contarPorRol(): array
    {
        return $this->db->query("
            SELECT rol, COUNT(*) as total
            FROM usuarios
            WHERE activo = 1
            GROUP BY rol
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

// ---- Demostrar el uso del UsuarioRepository ----

$repo = new UsuarioRepository($db);

// Crear un nuevo usuario
echo "--- Crear usuario ---\n";
$nuevoUsuario = new Usuario('Helena Cruz', 'helena@ejemplo.com', 27, 'usuario');
$idHelena = $repo->crear($nuevoUsuario);
echo "Creado: {$nuevoUsuario->nombre} con ID: $idHelena\n\n";

// Buscar por ID
echo "--- Buscar por ID ---\n";
$encontrado = $repo->buscarPorId(1);
if ($encontrado) {
    echo "ID 1: {$encontrado->nombre} ({$encontrado->email})\n\n";
}

// Buscar por email
echo "--- Buscar por email ---\n";
$encontrado = $repo->buscarPorEmail('diana@ejemplo.com');
echo "Email diana@: " . ($encontrado ? $encontrado->nombre : 'No encontrado') . "\n\n";

// Listar activos con paginacion
echo "--- Listar activos (pagina 1, 3 por pagina) ---\n";
$resultado = $repo->listarActivos(1, 3);
echo "Total: {$resultado['total']} | Mostrando: " . count($resultado['usuarios']) . "\n";
foreach ($resultado['usuarios'] as $u) {
    echo "  - [{$u->id}] {$u->nombre} ({$u->rol})\n";
}

// Buscar con criterios
echo "\n--- Buscar con criterios ---\n";
$encontrados = $repo->buscar(['edad_min' => 30, 'edad_max' => 45]);
echo "Usuarios entre 30 y 45 anos:\n";
foreach ($encontrados as $u) {
    echo "  - {$u->nombre} (edad: {$u->edad})\n";
}

// Actualizar
echo "\n--- Actualizar usuario ---\n";
$usuario = $repo->buscarPorId(2);
$usuario->nombre = 'Carlos Lopez Actualizado';
$usuario->rol = 'moderador';
$actualizado = $repo->actualizar($usuario);
echo "Actualizado: " . ($actualizado ? 'SI' : 'NO') . "\n";
$verificar = $repo->buscarPorId(2);
echo "Verificar: {$verificar->nombre} (rol: {$verificar->rol})\n";

// Desactivar
echo "\n--- Desactivar usuario ---\n";
$desactivado = $repo->desactivar(4);
echo "Desactivar ID 4: " . ($desactivado ? 'SI' : 'NO') . "\n";

// Contar por rol
echo "\n--- Contar por rol ---\n";
$conteo = $repo->contarPorRol();
foreach ($conteo as $rol => $total) {
    echo "  $rol: $total\n";
}

// Verificar email
echo "\n--- Verificar email ---\n";
echo "ana@ejemplo.com existe? " . ($repo->emailExiste('ana@ejemplo.com') ? 'SI' : 'NO') . "\n";
echo "nuevo@ejemplo.com existe? " . ($repo->emailExiste('nuevo@ejemplo.com') ? 'SI' : 'NO') . "\n";

echo "\n";

// ============================================================
// Ejemplo 6: Patron INSERT o UPDATE (Upsert)
// ============================================================
// Insertar si no existe, actualizar si ya existe

echo "=== Ejemplo 6: INSERT o UPDATE (Upsert) ===\n\n";

// SQLite soporta INSERT OR REPLACE (reemplaza si existe clave unica)
echo "--- INSERT OR REPLACE (SQLite) ---\n";
$stmt = $db->prepare("
    INSERT OR REPLACE INTO usuarios (id, nombre, email, edad, rol, activo, creado_en, actualizado_en)
    VALUES (:id, :nombre, :email, :edad, :rol, 1, datetime('now'), datetime('now'))
");

// Insertar nuevo
$stmt->execute([':id' => 100, ':nombre' => 'Nuevo Usuario', ':email' => 'nuevo@test.com', ':edad' => 25, ':rol' => 'usuario']);
echo "Insertado ID 100: Nuevo Usuario\n";

// Reemplazar existente (mismo ID)
$stmt->execute([':id' => 100, ':nombre' => 'Usuario Actualizado', ':email' => 'actualizado@test.com', ':edad' => 26, ':rol' => 'admin']);
echo "Reemplazado ID 100: Usuario Actualizado\n";

$verificar = $repo->buscarPorId(100);
echo "Verificar ID 100: {$verificar->nombre} ({$verificar->email})\n\n";

// SQLite 3.24+ soporta ON CONFLICT (similar a MySQL ON DUPLICATE KEY)
echo "--- INSERT ... ON CONFLICT (SQLite 3.24+) ---\n";
try {
    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre, email, edad, rol)
        VALUES (:nombre, :email, :edad, :rol)
        ON CONFLICT(email) DO UPDATE SET
            nombre = excluded.nombre,
            edad = excluded.edad,
            actualizado_en = datetime('now')
    ");

    $stmt->execute([':nombre' => 'Ana Actualizada', ':email' => 'ana@ejemplo.com', ':edad' => 30, ':rol' => 'admin']);
    echo "Upsert ana@ejemplo.com: fila afectada\n";

    $verificar = $repo->buscarPorEmail('ana@ejemplo.com');
    echo "Verificar: {$verificar->nombre} (edad: {$verificar->edad})\n";
} catch (PDOException $e) {
    echo "ON CONFLICT requiere SQLite 3.24+\n";
}

// Para MySQL, el equivalente seria:
// INSERT INTO usuarios (nombre, email, edad) VALUES (?, ?, ?)
// ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), edad = VALUES(edad)

// Para PostgreSQL, el equivalente seria:
// INSERT INTO usuarios (nombre, email, edad) VALUES ($1, $2, $3)
// ON CONFLICT (email) DO UPDATE SET nombre = EXCLUDED.nombre, edad = EXCLUDED.edad

echo "\n=== Resumen de operaciones CRUD ===\n";
echo "CREATE: INSERT + lastInsertId() para obtener el ID generado\n";
echo "READ:   SELECT + WHERE + prepared statements para filtrar\n";
echo "UPDATE: UPDATE + rowCount() para verificar cambios\n";
echo "DELETE: Preferir soft delete (activo = 0) sobre hard delete\n";
echo "UPSERT: INSERT OR REPLACE / ON CONFLICT para insertar o actualizar\n";
echo "PATRON: Repository encapsula toda la logica de acceso a datos\n";
?>
