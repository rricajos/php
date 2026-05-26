<?php
// ============================================
// VISIBILIDAD EN PHP - public, protected, private
// ============================================

// --- Ejemplo 1: Las tres visibilidades con propiedades ---
// Demostración del alcance de cada nivel de acceso

class CuentaUsuario {
    // public: accesible desde cualquier lugar
    public string $nombreUsuario;

    // protected: accesible desde la clase y sus hijas
    protected string $email;

    // private: accesible SOLO dentro de esta clase
    private string $contrasena;
    private int $intentosFallidos = 0;

    public function __construct(string $nombreUsuario, string $email, string $contrasena) {
        $this->nombreUsuario = $nombreUsuario;
        $this->email = $email;
        $this->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
    }

    // Método público: la interfaz que expone la clase al mundo exterior
    public function verificarContrasena(string $intento): bool {
        if (password_verify($intento, $this->contrasena)) {
            $this->reiniciarIntentos();
            return true;
        }
        $this->registrarIntentoFallido();
        return false;
    }

    public function obtenerIntentosFallidos(): int {
        return $this->intentosFallidos;
    }

    // Métodos privados: lógica interna que no debe exponerse
    private function registrarIntentoFallido(): void {
        $this->intentosFallidos++;
        echo "Intento fallido #{$this->intentosFallidos}\n";
    }

    private function reiniciarIntentos(): void {
        $this->intentosFallidos = 0;
    }
}

$cuenta = new CuentaUsuario("sandra", "sandra@correo.com", "clave123");

// Acceso público: funciona
echo "Usuario: {$cuenta->nombreUsuario}\n"; // sandra

// Acceso protegido desde fuera: ERROR
// echo $cuenta->email; // Fatal error: Cannot access protected property

// Acceso privado desde fuera: ERROR
// echo $cuenta->contrasena; // Fatal error: Cannot access private property

// Interactuamos mediante la interfaz pública
$cuenta->verificarContrasena("incorrecta"); // Intento fallido #1
$cuenta->verificarContrasena("incorrecta"); // Intento fallido #2

echo "Intentos fallidos: " . $cuenta->obtenerIntentosFallidos() . "\n"; // 2


// --- Ejemplo 2: protected vs private en herencia ---
// protected es accesible en clases hijas, private no

class Vehiculo {
    public string $marca;
    protected int $velocidadMaxima;
    private string $numeroSerie;

    public function __construct(string $marca, int $velocidadMaxima, string $numeroSerie) {
        $this->marca = $marca;
        $this->velocidadMaxima = $velocidadMaxima;
        $this->numeroSerie = $numeroSerie;
    }

    public function obtenerNumeroSerie(): string {
        return $this->numeroSerie;
    }

    private function generarCodigoInterno(): string {
        return substr(md5($this->numeroSerie), 0, 8);
    }

    protected function validarVelocidad(int $velocidad): bool {
        return $velocidad >= 0 && $velocidad <= $this->velocidadMaxima;
    }
}

class Automovil extends Vehiculo {
    private int $velocidadActual = 0;

    public function acelerar(int $velocidad): string {
        // Acceso a miembro protected del padre: OK
        if ($this->validarVelocidad($velocidad)) {
            $this->velocidadActual = $velocidad;
            return "{$this->marca}: Acelerando a {$velocidad}/{$this->velocidadMaxima} km/h";
        }

        // $this->velocidadMaxima es protected: accesible aquí
        return "{$this->marca}: Velocidad máxima es {$this->velocidadMaxima} km/h";
    }

    public function info(): string {
        // $this->marca → public: OK
        // $this->velocidadMaxima → protected: OK
        // $this->numeroSerie → private: ERROR, no accesible
        // $this->generarCodigoInterno() → private: ERROR, no accesible

        // Para acceder al número de serie, usamos el getter público
        $serie = $this->obtenerNumeroSerie();
        return "{$this->marca} (Serie: {$serie}) - Máx: {$this->velocidadMaxima} km/h";
    }
}

$auto = new Automovil("Toyota", 200, "ABC-12345");
echo $auto->acelerar(120) . "\n"; // Toyota: Acelerando a 120/200 km/h
echo $auto->acelerar(250) . "\n"; // Toyota: Velocidad máxima es 200 km/h
echo $auto->info() . "\n";


// --- Ejemplo 3: Visibilidad en métodos - encapsulamiento práctico ---
// La encapsulación protege la integridad de los datos

class CarritoCompras {
    private array $items = [];
    private float $descuento = 0.0;

    // API pública: métodos que los consumidores pueden usar
    public function agregarProducto(string $nombre, float $precio, int $cantidad = 1): void {
        $this->validarPrecio($precio);
        $this->validarCantidad($cantidad);

        $clave = $this->generarClave($nombre);
        if (isset($this->items[$clave])) {
            $this->items[$clave]['cantidad'] += $cantidad;
        } else {
            $this->items[$clave] = [
                'nombre' => $nombre,
                'precio' => $precio,
                'cantidad' => $cantidad,
            ];
        }
    }

    public function aplicarDescuento(float $porcentaje): void {
        if ($porcentaje < 0 || $porcentaje > 50) {
            throw new InvalidArgumentException("El descuento debe estar entre 0% y 50%");
        }
        $this->descuento = $porcentaje / 100;
    }

    public function obtenerTotal(): float {
        $subtotal = $this->calcularSubtotal();
        $montoDescuento = $this->calcularDescuento($subtotal);
        return round($subtotal - $montoDescuento, 2);
    }

    public function mostrarResumen(): string {
        $lineas = ["=== Carrito de Compras ==="];
        foreach ($this->items as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $lineas[] = "  {$item['nombre']} x{$item['cantidad']} = \${$subtotal}";
        }
        $lineas[] = "Subtotal: \$" . $this->calcularSubtotal();
        if ($this->descuento > 0) {
            $lineas[] = "Descuento: " . ($this->descuento * 100) . "%";
        }
        $lineas[] = "TOTAL: \$" . $this->obtenerTotal();
        return implode("\n", $lineas);
    }

    // Métodos protegidos: podrían ser útiles para subclases
    protected function calcularSubtotal(): float {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return round($total, 2);
    }

    protected function calcularDescuento(float $subtotal): float {
        return round($subtotal * $this->descuento, 2);
    }

    // Métodos privados: lógica interna, detalles de implementación
    private function generarClave(string $nombre): string {
        return strtolower(str_replace(' ', '_', $nombre));
    }

    private function validarPrecio(float $precio): void {
        if ($precio <= 0) {
            throw new InvalidArgumentException("El precio debe ser mayor a cero");
        }
    }

    private function validarCantidad(int $cantidad): void {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException("La cantidad debe ser mayor a cero");
        }
    }
}

$carrito = new CarritoCompras();
$carrito->agregarProducto("Camiseta", 29.99, 2);
$carrito->agregarProducto("Pantalón", 49.99);
$carrito->agregarProducto("Zapatos", 89.99);
$carrito->aplicarDescuento(10);

echo $carrito->mostrarResumen() . "\n";
// === Carrito de Compras ===
//   Camiseta x2 = $59.98
//   Pantalón x1 = $49.99
//   Zapatos x1 = $89.99
// Subtotal: $199.96
// Descuento: 10%
// TOTAL: $179.96


// --- Ejemplo 4: Visibilidad con constantes y readonly ---
// Desde PHP 8.1, las constantes de clase pueden tener visibilidad

class ServicioAPI {
    // Constantes con diferentes niveles de visibilidad
    public const VERSION = "2.0";
    protected const URL_BASE = "https://api.ejemplo.com/v2";
    private const CLAVE_SECRETA = "sk_live_abc123xyz789";

    public readonly string $nombre;

    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }

    public function realizarLlamada(string $endpoint): string {
        // Dentro de la clase, accedemos a todos los niveles
        $url = self::URL_BASE . $endpoint;
        $auth = "Bearer " . self::CLAVE_SECRETA;
        return "GET {$url} [Auth: {$auth}]";
    }

    public function obtenerVersion(): string {
        return self::VERSION;
    }
}

class ServicioExtendido extends ServicioAPI {
    public function construirURL(string $ruta): string {
        // protected es accesible desde la clase hija
        return self::URL_BASE . $ruta;
    }

    public function intentarAccederSecreto(): void {
        // Esto daría error: private no es accesible
        // echo self::CLAVE_SECRETA;
        echo "No se puede acceder a CLAVE_SECRETA desde la clase hija\n";
    }
}

$api = new ServicioAPI("Mi Servicio");

// Constante pública: accesible desde fuera
echo "Versión: " . ServicioAPI::VERSION . "\n"; // 2.0

// Constante protegida: NO accesible desde fuera
// echo ServicioAPI::URL_BASE; // Error

// Constante privada: NO accesible desde fuera
// echo ServicioAPI::CLAVE_SECRETA; // Error

echo $api->realizarLlamada("/usuarios") . "\n";
// GET https://api.ejemplo.com/v2/usuarios [Auth: Bearer sk_live_abc123xyz789]

$ext = new ServicioExtendido("Extendido");
echo $ext->construirURL("/productos") . "\n";
// https://api.ejemplo.com/v2/productos

?>
