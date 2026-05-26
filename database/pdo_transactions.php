<?php
/**
 * PDO TRANSACTIONS - Transacciones con PDO
 *
 * Las transacciones garantizan que un grupo de operaciones se ejecute
 * de forma atomica: todas se aplican o ninguna (ACID).
 *
 * Temas cubiertos:
 * - beginTransaction / commit / rollback
 * - try/catch con rollback automatico
 * - Transacciones anidadas con savepoints
 * - Ejemplo practico: transferencia de dinero
 * - Comparacion de rendimiento: con y sin transacciones para inserciones masivas
 */

// Configurar base de datos SQLite en memoria
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// Crear tablas de ejemplo
$db->exec("
    CREATE TABLE cuentas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titular TEXT NOT NULL,
        saldo REAL NOT NULL DEFAULT 0.00,
        activa INTEGER NOT NULL DEFAULT 1
    )
");

$db->exec("
    CREATE TABLE movimientos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cuenta_origen INTEGER,
        cuenta_destino INTEGER,
        monto REAL NOT NULL,
        tipo TEXT NOT NULL,
        descripcion TEXT,
        fecha TEXT NOT NULL DEFAULT (datetime('now')),
        FOREIGN KEY (cuenta_origen) REFERENCES cuentas(id),
        FOREIGN KEY (cuenta_destino) REFERENCES cuentas(id)
    )
");

$db->exec("
    CREATE TABLE productos_inventario (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        stock INTEGER NOT NULL DEFAULT 0,
        precio REAL NOT NULL
    )
");

// Datos iniciales
$db->exec("
    INSERT INTO cuentas (titular, saldo) VALUES
    ('Ana Martinez', 5000.00),
    ('Carlos Lopez', 3000.00),
    ('Diana Ramirez', 1500.00)
");

$db->exec("
    INSERT INTO productos_inventario (nombre, stock, precio) VALUES
    ('Laptop', 10, 1200.00),
    ('Mouse', 50, 25.00),
    ('Teclado', 30, 75.00)
");

echo "Tablas creadas: cuentas, movimientos, productos_inventario\n\n";

// ============================================================
// Ejemplo 1: beginTransaction / commit / rollback basico
// ============================================================
// La transaccion mas simple: iniciar, operar, confirmar

echo "=== Ejemplo 1: Transaccion basica ===\n\n";

// Mostrar saldos antes
$saldos = $db->query("SELECT titular, saldo FROM cuentas ORDER BY id")->fetchAll();
echo "Saldos ANTES de la transaccion:\n";
foreach ($saldos as $c) {
    echo "  {$c['titular']}: \$" . number_format($c['saldo'], 2) . "\n";
}

// Iniciar transaccion
$db->beginTransaction();

try {
    // Restar del origen
    $db->prepare("UPDATE cuentas SET saldo = saldo - :monto WHERE id = :id")
       ->execute([':monto' => 500.00, ':id' => 1]);

    // Sumar al destino
    $db->prepare("UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id")
       ->execute([':monto' => 500.00, ':id' => 2]);

    // Registrar movimiento
    $db->prepare("
        INSERT INTO movimientos (cuenta_origen, cuenta_destino, monto, tipo, descripcion)
        VALUES (:origen, :destino, :monto, 'transferencia', :desc)
    ")->execute([
        ':origen'  => 1,
        ':destino' => 2,
        ':monto'   => 500.00,
        ':desc'    => 'Pago de servicio',
    ]);

    // Confirmar todos los cambios
    $db->commit();
    echo "\nTransaccion CONFIRMADA (commit)\n";

} catch (PDOException $e) {
    // Si algo falla, revertir TODO
    $db->rollBack();
    echo "\nTransaccion REVERTIDA (rollback): " . $e->getMessage() . "\n";
}

// Verificar saldos despues
$saldos = $db->query("SELECT titular, saldo FROM cuentas ORDER BY id")->fetchAll();
echo "\nSaldos DESPUES de la transaccion:\n";
foreach ($saldos as $c) {
    echo "  {$c['titular']}: \$" . number_format($c['saldo'], 2) . "\n";
}

echo "\n";

// ============================================================
// Ejemplo 2: try/catch con rollback automatico
// ============================================================
// Patron recomendado: el catch SIEMPRE hace rollback

echo "=== Ejemplo 2: Rollback automatico en caso de error ===\n\n";

// Intentar una operacion que fallara (saldo insuficiente simulado)
echo "Intentar transferencia con validacion...\n";

$db->beginTransaction();

try {
    $montoTransferencia = 10000.00;  // Mas de lo que tiene Diana

    // Verificar saldo disponible
    $stmt = $db->prepare("SELECT saldo FROM cuentas WHERE id = :id FOR UPDATE");
    // Nota: FOR UPDATE no funciona en SQLite, pero si en MySQL/PostgreSQL
    // Aqui usamos un SELECT normal para SQLite
    $stmt = $db->prepare("SELECT saldo FROM cuentas WHERE id = :id");
    $stmt->execute([':id' => 3]);
    $saldoActual = (float) $stmt->fetchColumn();

    if ($saldoActual < $montoTransferencia) {
        // Lanzar excepcion personalizada para forzar el rollback
        throw new RuntimeException(
            "Saldo insuficiente. Disponible: \$$saldoActual, Requerido: \$$montoTransferencia"
        );
    }

    // Esta linea nunca se ejecutara por la excepcion
    $db->prepare("UPDATE cuentas SET saldo = saldo - :monto WHERE id = :id")
       ->execute([':monto' => $montoTransferencia, ':id' => 3]);

    $db->commit();
    echo "Transferencia completada\n";

} catch (RuntimeException $e) {
    // Error de logica de negocio
    $db->rollBack();
    echo "Error de negocio: {$e->getMessage()}\n";
    echo "Transaccion revertida automaticamente\n";

} catch (PDOException $e) {
    // Error de base de datos
    $db->rollBack();
    echo "Error de BD: {$e->getMessage()}\n";
    echo "Transaccion revertida automaticamente\n";
}

// Verificar que Diana conserva su saldo
$saldoDiana = $db->query("SELECT saldo FROM cuentas WHERE id = 3")->fetchColumn();
echo "Saldo de Diana despues del intento fallido: \$" . number_format($saldoDiana, 2) . "\n";
echo "(No se modifico gracias al rollback)\n";

echo "\n";

// ============================================================
// Ejemplo 3: Transacciones anidadas con savepoints
// ============================================================
// Los savepoints permiten revertir parcialmente una transaccion

echo "=== Ejemplo 3: Savepoints (transacciones anidadas) ===\n\n";

/**
 * Clase que maneja transacciones anidadas usando savepoints
 * PDO no soporta transacciones anidadas nativamente, pero podemos
 * emularlas con SAVEPOINT y RELEASE/ROLLBACK TO SAVEPOINT
 */
class TransaccionAnidada
{
    private PDO $db;
    private int $nivelAnidamiento = 0;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Iniciar transaccion (o savepoint si ya hay una activa)
     */
    public function begin(): void
    {
        if ($this->nivelAnidamiento === 0) {
            $this->db->beginTransaction();
            echo "  [TX] BEGIN TRANSACTION (nivel 0)\n";
        } else {
            $savepoint = "savepoint_nivel_{$this->nivelAnidamiento}";
            $this->db->exec("SAVEPOINT $savepoint");
            echo "  [TX] SAVEPOINT $savepoint (nivel {$this->nivelAnidamiento})\n";
        }
        $this->nivelAnidamiento++;
    }

    /**
     * Confirmar transaccion (o liberar savepoint)
     */
    public function commit(): void
    {
        $this->nivelAnidamiento--;

        if ($this->nivelAnidamiento === 0) {
            $this->db->commit();
            echo "  [TX] COMMIT (nivel 0 - todo confirmado)\n";
        } else {
            $savepoint = "savepoint_nivel_{$this->nivelAnidamiento}";
            $this->db->exec("RELEASE SAVEPOINT $savepoint");
            echo "  [TX] RELEASE $savepoint (nivel {$this->nivelAnidamiento})\n";
        }
    }

    /**
     * Revertir transaccion (o volver al savepoint)
     */
    public function rollback(): void
    {
        $this->nivelAnidamiento--;

        if ($this->nivelAnidamiento === 0) {
            $this->db->rollBack();
            echo "  [TX] ROLLBACK (nivel 0 - todo revertido)\n";
        } else {
            $savepoint = "savepoint_nivel_{$this->nivelAnidamiento}";
            $this->db->exec("ROLLBACK TO SAVEPOINT $savepoint");
            echo "  [TX] ROLLBACK TO $savepoint (nivel {$this->nivelAnidamiento})\n";
        }
    }

    public function getNivel(): int
    {
        return $this->nivelAnidamiento;
    }
}

$tx = new TransaccionAnidada($db);

// Transaccion principal
$tx->begin();  // Nivel 0 -> BEGIN TRANSACTION

// Operacion 1: Deposito (queremos que esta SE confirme)
$db->prepare("UPDATE cuentas SET saldo = saldo + 1000 WHERE id = :id")
   ->execute([':id' => 1]);
echo "  Deposito de \$1000 a cuenta 1\n";

// Sub-transaccion: intentar operacion arriesgada
$tx->begin();  // Nivel 1 -> SAVEPOINT

try {
    // Operacion 2: Descontar de inventario
    $db->prepare("UPDATE productos_inventario SET stock = stock - 5 WHERE id = 1")
       ->execute();
    echo "  Descontados 5 laptops del inventario\n";

    // Operacion 3: Esto falla intencionalmente
    throw new RuntimeException("Error simulado en sub-transaccion");

    $tx->commit();  // Nunca se ejecuta
} catch (RuntimeException $e) {
    echo "  Error en sub-transaccion: {$e->getMessage()}\n";
    $tx->rollback();  // Solo revierte el SAVEPOINT, no la transaccion principal
}

// Verificar: el deposito sigue, pero el inventario se revirtio
$saldo = $db->query("SELECT saldo FROM cuentas WHERE id = 1")->fetchColumn();
$stock = $db->query("SELECT stock FROM productos_inventario WHERE id = 1")->fetchColumn();
echo "  Verificacion intermedia: Saldo=\$$saldo, Stock laptops=$stock\n";

// Confirmar la transaccion principal (el deposito se guarda)
$tx->commit();

echo "\nResultado final:\n";
$saldo = $db->query("SELECT saldo FROM cuentas WHERE id = 1")->fetchColumn();
$stock = $db->query("SELECT stock FROM productos_inventario WHERE id = 1")->fetchColumn();
echo "  Saldo cuenta 1: \$" . number_format($saldo, 2) . " (deposito guardado)\n";
echo "  Stock laptops: $stock (rollback del savepoint)\n";

echo "\n";

// ============================================================
// Ejemplo 4: Transferencia de dinero completa
// ============================================================
// Ejemplo realista con todas las validaciones necesarias

echo "=== Ejemplo 4: Transferencia de dinero completa ===\n\n";

/**
 * Servicio de transferencias bancarias
 * Implementa la logica de negocio con transacciones
 */
class ServicioTransferencia
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Realizar una transferencia entre dos cuentas
     *
     * @param int   $idOrigen   ID de la cuenta origen
     * @param int   $idDestino  ID de la cuenta destino
     * @param float $monto      Monto a transferir
     * @param string $descripcion Descripcion del movimiento
     * @return array Resultado de la operacion
     * @throws RuntimeException Si la transferencia no es valida
     */
    public function transferir(
        int $idOrigen,
        int $idDestino,
        float $monto,
        string $descripcion = ''
    ): array {
        // Validaciones previas (sin transaccion)
        if ($monto <= 0) {
            throw new InvalidArgumentException('El monto debe ser positivo');
        }

        if ($idOrigen === $idDestino) {
            throw new InvalidArgumentException('Origen y destino deben ser diferentes');
        }

        $this->db->beginTransaction();

        try {
            // 1. Verificar que ambas cuentas existen y estan activas
            $cuentaOrigen = $this->obtenerCuenta($idOrigen);
            $cuentaDestino = $this->obtenerCuenta($idDestino);

            if (!$cuentaOrigen) {
                throw new RuntimeException("Cuenta origen $idOrigen no existe");
            }
            if (!$cuentaDestino) {
                throw new RuntimeException("Cuenta destino $idDestino no existe");
            }
            if (!$cuentaOrigen['activa']) {
                throw new RuntimeException("Cuenta origen esta inactiva");
            }
            if (!$cuentaDestino['activa']) {
                throw new RuntimeException("Cuenta destino esta inactiva");
            }

            // 2. Verificar saldo suficiente
            if ($cuentaOrigen['saldo'] < $monto) {
                throw new RuntimeException(sprintf(
                    'Saldo insuficiente. Disponible: $%.2f, Requerido: $%.2f',
                    $cuentaOrigen['saldo'],
                    $monto
                ));
            }

            // 3. Aplicar la transferencia
            $this->db->prepare("UPDATE cuentas SET saldo = saldo - :monto WHERE id = :id")
                     ->execute([':monto' => $monto, ':id' => $idOrigen]);

            $this->db->prepare("UPDATE cuentas SET saldo = saldo + :monto WHERE id = :id")
                     ->execute([':monto' => $monto, ':id' => $idDestino]);

            // 4. Registrar el movimiento
            $this->db->prepare("
                INSERT INTO movimientos (cuenta_origen, cuenta_destino, monto, tipo, descripcion)
                VALUES (:origen, :destino, :monto, 'transferencia', :desc)
            ")->execute([
                ':origen'  => $idOrigen,
                ':destino' => $idDestino,
                ':monto'   => $monto,
                ':desc'    => $descripcion,
            ]);

            // 5. Confirmar todo
            $this->db->commit();

            // 6. Retornar resultado
            return [
                'exito'      => true,
                'mensaje'    => "Transferencia de \$$monto completada",
                'origen'     => $cuentaOrigen['titular'],
                'destino'    => $cuentaDestino['titular'],
                'monto'      => $monto,
                'movimiento' => $this->db->lastInsertId(),
            ];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            return [
                'exito'   => false,
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    private function obtenerCuenta(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cuentas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Obtener el historial de movimientos de una cuenta
     */
    public function historial(int $idCuenta): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   co.titular as titular_origen,
                   cd.titular as titular_destino
            FROM movimientos m
            LEFT JOIN cuentas co ON m.cuenta_origen = co.id
            LEFT JOIN cuentas cd ON m.cuenta_destino = cd.id
            WHERE m.cuenta_origen = :id OR m.cuenta_destino = :id
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([':id' => $idCuenta]);
        return $stmt->fetchAll();
    }
}

$servicio = new ServicioTransferencia($db);

// Transferencia exitosa
$resultado = $servicio->transferir(1, 3, 800.00, 'Pago de alquiler');
echo "Transferencia 1 -> 3 (\$800):\n";
echo "  Exito: " . ($resultado['exito'] ? 'SI' : 'NO') . "\n";
echo "  {$resultado['mensaje']}\n\n";

// Transferencia con saldo insuficiente
$resultado = $servicio->transferir(3, 1, 50000.00, 'Intento imposible');
echo "Transferencia 3 -> 1 (\$50000):\n";
echo "  Exito: " . ($resultado['exito'] ? 'SI' : 'NO') . "\n";
echo "  {$resultado['mensaje']}\n\n";

// Transferencia a cuenta inexistente
$resultado = $servicio->transferir(1, 999, 100.00, 'Cuenta fantasma');
echo "Transferencia 1 -> 999:\n";
echo "  Exito: " . ($resultado['exito'] ? 'SI' : 'NO') . "\n";
echo "  {$resultado['mensaje']}\n\n";

// Saldos finales
echo "Saldos finales:\n";
$saldos = $db->query("SELECT titular, saldo FROM cuentas ORDER BY id")->fetchAll();
foreach ($saldos as $c) {
    echo "  {$c['titular']}: \$" . number_format($c['saldo'], 2) . "\n";
}

// Historial
echo "\nHistorial de cuenta 1 (Ana):\n";
$historial = $servicio->historial(1);
foreach ($historial as $mov) {
    echo "  [{$mov['fecha']}] {$mov['tipo']}: \${$mov['monto']} ";
    echo "({$mov['titular_origen']} -> {$mov['titular_destino']})\n";
}

echo "\n";

// ============================================================
// Ejemplo 5: Rendimiento - Batch insert con y sin transaccion
// ============================================================
// Las transacciones mejoran dramaticamente el rendimiento en inserciones masivas

echo "=== Ejemplo 5: Rendimiento de inserciones masivas ===\n\n";

// Crear tabla para la prueba
$db->exec("
    CREATE TABLE benchmark (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        dato TEXT NOT NULL,
        valor REAL NOT NULL
    )
");

$cantidadRegistros = 1000;

// --- Sin transaccion (cada INSERT es su propia transaccion implicita) ---
echo "Insertando $cantidadRegistros registros SIN transaccion explicita...\n";
$db->exec("DELETE FROM benchmark");

$stmt = $db->prepare("INSERT INTO benchmark (dato, valor) VALUES (:dato, :valor)");

$inicio = microtime(true);
for ($i = 0; $i < $cantidadRegistros; $i++) {
    $stmt->execute([
        ':dato'  => "registro_$i",
        ':valor' => $i * 1.5,
    ]);
}
$tiempoSinTx = microtime(true) - $inicio;

$count = $db->query("SELECT COUNT(*) FROM benchmark")->fetchColumn();
echo "  Tiempo: " . number_format($tiempoSinTx * 1000, 2) . " ms ($count registros)\n\n";

// --- Con transaccion (un solo commit al final) ---
echo "Insertando $cantidadRegistros registros CON transaccion...\n";
$db->exec("DELETE FROM benchmark");

$inicio = microtime(true);
$db->beginTransaction();
for ($i = 0; $i < $cantidadRegistros; $i++) {
    $stmt->execute([
        ':dato'  => "registro_$i",
        ':valor' => $i * 1.5,
    ]);
}
$db->commit();
$tiempoConTx = microtime(true) - $inicio;

$count = $db->query("SELECT COUNT(*) FROM benchmark")->fetchColumn();
echo "  Tiempo: " . number_format($tiempoConTx * 1000, 2) . " ms ($count registros)\n\n";

// --- Comparacion ---
if ($tiempoSinTx > 0 && $tiempoConTx > 0) {
    $mejora = $tiempoSinTx / $tiempoConTx;
    echo "Comparacion:\n";
    echo "  Sin transaccion: " . number_format($tiempoSinTx * 1000, 2) . " ms\n";
    echo "  Con transaccion: " . number_format($tiempoConTx * 1000, 2) . " ms\n";
    echo "  Mejora: " . number_format($mejora, 1) . "x mas rapido con transaccion\n\n";
}

// --- Batch insert con transaccion y rollback en caso de error ---
echo "--- Batch insert con control de errores ---\n";
$db->exec("DELETE FROM benchmark");

// Crear un indice unico para provocar un error a proposito
$db->exec("CREATE UNIQUE INDEX idx_benchmark_dato ON benchmark(dato)");

$datosLote = [];
for ($i = 0; $i < 100; $i++) {
    $datosLote[] = ["registro_$i", $i * 2.5];
}
// Agregar un duplicado para causar error
$datosLote[] = ["registro_50", 999.99];  // Duplicado!

$stmt = $db->prepare("INSERT INTO benchmark (dato, valor) VALUES (?, ?)");

$db->beginTransaction();
$insertados = 0;
try {
    foreach ($datosLote as $dato) {
        $stmt->execute($dato);
        $insertados++;
    }
    $db->commit();
    echo "  Todos los registros insertados correctamente\n";
} catch (PDOException $e) {
    $db->rollBack();
    echo "  Error en registro #$insertados: duplicado detectado\n";
    echo "  ROLLBACK: Todos los $insertados registros anteriores fueron revertidos\n";
}

$count = $db->query("SELECT COUNT(*) FROM benchmark")->fetchColumn();
echo "  Registros en tabla: $count (0 porque se hizo rollback)\n";

echo "\n=== Resumen de transacciones ===\n";
echo "1. Siempre usar transacciones para operaciones multiples relacionadas\n";
echo "2. El patron try/catch con rollback es OBLIGATORIO\n";
echo "3. Savepoints permiten rollback parcial dentro de una transaccion\n";
echo "4. Las transacciones mejoran dramaticamente el rendimiento en batch inserts\n";
echo "5. Verificar condiciones DENTRO de la transaccion (no antes)\n";
echo "6. En produccion, usar SELECT FOR UPDATE para evitar race conditions\n";
?>
