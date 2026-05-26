<?php
/**
 * set_error_handler() - Manejador de errores personalizado
 *
 * Permite definir una función personalizada para manejar errores de PHP.
 * Esto da control total sobre cómo se reportan y registran los errores.
 */

// ============================================
// Ejemplo 1: Manejador básico de errores
// ============================================

echo "=== Ejemplo 1: Manejador básico ===\n";

/**
 * Función manejadora que recibe los detalles del error.
 * Parámetros: nivel de error, mensaje, archivo, línea.
 */
function manejadorBasico(int $nivel, string $mensaje, string $archivo, int $linea): bool
{
    $nombreNivel = match ($nivel) {
        E_WARNING     => 'WARNING',
        E_NOTICE      => 'NOTICE',
        E_USER_ERROR  => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT      => 'STRICT',
        E_DEPRECATED  => 'DEPRECATED',
        default       => "NIVEL_$nivel",
    };

    echo "  [$nombreNivel] $mensaje\n";
    echo "  Archivo: " . basename($archivo) . " | Línea: $linea\n\n";

    // Retornar true para evitar que PHP ejecute su manejador interno
    // Retornar false para que PHP siga con su manejo normal
    return true;
}

// Registrar el manejador personalizado
set_error_handler('manejadorBasico');

// Provocar diferentes tipos de errores
trigger_error("Este es un aviso del usuario", E_USER_NOTICE);
trigger_error("Esta es una advertencia del usuario", E_USER_WARNING);

// Restaurar el manejador original de PHP
restore_error_handler();

echo "Manejador restaurado al original de PHP.\n";

// ============================================
// Ejemplo 2: Niveles de error (E_WARNING, E_NOTICE, etc.)
// ============================================

echo "\n=== Ejemplo 2: Filtrar por niveles de error ===\n";

/**
 * El segundo parámetro de set_error_handler especifica qué niveles
 * de error captura. Se pueden combinar con el operador |.
 */

// Solo capturar advertencias (E_WARNING y E_USER_WARNING)
$contadorWarnings = 0;

set_error_handler(function (int $nivel, string $mensaje) use (&$contadorWarnings): bool {
    $contadorWarnings++;
    echo "  [WARNING #$contadorWarnings] $mensaje\n";
    return true;
}, E_WARNING | E_USER_WARNING);

// Esto SÍ será capturado (es un warning)
trigger_error("Advertencia capturada", E_USER_WARNING);

// Esto SÍ será capturado (warning interno de PHP)
// Intentar acceder una clave inexistente en modo que genere warning
$fp = @fopen('/archivo/que/no/existe/nunca.txt', 'r'); // @ suprime el error por defecto

echo "Total warnings capturados: $contadorWarnings\n";

restore_error_handler();

// Mostrar todos los niveles de error disponibles
echo "\nConstantes de niveles de error:\n";
$niveles = [
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
    'E_ALL'               => E_ALL,
];

foreach ($niveles as $nombre => $valor) {
    echo "  $nombre = $valor\n";
}

// ============================================
// Ejemplo 3: Convertir errores a excepciones (ErrorException)
// ============================================

echo "\n=== Ejemplo 3: Convertir errores a excepciones ===\n";

/**
 * Patrón muy común: convertir errores de PHP a excepciones
 * para manejarlos de forma uniforme con try/catch.
 * Se usa la clase ErrorException de PHP.
 */
function erroresComoExcepciones(int $nivel, string $mensaje, string $archivo, int $linea): bool
{
    // No convertir errores suprimidos con @
    if (!(error_reporting() & $nivel)) {
        return false;
    }

    throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
}

set_error_handler('erroresComoExcepciones');

// Ahora los errores se pueden capturar con try/catch
echo "Intentando operaciones que normalmente generan warnings:\n";

// Ejemplo: División que genera warning
try {
    $resultado = 1 / 0; // Warning en PHP 8+: DivisionByZeroError
    echo "Resultado: $resultado\n";
} catch (ErrorException $e) {
    echo "  ErrorException capturada: " . $e->getMessage() . "\n";
    echo "  Severidad: " . $e->getSeverity() . "\n";
} catch (DivisionByZeroError $e) {
    echo "  DivisionByZeroError: " . $e->getMessage() . "\n";
}

// Ejemplo: trigger_error convertido a excepción
try {
    trigger_error("Error crítico del usuario", E_USER_ERROR);
} catch (ErrorException $e) {
    echo "  Error de usuario capturado como excepción: " . $e->getMessage() . "\n";
}

restore_error_handler();

// ============================================
// Ejemplo 4: Caso práctico - Logger de errores con niveles
// ============================================

echo "\n=== Ejemplo 4: Logger de errores personalizado ===\n";

/**
 * Sistema de logging que registra errores con formato,
 * colores de consola y almacenamiento en memoria.
 */
class ErrorLogger
{
    private array $registros = [];
    private bool $mostrarEnConsola;

    public function __construct(bool $mostrarEnConsola = true)
    {
        $this->mostrarEnConsola = $mostrarEnConsola;
    }

    /**
     * Manejador de errores para usar con set_error_handler
     */
    public function manejarError(int $nivel, string $mensaje, string $archivo, int $linea): bool
    {
        $registro = [
            'timestamp' => date('Y-m-d H:i:s'),
            'nivel'     => $this->nombreNivel($nivel),
            'mensaje'   => $mensaje,
            'archivo'   => basename($archivo),
            'linea'     => $linea,
        ];

        $this->registros[] = $registro;

        if ($this->mostrarEnConsola) {
            $icono = $this->iconoNivel($nivel);
            echo "  $icono [{$registro['nivel']}] {$registro['timestamp']} - $mensaje\n";
            echo "    en {$registro['archivo']}:{$linea}\n";
        }

        return true;
    }

    private function nombreNivel(int $nivel): string
    {
        return match ($nivel) {
            E_WARNING, E_USER_WARNING        => 'WARNING',
            E_NOTICE, E_USER_NOTICE          => 'NOTICE',
            E_USER_ERROR                     => 'ERROR',
            E_DEPRECATED, E_USER_DEPRECATED  => 'DEPRECATED',
            E_STRICT                         => 'STRICT',
            default                          => 'UNKNOWN',
        };
    }

    private function iconoNivel(int $nivel): string
    {
        return match ($nivel) {
            E_USER_ERROR                     => '[!!]',
            E_WARNING, E_USER_WARNING        => '[!]',
            E_NOTICE, E_USER_NOTICE          => '[i]',
            E_DEPRECATED, E_USER_DEPRECATED  => '[D]',
            default                          => '[?]',
        };
    }

    public function obtenerRegistros(): array
    {
        return $this->registros;
    }

    public function resumen(): void
    {
        $conteo = array_count_values(array_column($this->registros, 'nivel'));
        echo "\n  Resumen de errores:\n";
        foreach ($conteo as $nivel => $cantidad) {
            echo "    $nivel: $cantidad\n";
        }
        echo "    Total: " . count($this->registros) . "\n";
    }
}

$logger = new ErrorLogger();

// Registrar el método del logger como manejador
set_error_handler([$logger, 'manejarError']);

// Generar varios errores
trigger_error("Configuración obsoleta detectada", E_USER_DEPRECATED);
trigger_error("Variable sin inicializar", E_USER_NOTICE);
trigger_error("Memoria disponible baja", E_USER_WARNING);
trigger_error("Archivo de cache corrupto", E_USER_WARNING);
trigger_error("Función legacy utilizada", E_USER_DEPRECATED);
trigger_error("Fallo al guardar en disco", E_USER_ERROR);

// Mostrar resumen
$logger->resumen();

restore_error_handler();

// ============================================
// Ejemplo 5: Apilar manejadores de errores
// ============================================

echo "\n=== Ejemplo 5: Apilar manejadores ===\n";

/**
 * set_error_handler devuelve el manejador anterior,
 * lo que permite apilar manejadores y restaurarlos.
 */

// Primer manejador
$manejador1 = set_error_handler(function ($nivel, $msg) {
    echo "  [Manejador 1] $msg\n";
    return true;
});
echo "Manejador anterior era: " . ($manejador1 === null ? 'el interno de PHP' : 'personalizado') . "\n";

trigger_error("Prueba con manejador 1", E_USER_NOTICE);

// Segundo manejador (apila sobre el primero)
$manejador2 = set_error_handler(function ($nivel, $msg) {
    echo "  [Manejador 2] $msg\n";
    return true;
});

trigger_error("Prueba con manejador 2", E_USER_NOTICE);

// Restaurar al manejador 1
restore_error_handler();
trigger_error("De vuelta al manejador 1", E_USER_NOTICE);

// Restaurar al manejador interno de PHP
restore_error_handler();
echo "\nManejadores restaurados al original.\n";

?>
