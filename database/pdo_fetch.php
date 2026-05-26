<?php
/**
 * PDO FETCH - Modos de recuperacion de datos con PDO
 *
 * PDO ofrece multiples formas de recuperar datos de la base de datos.
 * Cada modo es util para diferentes situaciones.
 *
 * Temas cubiertos:
 * - fetch() vs fetchAll()
 * - FETCH_ASSOC, FETCH_OBJ, FETCH_CLASS
 * - FETCH_COLUMN, FETCH_KEY_PAIR, FETCH_GROUP
 * - fetchColumn() y rowCount()
 * - Ejemplo practico de paginacion
 */

// Configurar base de datos SQLite en memoria
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// Crear tabla y datos de ejemplo
$db->exec("
    CREATE TABLE empleados (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        departamento TEXT NOT NULL,
        salario REAL NOT NULL,
        fecha_ingreso TEXT NOT NULL,
        activo INTEGER NOT NULL DEFAULT 1
    )
");

$db->exec("
    INSERT INTO empleados (nombre, departamento, salario, fecha_ingreso, activo) VALUES
    ('Ana Martinez', 'Ingenieria', 85000.00, '2020-03-15', 1),
    ('Carlos Lopez', 'Marketing', 62000.00, '2019-07-01', 1),
    ('Diana Ramirez', 'Ingenieria', 92000.00, '2018-11-20', 1),
    ('Eduardo Gomez', 'Ventas', 58000.00, '2021-01-10', 1),
    ('Fernanda Silva', 'Marketing', 67000.00, '2020-09-05', 0),
    ('Gabriel Torres', 'Ingenieria', 95000.00, '2017-04-22', 1),
    ('Helena Cruz', 'Ventas', 61000.00, '2022-02-14', 1),
    ('Ivan Morales', 'Recursos Humanos', 70000.00, '2019-06-30', 1),
    ('Julia Fernandez', 'Recursos Humanos', 73000.00, '2018-08-12', 1),
    ('Kevin Ruiz', 'Ventas', 55000.00, '2023-05-01', 1),
    ('Laura Herrera', 'Ingenieria', 88000.00, '2021-03-18', 1),
    ('Miguel Castillo', 'Marketing', 64000.00, '2022-11-07', 1)
");

echo "Base de datos creada con 12 empleados\n\n";

// ============================================================
// Ejemplo 1: fetch() vs fetchAll()
// ============================================================
// fetch() obtiene UNA fila, fetchAll() obtiene TODAS las filas

echo "=== Ejemplo 1: fetch() vs fetchAll() ===\n\n";

// --- fetch(): Obtener una fila a la vez ---
echo "--- fetch(): Una fila a la vez ---\n";
$stmt = $db->query("SELECT nombre, departamento FROM empleados ORDER BY nombre LIMIT 5");

// Cada llamada a fetch() avanza el cursor a la siguiente fila
$fila1 = $stmt->fetch();  // Primera fila
$fila2 = $stmt->fetch();  // Segunda fila
echo "Primera fila: {$fila1['nombre']} - {$fila1['departamento']}\n";
echo "Segunda fila: {$fila2['nombre']} - {$fila2['departamento']}\n";

// Iterar las restantes con while
echo "Restantes:\n";
while ($fila = $stmt->fetch()) {
    echo "  - {$fila['nombre']}\n";
}

// fetch() retorna false cuando no hay mas filas
$fila = $stmt->fetch();
echo "Despues de la ultima fila: " . var_export($fila, true) . "\n\n";

// --- fetchAll(): Obtener todas las filas de golpe ---
echo "--- fetchAll(): Todas las filas de golpe ---\n";
$stmt = $db->query("SELECT nombre, salario FROM empleados ORDER BY salario DESC LIMIT 3");
$todos = $stmt->fetchAll();

echo "Top 3 salarios:\n";
foreach ($todos as $empleado) {
    echo "  - {$empleado['nombre']}: \$" . number_format($empleado['salario'], 2) . "\n";
}

// CUANTAS filas retorno fetchAll
echo "Total filas: " . count($todos) . "\n\n";

// Cuando usar cada uno:
echo "Cuando usar fetch(): datasets grandes (no cargar todo en memoria)\n";
echo "Cuando usar fetchAll(): datasets pequenos, necesitas el array completo\n";

echo "\n";

// ============================================================
// Ejemplo 2: FETCH_ASSOC, FETCH_OBJ, FETCH_NUM
// ============================================================
// Diferentes formatos de fila segun la necesidad

echo "=== Ejemplo 2: FETCH_ASSOC vs FETCH_OBJ vs FETCH_NUM ===\n\n";

$sql = "SELECT id, nombre, salario FROM empleados WHERE id = 1";

// FETCH_ASSOC: Array asociativo (clave => valor)
$fila = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
echo "FETCH_ASSOC:\n";
echo "  Tipo: " . gettype($fila) . "\n";
echo "  Acceso: \$fila['nombre'] = {$fila['nombre']}\n";
echo "  Estructura: " . json_encode($fila) . "\n\n";

// FETCH_OBJ: Objeto stdClass
$fila = $db->query($sql)->fetch(PDO::FETCH_OBJ);
echo "FETCH_OBJ:\n";
echo "  Tipo: " . get_class($fila) . "\n";
echo "  Acceso: \$fila->nombre = {$fila->nombre}\n";
echo "  Estructura: " . json_encode($fila) . "\n\n";

// FETCH_NUM: Array numerico (solo indices)
$fila = $db->query($sql)->fetch(PDO::FETCH_NUM);
echo "FETCH_NUM:\n";
echo "  Tipo: " . gettype($fila) . "\n";
echo "  Acceso: \$fila[1] = {$fila[1]}\n";
echo "  Estructura: " . json_encode($fila) . "\n\n";

// FETCH_BOTH: Array con indices asociativos Y numericos (default)
$fila = $db->query($sql)->fetch(PDO::FETCH_BOTH);
echo "FETCH_BOTH (default):\n";
echo "  Acceso: \$fila['nombre'] = {$fila['nombre']}, \$fila[1] = {$fila[1]}\n";
echo "  Nota: Duplica la memoria, no recomendado\n";

echo "\n";

// ============================================================
// Ejemplo 3: FETCH_CLASS - Mapear a objetos propios
// ============================================================
// Permite llenar propiedades de una clase con los datos de la BD

echo "=== Ejemplo 3: FETCH_CLASS - Mapear filas a objetos ===\n\n";

// Definir una clase que represente un empleado
class Empleado
{
    // PDO llenara estas propiedades automaticamente
    // Los nombres deben coincidir con las columnas del SELECT
    public int $id;
    public string $nombre;
    public string $departamento;
    public float $salario;
    public string $fecha_ingreso;
    public int $activo;

    // Metodo de utilidad
    public function getSalarioFormateado(): string
    {
        return '$' . number_format($this->salario, 2);
    }

    public function estaActivo(): bool
    {
        return $this->activo === 1;
    }

    public function __toString(): string
    {
        $estado = $this->estaActivo() ? 'Activo' : 'Inactivo';
        return "{$this->nombre} ({$this->departamento}) - {$this->getSalarioFormateado()} [$estado]";
    }
}

// Obtener un solo empleado como objeto Empleado
$stmt = $db->query("SELECT * FROM empleados WHERE id = 3");
$stmt->setFetchMode(PDO::FETCH_CLASS, Empleado::class);
$empleado = $stmt->fetch();

echo "Un empleado como objeto:\n";
echo "  Clase: " . get_class($empleado) . "\n";
echo "  toString: $empleado\n";
echo "  Metodo: " . $empleado->getSalarioFormateado() . "\n\n";

// Obtener todos como objetos Empleado
$stmt = $db->query("SELECT * FROM empleados WHERE departamento = 'Ingenieria' ORDER BY salario DESC");
$ingenieros = $stmt->fetchAll(PDO::FETCH_CLASS, Empleado::class);

echo "Ingenieros (como objetos Empleado):\n";
foreach ($ingenieros as $ing) {
    echo "  - $ing\n";
}

// FETCH_CLASS con constructor: los datos se asignan ANTES del constructor
// Si necesitas que se asignen DESPUES, usa FETCH_CLASS | FETCH_PROPS_LATE
echo "\nNota: Con FETCH_CLASS, las propiedades se llenan ANTES del constructor.\n";
echo "Usa FETCH_CLASS | FETCH_PROPS_LATE para llenarlas DESPUES.\n";

echo "\n";

// ============================================================
// Ejemplo 4: FETCH_COLUMN, FETCH_KEY_PAIR, FETCH_GROUP
// ============================================================
// Modos especiales para obtener datos en formatos especificos

echo "=== Ejemplo 4: FETCH_COLUMN, FETCH_KEY_PAIR, FETCH_GROUP ===\n\n";

// --- FETCH_COLUMN: Obtener una sola columna como array plano ---
echo "--- FETCH_COLUMN: Array plano de una columna ---\n";
$nombres = $db->query("SELECT nombre FROM empleados ORDER BY nombre")
              ->fetchAll(PDO::FETCH_COLUMN);

echo "Nombres: " . implode(', ', $nombres) . "\n\n";

// Tambien se puede especificar el indice de columna (0-based)
$salarios = $db->query("SELECT nombre, salario FROM empleados ORDER BY salario DESC")
               ->fetchAll(PDO::FETCH_COLUMN, 1);  // Columna 1 = salario

echo "Salarios (columna 1): " . implode(', ', $salarios) . "\n\n";

// --- FETCH_KEY_PAIR: Mapear clave => valor (exactamente 2 columnas) ---
echo "--- FETCH_KEY_PAIR: Diccionario clave => valor ---\n";
$nombreSalario = $db->query("SELECT nombre, salario FROM empleados ORDER BY nombre")
                    ->fetchAll(PDO::FETCH_KEY_PAIR);

echo "Mapa nombre => salario:\n";
foreach (array_slice($nombreSalario, 0, 5) as $nombre => $salario) {
    echo "  '$nombre' => $salario\n";
}
echo "  ... (" . count($nombreSalario) . " total)\n\n";

// Caso practico: mapear id => nombre para un dropdown
$idNombre = $db->query("SELECT id, nombre FROM empleados ORDER BY nombre")
               ->fetchAll(PDO::FETCH_KEY_PAIR);
echo "Para dropdown (id => nombre):\n";
echo "  " . json_encode(array_slice($idNombre, 0, 4, true), JSON_UNESCAPED_UNICODE) . "\n\n";

// --- FETCH_GROUP: Agrupar resultados por la primera columna ---
echo "--- FETCH_GROUP: Agrupar por una columna ---\n";
$porDepartamento = $db->query("
    SELECT departamento, nombre, salario
    FROM empleados
    WHERE activo = 1
    ORDER BY departamento, nombre
")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

echo "Empleados agrupados por departamento:\n";
foreach ($porDepartamento as $depto => $empleados) {
    echo "  $depto:\n";
    foreach ($empleados as $emp) {
        echo "    - {$emp['nombre']} (\${$emp['salario']})\n";
    }
}

// FETCH_GROUP + FETCH_COLUMN: grupo con solo una columna
echo "\nNombres agrupados por departamento:\n";
$nombresDepto = $db->query("
    SELECT departamento, nombre FROM empleados WHERE activo = 1 ORDER BY departamento
")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

foreach ($nombresDepto as $depto => $nombres) {
    echo "  $depto: " . implode(', ', $nombres) . "\n";
}

echo "\n";

// ============================================================
// Ejemplo 5: fetchColumn() y rowCount()
// ============================================================
// fetchColumn() obtiene un solo valor, rowCount() cuenta filas afectadas

echo "=== Ejemplo 5: fetchColumn() y rowCount() ===\n\n";

// --- fetchColumn(): Obtener un valor escalar ---
echo "--- fetchColumn(): Valor escalar ---\n";

// Contar empleados activos
$total = $db->query("SELECT COUNT(*) FROM empleados WHERE activo = 1")->fetchColumn();
echo "Empleados activos: $total\n";

// Obtener el salario promedio
$promedio = $db->query("SELECT AVG(salario) FROM empleados WHERE activo = 1")->fetchColumn();
echo "Salario promedio: \$" . number_format($promedio, 2) . "\n";

// Obtener el salario maximo
$maximo = $db->query("SELECT MAX(salario) FROM empleados")->fetchColumn();
echo "Salario maximo: \$" . number_format($maximo, 2) . "\n";

// fetchColumn con indice (0 = primera columna, 1 = segunda, etc.)
$stmt = $db->query("SELECT nombre, salario FROM empleados ORDER BY salario DESC LIMIT 1");
$nombre = $stmt->fetchColumn(0);  // Primera columna = nombre
echo "Empleado mejor pagado: $nombre\n";

// CUIDADO: fetchColumn() mueve el cursor, no puedes leer la misma fila dos veces
$stmt = $db->query("SELECT nombre, salario FROM empleados ORDER BY salario DESC LIMIT 1");
$nombre = $stmt->fetchColumn(0);
$salario = $stmt->fetchColumn(1);  // Esto lee la SEGUNDA fila, columna 1!
echo "CUIDADO - fetchColumn consecutivo lee filas diferentes\n\n";

// --- rowCount(): Filas afectadas por INSERT/UPDATE/DELETE ---
echo "--- rowCount(): Filas afectadas ---\n";

// rowCount con UPDATE
$stmt = $db->prepare("UPDATE empleados SET salario = salario * 1.05 WHERE departamento = :depto");
$stmt->execute([':depto' => 'Ingenieria']);
echo "UPDATE Ingenieria +5%%: {$stmt->rowCount()} filas afectadas\n";

// rowCount con DELETE
$stmt = $db->prepare("DELETE FROM empleados WHERE activo = 0");
$stmt->execute();
echo "DELETE inactivos: {$stmt->rowCount()} filas eliminadas\n";

// NOTA IMPORTANTE: rowCount() con SELECT no es confiable en todos los drivers
// Para contar resultados SELECT, usar COUNT(*) o count(fetchAll())
$stmt = $db->query("SELECT * FROM empleados WHERE departamento = 'Ventas'");
$filas = $stmt->fetchAll();
echo "SELECT Ventas: " . count($filas) . " filas (usar count() del array)\n";

// Revertir el cambio de salario para los siguientes ejemplos
$db->exec("UPDATE empleados SET salario = salario / 1.05 WHERE departamento = 'Ingenieria'");

echo "\n";

// ============================================================
// Ejemplo 6: Paginacion practica con LIMIT y OFFSET
// ============================================================
// Implementacion completa de paginacion para interfaces web

echo "=== Ejemplo 6: Paginacion practica ===\n\n";

/**
 * Clase para manejar paginacion de resultados
 * Encapsula la logica de LIMIT, OFFSET y calculo de paginas
 */
class Paginador
{
    private PDO $db;
    private int $totalRegistros;
    private int $totalPaginas;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Ejecutar una consulta paginada
     *
     * @param string $sqlBase    Query sin LIMIT/OFFSET
     * @param string $sqlCount   Query para contar el total (sin LIMIT)
     * @param array  $params     Parametros para ambas queries
     * @param int    $pagina     Numero de pagina (empezando en 1)
     * @param int    $porPagina  Resultados por pagina
     * @return array Datos de la pagina actual
     */
    public function paginar(
        string $sqlBase,
        string $sqlCount,
        array $params,
        int $pagina = 1,
        int $porPagina = 5
    ): array {
        // Validar pagina minima
        $pagina = max(1, $pagina);
        $porPagina = max(1, min(100, $porPagina));  // Entre 1 y 100

        // Contar total de registros
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $this->totalRegistros = (int) $stmtCount->fetchColumn();

        // Calcular total de paginas
        $this->totalPaginas = (int) ceil($this->totalRegistros / $porPagina);

        // Ajustar si la pagina solicitada excede el total
        $pagina = min($pagina, max(1, $this->totalPaginas));

        // Calcular offset
        $offset = ($pagina - 1) * $porPagina;

        // Ejecutar query paginada
        $sqlPaginada = "$sqlBase LIMIT :limite OFFSET :desplazamiento";
        $stmtData = $this->db->prepare($sqlPaginada);

        // Bind todos los parametros originales
        foreach ($params as $clave => $valor) {
            $stmtData->bindValue($clave, $valor);
        }
        // Bind LIMIT y OFFSET como enteros
        $stmtData->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmtData->bindValue(':desplazamiento', $offset, PDO::PARAM_INT);
        $stmtData->execute();

        return [
            'datos'           => $stmtData->fetchAll(),
            'pagina_actual'   => $pagina,
            'por_pagina'      => $porPagina,
            'total_registros' => $this->totalRegistros,
            'total_paginas'   => $this->totalPaginas,
            'tiene_anterior'  => $pagina > 1,
            'tiene_siguiente' => $pagina < $this->totalPaginas,
            'pagina_anterior' => max(1, $pagina - 1),
            'pagina_siguiente'=> min($this->totalPaginas, $pagina + 1),
        ];
    }

    /**
     * Generar informacion de navegacion (para mostrar en UI)
     */
    public function generarNavegacion(array $resultado): string
    {
        $nav = "Pagina {$resultado['pagina_actual']} de {$resultado['total_paginas']}";
        $nav .= " ({$resultado['total_registros']} registros totales)";
        $nav .= "\n  ";

        if ($resultado['tiene_anterior']) {
            $nav .= "[<< Anterior (p.{$resultado['pagina_anterior']})] ";
        }

        // Generar numeros de pagina
        for ($i = 1; $i <= $resultado['total_paginas']; $i++) {
            if ($i === $resultado['pagina_actual']) {
                $nav .= " [$i] ";  // Pagina actual resaltada
            } else {
                $nav .= " $i ";
            }
        }

        if ($resultado['tiene_siguiente']) {
            $nav .= " [Siguiente (p.{$resultado['pagina_siguiente']}) >>]";
        }

        return $nav;
    }
}

// Usar el paginador
$paginador = new Paginador($db);

// Re-insertar el registro inactivo para tener mas datos
$db->exec("INSERT INTO empleados (nombre, departamento, salario, fecha_ingreso, activo)
           VALUES ('Fernanda Silva', 'Marketing', 67000.00, '2020-09-05', 1)");

// Consulta base (sin LIMIT/OFFSET)
$sqlBase  = "SELECT nombre, departamento, salario FROM empleados WHERE activo = :activo ORDER BY nombre";
$sqlCount = "SELECT COUNT(*) FROM empleados WHERE activo = :activo";
$params   = [':activo' => 1];

// Simular navegacion por paginas
$porPagina = 4;
for ($pagina = 1; $pagina <= 3; $pagina++) {
    $resultado = $paginador->paginar($sqlBase, $sqlCount, $params, $pagina, $porPagina);

    echo "--- Pagina $pagina ---\n";
    echo $paginador->generarNavegacion($resultado) . "\n\n";

    foreach ($resultado['datos'] as $emp) {
        echo "  {$emp['nombre']} | {$emp['departamento']} | \$" .
             number_format($emp['salario'], 2) . "\n";
    }
    echo "\n";
}

echo "=== Resumen de modos de fetch ===\n";
echo "FETCH_ASSOC     -> Array asociativo (mas comun)\n";
echo "FETCH_OBJ       -> Objeto stdClass\n";
echo "FETCH_CLASS     -> Mapear a clase propia\n";
echo "FETCH_COLUMN    -> Array plano de una columna\n";
echo "FETCH_KEY_PAIR  -> Diccionario clave => valor\n";
echo "FETCH_GROUP     -> Agrupar por primera columna\n";
echo "fetchColumn()   -> Un solo valor escalar\n";
echo "rowCount()      -> Filas afectadas (INSERT/UPDATE/DELETE)\n";
?>
