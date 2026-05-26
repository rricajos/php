<?php
/**
 * try/catch/finally - Manejo estructurado de excepciones
 *
 * El bloque try/catch permite capturar excepciones que ocurren
 * durante la ejecución del código y manejarlas de forma controlada.
 */

// ============================================
// Ejemplo 1: try/catch básico
// ============================================

echo "=== Ejemplo 1: try/catch básico ===\n";

// Capturar una excepción genérica
try {
    echo "Intentando dividir 10 / 0...\n";
    $resultado = intdiv(10, 0); // intdiv lanza DivisionByZeroError
    echo "Este código no se ejecuta.\n";
} catch (DivisionByZeroError $e) {
    echo "Error capturado: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "El programa continúa después del catch.\n";

// Capturar Exception genérica
try {
    $archivo = 'archivo_inexistente.txt';
    if (!file_exists($archivo)) {
        throw new Exception("El archivo '$archivo' no existe.");
    }
} catch (Exception $e) {
    echo "\nExcepción: " . $e->getMessage() . "\n";
}

// ============================================
// Ejemplo 2: Múltiples bloques catch
// ============================================

echo "\n=== Ejemplo 2: Múltiples catch ===\n";

/**
 * Función que procesa datos y puede lanzar diferentes tipos de excepciones.
 */
function procesarDato(mixed $dato): string
{
    if (!is_string($dato) && !is_numeric($dato)) {
        throw new TypeError("Se esperaba string o número, se recibió: " . gettype($dato));
    }

    if (is_numeric($dato) && $dato < 0) {
        throw new RangeException("El valor numérico no puede ser negativo: $dato");
    }

    if (is_string($dato) && strlen($dato) === 0) {
        throw new InvalidArgumentException("La cadena no puede estar vacía.");
    }

    if (is_string($dato) && strlen($dato) > 100) {
        throw new LengthException("La cadena excede los 100 caracteres.");
    }

    return "Dato procesado: $dato";
}

// Probar diferentes casos
$casosPrueba = ['Hola mundo', -5, '', [1, 2, 3], 42];

foreach ($casosPrueba as $caso) {
    try {
        $resultado = procesarDato($caso);
        echo "OK: $resultado\n";
    } catch (TypeError $e) {
        echo "TypeError: " . $e->getMessage() . "\n";
    } catch (RangeException $e) {
        echo "RangeException: " . $e->getMessage() . "\n";
    } catch (InvalidArgumentException $e) {
        echo "InvalidArgumentException: " . $e->getMessage() . "\n";
    } catch (LengthException $e) {
        echo "LengthException: " . $e->getMessage() . "\n";
    }
}

// ============================================
// Ejemplo 3: Catch con unión de tipos (PHP 8+)
// ============================================

echo "\n=== Ejemplo 3: Catch con unión de tipos (PHP 8+) ===\n";

/**
 * A partir de PHP 8, se puede capturar múltiples tipos de excepción
 * en un solo bloque catch usando el operador |
 */
function realizarOperacion(string $tipo): void
{
    match ($tipo) {
        'overflow'  => throw new OverflowException("Desbordamiento de datos"),
        'underflow' => throw new UnderflowException("Datos insuficientes"),
        'rango'     => throw new RangeException("Valor fuera de rango"),
        'runtime'   => throw new RuntimeException("Error en tiempo de ejecución"),
        default     => throw new InvalidArgumentException("Tipo desconocido: $tipo"),
    };
}

$operaciones = ['overflow', 'underflow', 'rango', 'runtime', 'desconocido'];

foreach ($operaciones as $op) {
    try {
        realizarOperacion($op);
    } catch (OverflowException | UnderflowException $e) {
        // Capturar overflow o underflow juntos
        echo "[Flujo de datos] " . get_class($e) . ": " . $e->getMessage() . "\n";
    } catch (RangeException | RuntimeException $e) {
        // Capturar rango o runtime juntos
        echo "[Error operacional] " . get_class($e) . ": " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        // Capturar cualquier otra excepción
        echo "[General] " . get_class($e) . ": " . $e->getMessage() . "\n";
    }
}

// ============================================
// Ejemplo 4: try/catch/finally
// ============================================

echo "\n=== Ejemplo 4: try/catch/finally ===\n";

/**
 * El bloque finally SIEMPRE se ejecuta, sin importar si hubo
 * excepción o no. Es ideal para limpieza de recursos.
 */
function leerArchivoSeguro(string $ruta): string
{
    $archivo = null;
    try {
        echo "  Abriendo archivo: $ruta\n";

        if (!file_exists($ruta)) {
            throw new RuntimeException("Archivo no encontrado: $ruta");
        }

        $archivo = fopen($ruta, 'r');
        if ($archivo === false) {
            throw new RuntimeException("No se pudo abrir: $ruta");
        }

        $contenido = fread($archivo, filesize($ruta));
        echo "  Archivo leído correctamente.\n";
        return $contenido;

    } catch (RuntimeException $e) {
        echo "  Error: " . $e->getMessage() . "\n";
        return '';

    } finally {
        // Este bloque SIEMPRE se ejecuta
        if ($archivo !== null && is_resource($archivo)) {
            fclose($archivo);
            echo "  [finally] Archivo cerrado.\n";
        } else {
            echo "  [finally] No hay archivo que cerrar.\n";
        }
    }
}

// Probar con archivo inexistente
$contenido = leerArchivoSeguro('/tmp/no_existe.txt');

// Probar con archivo que sí existe (si hay alguno disponible)
echo "\n";
$archivoTemp = tempnam(sys_get_temp_dir(), 'php_test_');
file_put_contents($archivoTemp, 'Contenido de prueba');
$contenido = leerArchivoSeguro($archivoTemp);
echo "  Contenido: $contenido\n";
unlink($archivoTemp);

// ============================================
// Ejemplo 5: Caso práctico - Validación de formulario con excepciones
// ============================================

echo "\n=== Ejemplo 5: Validación de formulario ===\n";

/**
 * Clase de excepción para errores de validación que acumula
 * múltiples errores antes de lanzar.
 */
class ExcepcionValidacion extends Exception
{
    private array $errores = [];

    public function agregarError(string $campo, string $mensaje): void
    {
        $this->errores[$campo][] = $mensaje;
    }

    public function tieneErrores(): bool
    {
        return !empty($this->errores);
    }

    public function obtenerErrores(): array
    {
        return $this->errores;
    }
}

function validarRegistro(array $datos): array
{
    $validacion = new ExcepcionValidacion("Error de validación en el formulario.");

    // Validar nombre
    if (empty($datos['nombre'] ?? '')) {
        $validacion->agregarError('nombre', 'El nombre es obligatorio.');
    } elseif (strlen($datos['nombre']) < 2) {
        $validacion->agregarError('nombre', 'El nombre debe tener al menos 2 caracteres.');
    }

    // Validar email
    if (empty($datos['email'] ?? '')) {
        $validacion->agregarError('email', 'El email es obligatorio.');
    } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $validacion->agregarError('email', 'El email no es válido.');
    }

    // Validar edad
    if (!isset($datos['edad'])) {
        $validacion->agregarError('edad', 'La edad es obligatoria.');
    } elseif (!is_numeric($datos['edad']) || $datos['edad'] < 18 || $datos['edad'] > 120) {
        $validacion->agregarError('edad', 'La edad debe ser un número entre 18 y 120.');
    }

    // Si hay errores, lanzar la excepción
    if ($validacion->tieneErrores()) {
        throw $validacion;
    }

    return ['status' => 'ok', 'datos' => $datos];
}

// Caso 1: Datos inválidos
$datosInvalidos = [
    'nombre' => '',
    'email'  => 'correo-invalido',
    'edad'   => 15,
];

try {
    $resultado = validarRegistro($datosInvalidos);
    echo "Registro exitoso.\n";
} catch (ExcepcionValidacion $e) {
    echo "Errores de validación:\n";
    foreach ($e->obtenerErrores() as $campo => $errores) {
        foreach ($errores as $error) {
            echo "  [$campo] $error\n";
        }
    }
}

// Caso 2: Datos válidos
echo "\n";
$datosValidos = [
    'nombre' => 'María García',
    'email'  => 'maria@ejemplo.com',
    'edad'   => 30,
];

try {
    $resultado = validarRegistro($datosValidos);
    echo "Registro exitoso: " . json_encode($resultado['datos']) . "\n";
} catch (ExcepcionValidacion $e) {
    echo "Errores: " . json_encode($e->obtenerErrores()) . "\n";
}

?>
