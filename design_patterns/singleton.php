<?php
/**
 * =============================================================================
 * PATRON SINGLETON EN PHP
 * =============================================================================
 *
 * El patron Singleton garantiza que una clase tenga una unica instancia
 * y proporciona un punto de acceso global a ella.
 *
 * Casos de uso comunes:
 * - Conexiones a bases de datos
 * - Sistemas de configuracion
 * - Registros de logs
 * - Caches en memoria
 *
 * ADVERTENCIA: Singleton es considerado un anti-patron en muchos contextos.
 * Dificulta las pruebas unitarias, oculta dependencias y acopla el codigo.
 * Considerar inyeccion de dependencias como alternativa mas limpia.
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Singleton Clasico con Constructor Privado
// =============================================================================
// Problema: Multiples instancias de un recurso costoso desperdician memoria
// y pueden causar inconsistencias (ej: varias conexiones a BD innecesarias).

echo "=== Ejemplo 1: Singleton Clasico ===\n\n";

class ConfiguracionApp
{
    // La unica instancia de la clase
    private static ?ConfiguracionApp $instancia = null;

    // Almacen de configuraciones
    private array $ajustes = [];

    // Constructor privado: impide crear instancias con 'new'
    private function __construct()
    {
        // Simulamos cargar configuracion desde un archivo
        $this->ajustes = [
            'app_nombre'  => 'Mi Aplicacion',
            'version'     => '2.1.0',
            'debug'       => true,
            'idioma'      => 'es',
            'zona_horaria' => 'America/Mexico_City',
        ];
        echo "  [ConfiguracionApp] Configuracion cargada (esto solo debe ocurrir UNA vez).\n";
    }

    // Prevenir la clonacion del objeto
    private function __clone(): void
    {
        // Lanzar excepcion si alguien intenta clonar
    }

    // Prevenir la deserializacion (unserialize)
    public function __wakeup(): void
    {
        throw new \Exception("No se puede deserializar un Singleton.");
    }

    // Punto de acceso global a la instancia unica
    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function obtener(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->ajustes[$clave] ?? $predeterminado;
    }

    public function establecer(string $clave, mixed $valor): void
    {
        $this->ajustes[$clave] = $valor;
    }

    public function todos(): array
    {
        return $this->ajustes;
    }
}

// Uso: ambas variables apuntan a la MISMA instancia
$config1 = ConfiguracionApp::obtenerInstancia();
$config2 = ConfiguracionApp::obtenerInstancia();

// El constructor solo se ejecuto una vez
echo "  Nombre app: " . $config1->obtener('app_nombre') . "\n";

$config1->establecer('debug', false);
echo "  Debug desde config2: " . ($config2->obtener('debug') ? 'true' : 'false') . "\n";

// Verificar que son la misma instancia
echo "  Misma instancia: " . ($config1 === $config2 ? 'SI' : 'NO') . "\n\n";


// =============================================================================
// Ejemplo 2: Singleton para Conexion a Base de Datos (caso practico)
// =============================================================================
// Problema: Abrir multiples conexiones a la BD en una misma peticion
// consume recursos del servidor y puede agotar el pool de conexiones.

echo "=== Ejemplo 2: Conexion a Base de Datos Singleton ===\n\n";

class ConexionBaseDatos
{
    private static ?ConexionBaseDatos $instancia = null;

    // En produccion, esto seria un objeto PDO real
    private ?string $conexion = null;
    private int $contadorConsultas = 0;
    private array $registroConsultas = [];

    private string $host;
    private string $baseDatos;
    private string $usuario;

    private function __construct(string $host, string $baseDatos, string $usuario, string $contrasena)
    {
        $this->host = $host;
        $this->baseDatos = $baseDatos;
        $this->usuario = $usuario;

        // Simulacion de conexion (en produccion: new PDO(...))
        $this->conexion = "conexion_activa_{$host}_{$baseDatos}";
        echo "  [BD] Conexion establecida a {$baseDatos}@{$host}\n";
    }

    private function __clone(): void {}

    public function __wakeup(): void
    {
        throw new \Exception("No se puede deserializar la conexion.");
    }

    public static function obtenerInstancia(
        string $host = 'localhost',
        string $baseDatos = 'mi_app',
        string $usuario = 'root',
        string $contrasena = ''
    ): self {
        if (self::$instancia === null) {
            self::$instancia = new self($host, $baseDatos, $usuario, $contrasena);
        }
        return self::$instancia;
    }

    // Simula ejecutar una consulta SQL
    public function consulta(string $sql, array $parametros = []): array
    {
        $this->contadorConsultas++;
        $this->registroConsultas[] = $sql;

        echo "  [BD] Ejecutando consulta #{$this->contadorConsultas}: {$sql}\n";

        // Simulacion de resultados
        return ['resultado' => 'datos_simulados', 'filas' => rand(1, 100)];
    }

    public function obtenerContadorConsultas(): int
    {
        return $this->contadorConsultas;
    }

    public function obtenerRegistroConsultas(): array
    {
        return $this->registroConsultas;
    }

    public function obtenerInfoConexion(): string
    {
        return "{$this->baseDatos}@{$this->host} (usuario: {$this->usuario})";
    }

    // Metodo para cerrar la conexion limpiamente
    public function cerrar(): void
    {
        $this->conexion = null;
        self::$instancia = null;
        echo "  [BD] Conexion cerrada.\n";
    }
}

// Distintas partes de la aplicacion comparten la misma conexion
$bd = ConexionBaseDatos::obtenerInstancia('localhost', 'tienda_online', 'admin', 'secreto');
$bd->consulta("SELECT * FROM productos WHERE activo = ?", [1]);
$bd->consulta("SELECT * FROM usuarios WHERE id = ?", [42]);

// Desde otro modulo, se obtiene la misma conexion
$bdOtroModulo = ConexionBaseDatos::obtenerInstancia();
$bdOtroModulo->consulta("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)", [42, 99.99]);

echo "  Total consultas ejecutadas: " . $bd->obtenerContadorConsultas() . "\n";
echo "  Info conexion: " . $bdOtroModulo->obtenerInfoConexion() . "\n";
$bd->cerrar();
echo "\n";


// =============================================================================
// Ejemplo 3: Singleton de Registro (Logger) con Niveles
// =============================================================================
// Problema: Queremos un unico punto centralizado para registrar mensajes
// de la aplicacion, con distintos niveles de severidad.

echo "=== Ejemplo 3: Logger Singleton con Niveles ===\n\n";

class Registro
{
    private static ?Registro $instancia = null;

    // Niveles de log de menor a mayor severidad
    public const NIVEL_DEBUG = 0;
    public const NIVEL_INFO = 1;
    public const NIVEL_ADVERTENCIA = 2;
    public const NIVEL_ERROR = 3;
    public const NIVEL_CRITICO = 4;

    private const NOMBRES_NIVEL = [
        self::NIVEL_DEBUG       => 'DEBUG',
        self::NIVEL_INFO        => 'INFO',
        self::NIVEL_ADVERTENCIA => 'ADVERTENCIA',
        self::NIVEL_ERROR       => 'ERROR',
        self::NIVEL_CRITICO     => 'CRITICO',
    ];

    private int $nivelMinimo;
    private array $entradas = [];

    private function __construct(int $nivelMinimo = self::NIVEL_DEBUG)
    {
        $this->nivelMinimo = $nivelMinimo;
    }

    private function __clone(): void {}

    public static function obtenerInstancia(int $nivelMinimo = self::NIVEL_DEBUG): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self($nivelMinimo);
        }
        return self::$instancia;
    }

    // Metodo general para registrar un mensaje
    public function registrar(int $nivel, string $mensaje, array $contexto = []): void
    {
        if ($nivel < $this->nivelMinimo) {
            return; // Ignorar mensajes por debajo del nivel minimo
        }

        $entrada = [
            'fecha'    => date('Y-m-d H:i:s'),
            'nivel'    => self::NOMBRES_NIVEL[$nivel] ?? 'DESCONOCIDO',
            'mensaje'  => $mensaje,
            'contexto' => $contexto,
        ];

        $this->entradas[] = $entrada;

        // En produccion, aqui se escribiria a un archivo o servicio externo
        $contextoStr = !empty($contexto) ? ' | ' . json_encode($contexto) : '';
        echo "  [{$entrada['fecha']}] [{$entrada['nivel']}] {$mensaje}{$contextoStr}\n";
    }

    // Metodos de conveniencia para cada nivel
    public function debug(string $mensaje, array $contexto = []): void
    {
        $this->registrar(self::NIVEL_DEBUG, $mensaje, $contexto);
    }

    public function info(string $mensaje, array $contexto = []): void
    {
        $this->registrar(self::NIVEL_INFO, $mensaje, $contexto);
    }

    public function advertencia(string $mensaje, array $contexto = []): void
    {
        $this->registrar(self::NIVEL_ADVERTENCIA, $mensaje, $contexto);
    }

    public function error(string $mensaje, array $contexto = []): void
    {
        $this->registrar(self::NIVEL_ERROR, $mensaje, $contexto);
    }

    public function critico(string $mensaje, array $contexto = []): void
    {
        $this->registrar(self::NIVEL_CRITICO, $mensaje, $contexto);
    }

    public function obtenerEntradas(): array
    {
        return $this->entradas;
    }

    // Reiniciar para permitir nuevas pruebas
    public static function reiniciar(): void
    {
        self::$instancia = null;
    }
}

$log = Registro::obtenerInstancia(Registro::NIVEL_INFO);
$log->debug("Este mensaje no aparecera (nivel inferior al minimo)");
$log->info("Aplicacion iniciada correctamente");
$log->advertencia("Uso de memoria elevado", ['memoria_mb' => 256]);
$log->error("Fallo al enviar correo", ['destinatario' => 'user@example.com']);
$log->critico("Base de datos no disponible", ['host' => 'db.servidor.com']);

echo "  Total de entradas registradas: " . count($log->obtenerEntradas()) . "\n";
Registro::reiniciar();
echo "\n";


// =============================================================================
// Ejemplo 4: Consideraciones de Seguridad en Hilos (Thread-Safety)
// =============================================================================
// Problema: En entornos multi-hilo (como con la extension parallel de PHP),
// dos hilos podrian crear instancias simultaneamente si no hay proteccion.

echo "=== Ejemplo 4: Singleton Thread-Safe (Conceptual) ===\n\n";

/**
 * NOTA IMPORTANTE SOBRE PHP Y MULTI-HILO:
 *
 * PHP tradicionalmente usa un modelo de "nada compartido" (shared-nothing),
 * donde cada peticion tiene su propio proceso. Esto hace que el Singleton
 * clasico sea seguro en la mayoria de casos web.
 *
 * Sin embargo, con extensiones como 'parallel' o 'pthreads', se necesitan
 * precauciones adicionales. El ejemplo siguiente es CONCEPTUAL para ilustrar
 * el patron de doble verificacion (double-checked locking).
 */

class CacheSingletonSeguro
{
    private static ?CacheSingletonSeguro $instancia = null;

    // En un entorno real multi-hilo, usariamos un mutex o semaforo
    // private static $mutex = null;

    private array $almacen = [];
    private int $aciertos = 0;
    private int $fallos = 0;

    private function __construct()
    {
        echo "  [Cache] Inicializada.\n";
    }

    private function __clone(): void {}

    /**
     * Implementacion con doble verificacion (double-checked locking).
     * En PHP puro no es estrictamente necesario, pero es el patron
     * correcto para lenguajes con hilos reales.
     *
     * Pseudocodigo del patron thread-safe:
     *
     *   if ($instancia === null) {         // Primera verificacion (sin bloqueo)
     *       mutex_lock($mutex);             // Adquirir bloqueo
     *       if ($instancia === null) {      // Segunda verificacion (con bloqueo)
     *           $instancia = new self();
     *       }
     *       mutex_unlock($mutex);           // Liberar bloqueo
     *   }
     */
    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            // En un entorno multi-hilo real:
            // synchronized { ... } o mutex_lock()
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function obtener(string $clave): mixed
    {
        if (array_key_exists($clave, $this->almacen)) {
            $this->aciertos++;
            return $this->almacen[$clave];
        }
        $this->fallos++;
        return null;
    }

    public function establecer(string $clave, mixed $valor, int $ttl = 3600): void
    {
        $this->almacen[$clave] = [
            'valor'      => $valor,
            'expira_en'  => time() + $ttl,
            'creado_en'  => time(),
        ];
    }

    public function eliminar(string $clave): bool
    {
        if (array_key_exists($clave, $this->almacen)) {
            unset($this->almacen[$clave]);
            return true;
        }
        return false;
    }

    public function estadisticas(): array
    {
        return [
            'elementos'  => count($this->almacen),
            'aciertos'   => $this->aciertos,
            'fallos'     => $this->fallos,
            'tasa_acierto' => $this->aciertos + $this->fallos > 0
                ? round($this->aciertos / ($this->aciertos + $this->fallos) * 100, 2) . '%'
                : '0%',
        ];
    }

    public static function reiniciar(): void
    {
        self::$instancia = null;
    }
}

$cache = CacheSingletonSeguro::obtenerInstancia();
$cache->establecer('usuario_42', ['nombre' => 'Carlos', 'rol' => 'admin']);
$cache->establecer('config_tema', 'oscuro');

echo "  Buscar usuario_42: ";
$usuario = $cache->obtener('usuario_42');
echo $usuario ? $usuario['nombre'] : 'no encontrado';
echo "\n";

$cache->obtener('pagina_inexistente'); // Fallo de cache
$cache->obtener('usuario_42');          // Acierto de cache

$stats = $cache->estadisticas();
echo "  Estadisticas: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n";
CacheSingletonSeguro::reiniciar();
echo "\n";


// =============================================================================
// Ejemplo 5: Cuando NO Usar Singleton (Discusion Anti-Patron)
// =============================================================================
// El Singleton tiene problemas serios que debemos entender antes de usarlo.

echo "=== Ejemplo 5: Singleton como Anti-Patron y Alternativas ===\n\n";

/**
 * PROBLEMAS DEL SINGLETON:
 *
 * 1. ESTADO GLOBAL OCULTO
 *    Cualquier parte del codigo puede modificar el estado del Singleton,
 *    haciendo dificil rastrear bugs y entender el flujo de datos.
 *
 * 2. DIFICULTA LAS PRUEBAS UNITARIAS
 *    No se puede inyectar un mock o stub facilmente. El estado persiste
 *    entre pruebas si no se reinicia manualmente.
 *
 * 3. VIOLA EL PRINCIPIO DE RESPONSABILIDAD UNICA
 *    La clase gestiona su propia creacion ademas de su funcionalidad.
 *
 * 4. ACOPLAMIENTO FUERTE
 *    El codigo que usa el Singleton esta acoplado a esa clase concreta.
 *    No se puede cambiar la implementacion sin modificar todos los usos.
 *
 * 5. PROBLEMAS CON HERENCIA
 *    Extender un Singleton es complicado y contra-intuitivo.
 */

// --- MAL: Uso directo de Singleton (acoplamiento fuerte) ---

class ServicioCorreoMalo
{
    public function enviar(string $destinatario, string $asunto): void
    {
        // Acoplado directamente al Singleton - dificil de probar
        $log = Registro::obtenerInstancia();
        $log->info("Enviando correo a {$destinatario}");

        $config = ConfiguracionApp::obtenerInstancia();
        $remitente = $config->obtener('correo_remitente', 'noreply@app.com');

        echo "  [MAL] Correo enviado desde {$remitente} a {$destinatario}: {$asunto}\n";
    }
}

// --- BIEN: Inyeccion de dependencias (desacoplado y testeable) ---

// Definimos interfaces para las dependencias
interface InterfazRegistro
{
    public function info(string $mensaje, array $contexto = []): void;
}

interface InterfazConfiguracion
{
    public function obtener(string $clave, mixed $predeterminado = null): mixed;
}

// Implementaciones concretas
class RegistroSimple implements InterfazRegistro
{
    public function info(string $mensaje, array $contexto = []): void
    {
        echo "  [LOG] {$mensaje}\n";
    }
}

class ConfiguracionSimple implements InterfazConfiguracion
{
    private array $datos;

    public function __construct(array $datos = [])
    {
        $this->datos = $datos;
    }

    public function obtener(string $clave, mixed $predeterminado = null): mixed
    {
        return $this->datos[$clave] ?? $predeterminado;
    }
}

// El servicio recibe sus dependencias por constructor (inyeccion)
class ServicioCorreoBueno
{
    public function __construct(
        private InterfazRegistro $registro,
        private InterfazConfiguracion $config
    ) {}

    public function enviar(string $destinatario, string $asunto): void
    {
        $this->registro->info("Enviando correo a {$destinatario}");
        $remitente = $this->config->obtener('correo_remitente', 'noreply@app.com');
        echo "  [BIEN] Correo enviado desde {$remitente} a {$destinatario}: {$asunto}\n";
    }
}

// Uso en produccion
$registro = new RegistroSimple();
$config = new ConfiguracionSimple(['correo_remitente' => 'info@mitienda.com']);
$servicioCorreo = new ServicioCorreoBueno($registro, $config);
$servicioCorreo->enviar('cliente@email.com', 'Su pedido ha sido enviado');

// Para pruebas, se pueden inyectar mocks facilmente
// $mockRegistro = new MockRegistro(); // Simula el registro sin efectos reales
// $mockConfig = new ConfiguracionSimple(['correo_remitente' => 'test@test.com']);
// $servicioCorreoPrueba = new ServicioCorreoBueno($mockRegistro, $mockConfig);

echo "\n";
echo "  CONCLUSION: Usar Singleton solo cuando sea estrictamente necesario.\n";
echo "  Preferir inyeccion de dependencias + contenedor DI para gestionar\n";
echo "  instancias unicas de forma limpia y testeable.\n";

?>
