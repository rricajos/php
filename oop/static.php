<?php
// ============================================
// PROPIEDADES Y MÉTODOS ESTÁTICOS EN PHP
// static, self::, static::, late static binding
// ============================================

// --- Ejemplo 1: Propiedades y métodos estáticos básicos ---
// Los miembros estáticos pertenecen a la clase, no a las instancias

class Contador {
    // Propiedad estática: compartida entre todas las instancias
    private static int $cuenta = 0;

    public function __construct() {
        // self:: accede a miembros estáticos de la clase actual
        self::$cuenta++;
    }

    // Método estático: se llama sin necesidad de instanciar la clase
    public static function obtenerCuenta(): int {
        return self::$cuenta;
    }

    public static function reiniciar(): void {
        self::$cuenta = 0;
    }
}

// Los métodos estáticos se llaman con :: en lugar de ->
echo "Conteo inicial: " . Contador::obtenerCuenta() . "\n"; // 0

$a = new Contador();
$b = new Contador();
$c = new Contador();

// La propiedad estática es compartida
echo "Conteo después de 3 instancias: " . Contador::obtenerCuenta() . "\n"; // 3

Contador::reiniciar();
echo "Conteo después de reiniciar: " . Contador::obtenerCuenta() . "\n"; // 0


// --- Ejemplo 2: Patrón Singleton con static ---
// Un Singleton garantiza que solo exista una instancia de la clase

class ConfiguracionApp {
    private static ?ConfiguracionApp $instancia = null;
    private array $ajustes = [];

    // Constructor privado: no se puede instanciar desde fuera
    private function __construct() {
        // Cargar configuración por defecto
        $this->ajustes = [
            'app_nombre' => 'Mi Aplicación',
            'version' => '1.0.0',
            'debug' => false,
            'idioma' => 'es',
        ];
    }

    // Método estático para obtener la única instancia
    public static function obtenerInstancia(): self {
        if (self::$instancia === null) {
            self::$instancia = new self();
            echo "Instancia de configuración creada\n";
        }
        return self::$instancia;
    }

    public function obtener(string $clave): mixed {
        return $this->ajustes[$clave] ?? null;
    }

    public function establecer(string $clave, mixed $valor): void {
        $this->ajustes[$clave] = $valor;
    }

    // Prevenimos la clonación
    private function __clone() {}
}

// Siempre obtenemos la misma instancia
$config1 = ConfiguracionApp::obtenerInstancia(); // Instancia creada
$config1->establecer('debug', true);

$config2 = ConfiguracionApp::obtenerInstancia(); // NO crea nueva instancia
echo "Debug: " . ($config2->obtener('debug') ? 'true' : 'false') . "\n"; // true

// Ambas variables apuntan al mismo objeto
echo ($config1 === $config2) ? "Son la misma instancia\n" : "Son diferentes\n";
// Son la misma instancia


// --- Ejemplo 3: self:: vs static:: (Late Static Binding) ---
// self:: siempre se refiere a la clase donde se definió el método
// static:: se refiere a la clase que realmente se está usando (enlace tardío)

class Modelo {
    protected static string $tabla = "modelos";

    // self:: siempre retorna "modelos", sin importar quién lo llame
    public static function obtenerTablaSelf(): string {
        return "Tabla (self): " . self::$tabla;
    }

    // static:: retorna la tabla de la clase que realmente se está usando
    public static function obtenerTablaStatic(): string {
        return "Tabla (static): " . static::$tabla;
    }

    // Patrón Factory con late static binding
    public static function crear(): static {
        echo "Creando instancia de: " . static::class . "\n";
        return new static();
    }

    public function info(): string {
        return "Soy una instancia de " . static::class . " usando tabla '" . static::$tabla . "'";
    }
}

class UsuarioModelo extends Modelo {
    protected static string $tabla = "usuarios";
}

class ProductoModelo extends Modelo {
    protected static string $tabla = "productos";
}

// Demostración de la diferencia entre self:: y static::
echo UsuarioModelo::obtenerTablaSelf() . "\n";
// Tabla (self): modelos  ← self:: resuelve en Modelo, donde se definió

echo UsuarioModelo::obtenerTablaStatic() . "\n";
// Tabla (static): usuarios  ← static:: resuelve en UsuarioModelo

echo ProductoModelo::obtenerTablaStatic() . "\n";
// Tabla (static): productos

// Factory con late static binding
$usuario = UsuarioModelo::crear();   // Creando instancia de: UsuarioModelo
$producto = ProductoModelo::crear(); // Creando instancia de: ProductoModelo

echo $usuario->info() . "\n";  // Soy una instancia de UsuarioModelo usando tabla 'usuarios'
echo $producto->info() . "\n"; // Soy una instancia de ProductoModelo usando tabla 'productos'


// --- Ejemplo 4: Métodos estáticos como utilidades y constructores nombrados ---
// Los métodos estáticos son ideales para funciones auxiliares y factories

class Moneda {
    private function __construct(
        private float $cantidad,
        private string $codigo
    ) {}

    // Constructores nombrados (Named Constructors) usando métodos estáticos
    public static function enDolares(float $cantidad): static {
        return new static($cantidad, 'USD');
    }

    public static function enEuros(float $cantidad): static {
        return new static($cantidad, 'EUR');
    }

    public static function enPesos(float $cantidad): static {
        return new static($cantidad, 'MXN');
    }

    public static function desdeCadena(string $cadena): static {
        // Formato esperado: "100.00 USD"
        $partes = explode(' ', trim($cadena));
        if (count($partes) !== 2) {
            throw new InvalidArgumentException("Formato inválido: use '100.00 USD'");
        }
        return new static((float) $partes[0], strtoupper($partes[1]));
    }

    // Método estático de utilidad para sumar monedas del mismo tipo
    public static function sumar(Moneda $a, Moneda $b): static {
        if ($a->codigo !== $b->codigo) {
            throw new InvalidArgumentException("No se pueden sumar monedas diferentes");
        }
        return new static($a->cantidad + $b->cantidad, $a->codigo);
    }

    public function formatear(): string {
        $simbolos = ['USD' => '$', 'EUR' => "\u{20AC}", 'MXN' => 'MX$'];
        $simbolo = $simbolos[$this->codigo] ?? $this->codigo;
        return $simbolo . number_format($this->cantidad, 2);
    }

    public function __toString(): string {
        return $this->formatear();
    }
}

// Constructores nombrados son más expresivos que new Moneda(100, 'USD')
$precio = Moneda::enDolares(99.99);
$impuesto = Moneda::enDolares(16.00);
$total = Moneda::sumar($precio, $impuesto);

echo "Precio: {$precio}\n";     // Precio: $99.99
echo "Impuesto: {$impuesto}\n"; // Impuesto: $16.00
echo "Total: {$total}\n";       // Total: $115.99

$euroMonto = Moneda::enEuros(250.00);
echo "En euros: {$euroMonto}\n"; // En euros: €250.00

$desdeTexto = Moneda::desdeCadena("1500.00 MXN");
echo "Desde cadena: {$desdeTexto}\n"; // Desde cadena: MX$1,500.00

?>
