<?php
/**
 * error_reporting() - Configuración del nivel de reporte de errores
 *
 * Controla qué tipos de errores PHP reporta. Se puede configurar
 * en php.ini, .htaccess o en tiempo de ejecución con error_reporting().
 * Trabaja junto con display_errors y log_errors.
 */

// ============================================
// Ejemplo 1: error_reporting() - Obtener y establecer niveles
// ============================================

echo "=== Ejemplo 1: Niveles de reporte ===\n";

// Obtener el nivel actual de reporte
$nivelActual = error_reporting();
echo "Nivel actual de error_reporting: $nivelActual\n";
echo "¿E_ALL? " . ($nivelActual === E_ALL ? 'Sí' : 'No') . "\n\n";

// Reportar TODOS los errores (recomendado en desarrollo)
error_reporting(E_ALL);
echo "E_ALL = " . E_ALL . " (todos los errores)\n";

// Reportar todos excepto notices
error_reporting(E_ALL & ~E_NOTICE);
echo "E_ALL & ~E_NOTICE = " . (E_ALL & ~E_NOTICE) . "\n";

// Reportar todos excepto notices y deprecados
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
echo "E_ALL & ~E_NOTICE & ~E_DEPRECATED = " . (E_ALL & ~E_NOTICE & ~E_DEPRECATED) . "\n";

// Solo errores fatales y warnings
error_reporting(E_ERROR | E_WARNING);
echo "E_ERROR | E_WARNING = " . (E_ERROR | E_WARNING) . "\n";

// Desactivar todo el reporte de errores
error_reporting(0);
echo "error_reporting(0) = sin reporte\n";

// Restaurar E_ALL para el resto del script
error_reporting(E_ALL);
echo "\nRestaurado a E_ALL para los siguientes ejemplos.\n";

// ============================================
// Ejemplo 2: display_errors y log_errors (ini_set)
// ============================================

echo "\n=== Ejemplo 2: display_errors y log_errors ===\n";

// Obtener valores actuales
echo "Valores actuales de configuración:\n";
echo "  display_errors: " . ini_get('display_errors') . "\n";
echo "  display_startup_errors: " . ini_get('display_startup_errors') . "\n";
echo "  log_errors: " . ini_get('log_errors') . "\n";
echo "  error_log: " . (ini_get('error_log') ?: '(por defecto del servidor)') . "\n";
echo "  log_errors_max_len: " . ini_get('log_errors_max_len') . "\n";

/**
 * Configuración recomendada para DESARROLLO:
 * - display_errors = On (mostrar errores en pantalla)
 * - error_reporting = E_ALL (reportar todo)
 * - log_errors = On (también registrar en log)
 */
echo "\nConfiguración recomendada para DESARROLLO:\n";
echo "  ini_set('display_errors', '1');\n";
echo "  ini_set('display_startup_errors', '1');\n";
echo "  error_reporting(E_ALL);\n";

/**
 * Configuración recomendada para PRODUCCION:
 * - display_errors = Off (NO mostrar errores al usuario)
 * - error_reporting = E_ALL (reportar todo internamente)
 * - log_errors = On (registrar en archivo de log)
 * - error_log = /ruta/al/archivo.log
 */
echo "\nConfiguración recomendada para PRODUCCION:\n";
echo "  ini_set('display_errors', '0');\n";
echo "  ini_set('log_errors', '1');\n";
echo "  ini_set('error_log', '/var/log/php/app-errors.log');\n";
echo "  error_reporting(E_ALL);\n";

// Demostrar cambio en tiempo de ejecución
ini_set('display_errors', '1');
echo "\ndisplay_errors ahora: " . ini_get('display_errors') . "\n";

// ============================================
// Ejemplo 3: Operador de control de errores (@)
// ============================================

echo "\n=== Ejemplo 3: Operador @ (supresión de errores) ===\n";

/**
 * El operador @ suprime la salida de errores para una expresión.
 * NO es recomendable usarlo frecuentemente ya que oculta problemas.
 * Sin embargo, hay casos legítimos.
 */

// Sin @: genera un warning si el archivo no existe
echo "Sin operador @:\n";
$manejadorAnterior = set_error_handler(function ($nivel, $msg) {
    echo "  Error: $msg\n";
    return true;
});

$contenido = file_get_contents('/archivo/que/no/existe.txt');
restore_error_handler();

// Con @: suprime el warning
echo "\nCon operador @:\n";
$contenido = @file_get_contents('/archivo/que/no/existe.txt');
echo "  Resultado: " . ($contenido === false ? 'false (sin error visible)' : $contenido) . "\n";

// Alternativa recomendada: verificar antes de actuar
echo "\nAlternativa recomendada (verificar antes):\n";
$ruta = '/archivo/que/no/existe.txt';
if (file_exists($ruta) && is_readable($ruta)) {
    $contenido = file_get_contents($ruta);
} else {
    echo "  El archivo no existe o no es legible: $ruta\n";
}

// ============================================
// Ejemplo 4: Caso práctico - Clase de configuración de errores por entorno
// ============================================

echo "\n=== Ejemplo 4: Configuración por entorno ===\n";

/**
 * Clase que configura el manejo de errores según el entorno.
 * Centraliza toda la configuración en un solo lugar.
 */
class ErrorConfig
{
    // Entornos soportados
    public const ENV_DEVELOPMENT = 'development';
    public const ENV_TESTING     = 'testing';
    public const ENV_STAGING     = 'staging';
    public const ENV_PRODUCTION  = 'production';

    private string $entorno;
    private string $logPath;
    private array $configuracion = [];

    public function __construct(string $entorno, string $logPath = '')
    {
        $this->entorno = $entorno;
        $this->logPath = $logPath;
        $this->configurarSegunEntorno();
    }

    private function configurarSegunEntorno(): void
    {
        $this->configuracion = match ($this->entorno) {
            self::ENV_DEVELOPMENT => [
                'error_reporting'        => E_ALL,
                'display_errors'         => '1',
                'display_startup_errors' => '1',
                'log_errors'             => '1',
                'html_errors'            => '1',
            ],
            self::ENV_TESTING => [
                'error_reporting'        => E_ALL,
                'display_errors'         => '0',
                'display_startup_errors' => '0',
                'log_errors'             => '1',
                'html_errors'            => '0',
            ],
            self::ENV_STAGING => [
                'error_reporting'        => E_ALL & ~E_DEPRECATED & ~E_STRICT,
                'display_errors'         => '0',
                'display_startup_errors' => '0',
                'log_errors'             => '1',
                'html_errors'            => '0',
            ],
            self::ENV_PRODUCTION => [
                'error_reporting'        => E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE,
                'display_errors'         => '0',
                'display_startup_errors' => '0',
                'log_errors'             => '1',
                'html_errors'            => '0',
            ],
            default => throw new InvalidArgumentException("Entorno desconocido: {$this->entorno}"),
        };
    }

    /**
     * Aplicar la configuración al sistema PHP
     */
    public function aplicar(): void
    {
        error_reporting($this->configuracion['error_reporting']);

        foreach ($this->configuracion as $clave => $valor) {
            if ($clave !== 'error_reporting') {
                ini_set($clave, $valor);
            }
        }

        if ($this->logPath) {
            ini_set('error_log', $this->logPath);
        }
    }

    /**
     * Mostrar la configuración actual
     */
    public function mostrar(): void
    {
        echo "  Entorno: {$this->entorno}\n";
        foreach ($this->configuracion as $clave => $valor) {
            if ($clave === 'error_reporting') {
                echo "  $clave: $valor (" . $this->describirNivel($valor) . ")\n";
            } else {
                echo "  $clave: $valor\n";
            }
        }
        if ($this->logPath) {
            echo "  error_log: {$this->logPath}\n";
        }
    }

    private function describirNivel(int $nivel): string
    {
        if ($nivel === E_ALL) return 'E_ALL';
        if ($nivel === 0) return 'NINGUNO';

        $partes = [];
        if ($nivel & E_ERROR) $partes[] = 'E_ERROR';
        if ($nivel & E_WARNING) $partes[] = 'E_WARNING';
        if ($nivel & E_NOTICE) $partes[] = 'E_NOTICE';
        if (!($nivel & E_DEPRECATED)) $partes[] = '~E_DEPRECATED';

        return implode(' | ', $partes) . '...';
    }
}

// Mostrar configuración para cada entorno
$entornos = [
    ErrorConfig::ENV_DEVELOPMENT,
    ErrorConfig::ENV_TESTING,
    ErrorConfig::ENV_STAGING,
    ErrorConfig::ENV_PRODUCTION,
];

foreach ($entornos as $env) {
    $config = new ErrorConfig($env, "/var/log/php/$env.log");
    echo "\n";
    $config->mostrar();
}

// ============================================
// Ejemplo 5: Verificar qué niveles están activos
// ============================================

echo "\n=== Ejemplo 5: Verificar niveles activos ===\n";

/**
 * Función auxiliar para ver exactamente qué niveles de error
 * están habilitados en la configuración actual.
 */
function mostrarNivelesActivos(int $nivelReporte): void
{
    $todosLosNiveles = [
        'E_ERROR'             => E_ERROR,
        'E_WARNING'           => E_WARNING,
        'E_PARSE'             => E_PARSE,
        'E_NOTICE'            => E_NOTICE,
        'E_CORE_ERROR'        => E_CORE_ERROR,
        'E_CORE_WARNING'      => E_CORE_WARNING,
        'E_COMPILE_ERROR'     => E_COMPILE_ERROR,
        'E_COMPILE_WARNING'   => E_COMPILE_WARNING,
        'E_USER_ERROR'        => E_USER_ERROR,
        'E_USER_WARNING'      => E_USER_WARNING,
        'E_USER_NOTICE'       => E_USER_NOTICE,
        'E_STRICT'            => E_STRICT,
        'E_RECOVERABLE_ERROR' => E_RECOVERABLE_ERROR,
        'E_DEPRECATED'        => E_DEPRECATED,
        'E_USER_DEPRECATED'   => E_USER_DEPRECATED,
    ];

    echo "  Niveles activos (error_reporting = $nivelReporte):\n";
    foreach ($todosLosNiveles as $nombre => $valor) {
        $activo = ($nivelReporte & $valor) ? 'SI' : 'NO';
        echo "    $activo  $nombre ($valor)\n";
    }
}

// Verificar con E_ALL
error_reporting(E_ALL);
echo "Con E_ALL:\n";
mostrarNivelesActivos(error_reporting());

// Verificar con configuración típica de producción
echo "\nCon E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT:\n";
$nivelProd = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT;
mostrarNivelesActivos($nivelProd);

// Restaurar E_ALL
error_reporting(E_ALL);

?>
