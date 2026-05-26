<?php
/**
 * =============================================================================
 * INYECCION DE DEPENDENCIAS (DEPENDENCY INJECTION) EN PHP
 * =============================================================================
 *
 * La Inyeccion de Dependencias (DI) es un patron donde un objeto recibe
 * sus dependencias desde el exterior en lugar de crearlas internamente.
 *
 * Tipos de inyeccion:
 * - Por constructor: las dependencias se pasan al crear el objeto
 * - Por setter: se inyectan mediante metodos setter despues de la creacion
 * - Por interfaz: el objeto implementa una interfaz que define como recibir
 *   la dependencia
 *
 * Beneficios:
 * - Bajo acoplamiento entre clases
 * - Facilita las pruebas unitarias (inyectar mocks)
 * - Cumple con el principio de Inversion de Dependencias (SOLID)
 * - Mayor flexibilidad y reutilizacion del codigo
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Inyeccion por Constructor
// =============================================================================
// Problema: Un servicio de envio de correos necesita un servidor SMTP y un
// logger. Sin DI, el servicio crearia estas dependencias internamente,
// haciendo imposible cambiarlas o probarlas.

echo "=== Ejemplo 1: Inyeccion por Constructor ===\n\n";

// --- SIN Inyeccion de Dependencias (MAL) ---

/**
 * class ServicioCorreoAcoplado {
 *     private $logger;
 *     private $smtp;
 *
 *     public function __construct() {
 *         // Crea sus propias dependencias - ACOPLAMIENTO FUERTE
 *         $this->logger = new ArchivoLogger('/var/log/correo.log');
 *         $this->smtp = new ConexionSMTP('smtp.gmail.com', 587, 'user', 'pass');
 *     }
 *
 *     // Problemas:
 *     // - No se puede cambiar el logger sin modificar esta clase
 *     // - No se puede probar sin un servidor SMTP real
 *     // - Las credenciales estan hardcodeadas
 * }
 */

// --- CON Inyeccion de Dependencias (BIEN) ---

// Interfaces: las dependencias se definen por contrato, no por implementacion
interface InterfazLogger
{
    public function info(string $mensaje): void;
    public function error(string $mensaje): void;
    public function debug(string $mensaje): void;
}

interface InterfazClienteCorreo
{
    public function enviar(string $para, string $asunto, string $cuerpo): bool;
    public function estaConectado(): bool;
}

// Implementaciones concretas
class LoggerConsola implements InterfazLogger
{
    public function info(string $mensaje): void
    {
        echo "    [INFO] {$mensaje}\n";
    }

    public function error(string $mensaje): void
    {
        echo "    [ERROR] {$mensaje}\n";
    }

    public function debug(string $mensaje): void
    {
        echo "    [DEBUG] {$mensaje}\n";
    }
}

class LoggerArchivo implements InterfazLogger
{
    private array $registros = [];

    public function __construct(private string $rutaArchivo) {}

    public function info(string $mensaje): void
    {
        $this->registros[] = "[INFO] {$mensaje}";
    }

    public function error(string $mensaje): void
    {
        $this->registros[] = "[ERROR] {$mensaje}";
    }

    public function debug(string $mensaje): void
    {
        $this->registros[] = "[DEBUG] {$mensaje}";
    }

    public function obtenerRegistros(): array { return $this->registros; }
}

class ClienteCorreoSMTP implements InterfazClienteCorreo
{
    public function __construct(
        private string $host,
        private int $puerto,
        private string $usuario,
        private string $contrasena
    ) {
        echo "    [SMTP] Conectado a {$host}:{$puerto}\n";
    }

    public function enviar(string $para, string $asunto, string $cuerpo): bool
    {
        echo "    [SMTP] Enviando '{$asunto}' a {$para}\n";
        return true; // Simulacion
    }

    public function estaConectado(): bool { return true; }
}

// Para pruebas: cliente de correo que no envia nada
class ClienteCorreoFalso implements InterfazClienteCorreo
{
    public array $correosEnviados = [];

    public function enviar(string $para, string $asunto, string $cuerpo): bool
    {
        $this->correosEnviados[] = compact('para', 'asunto', 'cuerpo');
        return true;
    }

    public function estaConectado(): bool { return true; }
}

/**
 * Servicio con inyeccion por constructor.
 * Las dependencias se declaran como parametros del constructor.
 * La clase solo conoce las INTERFACES, no las implementaciones concretas.
 */
class ServicioNotificacionEmail
{
    // Las dependencias se inyectan, no se crean internamente
    public function __construct(
        private InterfazClienteCorreo $clienteCorreo,
        private InterfazLogger $logger
    ) {
        $this->logger->info('ServicioNotificacionEmail inicializado');
    }

    public function notificarBienvenida(string $email, string $nombre): bool
    {
        $this->logger->info("Preparando email de bienvenida para {$nombre}");

        $asunto = "Bienvenido/a, {$nombre}!";
        $cuerpo = "Hola {$nombre}, gracias por registrarte en nuestra plataforma.";

        $enviado = $this->clienteCorreo->enviar($email, $asunto, $cuerpo);

        if ($enviado) {
            $this->logger->info("Email de bienvenida enviado exitosamente a {$email}");
        } else {
            $this->logger->error("Fallo al enviar email de bienvenida a {$email}");
        }

        return $enviado;
    }

    public function notificarPedido(string $email, string $pedidoId, float $total): bool
    {
        $this->logger->info("Preparando confirmacion de pedido {$pedidoId}");

        $totalFmt = number_format($total, 2);
        $asunto = "Confirmacion de Pedido #{$pedidoId}";
        $cuerpo = "Su pedido #{$pedidoId} por \${$totalFmt} ha sido confirmado.";

        return $this->clienteCorreo->enviar($email, $asunto, $cuerpo);
    }
}

// --- USO EN PRODUCCION ---
echo "  --- Uso en produccion ---\n";
$loggerProd = new LoggerConsola();
$smtpProd = new ClienteCorreoSMTP('smtp.empresa.com', 587, 'noreply', 'secreto');

$servicioProd = new ServicioNotificacionEmail($smtpProd, $loggerProd);
$servicioProd->notificarBienvenida('nuevo@usuario.com', 'Laura');
echo "\n";

// --- USO EN PRUEBAS (inyectando mocks) ---
echo "  --- Uso en pruebas (con mocks) ---\n";
$loggerPrueba = new LoggerArchivo('/tmp/test.log');
$clienteFalso = new ClienteCorreoFalso();

$servicioPrueba = new ServicioNotificacionEmail($clienteFalso, $loggerPrueba);
$servicioPrueba->notificarBienvenida('test@test.com', 'TestUser');
$servicioPrueba->notificarPedido('test@test.com', 'ORD-001', 299.99);

echo "    Correos capturados por el mock: " . count($clienteFalso->correosEnviados) . "\n";
echo "    Logs registrados: " . count($loggerPrueba->obtenerRegistros()) . "\n";
foreach ($clienteFalso->correosEnviados as $i => $correo) {
    echo "    Correo " . ($i + 1) . ": '{$correo['asunto']}' a {$correo['para']}\n";
}
echo "\n";


// =============================================================================
// Ejemplo 2: Inyeccion por Setter e Inyeccion por Interfaz
// =============================================================================
// Problema: A veces las dependencias son opcionales o necesitan cambiarse
// despues de la creacion del objeto.

echo "=== Ejemplo 2: Inyeccion por Setter e Interfaz ===\n\n";

// --- Inyeccion por Interfaz ---
// La clase implementa una interfaz que define como recibir la dependencia

interface ConcienciaDeLogger
{
    public function establecerLogger(InterfazLogger $logger): void;
}

interface ConcienciaDeCache
{
    public function establecerCache(InterfazCache $cache): void;
}

interface InterfazCache
{
    public function obtener(string $clave): mixed;
    public function establecer(string $clave, mixed $valor, int $ttl = 3600): void;
    public function existe(string $clave): bool;
}

class CacheEnMemoria implements InterfazCache
{
    private array $almacen = [];

    public function obtener(string $clave): mixed
    {
        return $this->almacen[$clave]['valor'] ?? null;
    }

    public function establecer(string $clave, mixed $valor, int $ttl = 3600): void
    {
        $this->almacen[$clave] = ['valor' => $valor, 'expira' => time() + $ttl];
    }

    public function existe(string $clave): bool
    {
        return isset($this->almacen[$clave]);
    }
}

/**
 * Servicio que usa TRES formas de inyeccion:
 * - Constructor: dependencias obligatorias (repositorio)
 * - Setter: dependencias opcionales (cache)
 * - Interfaz: contrato tipado para la inyeccion (logger)
 */
class ServicioCatalogoProductos implements ConcienciaDeLogger, ConcienciaDeCache
{
    private ?InterfazLogger $logger = null;
    private ?InterfazCache $cache = null;

    // Inyeccion por CONSTRUCTOR: el repositorio es obligatorio
    public function __construct(
        private RepositorioProductosSimple $repositorio
    ) {}

    // Inyeccion por INTERFAZ: contrato tipado para el logger
    public function establecerLogger(InterfazLogger $logger): void
    {
        $this->logger = $logger;
    }

    // Inyeccion por INTERFAZ: contrato tipado para el cache
    public function establecerCache(InterfazCache $cache): void
    {
        $this->cache = $cache;
    }

    public function buscarProducto(int $id): ?array
    {
        $claveCache = "producto_{$id}";

        // Verificar cache (si esta disponible)
        if ($this->cache && $this->cache->existe($claveCache)) {
            $this->log("Cache HIT para producto {$id}");
            return $this->cache->obtener($claveCache);
        }

        $this->log("Cache MISS para producto {$id}, consultando repositorio");
        $producto = $this->repositorio->buscarPorId($id);

        // Guardar en cache para futuras consultas
        if ($producto && $this->cache) {
            $this->cache->establecer($claveCache, $producto, 300); // 5 minutos
        }

        return $producto;
    }

    public function listarProductos(): array
    {
        $this->log("Listando todos los productos");
        return $this->repositorio->buscarTodos();
    }

    // Metodo auxiliar: usar logger solo si esta disponible
    private function log(string $mensaje): void
    {
        $this->logger?->debug("[Catalogo] {$mensaje}");
    }
}

// Repositorio simple para el ejemplo
class RepositorioProductosSimple
{
    private array $productos = [
        1 => ['id' => 1, 'nombre' => 'Laptop', 'precio' => 15999.00],
        2 => ['id' => 2, 'nombre' => 'Teclado', 'precio' => 899.00],
        3 => ['id' => 3, 'nombre' => 'Monitor', 'precio' => 6499.00],
    ];

    public function buscarPorId(int $id): ?array
    {
        return $this->productos[$id] ?? null;
    }

    public function buscarTodos(): array
    {
        return array_values($this->productos);
    }
}

// Uso: inyeccion por constructor (obligatoria) + setter/interfaz (opcionales)
$catalogo = new ServicioCatalogoProductos(new RepositorioProductosSimple());

// Sin logger ni cache: funciona, pero sin extras
echo "  --- Sin logger ni cache ---\n";
$prod = $catalogo->buscarProducto(1);
echo "  Producto: {$prod['nombre']} - \${$prod['precio']}\n\n";

// Inyectar logger y cache posteriormente (setters)
$catalogo->establecerLogger(new LoggerConsola());
$catalogo->establecerCache(new CacheEnMemoria());

echo "  --- Con logger y cache ---\n";
$catalogo->buscarProducto(2); // Cache MISS -> consulta repo
$catalogo->buscarProducto(2); // Cache HIT -> no consulta repo
echo "\n";


// =============================================================================
// Ejemplo 3: Contenedor de Inyeccion de Dependencias (DI Container)
// =============================================================================
// Problema: En aplicaciones grandes, crear y conectar dependencias manualmente
// es tedioso y propenso a errores. Un contenedor DI automatiza este proceso.

echo "=== Ejemplo 3: Contenedor de Inyeccion de Dependencias ===\n\n";

class ContenedorDI
{
    // Almacena las definiciones de como crear cada servicio
    private array $definiciones = [];

    // Cache de instancias singleton
    private array $instancias = [];

    /**
     * Registrar un servicio con su fabrica (closure).
     * El closure recibe el contenedor como argumento para resolver dependencias.
     */
    public function registrar(string $nombre, callable $fabrica, bool $compartido = true): void
    {
        $this->definiciones[$nombre] = [
            'fabrica'    => $fabrica,
            'compartido' => $compartido, // true = singleton, false = nueva instancia cada vez
        ];
    }

    /**
     * Registrar una instancia ya creada directamente.
     */
    public function instancia(string $nombre, object $objeto): void
    {
        $this->instancias[$nombre] = $objeto;
    }

    /**
     * Resolver (obtener) un servicio por su nombre.
     */
    public function obtener(string $nombre): mixed
    {
        // Si ya hay una instancia en cache, devolverla
        if (isset($this->instancias[$nombre])) {
            return $this->instancias[$nombre];
        }

        // Verificar que el servicio esta registrado
        if (!isset($this->definiciones[$nombre])) {
            throw new \RuntimeException("Servicio no registrado: {$nombre}");
        }

        $definicion = $this->definiciones[$nombre];

        // Ejecutar la fabrica para crear la instancia
        $instancia = call_user_func($definicion['fabrica'], $this);

        // Si es compartido (singleton), guardarlo en cache
        if ($definicion['compartido']) {
            $this->instancias[$nombre] = $instancia;
        }

        return $instancia;
    }

    /**
     * Verificar si un servicio esta registrado.
     */
    public function tiene(string $nombre): bool
    {
        return isset($this->definiciones[$nombre]) || isset($this->instancias[$nombre]);
    }

    /**
     * Obtener la lista de servicios registrados.
     */
    public function serviciosRegistrados(): array
    {
        return array_unique(array_merge(
            array_keys($this->definiciones),
            array_keys($this->instancias)
        ));
    }
}

// Configurar el contenedor
$contenedor = new ContenedorDI();

// Registrar servicios con sus fabricas
$contenedor->registrar('logger', function (ContenedorDI $c) {
    echo "    [Contenedor] Creando LoggerConsola\n";
    return new LoggerConsola();
});

$contenedor->registrar('cache', function (ContenedorDI $c) {
    echo "    [Contenedor] Creando CacheEnMemoria\n";
    return new CacheEnMemoria();
});

$contenedor->registrar('repositorio.productos', function (ContenedorDI $c) {
    echo "    [Contenedor] Creando RepositorioProductosSimple\n";
    return new RepositorioProductosSimple();
});

// El servicio de catalogo depende de otros servicios del contenedor
$contenedor->registrar('servicio.catalogo', function (ContenedorDI $c) {
    echo "    [Contenedor] Creando ServicioCatalogoProductos con dependencias\n";
    $catalogo = new ServicioCatalogoProductos(
        $c->obtener('repositorio.productos')  // Inyeccion por constructor
    );
    $catalogo->establecerLogger($c->obtener('logger'));  // Inyeccion por setter
    $catalogo->establecerCache($c->obtener('cache'));     // Inyeccion por setter
    return $catalogo;
});

$contenedor->registrar('cliente.correo', function (ContenedorDI $c) {
    echo "    [Contenedor] Creando ClienteCorreoFalso\n";
    return new ClienteCorreoFalso();
});

$contenedor->registrar('servicio.notificacion', function (ContenedorDI $c) {
    echo "    [Contenedor] Creando ServicioNotificacionEmail con dependencias\n";
    return new ServicioNotificacionEmail(
        $c->obtener('cliente.correo'),
        $c->obtener('logger')
    );
});

// Usar el contenedor
echo "  Servicios registrados: " . implode(', ', $contenedor->serviciosRegistrados()) . "\n\n";

echo "  --- Resolviendo servicio.catalogo (primera vez - crea todo) ---\n";
$catalogo = $contenedor->obtener('servicio.catalogo');
echo "\n";

echo "  --- Resolviendo servicio.catalogo (segunda vez - usa cache) ---\n";
$catalogo2 = $contenedor->obtener('servicio.catalogo');
echo "    (Misma instancia: " . ($catalogo === $catalogo2 ? 'SI' : 'NO') . ")\n\n";

echo "  --- Resolviendo servicio.notificacion ---\n";
$notificador = $contenedor->obtener('servicio.notificacion');
echo "    (Reutiliza el mismo logger: SI - es singleton en el contenedor)\n\n";


// =============================================================================
// Ejemplo 4: Auto-Wiring (Cableado Automatico)
// =============================================================================
// Problema: Registrar cada servicio manualmente es tedioso. El auto-wiring
// analiza los type hints del constructor para resolver dependencias
// automaticamente.

echo "=== Ejemplo 4: Auto-Wiring (Resolucion Automatica) ===\n\n";

class ContenedorAutoWiring
{
    private array $definiciones = [];
    private array $instancias = [];

    // Registrar manualmente (para interfaces -> implementaciones)
    public function vincular(string $interfaz, string $implementacion): void
    {
        $this->definiciones[$interfaz] = $implementacion;
    }

    // Registrar una instancia directamente
    public function instancia(string $nombre, object $objeto): void
    {
        $this->instancias[$nombre] = $objeto;
    }

    /**
     * Resolver una clase automaticamente usando Reflection.
     * Analiza el constructor para determinar que dependencias necesita
     * y las resuelve recursivamente.
     */
    public function resolver(string $clase): object
    {
        // Si ya tenemos una instancia, devolverla
        if (isset($this->instancias[$clase])) {
            return $this->instancias[$clase];
        }

        // Si hay un vinculo interfaz -> implementacion, resolver la implementacion
        $claseReal = $this->definiciones[$clase] ?? $clase;

        // Si la clase real tambien tiene vinculo, seguir resolviendo
        if (isset($this->instancias[$claseReal])) {
            return $this->instancias[$claseReal];
        }

        // Usar Reflection para analizar el constructor
        $reflejo = new \ReflectionClass($claseReal);

        if (!$reflejo->isInstantiable()) {
            throw new \RuntimeException(
                "La clase {$claseReal} no es instanciable. Registre un vinculo."
            );
        }

        $constructor = $reflejo->getConstructor();

        // Si no tiene constructor, simplemente instanciar
        if ($constructor === null) {
            $instancia = new $claseReal();
            $this->instancias[$clase] = $instancia;
            return $instancia;
        }

        // Resolver cada parametro del constructor
        $parametros = $constructor->getParameters();
        $dependencias = [];

        foreach ($parametros as $parametro) {
            $tipo = $parametro->getType();

            if ($tipo === null || $tipo->isBuiltin()) {
                // Tipo primitivo o sin tipo: usar valor por defecto si existe
                if ($parametro->isDefaultValueAvailable()) {
                    $dependencias[] = $parametro->getDefaultValue();
                } else {
                    throw new \RuntimeException(
                        "No se puede resolver el parametro \${$parametro->getName()} de {$claseReal}"
                    );
                }
            } else {
                // Tipo de objeto: resolver recursivamente
                $nombreTipo = $tipo->getName();
                echo "    [AutoWire] Resolviendo {$nombreTipo} para {$claseReal}\n";
                $dependencias[] = $this->resolver($nombreTipo);
            }
        }

        $instancia = $reflejo->newInstanceArgs($dependencias);
        $this->instancias[$clase] = $instancia;
        return $instancia;
    }
}

// Clases de ejemplo para auto-wiring
class ConexionBD
{
    public function __construct()
    {
        echo "    [AutoWire] ConexionBD creada\n";
    }

    public function consulta(string $sql): array
    {
        return ['resultado' => 'datos'];
    }
}

class RepositorioUsuariosAW
{
    public function __construct(private ConexionBD $bd)
    {
        echo "    [AutoWire] RepositorioUsuariosAW creado (con ConexionBD inyectada)\n";
    }

    public function buscarPorId(int $id): array
    {
        return ['id' => $id, 'nombre' => 'Usuario Auto-Wired'];
    }
}

class ServicioUsuariosAW
{
    public function __construct(
        private RepositorioUsuariosAW $repo,
        private InterfazLogger $logger
    ) {
        echo "    [AutoWire] ServicioUsuariosAW creado (con Repo y Logger inyectados)\n";
    }

    public function obtenerUsuario(int $id): array
    {
        $this->logger->info("Buscando usuario {$id}");
        return $this->repo->buscarPorId($id);
    }
}

// Configurar auto-wiring
$autoWire = new ContenedorAutoWiring();

// Solo necesitamos vincular interfaces a implementaciones concretas
$autoWire->vincular(InterfazLogger::class, LoggerConsola::class);
// Las clases concretas se resuelven automaticamente

echo "  --- Resolviendo ServicioUsuariosAW (auto-wiring) ---\n";
$servicio = $autoWire->resolver(ServicioUsuariosAW::class);

echo "\n  --- Usando el servicio resuelto ---\n";
$usuario = $servicio->obtenerUsuario(42);
echo "    Usuario encontrado: " . json_encode($usuario) . "\n\n";

echo "  NOTA: El contenedor auto-wire resolvio toda la cadena:\n";
echo "    ServicioUsuariosAW -> necesita RepositorioUsuariosAW y InterfazLogger\n";
echo "    RepositorioUsuariosAW -> necesita ConexionBD\n";
echo "    ConexionBD -> sin dependencias\n";
echo "    InterfazLogger -> vinculado a LoggerConsola\n";
echo "    Todo se resolvio automaticamente via Reflection.\n\n";


// =============================================================================
// Ejemplo 5: DI vs Service Locator (Comparacion)
// =============================================================================
// Problema: El Service Locator es una alternativa a DI, pero tiene
// desventajas importantes. Veamos la comparacion.

echo "=== Ejemplo 5: Inyeccion de Dependencias vs Service Locator ===\n\n";

// --- SERVICE LOCATOR (Anti-patron en muchos contextos) ---

/**
 * El Service Locator es un registro global que los objetos consultan
 * para obtener sus dependencias. A primera vista parece conveniente,
 * pero tiene problemas serios.
 */
class LocalizadorServicios
{
    private static array $servicios = [];

    public static function registrar(string $nombre, object $servicio): void
    {
        self::$servicios[$nombre] = $servicio;
    }

    public static function obtener(string $nombre): object
    {
        if (!isset(self::$servicios[$nombre])) {
            throw new \RuntimeException("Servicio no encontrado: {$nombre}");
        }
        return self::$servicios[$nombre];
    }

    public static function reiniciar(): void
    {
        self::$servicios = [];
    }
}

// Servicio que USA Service Locator (MAL)
class ServicioPedidosSL
{
    // No declara sus dependencias, las busca internamente
    public function crearPedido(string $clienteEmail, float $total): array
    {
        // Las dependencias estan OCULTAS - no se ven en la firma
        $logger = LocalizadorServicios::obtener('logger');
        $correo = LocalizadorServicios::obtener('correo');

        $logger->info("Creando pedido para {$clienteEmail}");
        $correo->enviar($clienteEmail, 'Pedido Confirmado', "Total: \${$total}");

        return ['id' => rand(1000, 9999), 'email' => $clienteEmail, 'total' => $total];
    }
}

// Servicio que USA Inyeccion de Dependencias (BIEN)
class ServicioPedidosDI
{
    // Las dependencias son EXPLICITAS en el constructor
    public function __construct(
        private InterfazLogger $logger,
        private InterfazClienteCorreo $correo
    ) {}

    public function crearPedido(string $clienteEmail, float $total): array
    {
        $this->logger->info("Creando pedido para {$clienteEmail}");
        $this->correo->enviar($clienteEmail, 'Pedido Confirmado', "Total: \${$total}");

        return ['id' => rand(1000, 9999), 'email' => $clienteEmail, 'total' => $total];
    }
}

// --- Demostrar Service Locator ---
echo "  --- Service Locator (anti-patron) ---\n";

// Configurar el localizador
LocalizadorServicios::registrar('logger', new LoggerConsola());
LocalizadorServicios::registrar('correo', new ClienteCorreoFalso());

$servSL = new ServicioPedidosSL();
// Problema: mirando solo "new ServicioPedidosSL()" no sabemos que necesita
$pedido = $servSL->crearPedido('juan@email.com', 599.99);
echo "    Pedido creado: #{$pedido['id']}\n\n";

// --- Demostrar Inyeccion de Dependencias ---
echo "  --- Inyeccion de Dependencias (patron correcto) ---\n";

$servDI = new ServicioPedidosDI(
    new LoggerConsola(),       // Explicito: necesita un logger
    new ClienteCorreoFalso()   // Explicito: necesita un cliente de correo
);
// Claro: sabemos exactamente que dependencias tiene
$pedido = $servDI->crearPedido('maria@email.com', 1299.99);
echo "    Pedido creado: #{$pedido['id']}\n\n";

// Limpiar estado global del Service Locator
LocalizadorServicios::reiniciar();

// --- Tabla comparativa ---
echo "  COMPARACION: DI vs Service Locator\n";
echo "  " . str_repeat('=', 65) . "\n";
echo "  | Aspecto                  | Service Locator | Dep. Injection  |\n";
echo "  " . str_repeat('-', 65) . "\n";
echo "  | Dependencias visibles    | NO (ocultas)    | SI (constructor)|\n";
echo "  | Facil de probar          | Dificil         | Facil (mocks)   |\n";
echo "  | Estado global            | SI (problemas)  | NO              |\n";
echo "  | Detectar errores         | En runtime      | En compilacion* |\n";
echo "  | Acoplamiento             | Alto (al SL)    | Bajo (interfaz) |\n";
echo "  | Refactorizar             | Riesgoso        | Seguro          |\n";
echo "  " . str_repeat('-', 65) . "\n";
echo "  * Con herramientas de analisis estatico como PHPStan/Psalm\n\n";

echo "  PROBLEMAS DEL SERVICE LOCATOR:\n";
echo "  1. Dependencias ocultas: al leer 'new ServicioPedidosSL()'\n";
echo "     no se sabe que necesita logger y correo. Hay que leer\n";
echo "     todo el codigo fuente para descubrirlo.\n\n";
echo "  2. Pruebas fragiles: hay que configurar el Service Locator\n";
echo "     global antes de cada prueba y limpiarlo despues.\n\n";
echo "  3. Errores en runtime: si olvidas registrar un servicio,\n";
echo "     el error solo aparece cuando se ejecuta esa linea.\n\n";
echo "  4. Estado global mutable: cualquier parte del codigo puede\n";
echo "     cambiar los servicios registrados, causando bugs sutiles.\n\n";

echo "  RECOMENDACION FINAL:\n";
echo "  Usar siempre Inyeccion de Dependencias (preferiblemente por\n";
echo "  constructor) junto con un contenedor DI que resuelva el\n";
echo "  grafo de dependencias automaticamente.\n";

?>
