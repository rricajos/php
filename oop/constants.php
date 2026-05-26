<?php
// ============================================
// CONSTANTES DE CLASE EN PHP
// const, acceso con ::, patrones tipo enum
// ============================================

// --- Ejemplo 1: Constantes de clase básicas ---
// Las constantes de clase son valores fijos que no cambian

class Matematicas {
    // Constantes declaradas con 'const'
    const PI = 3.14159265358979;
    const E = 2.71828182845905;
    const RAIZ_2 = 1.41421356237310;
    const GRADOS_CIRCULO = 360;

    public static function areaCirculo(float $radio): float {
        // Acceso interno con self::
        return self::PI * ($radio ** 2);
    }

    public static function circunferencia(float $radio): float {
        return 2 * self::PI * $radio;
    }

    public static function gradosARadianes(float $grados): float {
        return $grados * (self::PI / 180);
    }
}

// Acceso externo con NombreClase::CONSTANTE
echo "PI = " . Matematicas::PI . "\n";
echo "Área de círculo (r=5): " . Matematicas::areaCirculo(5) . "\n";
echo "90° en radianes: " . Matematicas::gradosARadianes(90) . "\n";


// --- Ejemplo 2: Constantes como configuración inmutable ---
// Ideal para valores de configuración que no deben cambiar en tiempo de ejecución

class HttpStatus {
    // Códigos de estado HTTP como constantes
    const OK = 200;
    const CREATED = 201;
    const NO_CONTENT = 204;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const INTERNAL_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;

    // Constante con arreglo (permitido desde PHP 5.6)
    const MENSAJES = [
        200 => 'OK',
        201 => 'Creado',
        204 => 'Sin contenido',
        400 => 'Solicitud incorrecta',
        401 => 'No autorizado',
        403 => 'Prohibido',
        404 => 'No encontrado',
        500 => 'Error interno del servidor',
        503 => 'Servicio no disponible',
    ];

    public static function obtenerMensaje(int $codigo): string {
        return self::MENSAJES[$codigo] ?? 'Código desconocido';
    }

    public static function esExitoso(int $codigo): bool {
        return $codigo >= 200 && $codigo < 300;
    }

    public static function esError(int $codigo): bool {
        return $codigo >= 400;
    }
}

$codigo = HttpStatus::NOT_FOUND;
echo "Código: {$codigo} - " . HttpStatus::obtenerMensaje($codigo) . "\n";
// Código: 404 - No encontrado

echo "¿Es exitoso? " . (HttpStatus::esExitoso(200) ? "Sí" : "No") . "\n"; // Sí
echo "¿Es error? " . (HttpStatus::esError(500) ? "Sí" : "No") . "\n";     // Sí


// --- Ejemplo 3: Patrón tipo Enum con constantes (pre-PHP 8.1) ---
// Antes de los enums nativos, se usaban constantes para simular enumeraciones

class EstadoPedido {
    const PENDIENTE = 'pendiente';
    const CONFIRMADO = 'confirmado';
    const EN_PREPARACION = 'en_preparacion';
    const ENVIADO = 'enviado';
    const ENTREGADO = 'entregado';
    const CANCELADO = 'cancelado';

    // Lista de todos los estados válidos
    const TODOS = [
        self::PENDIENTE,
        self::CONFIRMADO,
        self::EN_PREPARACION,
        self::ENVIADO,
        self::ENTREGADO,
        self::CANCELADO,
    ];

    // Transiciones permitidas: estado actual => [estados siguientes]
    const TRANSICIONES = [
        self::PENDIENTE => [self::CONFIRMADO, self::CANCELADO],
        self::CONFIRMADO => [self::EN_PREPARACION, self::CANCELADO],
        self::EN_PREPARACION => [self::ENVIADO, self::CANCELADO],
        self::ENVIADO => [self::ENTREGADO],
        self::ENTREGADO => [],
        self::CANCELADO => [],
    ];

    const ETIQUETAS = [
        self::PENDIENTE => 'Pendiente',
        self::CONFIRMADO => 'Confirmado',
        self::EN_PREPARACION => 'En preparación',
        self::ENVIADO => 'Enviado',
        self::ENTREGADO => 'Entregado',
        self::CANCELADO => 'Cancelado',
    ];

    public static function esValido(string $estado): bool {
        return in_array($estado, self::TODOS, true);
    }

    public static function puedeTransicionar(string $actual, string $nuevo): bool {
        if (!isset(self::TRANSICIONES[$actual])) {
            return false;
        }
        return in_array($nuevo, self::TRANSICIONES[$actual], true);
    }

    public static function obtenerEtiqueta(string $estado): string {
        return self::ETIQUETAS[$estado] ?? 'Desconocido';
    }
}

// Uso del patrón tipo enum
class Pedido {
    private string $estado;

    public function __construct(private int $id) {
        $this->estado = EstadoPedido::PENDIENTE;
    }

    public function cambiarEstado(string $nuevoEstado): bool {
        if (!EstadoPedido::esValido($nuevoEstado)) {
            echo "Estado '{$nuevoEstado}' no es válido\n";
            return false;
        }

        if (!EstadoPedido::puedeTransicionar($this->estado, $nuevoEstado)) {
            $actual = EstadoPedido::obtenerEtiqueta($this->estado);
            $nuevo = EstadoPedido::obtenerEtiqueta($nuevoEstado);
            echo "No se puede cambiar de '{$actual}' a '{$nuevo}'\n";
            return false;
        }

        $anterior = EstadoPedido::obtenerEtiqueta($this->estado);
        $this->estado = $nuevoEstado;
        $nuevo = EstadoPedido::obtenerEtiqueta($this->estado);
        echo "Pedido #{$this->id}: {$anterior} → {$nuevo}\n";
        return true;
    }
}

$pedido = new Pedido(1001);
$pedido->cambiarEstado(EstadoPedido::CONFIRMADO);     // Pedido #1001: Pendiente → Confirmado
$pedido->cambiarEstado(EstadoPedido::EN_PREPARACION);  // Pedido #1001: Confirmado → En preparación
$pedido->cambiarEstado(EstadoPedido::PENDIENTE);        // No se puede cambiar de 'En preparación' a 'Pendiente'
$pedido->cambiarEstado(EstadoPedido::ENVIADO);          // Pedido #1001: En preparación → Enviado


// --- Ejemplo 4: Constantes con herencia y visibilidad ---
// Las constantes pueden ser sobrescritas en clases hijas

class ConfigBase {
    const APP_NOMBRE = "Mi App";
    const MAX_INTENTOS = 3;
    const TIMEOUT = 30;

    // Desde PHP 8.1, las constantes pueden tener visibilidad
    public const PUBLICO = "visible para todos";
    protected const PROTEGIDO = "visible para hijos";
    private const PRIVADO = "solo en esta clase";

    public function mostrarConfig(): void {
        echo "App: " . static::APP_NOMBRE . "\n";
        echo "Max intentos: " . static::MAX_INTENTOS . "\n";
        echo "Timeout: " . static::TIMEOUT . "s\n";
    }

    public function mostrarTodo(): void {
        echo self::PUBLICO . "\n";
        echo self::PROTEGIDO . "\n";
        echo self::PRIVADO . "\n";
    }
}

class ConfigProduccion extends ConfigBase {
    // Sobreescribimos constantes del padre
    const MAX_INTENTOS = 5;
    const TIMEOUT = 60;

    public function mostrarProtegido(): void {
        // Acceso a constante protegida del padre: OK
        echo "Constante protegida: " . parent::PROTEGIDO . "\n";
    }
}

$base = new ConfigBase();
$prod = new ConfigProduccion();

echo "=== Config Base ===\n";
$base->mostrarConfig();
// App: Mi App | Max intentos: 3 | Timeout: 30s

echo "\n=== Config Producción ===\n";
$prod->mostrarConfig();
// App: Mi App | Max intentos: 5 | Timeout: 60s

// Usando static:: en lugar de self:: permite obtener el valor sobrescrito
echo "\nTimeout base: " . ConfigBase::TIMEOUT . "\n";      // 30
echo "Timeout producción: " . ConfigProduccion::TIMEOUT . "\n"; // 60

?>
