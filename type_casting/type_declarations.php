<?php
// ============================================================================
// DECLARACIONES DE TIPO EN PHP
// ============================================================================
// PHP ha evolucionado de un lenguaje sin tipos a uno con un sistema de tipos
// robusto y opcional. Las declaraciones de tipo permiten especificar que tipos
// acepta una funcion (parametros) y que tipo retorna, atrapando errores en
// tiempo de ejecucion en vez de producir resultados silenciosamente incorrectos.
//
// Historico:
//   PHP 5.0: type hints para clases e interfaces
//   PHP 5.1: array type hint
//   PHP 7.0: tipos escalares (int, float, string, bool), return types, strict_types
//   PHP 7.1: nullable (?Type), void, iterable
//   PHP 7.2: object type hint
//   PHP 8.0: union types (int|string), mixed, static return type
//   PHP 8.1: intersection types (A&B), never, enums, readonly
//   PHP 8.2: DNF types ((A&B)|C), true/false/null standalone types
// ============================================================================

// ============================================================================
// Ejemplo 1: Tipos escalares y modo coercivo vs estricto
// ============================================================================
// Por defecto, PHP funciona en modo COERCIVO: convierte automaticamente los
// valores al tipo declarado (como haciamos con casting). Con strict_types=1,
// PHP exige que los tipos coincidan exactamente.

// IMPORTANTE: declare(strict_types=1) afecta solo al archivo donde se declara,
// no a los archivos que se incluyen. Debe ser la PRIMERA declaracion del archivo.
declare(strict_types=1);

echo "=== Ejemplo 1: Tipos escalares y strict_types ===\n\n";

// --- Tipos escalares basicos ---
function sumar(int $a, int $b): int {
    return $a + $b;
}

function dividir(float $dividendo, float $divisor): float {
    if ($divisor == 0.0) {
        throw new InvalidArgumentException("No se puede dividir por cero");
    }
    return $dividendo / $divisor;
}

function saludar(string $nombre): string {
    return "Hola, {$nombre}!";
}

function esAdulto(int $edad): bool {
    return $edad >= 18;
}

echo "  sumar(5, 3): " . sumar(5, 3) . "\n";
echo "  dividir(10.0, 3.0): " . dividir(10.0, 3.0) . "\n";
echo "  saludar('Maria'): " . saludar('Maria') . "\n";
echo "  esAdulto(25): " . var_export(esAdulto(25), true) . "\n\n";

// --- Efecto de strict_types ---
echo "--- Efecto de strict_types=1 ---\n";

// En modo estricto, esto lanzaria TypeError:
// sumar('5', '3');   // TypeError: Argument #1 must be of type int, string given
// saludar(42);       // TypeError: Argument #1 must be of type string, int given

// Demostramos capturando el error
try {
    // En modo estricto, '5' no se acepta como int
    echo "  Intentando sumar('5', '3') con strict_types=1...\n";
    $resultado = sumar((int)'5', (int)'3'); // Necesitamos cast explicito
    echo "  Con cast explicito: sumar((int)'5', (int)'3') = {$resultado}\n";
} catch (TypeError $e) {
    echo "  TypeError: {$e->getMessage()}\n";
}

echo "\n  Sin strict_types (modo coercivo, el default):\n";
echo "    sumar('5', '3') -> PHP convierte '5' a 5, '3' a 3 automaticamente\n";
echo "    saludar(42) -> PHP convierte 42 a '42' automaticamente\n";
echo "  Con strict_types=1 (este archivo):\n";
echo "    sumar('5', '3') -> TypeError! Debe ser int, no string\n";
echo "    Se necesita cast explicito: sumar((int)'5', (int)'3')\n\n";

// --- Tabla de coerciones permitidas en modo coercivo ---
echo "  Coerciones permitidas en modo coercivo (sin strict_types):\n";
echo "  ┌─────────────────┬───────────────────────────────────────────┐\n";
echo "  │ Tipo declarado  │ Tipos aceptados                          │\n";
echo "  ├─────────────────┼───────────────────────────────────────────┤\n";
echo "  │ int             │ int, float (trunca), string numerica     │\n";
echo "  │ float           │ float, int, string numerica              │\n";
echo "  │ string          │ string, int, float                       │\n";
echo "  │ bool            │ cualquier tipo (se aplican reglas truthy) │\n";
echo "  └─────────────────┴───────────────────────────────────────────┘\n\n";


// ============================================================================
// Ejemplo 2: Return types, void y nullable types
// ============================================================================
// El tipo de retorno se declara despues de los parentesis con ':'.
// 'void' indica que la funcion no retorna nada.
// '?Type' indica que puede retornar el tipo o null.

echo "=== Ejemplo 2: Return types, void y nullable types ===\n\n";

// --- Tipo de retorno ---
function calcularArea(float $largo, float $ancho): float {
    return $largo * $ancho;
}

echo "  calcularArea(5.0, 3.0): " . calcularArea(5.0, 3.0) . "\n";

// --- void: no retorna nada (ni siquiera null explicitamente) ---
function registrarEvento(string $mensaje): void {
    // Solo ejecuta una accion, no retorna nada
    // echo se usa para demostrar, en produccion escribiriamos a un archivo
    echo "  [LOG] {$mensaje}\n";
    // return; // OK - return sin valor es permitido en void
    // return null; // ERROR! void no permite retornar null
}

registrarEvento("Usuario conectado");

// --- Nullable types con ? ---
// ?string significa que puede retornar string o null
function buscarUsuarioPorId(int $id): ?string {
    $usuarios = [
        1 => 'Ana Garcia',
        2 => 'Luis Torres',
        3 => 'Sofia Mendez',
    ];
    return $usuarios[$id] ?? null; // Retorna null si no existe
}

echo "\n  buscarUsuarioPorId(1): " . var_export(buscarUsuarioPorId(1), true) . "\n";
echo "  buscarUsuarioPorId(99): " . var_export(buscarUsuarioPorId(99), true) . "\n";

// --- Parametros nullable ---
// ?Type en parametro acepta el tipo o null
function formatearFecha(?string $fecha, string $formato = 'd/m/Y'): string {
    if ($fecha === null) {
        return 'Sin fecha';
    }
    try {
        $dt = new DateTime($fecha);
        return $dt->format($formato);
    } catch (Exception $e) {
        return 'Fecha invalida';
    }
}

echo "\n  formatearFecha('2025-12-25'): " . formatearFecha('2025-12-25') . "\n";
echo "  formatearFecha(null): " . formatearFecha(null) . "\n";
echo "  formatearFecha('invalida'): " . formatearFecha('invalida') . "\n\n";

// --- Combinacion de nullable parametros y retorno ---
function dividirSeguro(?float $a, ?float $b): ?float {
    if ($a === null || $b === null || $b == 0.0) {
        return null;
    }
    return $a / $b;
}

echo "  dividirSeguro(10.0, 3.0): " . var_export(dividirSeguro(10.0, 3.0), true) . "\n";
echo "  dividirSeguro(10.0, 0.0): " . var_export(dividirSeguro(10.0, 0.0), true) . "\n";
echo "  dividirSeguro(null, 5.0): " . var_export(dividirSeguro(null, 5.0), true) . "\n\n";


// ============================================================================
// Ejemplo 3: Union types e intersection types
// ============================================================================
// Union types (PHP 8.0): int|string - acepta CUALQUIERA de los tipos listados.
// Intersection types (PHP 8.1): Countable&Iterator - debe implementar TODOS.
// DNF types (PHP 8.2): (A&B)|null - combinacion de ambos.

echo "=== Ejemplo 3: Union types e intersection types ===\n\n";

// --- Union types: acepta cualquiera de los tipos ---
function mostrarId(int|string $id): string {
    if (is_int($id)) {
        return "ID numerico: #{$id}";
    }
    return "ID alfanumerico: {$id}";
}

echo "  mostrarId(42): " . mostrarId(42) . "\n";
echo "  mostrarId('USR-001'): " . mostrarId('USR-001') . "\n\n";

// Union type con null (equivalente a nullable pero mas explicito)
function buscarProducto(int|string $identificador): array|null {
    $productos = [
        1 => ['nombre' => 'Laptop', 'sku' => 'LAP-001'],
        'LAP-001' => ['nombre' => 'Laptop', 'id' => 1],
    ];
    return $productos[$identificador] ?? null;
}

echo "  buscarProducto(1): " . json_encode(buscarProducto(1)) . "\n";
echo "  buscarProducto('LAP-001'): " . json_encode(buscarProducto('LAP-001')) . "\n";
echo "  buscarProducto(999): " . var_export(buscarProducto(999), true) . "\n\n";

// Union con multiples tipos
function procesar(int|float|string|array $datos): string {
    return match(true) {
        is_int($datos) => "Entero: {$datos}",
        is_float($datos) => "Decimal: {$datos}",
        is_string($datos) => "Texto: {$datos}",
        is_array($datos) => "Array con " . count($datos) . " elementos",
    };
}

echo "  procesar(42): " . procesar(42) . "\n";
echo "  procesar(3.14): " . procesar(3.14) . "\n";
echo "  procesar('hola'): " . procesar('hola') . "\n";
echo "  procesar([1,2,3]): " . procesar([1, 2, 3]) . "\n\n";

// --- Intersection types: debe implementar TODOS los tipos ---
// Util con interfaces
interface Serializable {
    public function serializar(): string;
}

interface Validable {
    public function esValido(): bool;
}

interface Auditable {
    public function getUltimaModificacion(): DateTime;
}

class Pedido implements Serializable, Validable, Auditable {
    private DateTime $ultimaModificacion;

    public function __construct(
        private int $id,
        private string $cliente,
        private float $total,
        private array $items = []
    ) {
        $this->ultimaModificacion = new DateTime();
    }

    public function serializar(): string {
        return json_encode([
            'id' => $this->id,
            'cliente' => $this->cliente,
            'total' => $this->total,
            'items' => $this->items,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function esValido(): bool {
        return $this->total > 0 && !empty($this->cliente) && !empty($this->items);
    }

    public function getUltimaModificacion(): DateTime {
        return $this->ultimaModificacion;
    }

    public function getCliente(): string {
        return $this->cliente;
    }

    public function getTotal(): float {
        return $this->total;
    }
}

// Intersection type: el parametro debe implementar AMBAS interfaces
function guardarConValidacion(Serializable&Validable $entidad): string {
    if (!$entidad->esValido()) {
        return "Error: la entidad no es valida";
    }
    $json = $entidad->serializar();
    return "Guardado: {$json}";
}

// DNF type (PHP 8.2): Combinacion de intersection y union
// (Serializable&Validable)|null - acepta un objeto que implemente ambas, o null
function guardarOpcional((Serializable&Validable)|null $entidad): string {
    if ($entidad === null) {
        return "Nada que guardar";
    }
    return guardarConValidacion($entidad);
}

$pedidoValido = new Pedido(1, 'Maria', 250.00, ['Laptop']);
$pedidoInvalido = new Pedido(2, '', 0, []);

echo "--- Intersection types ---\n";
echo "  Pedido valido: " . guardarConValidacion($pedidoValido) . "\n";
echo "  Pedido invalido: " . guardarConValidacion($pedidoInvalido) . "\n";
echo "  guardarOpcional(null): " . guardarOpcional(null) . "\n\n";


// ============================================================================
// Ejemplo 4: never return type y mixed type
// ============================================================================
// 'never' (PHP 8.1): la funcion NUNCA retorna (siempre lanza excepcion o termina).
// 'mixed' (PHP 8.0): acepta cualquier tipo (equivalente a no declarar tipo, pero explicito).

echo "=== Ejemplo 4: never y mixed ===\n\n";

// --- never: la funcion siempre termina la ejecucion ---
// Util para funciones que lanzan excepciones o llaman a exit()
function errorFatal(string $mensaje, int $codigo = 500): never {
    // Esta funcion NUNCA retorna - siempre lanza una excepcion
    throw new RuntimeException("[Error {$codigo}] {$mensaje}");
}

function redirigir(string $url): never {
    // En una aplicacion web real:
    // header("Location: {$url}");
    // exit;
    throw new RuntimeException("Redirigiendo a: {$url}");
}

// 'never' es util para que el analizador estatico sepa que el codigo
// despues de la llamada es inalcanzable
function procesarSolicitud(string $metodo): string {
    return match($metodo) {
        'GET' => 'Procesando GET',
        'POST' => 'Procesando POST',
        // Si llega aqui, errorFatal() nunca retorna, asi que match sabe
        // que este branch nunca producira un valor incompatible
        default => errorFatal("Metodo no soportado: {$metodo}", 405),
    };
}

echo "  procesarSolicitud('GET'): " . procesarSolicitud('GET') . "\n";
echo "  procesarSolicitud('POST'): " . procesarSolicitud('POST') . "\n";
try {
    procesarSolicitud('DELETE');
} catch (RuntimeException $e) {
    echo "  procesarSolicitud('DELETE'): " . $e->getMessage() . "\n";
}

// --- mixed: acepta cualquier tipo ---
echo "\n--- Tipo mixed ---\n";

// 'mixed' es equivalente a: int|float|string|bool|array|object|null
// Pero es mas explicito que no poner tipo (indica intencion)
function almacenarEnCache(string $clave, mixed $valor): void {
    $tipo = gettype($valor);
    $repr = match(true) {
        is_null($valor) => 'null',
        is_bool($valor) => var_export($valor, true),
        is_array($valor) => json_encode($valor),
        is_object($valor) => get_class($valor),
        default => (string) $valor,
    };
    echo "  Cache['{$clave}'] = {$repr} ({$tipo})\n";
}

function obtenerDeCache(string $clave): mixed {
    // Puede retornar cualquier tipo
    $cache = [
        'nombre' => 'Carlos',
        'edad' => 30,
        'activo' => true,
        'datos' => ['a', 'b', 'c'],
        'vacio' => null,
    ];
    return $cache[$clave] ?? null;
}

almacenarEnCache('usuario', 'Maria');
almacenarEnCache('intentos', 3);
almacenarEnCache('config', ['debug' => true]);
almacenarEnCache('token', null);

echo "\n  Nota: mixed != void. mixed puede retornar null, void no puede.\n";
echo "  Nota: mixed incluye null, asi que ?mixed no tiene sentido.\n\n";


// ============================================================================
// Ejemplo 5: Tipos en propiedades de clase y readonly
// ============================================================================
// Las propiedades tipadas (PHP 7.4+) aplican las mismas reglas que los
// parametros. readonly (PHP 8.1) hace que una propiedad solo se pueda
// asignar una vez.

echo "=== Ejemplo 5: Propiedades tipadas y readonly ===\n\n";

class Configuracion {
    // Propiedades tipadas con valores por defecto
    public string $nombre = 'Mi App';
    public int $maxIntentos = 3;
    public float $timeout = 30.0;
    public bool $debug = false;
    public ?string $secreto = null;
    public array $opciones = [];

    // Propiedad readonly: solo se puede asignar una vez (en constructor o declaracion)
    public readonly string $version;
    public readonly DateTime $creadoEn;

    // Constructor con propiedades promovidas (PHP 8.0)
    // La promocion declara y asigna automaticamente
    public function __construct(
        public readonly string $entorno,
        public readonly string $region = 'mx-central',
        string $version = '1.0.0'
    ) {
        $this->version = $version;
        $this->creadoEn = new DateTime();
    }
}

$config = new Configuracion(entorno: 'produccion', version: '2.5.1');
echo "  Entorno: {$config->entorno}\n";
echo "  Region: {$config->region}\n";
echo "  Version: {$config->version}\n";
echo "  Debug: " . var_export($config->debug, true) . "\n";
echo "  Creado: " . $config->creadoEn->format('Y-m-d H:i:s') . "\n";

// Intentar modificar una propiedad readonly
try {
    // @phpstan-ignore-next-line
    $config->entorno = 'desarrollo'; // Error!
} catch (Error $e) {
    echo "\n  Error al modificar readonly: {$e->getMessage()}\n";
}

// Propiedades normales si se pueden modificar
$config->debug = true;
$config->secreto = 'clave-secreta-123';
echo "  Debug cambiado a: " . var_export($config->debug, true) . "\n\n";

// --- Clase con todas las propiedades promovidas y readonly ---
class Coordenada {
    public function __construct(
        public readonly float $latitud,
        public readonly float $longitud,
        public readonly ?float $altitud = null
    ) {
        if ($latitud < -90 || $latitud > 90) {
            throw new InvalidArgumentException("Latitud invalida: {$latitud}");
        }
        if ($longitud < -180 || $longitud > 180) {
            throw new InvalidArgumentException("Longitud invalida: {$longitud}");
        }
    }

    public function distanciaA(Coordenada $otra): float {
        // Formula de Haversine simplificada
        $radioTierra = 6371; // km
        $dLat = deg2rad($otra->latitud - $this->latitud);
        $dLon = deg2rad($otra->longitud - $this->longitud);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($this->latitud)) * cos(deg2rad($otra->latitud)) *
            sin($dLon / 2) ** 2;
        return $radioTierra * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function __toString(): string {
        $alt = $this->altitud !== null ? ", alt: {$this->altitud}m" : '';
        return "({$this->latitud}, {$this->longitud}{$alt})";
    }
}

$cdmx = new Coordenada(19.4326, -99.1332, 2240);
$guadalajara = new Coordenada(20.6597, -103.3496, 1566);
$distancia = round($cdmx->distanciaA($guadalajara), 2);
echo "  CDMX: {$cdmx}\n";
echo "  Guadalajara: {$guadalajara}\n";
echo "  Distancia: {$distancia} km\n";

try {
    $invalida = new Coordenada(999, 0); // Latitud invalida
} catch (InvalidArgumentException $e) {
    echo "  Coordenada invalida: {$e->getMessage()}\n";
}
echo "\n";


// ============================================================================
// Ejemplo 6: API Response estrictamente tipada (aplicacion practica)
// ============================================================================
// Implementacion completa de un sistema de respuesta de API con tipos
// estrictos, demostrando como los tipos previenen bugs y mejoran
// la documentacion del codigo.

echo "=== Ejemplo 6: API Response estrictamente tipada ===\n\n";

// Enum para codigos de estado (PHP 8.1+)
enum HttpStatus: int {
    case OK = 200;
    case Created = 201;
    case NoContent = 204;
    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case UnprocessableEntity = 422;
    case InternalServerError = 500;

    public function getMensaje(): string {
        return match($this) {
            self::OK => 'OK',
            self::Created => 'Created',
            self::NoContent => 'No Content',
            self::BadRequest => 'Bad Request',
            self::Unauthorized => 'Unauthorized',
            self::Forbidden => 'Forbidden',
            self::NotFound => 'Not Found',
            self::UnprocessableEntity => 'Unprocessable Entity',
            self::InternalServerError => 'Internal Server Error',
        };
    }

    public function esExitoso(): bool {
        return $this->value >= 200 && $this->value < 300;
    }
}

// Clase inmutable para errores de validacion
class ErrorValidacion {
    public function __construct(
        public readonly string $campo,
        public readonly string $mensaje,
        public readonly string $codigo = 'INVALID'
    ) {}

    public function toArray(): array {
        return [
            'campo' => $this->campo,
            'mensaje' => $this->mensaje,
            'codigo' => $this->codigo,
        ];
    }
}

// Clase generica de respuesta API (simulacion de generics con docblocks)
class ApiResponse {
    /** @var array<string, string> */
    private array $headers = [];

    private function __construct(
        public readonly HttpStatus $status,
        public readonly mixed $datos,
        public readonly ?string $mensaje,
        /** @var ErrorValidacion[] */
        public readonly array $errores,
        public readonly float $tiempoProceso
    ) {
        $this->headers['Content-Type'] = 'application/json';
        $this->headers['X-Response-Time'] = "{$tiempoProceso}ms";
    }

    // Factory methods con tipos estrictos

    /** @param array<string, mixed>|array<int, mixed> $datos */
    public static function exito(
        array|object $datos,
        string $mensaje = 'Operacion exitosa',
        HttpStatus $status = HttpStatus::OK
    ): self {
        return new self(
            status: $status,
            datos: $datos,
            mensaje: $mensaje,
            errores: [],
            tiempoProceso: round(microtime(true) * 1000 % 1000, 2)
        );
    }

    public static function creado(array|object $datos, string $mensaje = 'Recurso creado'): self {
        return self::exito($datos, $mensaje, HttpStatus::Created);
    }

    /** @param ErrorValidacion[] $errores */
    public static function errorValidacion(array $errores): self {
        return new self(
            status: HttpStatus::UnprocessableEntity,
            datos: null,
            mensaje: 'Error de validacion',
            errores: $errores,
            tiempoProceso: round(microtime(true) * 1000 % 1000, 2)
        );
    }

    public static function noEncontrado(string $recurso = 'Recurso'): self {
        return new self(
            status: HttpStatus::NotFound,
            datos: null,
            mensaje: "{$recurso} no encontrado",
            errores: [],
            tiempoProceso: round(microtime(true) * 1000 % 1000, 2)
        );
    }

    public static function errorServidor(string $mensaje = 'Error interno'): self {
        return new self(
            status: HttpStatus::InternalServerError,
            datos: null,
            mensaje: $mensaje,
            errores: [],
            tiempoProceso: round(microtime(true) * 1000 % 1000, 2)
        );
    }

    public function conHeader(string $nombre, string $valor): self {
        $nuevo = clone $this;
        $nuevo->headers[$nombre] = $valor;
        return $nuevo;
    }

    public function toJson(): string {
        $cuerpo = [
            'status' => $this->status->value,
            'mensaje' => $this->mensaje,
        ];

        if ($this->status->esExitoso()) {
            $cuerpo['datos'] = $this->datos;
        }

        if (!empty($this->errores)) {
            $cuerpo['errores'] = array_map(
                fn(ErrorValidacion $e) => $e->toArray(),
                $this->errores
            );
        }

        $cuerpo['meta'] = [
            'tiempo_ms' => $this->tiempoProceso,
        ];

        return json_encode($cuerpo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getHeaders(): array {
        return $this->headers;
    }
}

// --- Controlador de API simulado que usa el sistema de tipos ---
class ApiProductoController {
    /** @var array<int, array<string, mixed>> */
    private array $productos = [];
    private int $siguienteId = 1;

    public function __construct() {
        // Datos iniciales
        $this->crearProducto('Laptop HP', 18500.00, 'electronica');
        $this->crearProducto('Silla Ergonomica', 6500.00, 'muebles');
        $this->crearProducto('Monitor 27"', 7200.00, 'electronica');
    }

    private function crearProducto(string $nombre, float $precio, string $categoria): array {
        $producto = [
            'id' => $this->siguienteId++,
            'nombre' => $nombre,
            'precio' => $precio,
            'categoria' => $categoria,
            'creado_en' => (new DateTime())->format('Y-m-d\TH:i:s\Z'),
        ];
        $this->productos[$producto['id']] = $producto;
        return $producto;
    }

    // GET /api/productos
    public function index(): ApiResponse {
        return ApiResponse::exito(
            datos: array_values($this->productos),
            mensaje: count($this->productos) . ' productos encontrados'
        );
    }

    // GET /api/productos/{id}
    public function show(int $id): ApiResponse {
        if (!isset($this->productos[$id])) {
            return ApiResponse::noEncontrado('Producto');
        }
        return ApiResponse::exito($this->productos[$id]);
    }

    // POST /api/productos
    public function store(array $datos): ApiResponse {
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            return ApiResponse::errorValidacion($errores);
        }

        $producto = $this->crearProducto(
            nombre: $datos['nombre'],
            precio: (float) $datos['precio'],
            categoria: $datos['categoria']
        );

        return ApiResponse::creado($producto);
    }

    /** @return ErrorValidacion[] */
    private function validar(array $datos): array {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = new ErrorValidacion('nombre', 'El nombre es obligatorio', 'REQUIRED');
        } elseif (strlen($datos['nombre']) < 3) {
            $errores[] = new ErrorValidacion('nombre', 'Minimo 3 caracteres', 'MIN_LENGTH');
        }

        if (!isset($datos['precio'])) {
            $errores[] = new ErrorValidacion('precio', 'El precio es obligatorio', 'REQUIRED');
        } elseif (!is_numeric($datos['precio']) || (float)$datos['precio'] <= 0) {
            $errores[] = new ErrorValidacion('precio', 'Debe ser un numero positivo', 'INVALID_NUMBER');
        }

        if (empty($datos['categoria'])) {
            $errores[] = new ErrorValidacion('categoria', 'La categoria es obligatoria', 'REQUIRED');
        } else {
            $categoriasValidas = ['electronica', 'muebles', 'ropa', 'alimentos'];
            if (!in_array($datos['categoria'], $categoriasValidas, true)) {
                $errores[] = new ErrorValidacion(
                    'categoria',
                    'Categoria invalida. Opciones: ' . implode(', ', $categoriasValidas),
                    'INVALID_OPTION'
                );
            }
        }

        return $errores;
    }
}

// --- Simular peticiones a la API ---
$api = new ApiProductoController();

// Listar todos los productos
echo "--- GET /api/productos ---\n";
$respuesta = $api->index();
echo $respuesta->toJson() . "\n\n";

// Buscar un producto existente
echo "--- GET /api/productos/1 ---\n";
$respuesta = $api->show(1);
echo $respuesta->toJson() . "\n\n";

// Buscar un producto que no existe
echo "--- GET /api/productos/99 ---\n";
$respuesta = $api->show(99);
echo $respuesta->toJson() . "\n\n";

// Crear un producto valido
echo "--- POST /api/productos (valido) ---\n";
$respuesta = $api->store([
    'nombre' => 'Teclado Mecanico',
    'precio' => '1800.50',
    'categoria' => 'electronica',
]);
echo $respuesta->toJson() . "\n\n";

// Crear un producto con datos invalidos
echo "--- POST /api/productos (invalido) ---\n";
$respuesta = $api->store([
    'nombre' => 'AB',         // Muy corto
    'precio' => '-50',        // Negativo
    'categoria' => 'juguetes' // No existe
]);
echo $respuesta->toJson() . "\n";

echo "\n  Los tipos estrictos garantizan que:\n";
echo "    - HttpStatus solo puede ser un valor valido del enum\n";
echo "    - Los errores siempre son instancias de ErrorValidacion\n";
echo "    - Los datos de respuesta exitosa nunca son null\n";
echo "    - El tiempo de proceso siempre es float\n";
echo "    - readonly previene modificacion accidental de la respuesta\n";
echo "    - never en funciones de error ayuda al analisis estatico\n";
?>
