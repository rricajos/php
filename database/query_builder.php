<?php
/**
 * QUERY BUILDER - Constructor de consultas SQL con patron Builder
 *
 * Un Query Builder permite construir consultas SQL de forma programatica
 * usando encadenamiento de metodos (fluent interface), evitando concatenar
 * strings SQL manualmente.
 *
 * Temas cubiertos:
 * - Patron Builder aplicado a SQL
 * - Encadenamiento de metodos (method chaining)
 * - select()->from()->where()->orderBy()->limit()
 * - Soporte para JOINs (INNER, LEFT, RIGHT)
 * - Ejecucion via PDO con prepared statements
 * - Comparacion con queries raw
 */

// Configurar base de datos SQLite para pruebas
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// Crear esquema de prueba
$db->exec("
    CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        edad INTEGER,
        rol TEXT NOT NULL DEFAULT 'usuario',
        activo INTEGER NOT NULL DEFAULT 1,
        creado_en TEXT DEFAULT (datetime('now'))
    )
");

$db->exec("
    CREATE TABLE pedidos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        producto TEXT NOT NULL,
        cantidad INTEGER NOT NULL DEFAULT 1,
        precio REAL NOT NULL,
        estado TEXT NOT NULL DEFAULT 'pendiente',
        fecha TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )
");

$db->exec("
    CREATE TABLE direcciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        calle TEXT NOT NULL,
        ciudad TEXT NOT NULL,
        codigo_postal TEXT,
        principal INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )
");

// Datos de prueba
$db->exec("
    INSERT INTO usuarios (nombre, email, edad, rol) VALUES
    ('Ana Martinez', 'ana@test.com', 28, 'admin'),
    ('Carlos Lopez', 'carlos@test.com', 35, 'usuario'),
    ('Diana Ramirez', 'diana@test.com', 42, 'moderador'),
    ('Eduardo Gomez', 'eduardo@test.com', 23, 'usuario'),
    ('Fernanda Silva', 'fernanda@test.com', 31, 'usuario'),
    ('Gabriel Torres', 'gabriel@test.com', 29, 'usuario')
");

$db->exec("
    INSERT INTO pedidos (usuario_id, producto, cantidad, precio, estado) VALUES
    (1, 'Laptop HP', 1, 1299.99, 'completado'),
    (1, 'Mouse Logitech', 2, 39.99, 'completado'),
    (2, 'Teclado Mecanico', 1, 149.99, 'enviado'),
    (3, 'Monitor 27\"', 1, 449.00, 'completado'),
    (4, 'Cable HDMI', 3, 12.99, 'pendiente'),
    (1, 'Webcam HD', 1, 59.99, 'pendiente'),
    (5, 'SSD 1TB', 1, 89.99, 'enviado'),
    (2, 'Auriculares', 1, 79.99, 'completado')
");

$db->exec("
    INSERT INTO direcciones (usuario_id, calle, ciudad, codigo_postal, principal) VALUES
    (1, 'Calle Reforma 123', 'CDMX', '06600', 1),
    (1, 'Av. Insurgentes 456', 'CDMX', '03100', 0),
    (2, 'Calle Juarez 789', 'Guadalajara', '44100', 1),
    (3, 'Av. Hidalgo 321', 'Monterrey', '64000', 1),
    (5, 'Calle Morelos 654', 'Puebla', '72000', 1)
");

echo "=== QUERY BUILDER ===\n\n";

// ============================================================
// Ejemplo 1: La clase QueryBuilder completa
// ============================================================

echo "=== Ejemplo 1: Implementacion del QueryBuilder ===\n\n";

/**
 * Constructor de consultas SQL con interfaz fluida (fluent interface)
 *
 * Implementa el patron Builder para construir queries SQL de forma
 * segura y legible, usando prepared statements internamente.
 *
 * Uso:
 *   $resultado = QueryBuilder::table('usuarios')
 *       ->select('nombre', 'email')
 *       ->where('activo', '=', 1)
 *       ->orderBy('nombre')
 *       ->limit(10)
 *       ->get($pdo);
 */
class QueryBuilder
{
    // Partes de la query
    private string $tabla;
    private array $columnas = ['*'];
    private array $condiciones = [];
    private array $parametros = [];
    private array $joins = [];
    private array $ordenamiento = [];
    private array $agrupamiento = [];
    private ?string $having = null;
    private array $havingParams = [];
    private ?int $limite = null;
    private ?int $offset = null;
    private bool $distinct = false;
    private int $paramCounter = 0;

    /**
     * Crear un nuevo QueryBuilder para una tabla
     */
    public function __construct(string $tabla)
    {
        $this->tabla = $tabla;
    }

    /**
     * Factory method: forma elegante de iniciar el builder
     */
    public static function table(string $tabla): self
    {
        return new self($tabla);
    }

    // ---- SELECT ----

    /**
     * Definir las columnas a seleccionar
     * Acepta multiples argumentos o un array
     *
     * @param string ...$columnas Nombres de columnas
     * @return $this Para encadenamiento
     */
    public function select(string ...$columnas): self
    {
        $this->columnas = $columnas ?: ['*'];
        return $this;
    }

    /**
     * Agregar columnas al SELECT existente
     */
    public function addSelect(string ...$columnas): self
    {
        if ($this->columnas === ['*']) {
            $this->columnas = [];
        }
        $this->columnas = array_merge($this->columnas, $columnas);
        return $this;
    }

    /**
     * SELECT DISTINCT (sin duplicados)
     */
    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    // ---- FROM (ya definido en constructor via table()) ----

    // ---- WHERE ----

    /**
     * Agregar condicion WHERE
     *
     * @param string $columna  Nombre de la columna
     * @param string $operador Operador de comparacion (=, >, <, >=, <=, !=, LIKE, IN)
     * @param mixed  $valor    Valor a comparar
     * @return $this
     */
    public function where(string $columna, string $operador, mixed $valor): self
    {
        $placeholder = $this->generarPlaceholder();

        if (strtoupper($operador) === 'IN' && is_array($valor)) {
            return $this->whereIn($columna, $valor);
        }

        $this->condiciones[] = [
            'tipo'     => 'AND',
            'clausula' => "$columna $operador $placeholder",
        ];
        $this->parametros[$placeholder] = $valor;

        return $this;
    }

    /**
     * WHERE con OR en vez de AND
     */
    public function orWhere(string $columna, string $operador, mixed $valor): self
    {
        $placeholder = $this->generarPlaceholder();

        $this->condiciones[] = [
            'tipo'     => 'OR',
            'clausula' => "$columna $operador $placeholder",
        ];
        $this->parametros[$placeholder] = $valor;

        return $this;
    }

    /**
     * WHERE columna IN (valor1, valor2, ...)
     */
    public function whereIn(string $columna, array $valores): self
    {
        $placeholders = [];
        foreach ($valores as $valor) {
            $ph = $this->generarPlaceholder();
            $placeholders[] = $ph;
            $this->parametros[$ph] = $valor;
        }

        $listaPlaceholders = implode(', ', $placeholders);
        $this->condiciones[] = [
            'tipo'     => 'AND',
            'clausula' => "$columna IN ($listaPlaceholders)",
        ];

        return $this;
    }

    /**
     * WHERE columna NOT IN (valor1, valor2, ...)
     */
    public function whereNotIn(string $columna, array $valores): self
    {
        $placeholders = [];
        foreach ($valores as $valor) {
            $ph = $this->generarPlaceholder();
            $placeholders[] = $ph;
            $this->parametros[$ph] = $valor;
        }

        $listaPlaceholders = implode(', ', $placeholders);
        $this->condiciones[] = [
            'tipo'     => 'AND',
            'clausula' => "$columna NOT IN ($listaPlaceholders)",
        ];

        return $this;
    }

    /**
     * WHERE columna BETWEEN valor1 AND valor2
     */
    public function whereBetween(string $columna, mixed $min, mixed $max): self
    {
        $phMin = $this->generarPlaceholder();
        $phMax = $this->generarPlaceholder();

        $this->condiciones[] = [
            'tipo'     => 'AND',
            'clausula' => "$columna BETWEEN $phMin AND $phMax",
        ];
        $this->parametros[$phMin] = $min;
        $this->parametros[$phMax] = $max;

        return $this;
    }

    /**
     * WHERE columna IS NULL
     */
    public function whereNull(string $columna): self
    {
        $this->condiciones[] = [
            'tipo'     => 'AND',
            'clausula' => "$columna IS NULL",
        ];
        return $this;
    }

    /**
     * WHERE columna IS NOT NULL
     */
    public function whereNotNull(string $columna): self
    {
        $this->condiciones[] = [
            'tipo'     => 'AND',
            'clausula' => "$columna IS NOT NULL",
        ];
        return $this;
    }

    /**
     * WHERE columna LIKE patron
     */
    public function whereLike(string $columna, string $patron): self
    {
        return $this->where($columna, 'LIKE', $patron);
    }

    // ---- JOINS ----

    /**
     * INNER JOIN: solo filas que coinciden en ambas tablas
     */
    public function join(string $tabla, string $columna1, string $operador, string $columna2): self
    {
        $this->joins[] = "INNER JOIN $tabla ON $columna1 $operador $columna2";
        return $this;
    }

    /**
     * LEFT JOIN: todas las filas de la tabla izquierda
     */
    public function leftJoin(string $tabla, string $columna1, string $operador, string $columna2): self
    {
        $this->joins[] = "LEFT JOIN $tabla ON $columna1 $operador $columna2";
        return $this;
    }

    /**
     * RIGHT JOIN: todas las filas de la tabla derecha
     * Nota: SQLite no soporta RIGHT JOIN, pero MySQL/PostgreSQL si
     */
    public function rightJoin(string $tabla, string $columna1, string $operador, string $columna2): self
    {
        $this->joins[] = "RIGHT JOIN $tabla ON $columna1 $operador $columna2";
        return $this;
    }

    // ---- ORDER BY ----

    /**
     * Ordenar resultados
     *
     * @param string $columna   Columna para ordenar
     * @param string $direccion ASC o DESC
     */
    public function orderBy(string $columna, string $direccion = 'ASC'): self
    {
        $direccion = strtoupper($direccion) === 'DESC' ? 'DESC' : 'ASC';
        $this->ordenamiento[] = "$columna $direccion";
        return $this;
    }

    // ---- GROUP BY ----

    /**
     * Agrupar resultados
     */
    public function groupBy(string ...$columnas): self
    {
        $this->agrupamiento = array_merge($this->agrupamiento, $columnas);
        return $this;
    }

    /**
     * Condicion HAVING (filtra grupos)
     */
    public function having(string $condicion, array $params = []): self
    {
        $this->having = $condicion;
        $this->havingParams = $params;
        return $this;
    }

    // ---- LIMIT / OFFSET ----

    /**
     * Limitar resultados
     */
    public function limit(int $cantidad): self
    {
        $this->limite = max(0, $cantidad);
        return $this;
    }

    /**
     * Desplazar resultados (para paginacion)
     */
    public function offset(int $cantidad): self
    {
        $this->offset = max(0, $cantidad);
        return $this;
    }

    /**
     * Atajo para paginacion: pagina N con X resultados por pagina
     */
    public function pagina(int $numeroPagina, int $porPagina = 15): self
    {
        $this->limite = $porPagina;
        $this->offset = ($numeroPagina - 1) * $porPagina;
        return $this;
    }

    // ---- CONSTRUCCION Y EJECUCION ----

    /**
     * Construir la query SQL final
     * @return array{sql: string, params: array}
     */
    public function build(): array
    {
        $partes = [];

        // SELECT
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $partes[] = "SELECT {$distinct}" . implode(', ', $this->columnas);

        // FROM
        $partes[] = "FROM {$this->tabla}";

        // JOINs
        foreach ($this->joins as $join) {
            $partes[] = $join;
        }

        // WHERE
        if (!empty($this->condiciones)) {
            $clausulasWhere = [];
            foreach ($this->condiciones as $i => $condicion) {
                if ($i === 0) {
                    $clausulasWhere[] = $condicion['clausula'];
                } else {
                    $clausulasWhere[] = "{$condicion['tipo']} {$condicion['clausula']}";
                }
            }
            $partes[] = "WHERE " . implode(' ', $clausulasWhere);
        }

        // GROUP BY
        if (!empty($this->agrupamiento)) {
            $partes[] = "GROUP BY " . implode(', ', $this->agrupamiento);
        }

        // HAVING
        if ($this->having !== null) {
            $partes[] = "HAVING {$this->having}";
        }

        // ORDER BY
        if (!empty($this->ordenamiento)) {
            $partes[] = "ORDER BY " . implode(', ', $this->ordenamiento);
        }

        // LIMIT
        if ($this->limite !== null) {
            $partes[] = "LIMIT {$this->limite}";
        }

        // OFFSET
        if ($this->offset !== null) {
            $partes[] = "OFFSET {$this->offset}";
        }

        $params = array_merge($this->parametros, $this->havingParams);

        return [
            'sql'    => implode("\n", $partes),
            'params' => $params,
        ];
    }

    /**
     * Obtener la SQL generada (para depuracion)
     */
    public function toSql(): string
    {
        $build = $this->build();
        return $build['sql'];
    }

    /**
     * Ejecutar la query y retornar todos los resultados
     */
    public function get(PDO $db): array
    {
        $build = $this->build();
        $stmt = $db->prepare($build['sql']);
        $stmt->execute($build['params']);
        return $stmt->fetchAll();
    }

    /**
     * Ejecutar y retornar el primer resultado
     */
    public function first(PDO $db): ?array
    {
        $this->limite = 1;
        $resultados = $this->get($db);
        return $resultados[0] ?? null;
    }

    /**
     * Ejecutar y retornar el conteo
     */
    public function count(PDO $db): int
    {
        $originalColumnas = $this->columnas;
        $this->columnas = ['COUNT(*) as total'];
        $resultado = $this->first($db);
        $this->columnas = $originalColumnas;
        return (int) ($resultado['total'] ?? 0);
    }

    /**
     * Verificar si existen resultados
     */
    public function exists(PDO $db): bool
    {
        return $this->count($db) > 0;
    }

    /**
     * Generar un nombre de placeholder unico
     */
    private function generarPlaceholder(): string
    {
        return ':p' . ($this->paramCounter++);
    }

    /**
     * Clonar el builder (para reutilizar base)
     */
    public function clonar(): self
    {
        return clone $this;
    }
}

echo "Clase QueryBuilder definida con exito\n\n";

// ============================================================
// Ejemplo 2: Consultas basicas con encadenamiento
// ============================================================

echo "=== Ejemplo 2: Consultas basicas ===\n\n";

// SELECT simple
echo "--- SELECT basico ---\n";
$usuarios = QueryBuilder::table('usuarios')
    ->select('nombre', 'email', 'rol')
    ->where('activo', '=', 1)
    ->orderBy('nombre')
    ->get($db);

echo "Usuarios activos:\n";
foreach ($usuarios as $u) {
    echo "  {$u['nombre']} ({$u['email']}) - {$u['rol']}\n";
}

// Ver la SQL generada
$query = QueryBuilder::table('usuarios')
    ->select('nombre', 'email')
    ->where('activo', '=', 1)
    ->where('edad', '>', 25)
    ->orderBy('nombre');

echo "\nSQL generada:\n" . $query->toSql() . "\n";

// SELECT con DISTINCT
echo "\n--- SELECT DISTINCT ---\n";
$roles = QueryBuilder::table('usuarios')
    ->select('rol')
    ->distinct()
    ->where('activo', '=', 1)
    ->orderBy('rol')
    ->get($db);

echo "Roles unicos: " . implode(', ', array_column($roles, 'rol')) . "\n";

// Primer resultado solamente
echo "\n--- first() ---\n";
$primero = QueryBuilder::table('usuarios')
    ->select('nombre', 'edad')
    ->where('rol', '=', 'admin')
    ->first($db);

echo "Primer admin: {$primero['nombre']} ({$primero['edad']} anos)\n";

// Contar resultados
echo "\n--- count() ---\n";
$total = QueryBuilder::table('usuarios')
    ->where('activo', '=', 1)
    ->count($db);
echo "Total usuarios activos: $total\n";

echo "\n";

// ============================================================
// Ejemplo 3: WHERE avanzado (IN, BETWEEN, LIKE, NULL, OR)
// ============================================================

echo "=== Ejemplo 3: WHERE avanzado ===\n\n";

// WHERE IN
echo "--- WHERE IN ---\n";
$admins = QueryBuilder::table('usuarios')
    ->select('nombre', 'rol')
    ->whereIn('rol', ['admin', 'moderador'])
    ->orderBy('nombre')
    ->get($db);

foreach ($admins as $u) {
    echo "  {$u['nombre']} ({$u['rol']})\n";
}

// WHERE BETWEEN
echo "\n--- WHERE BETWEEN ---\n";
$rango = QueryBuilder::table('usuarios')
    ->select('nombre', 'edad')
    ->whereBetween('edad', 25, 35)
    ->orderBy('edad')
    ->get($db);

foreach ($rango as $u) {
    echo "  {$u['nombre']} ({$u['edad']} anos)\n";
}

// WHERE LIKE
echo "\n--- WHERE LIKE ---\n";
$busqueda = QueryBuilder::table('usuarios')
    ->select('nombre', 'email')
    ->whereLike('nombre', '%ez%')
    ->get($db);

echo "Nombres con 'ez':\n";
foreach ($busqueda as $u) {
    echo "  {$u['nombre']} ({$u['email']})\n";
}

// WHERE con OR
echo "\n--- WHERE con OR ---\n";
$orQuery = QueryBuilder::table('usuarios')
    ->select('nombre', 'edad', 'rol')
    ->where('edad', '<', 25)
    ->orWhere('rol', '=', 'admin')
    ->orderBy('nombre')
    ->get($db);

echo "Menores de 25 O admins:\n";
foreach ($orQuery as $u) {
    echo "  {$u['nombre']} ({$u['edad']}) - {$u['rol']}\n";
}

// WHERE NOT IN
echo "\n--- WHERE NOT IN ---\n";
$sinRoles = QueryBuilder::table('usuarios')
    ->select('nombre', 'rol')
    ->whereNotIn('rol', ['admin', 'moderador'])
    ->orderBy('nombre')
    ->get($db);

echo "No admin ni moderador:\n";
foreach ($sinRoles as $u) {
    echo "  {$u['nombre']} ({$u['rol']})\n";
}

echo "\n";

// ============================================================
// Ejemplo 4: JOINs entre tablas
// ============================================================

echo "=== Ejemplo 4: JOINs ===\n\n";

// INNER JOIN: Usuarios con sus pedidos
echo "--- INNER JOIN ---\n";
$pedidos = QueryBuilder::table('usuarios u')
    ->select('u.nombre', 'p.producto', 'p.precio', 'p.estado')
    ->join('pedidos p', 'u.id', '=', 'p.usuario_id')
    ->where('p.estado', '=', 'completado')
    ->orderBy('u.nombre')
    ->orderBy('p.precio', 'DESC')
    ->get($db);

echo "Pedidos completados:\n";
foreach ($pedidos as $p) {
    echo "  {$p['nombre']}: {$p['producto']} - \${$p['precio']}\n";
}

// LEFT JOIN: Todos los usuarios, con o sin pedidos
echo "\n--- LEFT JOIN ---\n";
$todosConPedidos = QueryBuilder::table('usuarios u')
    ->select(
        'u.nombre',
        'COUNT(p.id) as total_pedidos',
        'COALESCE(SUM(p.precio * p.cantidad), 0) as total_gastado'
    )
    ->leftJoin('pedidos p', 'u.id', '=', 'p.usuario_id')
    ->where('u.activo', '=', 1)
    ->groupBy('u.id', 'u.nombre')
    ->orderBy('total_gastado', 'DESC')
    ->get($db);

echo "Usuarios y sus gastos:\n";
foreach ($todosConPedidos as $u) {
    echo "  {$u['nombre']}: {$u['total_pedidos']} pedidos, \$" .
         number_format($u['total_gastado'], 2) . " total\n";
}

// JOIN multiple: Usuarios + pedidos + direcciones
echo "\n--- Multiple JOINs ---\n";
$completo = QueryBuilder::table('usuarios u')
    ->select('u.nombre', 'p.producto', 'd.ciudad')
    ->join('pedidos p', 'u.id', '=', 'p.usuario_id')
    ->leftJoin('direcciones d', 'u.id', '=', 'd.usuario_id')
    ->where('d.principal', '=', 1)
    ->orderBy('u.nombre')
    ->get($db);

echo "Usuarios con pedidos y ciudad:\n";
foreach ($completo as $fila) {
    echo "  {$fila['nombre']} ({$fila['ciudad']}): {$fila['producto']}\n";
}

echo "\n";

// ============================================================
// Ejemplo 5: Paginacion y agrupacion
// ============================================================

echo "=== Ejemplo 5: Paginacion y agrupacion ===\n\n";

// Paginacion con el atajo pagina()
echo "--- Paginacion ---\n";

$porPagina = 2;
$totalUsuarios = QueryBuilder::table('usuarios')
    ->where('activo', '=', 1)
    ->count($db);

$totalPaginas = (int) ceil($totalUsuarios / $porPagina);
echo "Total: $totalUsuarios usuarios, $totalPaginas paginas\n\n";

for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
    $resultados = QueryBuilder::table('usuarios')
        ->select('nombre', 'email')
        ->where('activo', '=', 1)
        ->orderBy('nombre')
        ->pagina($pagina, $porPagina)
        ->get($db);

    echo "Pagina $pagina:\n";
    foreach ($resultados as $u) {
        echo "  - {$u['nombre']} ({$u['email']})\n";
    }
    echo "\n";
}

// GROUP BY con HAVING
echo "--- GROUP BY con HAVING ---\n";
$sql = QueryBuilder::table('pedidos p')
    ->select(
        'u.nombre',
        'COUNT(p.id) as total_pedidos',
        'SUM(p.precio * p.cantidad) as total_gastado'
    )
    ->join('usuarios u', 'p.usuario_id', '=', 'u.id')
    ->groupBy('u.id', 'u.nombre')
    ->having('COUNT(p.id) >= :min_pedidos', [':min_pedidos' => 2])
    ->orderBy('total_gastado', 'DESC');

echo "SQL:\n" . $sql->toSql() . "\n\n";

$resultado = $sql->get($db);
echo "Usuarios con 2+ pedidos:\n";
foreach ($resultado as $u) {
    echo "  {$u['nombre']}: {$u['total_pedidos']} pedidos, \$" .
         number_format($u['total_gastado'], 2) . "\n";
}

echo "\n";

// ============================================================
// Ejemplo 6: Comparacion Query Builder vs SQL raw
// ============================================================

echo "=== Ejemplo 6: Comparacion con SQL raw ===\n\n";

// Escenario: Buscar usuarios activos con pedidos completados,
// mostrar nombre, email, total gastado, filtrar por monto minimo,
// ordenar por gasto descendente, pagina 1 con 5 resultados

// --- Con Query Builder ---
echo "--- Con Query Builder ---\n";
$queryBuilder = QueryBuilder::table('usuarios u')
    ->select(
        'u.nombre',
        'u.email',
        'COUNT(p.id) as total_pedidos',
        'SUM(p.precio * p.cantidad) as total_gastado'
    )
    ->join('pedidos p', 'u.id', '=', 'p.usuario_id')
    ->where('u.activo', '=', 1)
    ->where('p.estado', '=', 'completado')
    ->groupBy('u.id', 'u.nombre', 'u.email')
    ->having('SUM(p.precio * p.cantidad) >= :monto_min', [':monto_min' => 50])
    ->orderBy('total_gastado', 'DESC')
    ->pagina(1, 5);

$resultadoBuilder = $queryBuilder->get($db);

echo "SQL generada:\n" . $queryBuilder->toSql() . "\n\n";
echo "Resultados:\n";
foreach ($resultadoBuilder as $fila) {
    echo "  {$fila['nombre']} ({$fila['email']}): ";
    echo "{$fila['total_pedidos']} pedidos, \$" . number_format($fila['total_gastado'], 2) . "\n";
}

// --- Con SQL raw (equivalente) ---
echo "\n--- Con SQL raw (equivalente) ---\n";
$sqlRaw = "
    SELECT u.nombre, u.email,
           COUNT(p.id) as total_pedidos,
           SUM(p.precio * p.cantidad) as total_gastado
    FROM usuarios u
    INNER JOIN pedidos p ON u.id = p.usuario_id
    WHERE u.activo = :activo
    AND p.estado = :estado
    GROUP BY u.id, u.nombre, u.email
    HAVING SUM(p.precio * p.cantidad) >= :monto_min
    ORDER BY total_gastado DESC
    LIMIT 5 OFFSET 0
";

$stmt = $db->prepare($sqlRaw);
$stmt->execute([':activo' => 1, ':estado' => 'completado', ':monto_min' => 50]);
$resultadoRaw = $stmt->fetchAll();

echo "SQL escrita a mano (misma query)\n";
echo "Resultados:\n";
foreach ($resultadoRaw as $fila) {
    echo "  {$fila['nombre']} ({$fila['email']}): ";
    echo "{$fila['total_pedidos']} pedidos, \$" . number_format($fila['total_gastado'], 2) . "\n";
}

echo "\n--- Comparacion ---\n\n";
echo "Query Builder:\n";
echo "  + Encadenamiento legible y expresivo\n";
echo "  + Los parametros se manejan automaticamente (seguridad)\n";
echo "  + Facil de construir dinamicamente segun condiciones\n";
echo "  + Reutilizable y componible\n";
echo "  + Menos propenso a errores de sintaxis SQL\n";
echo "  - Overhead de rendimiento (minimo)\n";
echo "  - Limitado para queries muy complejas (subqueries, CTEs)\n";
echo "  - Puede ocultar la SQL real (dificulta optimizacion)\n\n";

echo "SQL Raw:\n";
echo "  + Control total sobre la query\n";
echo "  + Sin overhead de abstraccion\n";
echo "  + Mejor para queries extremadamente complejas\n";
echo "  + Mas facil de optimizar directamente\n";
echo "  - Propenso a errores de concatenacion\n";
echo "  - Mas dificil de construir dinamicamente\n";
echo "  - Requiere disciplina para usar prepared statements\n\n";

// --- Caso donde el Builder brilla: queries dinamicas ---
echo "--- Caso ideal del Builder: queries dinamicas ---\n\n";

/**
 * Funcion que construye una query dinamica segun los filtros del usuario
 * Esto es MUCHO mas limpio con QueryBuilder que con SQL raw
 */
function buscarUsuarios(PDO $db, array $filtros): array
{
    $query = QueryBuilder::table('usuarios u')
        ->select('u.nombre', 'u.email', 'u.edad', 'u.rol');

    // Agregar filtros solo si se proporcionan
    if (isset($filtros['nombre'])) {
        $query->whereLike('u.nombre', "%{$filtros['nombre']}%");
    }

    if (isset($filtros['rol'])) {
        $query->where('u.rol', '=', $filtros['rol']);
    }

    if (isset($filtros['edad_min'])) {
        $query->where('u.edad', '>=', $filtros['edad_min']);
    }

    if (isset($filtros['edad_max'])) {
        $query->where('u.edad', '<=', $filtros['edad_max']);
    }

    if (isset($filtros['roles'])) {
        $query->whereIn('u.rol', $filtros['roles']);
    }

    if (isset($filtros['con_pedidos']) && $filtros['con_pedidos']) {
        $query->join('pedidos p', 'u.id', '=', 'p.usuario_id');
        $query->addSelect('COUNT(p.id) as total_pedidos');
        $query->groupBy('u.id', 'u.nombre', 'u.email', 'u.edad', 'u.rol');
    }

    // Siempre filtrar activos
    $query->where('u.activo', '=', 1);

    // Ordenamiento y paginacion
    $orderBy = $filtros['ordenar_por'] ?? 'u.nombre';
    $direccion = $filtros['direccion'] ?? 'ASC';
    $query->orderBy($orderBy, $direccion);

    $pagina = $filtros['pagina'] ?? 1;
    $porPagina = $filtros['por_pagina'] ?? 10;
    $query->pagina($pagina, $porPagina);

    echo "  Query generada: " . $query->toSql() . "\n\n";

    return $query->get($db);
}

// Busqueda 1: Solo por rol
echo "Busqueda 1 - Por rol:\n";
$resultados = buscarUsuarios($db, ['rol' => 'usuario']);
foreach ($resultados as $u) {
    echo "  {$u['nombre']} ({$u['email']})\n";
}

// Busqueda 2: Por rango de edad con pedidos
echo "\nBusqueda 2 - Edad 25-35 con pedidos:\n";
$resultados = buscarUsuarios($db, [
    'edad_min'    => 25,
    'edad_max'    => 35,
    'con_pedidos' => true,
    'ordenar_por' => 'u.edad',
    'direccion'   => 'DESC',
]);
foreach ($resultados as $u) {
    $pedidos = $u['total_pedidos'] ?? 0;
    echo "  {$u['nombre']} (edad: {$u['edad']}, pedidos: $pedidos)\n";
}

echo "\n=== Resumen del Query Builder ===\n";
echo "1. El patron Builder crea una interfaz fluida y legible\n";
echo "2. Los metodos retornan \$this para permitir encadenamiento\n";
echo "3. Los parametros se manejan automaticamente con prepared statements\n";
echo "4. Ideal para queries dinamicas donde los filtros varian\n";
echo "5. Para queries muy complejas, SQL raw sigue siendo valido\n";
echo "6. Frameworks como Laravel (Eloquent) y Doctrine usan este patron\n";
?>
