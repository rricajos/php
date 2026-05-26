<?php
// ============================================
// TRAITS EN PHP - Declaración, uso y resolución de conflictos
// ============================================

// --- Ejemplo 1: Trait básico - reutilización de código ---
// Los traits permiten compartir métodos entre clases sin herencia

trait Timestamps {
    private string $creadoEn;
    private string $actualizadoEn;

    public function establecerCreadoEn(): void {
        $this->creadoEn = date('Y-m-d H:i:s');
    }

    public function establecerActualizadoEn(): void {
        $this->actualizadoEn = date('Y-m-d H:i:s');
    }

    public function obtenerCreadoEn(): string {
        return $this->creadoEn ?? 'No establecido';
    }

    public function obtenerActualizadoEn(): string {
        return $this->actualizadoEn ?? 'No establecido';
    }
}

trait SoftDelete {
    private ?string $eliminadoEn = null;

    public function eliminarSuave(): void {
        $this->eliminadoEn = date('Y-m-d H:i:s');
    }

    public function restaurar(): void {
        $this->eliminadoEn = null;
    }

    public function estaEliminado(): bool {
        return $this->eliminadoEn !== null;
    }
}

// Usamos los traits con la palabra clave 'use' dentro de la clase
class Articulo {
    use Timestamps, SoftDelete;

    public function __construct(
        private string $titulo,
        private string $contenido
    ) {
        $this->establecerCreadoEn();
    }

    public function mostrar(): string {
        $estado = $this->estaEliminado() ? " [ELIMINADO]" : "";
        return "{$this->titulo}{$estado} - Creado: {$this->obtenerCreadoEn()}";
    }
}

$articulo = new Articulo("Introducción a PHP", "PHP es un lenguaje...");
echo $articulo->mostrar() . "\n";
// Introducción a PHP - Creado: 2024-01-15 10:30:00

$articulo->eliminarSuave();
echo $articulo->mostrar() . "\n";
// Introducción a PHP [ELIMINADO] - Creado: 2024-01-15 10:30:00

$articulo->restaurar();
echo "¿Eliminado? " . ($articulo->estaEliminado() ? "Sí" : "No") . "\n"; // No


// --- Ejemplo 2: Trait con métodos abstractos ---
// Un trait puede exigir que la clase implemente ciertos métodos

trait Logeable {
    // La clase que use este trait DEBE implementar obtenerIdentificador()
    abstract protected function obtenerIdentificador(): string;

    public function registrarAccion(string $accion): string {
        $fecha = date('Y-m-d H:i:s');
        $id = $this->obtenerIdentificador();
        return "[{$fecha}] [{$id}] {$accion}";
    }

    public function registrarError(string $error): string {
        $fecha = date('Y-m-d H:i:s');
        $id = $this->obtenerIdentificador();
        return "[{$fecha}] [ERROR] [{$id}] {$error}";
    }
}

class Empleado {
    use Logeable;

    public function __construct(
        private int $id,
        private string $nombre
    ) {}

    // Implementación obligatoria del método abstracto del trait
    protected function obtenerIdentificador(): string {
        return "EMP-{$this->id}";
    }
}

class Servidor {
    use Logeable;

    public function __construct(
        private string $hostname
    ) {}

    protected function obtenerIdentificador(): string {
        return "SRV-{$this->hostname}";
    }
}

$empleado = new Empleado(42, "Roberto");
echo $empleado->registrarAccion("Inicio de sesión") . "\n";
// [2024-01-15 10:30:00] [EMP-42] Inicio de sesión

$servidor = new Servidor("web-prod-01");
echo $servidor->registrarError("Disco al 95% de capacidad") . "\n";
// [2024-01-15 10:30:00] [ERROR] [SRV-web-prod-01] Disco al 95% de capacidad


// --- Ejemplo 3: Resolución de conflictos entre traits ---
// Si dos traits tienen un método con el mismo nombre, debemos resolver el conflicto

trait FormatoJSON {
    public function exportar(): string {
        return json_encode($this->obtenerDatos(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function tipo(): string {
        return "JSON";
    }
}

trait FormatoXML {
    public function exportar(): string {
        $datos = $this->obtenerDatos();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<registro>\n";
        foreach ($datos as $clave => $valor) {
            $xml .= "  <{$clave}>{$valor}</{$clave}>\n";
        }
        $xml .= "</registro>";
        return $xml;
    }

    public function tipo(): string {
        return "XML";
    }
}

class Reporte {
    // Resolvemos conflictos con 'insteadof' y creamos alias con 'as'
    use FormatoJSON, FormatoXML {
        // Usamos exportar() de FormatoJSON en lugar de FormatoXML
        FormatoJSON::exportar insteadof FormatoXML;
        // Creamos un alias para el método de FormatoXML
        FormatoXML::exportar as exportarXML;

        // Lo mismo para tipo()
        FormatoJSON::tipo insteadof FormatoXML;
        FormatoXML::tipo as tipoXML;
    }

    private string $titulo;
    private array $items;

    public function __construct(string $titulo, array $items) {
        $this->titulo = $titulo;
        $this->items = $items;
    }

    public function obtenerDatos(): array {
        return [
            'titulo' => $this->titulo,
            'total_items' => count($this->items),
            'items' => implode(', ', $this->items),
        ];
    }
}

$reporte = new Reporte("Ventas Mensuales", ["Producto A", "Producto B", "Producto C"]);

echo "=== Formato por defecto (JSON) ===\n";
echo $reporte->exportar() . "\n";
// { "titulo": "Ventas Mensuales", "total_items": 3, "items": "..." }

echo "\n=== Formato alternativo (XML) ===\n";
echo $reporte->exportarXML() . "\n";
// <?xml version="1.0"...> <registro> <titulo>Ventas Mensuales</titulo> ...

echo "Tipo principal: " . $reporte->tipo() . "\n";     // JSON
echo "Tipo alternativo: " . $reporte->tipoXML() . "\n"; // XML


// --- Ejemplo 4: Trait con propiedades y constantes (PHP 8.2+) ---
// Los traits pueden contener propiedades y, desde PHP 8.2, constantes

trait Configurable {
    private array $configuracion = [];

    public function establecerConfig(string $clave, mixed $valor): void {
        $this->configuracion[$clave] = $valor;
    }

    public function obtenerConfig(string $clave, mixed $defecto = null): mixed {
        return $this->configuracion[$clave] ?? $defecto;
    }

    public function tieneConfig(string $clave): bool {
        return array_key_exists($clave, $this->configuracion);
    }

    public function todasLasConfig(): array {
        return $this->configuracion;
    }
}

trait Cacheable {
    private array $cache = [];
    private int $tiempoVida = 3600; // segundos

    public function guardarEnCache(string $clave, mixed $valor): void {
        $this->cache[$clave] = [
            'valor' => $valor,
            'expira' => time() + $this->tiempoVida,
        ];
    }

    public function obtenerDeCache(string $clave): mixed {
        if (!isset($this->cache[$clave])) {
            return null;
        }
        if (time() > $this->cache[$clave]['expira']) {
            unset($this->cache[$clave]);
            return null;
        }
        return $this->cache[$clave]['valor'];
    }

    public function limpiarCache(): void {
        $this->cache = [];
    }
}

class ServicioAPI {
    use Configurable, Cacheable;

    public function __construct(string $urlBase) {
        $this->establecerConfig('url_base', $urlBase);
        $this->establecerConfig('timeout', 30);
        $this->establecerConfig('reintentos', 3);
    }

    public function llamar(string $endpoint): string {
        $cacheKey = "api_{$endpoint}";
        $cached = $this->obtenerDeCache($cacheKey);

        if ($cached !== null) {
            return "CACHE: {$cached}";
        }

        // Simulamos la llamada a la API
        $url = $this->obtenerConfig('url_base') . $endpoint;
        $resultado = "Respuesta de {$url}";

        $this->guardarEnCache($cacheKey, $resultado);
        return $resultado;
    }
}

$api = new ServicioAPI("https://api.ejemplo.com");
echo $api->llamar("/usuarios") . "\n";
// Respuesta de https://api.ejemplo.com/usuarios

echo $api->llamar("/usuarios") . "\n";
// CACHE: Respuesta de https://api.ejemplo.com/usuarios

print_r($api->todasLasConfig());
// Array ( [url_base] => https://api.ejemplo.com [timeout] => 30 [reintentos] => 3 )

?>
