<?php
/**
 * set_exception_handler() - Manejador global de excepciones no capturadas
 *
 * Define una función que se ejecuta cuando una excepción no es capturada
 * por ningún bloque try/catch. Es la última línea de defensa antes
 * de que PHP termine el script.
 */

// ============================================
// Ejemplo 1: Manejador global básico
// ============================================

echo "=== Ejemplo 1: Manejador global básico ===\n";

/**
 * Función manejadora para excepciones no capturadas.
 * Recibe la excepción como único parámetro.
 */
function manejadorGlobalBasico(Throwable $excepcion): void
{
    echo "  [EXCEPCION NO CAPTURADA]\n";
    echo "  Tipo: " . get_class($excepcion) . "\n";
    echo "  Mensaje: " . $excepcion->getMessage() . "\n";
    echo "  Archivo: " . basename($excepcion->getFile()) . "\n";
    echo "  Línea: " . $excepcion->getLine() . "\n";
    echo "  Código: " . $excepcion->getCode() . "\n";
}

// Registrar el manejador
$anterior = set_exception_handler('manejadorGlobalBasico');

echo "Manejador anterior: " . ($anterior === null ? 'ninguno' : 'personalizado') . "\n";

// En un script real, esta excepción no capturada invocaría al manejador.
// Para demostración, la invocamos manualmente:
echo "\nSimulando excepción no capturada:\n";
manejadorGlobalBasico(new RuntimeException("Error inesperado del sistema", 500));

// Restaurar al comportamiento original
restore_exception_handler();

// ============================================
// Ejemplo 2: Manejador con formato de respuesta (HTML/JSON)
// ============================================

echo "\n=== Ejemplo 2: Manejador con formato de respuesta ===\n";

/**
 * Manejador que detecta si la petición espera JSON o HTML
 * y formatea la respuesta de error acorde.
 */
class ManejadorExcepciones
{
    private bool $modoDebug;
    private string $formato;

    public function __construct(bool $modoDebug = false, string $formato = 'text')
    {
        $this->modoDebug = $modoDebug;
        $this->formato = $formato;
    }

    public function manejar(Throwable $e): void
    {
        $datos = $this->prepararDatos($e);

        match ($this->formato) {
            'json' => $this->responderJson($datos),
            'html' => $this->responderHtml($datos),
            default => $this->responderTexto($datos),
        };
    }

    private function prepararDatos(Throwable $e): array
    {
        $datos = [
            'error'   => true,
            'tipo'    => get_class($e),
            'mensaje' => $e->getMessage(),
            'codigo'  => $e->getCode(),
        ];

        // Solo incluir detalles técnicos en modo debug
        if ($this->modoDebug) {
            $datos['debug'] = [
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'traza'   => $e->getTraceAsString(),
            ];

            // Incluir excepción anterior si existe
            if ($e->getPrevious()) {
                $datos['debug']['anterior'] = [
                    'tipo'    => get_class($e->getPrevious()),
                    'mensaje' => $e->getPrevious()->getMessage(),
                ];
            }
        }

        return $datos;
    }

    private function responderJson(array $datos): void
    {
        echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private function responderHtml(array $datos): void
    {
        echo "<div style='border:1px solid red; padding:10px;'>\n";
        echo "  <h3>Error: {$datos['mensaje']}</h3>\n";
        echo "  <p>Tipo: {$datos['tipo']}</p>\n";
        if (isset($datos['debug'])) {
            echo "  <pre>{$datos['debug']['traza']}</pre>\n";
        }
        echo "</div>\n";
    }

    private function responderTexto(array $datos): void
    {
        echo "  ERROR [{$datos['tipo']}]: {$datos['mensaje']}\n";
        if (isset($datos['debug'])) {
            echo "  Archivo: {$datos['debug']['archivo']}:{$datos['debug']['linea']}\n";
        }
    }
}

// Modo producción (JSON, sin debug)
echo "Modo producción (JSON):\n";
$manejadorProd = new ManejadorExcepciones(false, 'json');
$manejadorProd->manejar(new InvalidArgumentException("Parámetro 'email' inválido", 400));

// Modo desarrollo (texto, con debug)
echo "\nModo desarrollo (texto con debug):\n";
$manejadorDev = new ManejadorExcepciones(true, 'text');
$manejadorDev->manejar(new RuntimeException("Fallo en el servicio externo", 503));

// ============================================
// Ejemplo 3: Manejador con logging y notificaciones
// ============================================

echo "\n=== Ejemplo 3: Manejador con logging ===\n";

/**
 * Manejador que registra la excepción en un log, puede enviar
 * notificaciones y ejecuta acciones de limpieza.
 */
class ManejadorConLog
{
    private array $log = [];
    private array $callbacks = [];

    /**
     * Registrar un callback que se ejecuta cuando hay una excepción
     */
    public function alOcurrirError(callable $callback): void
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Manejador principal para set_exception_handler
     */
    public function manejar(Throwable $e): void
    {
        // Registrar en el log
        $entrada = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tipo'      => get_class($e),
            'mensaje'   => $e->getMessage(),
            'archivo'   => basename($e->getFile()),
            'linea'     => $e->getLine(),
        ];
        $this->log[] = $entrada;

        echo "  Registrado en log: [{$entrada['timestamp']}] {$entrada['tipo']}: {$entrada['mensaje']}\n";

        // Ejecutar todos los callbacks registrados
        foreach ($this->callbacks as $callback) {
            $callback($e);
        }
    }

    public function obtenerLog(): array
    {
        return $this->log;
    }
}

$manejadorLog = new ManejadorConLog();

// Registrar callbacks de notificación
$manejadorLog->alOcurrirError(function (Throwable $e) {
    echo "  [Notificación] Se enviaría email al admin sobre: " . $e->getMessage() . "\n";
});

$manejadorLog->alOcurrirError(function (Throwable $e) {
    echo "  [Métricas] Incrementar contador de errores tipo: " . get_class($e) . "\n";
});

// Simular excepciones no capturadas
$manejadorLog->manejar(new PDOException("SQLSTATE[42S02]: Table not found"));
echo "\n";
$manejadorLog->manejar(new RuntimeException("Timeout al conectar con API"));

echo "\nTotal de errores en log: " . count($manejadorLog->obtenerLog()) . "\n";

// ============================================
// Ejemplo 4: Registrar y restaurar manejadores
// ============================================

echo "\n=== Ejemplo 4: Registrar y restaurar manejadores ===\n";

/**
 * Demostrar cómo apilar y restaurar manejadores de excepciones.
 * Útil para cambiar temporalmente el manejo en secciones críticas.
 */

// Manejador principal
set_exception_handler(function (Throwable $e) {
    echo "  [Manejador Principal] " . $e->getMessage() . "\n";
});

echo "Manejador principal registrado.\n";

// Guardar referencia al manejador actual y registrar uno temporal
$manejadorTemporal = set_exception_handler(function (Throwable $e) {
    echo "  [Manejador Temporal] " . $e->getMessage() . "\n";
});

echo "Manejador temporal registrado.\n";
echo "El manejador anterior era: " . ($manejadorTemporal !== null ? 'personalizado' : 'ninguno') . "\n";

// Restaurar el manejador anterior
restore_exception_handler();
echo "Manejador temporal removido, restaurado al principal.\n";

// Restaurar al manejador interno de PHP
restore_exception_handler();
echo "Restaurado al manejador interno de PHP.\n";

// ============================================
// Ejemplo 5: Caso práctico - Manejador para aplicación web
// ============================================

echo "\n=== Ejemplo 5: Manejador para aplicación web ===\n";

/**
 * Manejador completo para una aplicación web que:
 * - Registra el error
 * - Genera una respuesta apropiada
 * - Limpia recursos
 * - Termina el script de forma controlada
 */
class WebExceptionHandler
{
    private string $entorno; // 'development' o 'production'
    private string $logFile;

    public function __construct(string $entorno = 'production', string $logFile = '')
    {
        $this->entorno = $entorno;
        $this->logFile = $logFile;
    }

    /**
     * Registrar este manejador con PHP
     */
    public function registrar(): void
    {
        set_exception_handler([$this, 'manejar']);
        echo "  WebExceptionHandler registrado (entorno: {$this->entorno})\n";
    }

    public function manejar(Throwable $e): void
    {
        // Determinar código de estado HTTP
        $statusCode = $this->determinarStatus($e);
        $statusText = $this->textoStatus($statusCode);

        echo "\n  === Respuesta de Error ===\n";
        echo "  HTTP/1.1 $statusCode $statusText\n";

        if ($this->entorno === 'development') {
            // En desarrollo: mostrar toda la información
            echo "  Tipo: " . get_class($e) . "\n";
            echo "  Mensaje: " . $e->getMessage() . "\n";
            echo "  Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "  Traza:\n";
            foreach (explode("\n", $e->getTraceAsString()) as $linea) {
                echo "    $linea\n";
            }
        } else {
            // En producción: mensaje genérico
            echo "  Mensaje: $statusText\n";
            echo "  ID de error: " . uniqid('err_') . "\n";
        }

        // Registrar en archivo de log
        if ($this->logFile) {
            $logEntry = sprintf(
                "[%s] %s: %s en %s:%d\n",
                date('Y-m-d H:i:s'),
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            echo "  [Log guardado en: {$this->logFile}]\n";
        }
    }

    private function determinarStatus(Throwable $e): int
    {
        if (method_exists($e, 'obtenerStatusCode')) {
            return $e->obtenerStatusCode();
        }

        return match (true) {
            $e instanceof InvalidArgumentException => 400,
            $e instanceof DomainException          => 422,
            $e instanceof RuntimeException         => 500,
            default                                => 500,
        };
    }

    private function textoStatus(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }
}

// Modo desarrollo
$handler = new WebExceptionHandler('development', '/var/log/app/errors.log');
$handler->registrar();

echo "\nSimulando excepción no capturada en desarrollo:\n";
$handler->manejar(new RuntimeException("Conexión a Redis fallida"));

// Modo producción
echo "\n";
$handlerProd = new WebExceptionHandler('production', '/var/log/app/errors.log');
echo "Simulando excepción no capturada en producción:\n";
$handlerProd->manejar(new RuntimeException("Conexión a Redis fallida"));

// Restaurar manejador original
restore_exception_handler();

?>
