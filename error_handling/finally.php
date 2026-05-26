<?php
/**
 * finally - Bloque que SIEMPRE se ejecuta
 *
 * El bloque finally se ejecuta sin importar si hubo excepción o no,
 * incluso si hay un return en try o catch. Es esencial para la
 * limpieza de recursos (archivos, conexiones, locks, etc.).
 */

// ============================================
// Ejemplo 1: Comportamiento básico de finally
// ============================================

echo "=== Ejemplo 1: Comportamiento básico ===\n";

// Caso 1: Sin excepción - finally se ejecuta después del try
echo "Caso 1 - Sin excepción:\n";
try {
    echo "  try: Ejecutando código normal.\n";
} catch (Exception $e) {
    echo "  catch: " . $e->getMessage() . "\n";
} finally {
    echo "  finally: Siempre se ejecuta.\n";
}

// Caso 2: Con excepción - finally se ejecuta después del catch
echo "\nCaso 2 - Con excepción:\n";
try {
    echo "  try: Antes de la excepción.\n";
    throw new RuntimeException("Error intencional");
    echo "  try: Esto nunca se ejecuta.\n";
} catch (RuntimeException $e) {
    echo "  catch: " . $e->getMessage() . "\n";
} finally {
    echo "  finally: Se ejecuta aunque hubo excepción.\n";
}

// Caso 3: try/finally sin catch
echo "\nCaso 3 - try/finally sin catch:\n";
try {
    try {
        echo "  try interno: Lanzando excepción.\n";
        throw new Exception("Error sin catch directo");
    } finally {
        echo "  finally: Se ejecuta ANTES de propagar la excepción.\n";
    }
} catch (Exception $e) {
    echo "  catch externo: " . $e->getMessage() . "\n";
}

// ============================================
// Ejemplo 2: finally con return (comportamiento especial)
// ============================================

echo "\n=== Ejemplo 2: finally con return ===\n";

/**
 * IMPORTANTE: Si tanto try/catch como finally tienen return,
 * el return de finally SOBREESCRIBE al return de try/catch.
 */

function ejemploReturnSinExcepcion(): string
{
    try {
        return "Retorno desde try";
    } finally {
        // Este bloque se ejecuta ANTES de que el return de try se complete
        echo "  finally ejecutado (antes del return de try)\n";
    }
}

function ejemploReturnConExcepcion(): string
{
    try {
        throw new Exception("Error");
    } catch (Exception $e) {
        return "Retorno desde catch";
    } finally {
        echo "  finally ejecutado (antes del return de catch)\n";
    }
}

function ejemploReturnEnFinally(): string
{
    try {
        return "Retorno desde try";
    } finally {
        // CUIDADO: Este return sobreescribe al de try
        return "Retorno desde finally (sobreescribe)";
    }
}

echo "Sin excepción: " . ejemploReturnSinExcepcion() . "\n\n";
echo "Con excepción: " . ejemploReturnConExcepcion() . "\n\n";
echo "Return en finally: " . ejemploReturnEnFinally() . "\n";
echo "  (El return de try fue sobreescrito por finally)\n";

// ============================================
// Ejemplo 3: Limpieza de recursos con finally
// ============================================

echo "\n=== Ejemplo 3: Limpieza de recursos ===\n";

/**
 * Patrón fundamental: usar finally para garantizar que los
 * recursos se liberan sin importar qué ocurra.
 */

// Ejemplo con archivo temporal
function procesarArchivo(string $contenido): string
{
    $tmpFile = null;

    try {
        // Crear archivo temporal
        $tmpFile = tempnam(sys_get_temp_dir(), 'php_finally_');
        echo "  Archivo temporal creado: " . basename($tmpFile) . "\n";

        // Escribir contenido
        file_put_contents($tmpFile, $contenido);
        echo "  Contenido escrito: " . strlen($contenido) . " bytes\n";

        // Simular procesamiento
        $datos = file_get_contents($tmpFile);
        $resultado = strtoupper($datos);

        echo "  Procesamiento exitoso.\n";
        return $resultado;

    } catch (Exception $e) {
        echo "  Error durante procesamiento: " . $e->getMessage() . "\n";
        return '';

    } finally {
        // SIEMPRE limpiar el archivo temporal
        if ($tmpFile && file_exists($tmpFile)) {
            unlink($tmpFile);
            echo "  [finally] Archivo temporal eliminado.\n";
        }
    }
}

$resultado = procesarArchivo("Datos de prueba para procesar");
echo "  Resultado: $resultado\n";

// Ejemplo con conexión simulada
echo "\nSimulación de conexión a base de datos:\n";

class ConexionSimulada
{
    private bool $conectada = false;
    private string $nombre;

    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function conectar(): void
    {
        $this->conectada = true;
        echo "  [$this->nombre] Conexión abierta.\n";
    }

    public function consultar(string $sql): array
    {
        if (!$this->conectada) {
            throw new RuntimeException("No hay conexión activa.");
        }
        echo "  [$this->nombre] Ejecutando: $sql\n";

        if (str_contains($sql, 'ERROR')) {
            throw new RuntimeException("Error en la consulta SQL");
        }

        return ['resultado' => 'datos simulados'];
    }

    public function cerrar(): void
    {
        $this->conectada = false;
        echo "  [$this->nombre] Conexión cerrada.\n";
    }

    public function estaConectada(): bool
    {
        return $this->conectada;
    }
}

function ejecutarConsulta(string $sql): array
{
    $db = new ConexionSimulada('MySQL');

    try {
        $db->conectar();
        $resultado = $db->consultar($sql);
        return $resultado;

    } catch (RuntimeException $e) {
        echo "  Error: " . $e->getMessage() . "\n";
        return [];

    } finally {
        // Garantizar que la conexión se cierra
        if ($db->estaConectada()) {
            $db->cerrar();
        }
    }
}

echo "\nConsulta exitosa:\n";
ejecutarConsulta("SELECT * FROM users");

echo "\nConsulta con error:\n";
ejecutarConsulta("SELECT * FROM ERROR_TABLE");

// ============================================
// Ejemplo 4: Finally anidados
// ============================================

echo "\n=== Ejemplo 4: Finally anidados ===\n";

/**
 * Cuando hay bloques try/finally anidados, los finally se ejecutan
 * de dentro hacia afuera (el más interno primero).
 */
function ejemploAnidado(): void
{
    try {
        echo "  try externo: inicio\n";

        try {
            echo "    try interno: inicio\n";

            try {
                echo "      try más interno: lanzando excepción\n";
                throw new Exception("Error profundo");
            } finally {
                echo "      finally más interno\n";
            }

        } catch (Exception $e) {
            echo "    catch interno: " . $e->getMessage() . "\n";
        } finally {
            echo "    finally interno\n";
        }

        echo "  try externo: continuando\n";

    } finally {
        echo "  finally externo\n";
    }
}

ejemploAnidado();

// ============================================
// Ejemplo 5: Caso práctico - Patrón de transacción con finally
// ============================================

echo "\n=== Ejemplo 5: Patrón de transacción ===\n";

/**
 * Simula el patrón de transacción donde:
 * - Se inicia una transacción
 * - Se realizan operaciones
 * - Si todo va bien: commit
 * - Si algo falla: rollback
 * - finally: liberar lock o cerrar conexión
 */
class TransaccionSimulada
{
    private bool $activa = false;
    private array $operaciones = [];
    private array $log = [];

    public function iniciar(): void
    {
        $this->activa = true;
        $this->operaciones = [];
        $this->registrar("Transacción iniciada");
    }

    public function ejecutar(string $operacion, bool $forzarError = false): void
    {
        if (!$this->activa) {
            throw new LogicException("No hay transacción activa.");
        }
        if ($forzarError) {
            throw new RuntimeException("Error en operación: $operacion");
        }
        $this->operaciones[] = $operacion;
        $this->registrar("Operación ejecutada: $operacion");
    }

    public function commit(): void
    {
        $this->registrar("COMMIT - " . count($this->operaciones) . " operaciones confirmadas");
        $this->activa = false;
    }

    public function rollback(): void
    {
        $this->registrar("ROLLBACK - " . count($this->operaciones) . " operaciones revertidas");
        $this->operaciones = [];
        $this->activa = false;
    }

    public function estaActiva(): bool
    {
        return $this->activa;
    }

    private function registrar(string $msg): void
    {
        $this->log[] = $msg;
        echo "    [TX] $msg\n";
    }

    public function obtenerLog(): array
    {
        return $this->log;
    }
}

function realizarTransferencia(TransaccionSimulada $tx, float $monto, bool $simularError = false): void
{
    $tx->iniciar();

    try {
        $tx->ejecutar("Debitar \$$monto de cuenta origen");
        $tx->ejecutar("Registrar movimiento de débito");

        // Aquí podría ocurrir un error
        $tx->ejecutar("Acreditar \$$monto en cuenta destino", $simularError);
        $tx->ejecutar("Registrar movimiento de crédito");

        // Todo salió bien: confirmar
        $tx->commit();

    } catch (RuntimeException $e) {
        echo "    [ERROR] " . $e->getMessage() . "\n";

        // Algo falló: revertir
        $tx->rollback();

    } finally {
        // Siempre verificar y limpiar
        if ($tx->estaActiva()) {
            echo "    [finally] Transacción aún activa, forzando rollback.\n";
            $tx->rollback();
        }
        echo "    [finally] Recursos de transacción liberados.\n";
    }
}

// Transferencia exitosa
echo "  Transferencia exitosa:\n";
$tx = new TransaccionSimulada();
realizarTransferencia($tx, 500.00, false);

echo "\n  Transferencia con error:\n";
$tx2 = new TransaccionSimulada();
realizarTransferencia($tx2, 300.00, true);

?>
