<?php
/**
 * Excepciones personalizadas - Extender la clase Exception
 *
 * Crear excepciones propias permite manejar errores específicos
 * de la aplicación con mayor precisión y con propiedades adicionales.
 */

// ============================================
// Ejemplo 1: Excepción personalizada básica
// ============================================

echo "=== Ejemplo 1: Excepción personalizada básica ===\n";

/**
 * Excepción personalizada simple que extiende Exception.
 * Agrega un código de error de aplicación y un contexto adicional.
 */
class AppException extends Exception
{
    private string $codigoApp;

    public function __construct(
        string $mensaje,
        string $codigoApp = 'APP_ERROR',
        int $code = 0,
        ?\Throwable $anterior = null
    ) {
        $this->codigoApp = $codigoApp;
        parent::__construct($mensaje, $code, $anterior);
    }

    public function obtenerCodigoApp(): string
    {
        return $this->codigoApp;
    }

    /**
     * Representación legible de la excepción
     */
    public function __toString(): string
    {
        return "[{$this->codigoApp}] {$this->getMessage()} (código: {$this->getCode()})";
    }
}

try {
    throw new AppException(
        'Operación no permitida para este usuario',
        'PERM_DENIED',
        403
    );
} catch (AppException $e) {
    echo "Código de app: " . $e->obtenerCodigoApp() . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código HTTP: " . $e->getCode() . "\n";
    echo "toString: " . $e . "\n";
}

// ============================================
// Ejemplo 2: Jerarquía de excepciones
// ============================================

echo "\n=== Ejemplo 2: Jerarquía de excepciones ===\n";

/**
 * Excepción base de la aplicación
 */
class BaseAppException extends RuntimeException
{
    protected string $nivel = 'error';

    public function obtenerNivel(): string
    {
        return $this->nivel;
    }
}

/**
 * Excepciones de la capa de base de datos
 */
class DatabaseException extends BaseAppException
{
    private string $consulta;

    public function __construct(string $mensaje, string $consulta = '', int $code = 0, ?\Throwable $prev = null)
    {
        $this->consulta = $consulta;
        parent::__construct($mensaje, $code, $prev);
    }

    public function obtenerConsulta(): string
    {
        return $this->consulta;
    }
}

class ConexionDBException extends DatabaseException
{
    protected string $nivel = 'critical';
}

class ConsultaDBException extends DatabaseException
{
    protected string $nivel = 'error';
}

/**
 * Excepciones de la capa de autenticación
 */
class AuthException extends BaseAppException {}

class CredencialesInvalidasException extends AuthException
{
    protected string $nivel = 'warning';
}

class SesionExpiradaException extends AuthException
{
    protected string $nivel = 'info';
}

// Simular diferentes errores y capturarlos en jerarquía
function simularError(int $tipo): void
{
    match ($tipo) {
        1 => throw new ConexionDBException('No se pudo conectar al servidor MySQL', '', 2002),
        2 => throw new ConsultaDBException('Sintaxis SQL inválida', 'SELEC * FORM users', 1064),
        3 => throw new CredencialesInvalidasException('Contraseña incorrecta'),
        4 => throw new SesionExpiradaException('La sesión ha expirado después de 30 minutos'),
    };
}

for ($i = 1; $i <= 4; $i++) {
    try {
        simularError($i);
    } catch (DatabaseException $e) {
        // Captura ConexionDBException y ConsultaDBException
        echo "[DB/{$e->obtenerNivel()}] " . $e->getMessage();
        if ($e->obtenerConsulta()) {
            echo " | SQL: " . $e->obtenerConsulta();
        }
        echo "\n";
    } catch (AuthException $e) {
        // Captura CredencialesInvalidasException y SesionExpiradaException
        echo "[Auth/{$e->obtenerNivel()}] " . $e->getMessage() . "\n";
    } catch (BaseAppException $e) {
        // Captura cualquier otra excepción de la app
        echo "[App/{$e->obtenerNivel()}] " . $e->getMessage() . "\n";
    }
}

// ============================================
// Ejemplo 3: Excepción con propiedades adicionales y contexto
// ============================================

echo "\n=== Ejemplo 3: Excepción con contexto adicional ===\n";

/**
 * Excepción de validación que almacena los errores por campo,
 * el valor que falló y sugerencias de corrección.
 */
class ValidationException extends BaseAppException
{
    private array $errores;
    private array $contexto;

    public function __construct(array $errores, array $contexto = [])
    {
        $this->errores = $errores;
        $this->contexto = $contexto;
        $cantErrores = count($errores);
        parent::__construct("Validación fallida con $cantErrores error(es).", 422);
    }

    public function obtenerErrores(): array
    {
        return $this->errores;
    }

    public function obtenerContexto(): array
    {
        return $this->contexto;
    }

    /**
     * Devuelve los errores en formato adecuado para respuesta JSON de API
     */
    public function paraJson(): array
    {
        return [
            'error'   => true,
            'codigo'  => $this->getCode(),
            'mensaje' => $this->getMessage(),
            'errores' => $this->errores,
        ];
    }
}

function validarProducto(array $datos): void
{
    $errores = [];

    if (empty($datos['nombre'])) {
        $errores['nombre'] = 'El nombre del producto es obligatorio.';
    }

    if (!isset($datos['precio']) || $datos['precio'] <= 0) {
        $errores['precio'] = 'El precio debe ser un número positivo.';
    }

    if (!isset($datos['stock']) || $datos['stock'] < 0) {
        $errores['stock'] = 'El stock no puede ser negativo.';
    }

    if (!empty($errores)) {
        throw new ValidationException($errores, ['datos_recibidos' => $datos]);
    }
}

try {
    validarProducto(['nombre' => '', 'precio' => -10, 'stock' => -5]);
} catch (ValidationException $e) {
    echo "Código HTTP: " . $e->getCode() . "\n";
    echo "Errores:\n";
    foreach ($e->obtenerErrores() as $campo => $error) {
        echo "  [$campo] $error\n";
    }
    echo "\nRespuesta JSON:\n";
    echo json_encode($e->paraJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

// ============================================
// Ejemplo 4: Encadenamiento de excepciones (Exception chaining)
// ============================================

echo "\n=== Ejemplo 4: Encadenamiento de excepciones ===\n";

/**
 * Encadenar excepciones permite preservar la causa original
 * mientras se lanza una excepción de nivel superior.
 */
class ServiceException extends BaseAppException {}
class RepositoryException extends BaseAppException {}

function conectarBD(): void
{
    // Simular error de conexión original
    throw new \PDOException("SQLSTATE[HY000] [2002] Connection refused");
}

function obtenerUsuario(int $id): array
{
    try {
        conectarBD();
    } catch (\PDOException $e) {
        // Encapsular la excepción PDO en una del repositorio
        throw new RepositoryException(
            "No se pudo obtener el usuario #$id",
            0,
            $e // Excepción anterior encadenada
        );
    }
    return [];
}

function loginUsuario(int $id, string $password): void
{
    try {
        obtenerUsuario($id);
    } catch (RepositoryException $e) {
        // Encapsular la excepción del repositorio en una del servicio
        throw new ServiceException(
            "Error en el servicio de autenticación",
            0,
            $e // Encadenar la excepción anterior
        );
    }
}

try {
    loginUsuario(42, 'secret123');
} catch (ServiceException $e) {
    // Recorrer toda la cadena de excepciones
    echo "Cadena de excepciones:\n";
    $excepcion = $e;
    $nivel = 0;
    while ($excepcion !== null) {
        $clase = get_class($excepcion);
        $indentacion = str_repeat('  ', $nivel);
        echo "{$indentacion}-> [$clase] {$excepcion->getMessage()}\n";
        $excepcion = $excepcion->getPrevious();
        $nivel++;
    }
}

// ============================================
// Ejemplo 5: Caso práctico - Sistema HTTP con excepciones tipadas
// ============================================

echo "\n=== Ejemplo 5: Excepciones HTTP tipadas ===\n";

/**
 * Jerarquía de excepciones HTTP para una API REST.
 * Cada excepción tiene su código de estado y formato de respuesta.
 */
abstract class HttpException extends RuntimeException
{
    protected int $statusCode;
    protected array $headers = [];

    public function __construct(string $mensaje = '', array $headers = [], ?\Throwable $prev = null)
    {
        $this->headers = $headers;
        parent::__construct($mensaje, $this->statusCode, $prev);
    }

    public function obtenerStatusCode(): int
    {
        return $this->statusCode;
    }

    public function obtenerHeaders(): array
    {
        return $this->headers;
    }

    public function obtenerRespuesta(): array
    {
        return [
            'status' => $this->statusCode,
            'error'  => static::class,
            'message' => $this->getMessage(),
        ];
    }
}

class BadRequestException extends HttpException
{
    protected int $statusCode = 400;
}

class UnauthorizedException extends HttpException
{
    protected int $statusCode = 401;

    public function __construct(string $msg = 'No autorizado', array $headers = [], ?\Throwable $prev = null)
    {
        $headers['WWW-Authenticate'] = 'Bearer';
        parent::__construct($msg, $headers, $prev);
    }
}

class ForbiddenException extends HttpException
{
    protected int $statusCode = 403;
}

class NotFoundException extends HttpException
{
    protected int $statusCode = 404;
}

class TooManyRequestsException extends HttpException
{
    protected int $statusCode = 429;

    public function __construct(int $reintentarEn = 60, ?\Throwable $prev = null)
    {
        $headers = ['Retry-After' => (string) $reintentarEn];
        parent::__construct("Demasiadas solicitudes. Reintentar en {$reintentarEn}s.", $headers, $prev);
    }
}

// Simular manejo de peticiones de API
$peticiones = [
    ['ruta' => '/api/users', 'error' => 'not_found'],
    ['ruta' => '/api/login', 'error' => 'unauthorized'],
    ['ruta' => '/api/admin', 'error' => 'forbidden'],
    ['ruta' => '/api/data', 'error' => 'rate_limit'],
    ['ruta' => '/api/items', 'error' => 'bad_request'],
];

foreach ($peticiones as $peticion) {
    try {
        match ($peticion['error']) {
            'not_found'    => throw new NotFoundException("Recurso no encontrado: {$peticion['ruta']}"),
            'unauthorized' => throw new UnauthorizedException(),
            'forbidden'    => throw new ForbiddenException("Acceso denegado a {$peticion['ruta']}"),
            'rate_limit'   => throw new TooManyRequestsException(120),
            'bad_request'  => throw new BadRequestException("Parámetros inválidos en {$peticion['ruta']}"),
        };
    } catch (HttpException $e) {
        $respuesta = $e->obtenerRespuesta();
        echo "  [{$respuesta['status']}] {$respuesta['message']}";
        if (!empty($e->obtenerHeaders())) {
            echo " | Headers: " . json_encode($e->obtenerHeaders());
        }
        echo "\n";
    }
}

?>
