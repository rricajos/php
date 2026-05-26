<?php
// ============================================
// CLASES EN PHP - Declaración, propiedades, métodos, $this e instanciación
// ============================================

// --- Ejemplo 1: Clase básica con propiedades y métodos ---
// Declaramos una clase simple que representa un producto

class Producto {
    // Propiedades (atributos) de la clase
    public string $nombre;
    public float $precio;
    public int $cantidad;

    // Método para establecer los datos del producto
    public function establecerDatos(string $nombre, float $precio, int $cantidad): void {
        // $this hace referencia a la instancia actual del objeto
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->cantidad = $cantidad;
    }

    // Método para calcular el valor total en inventario
    public function valorTotal(): float {
        return $this->precio * $this->cantidad;
    }

    // Método para mostrar información del producto
    public function mostrarInfo(): string {
        return "Producto: {$this->nombre} | Precio: \${$this->precio} | Cantidad: {$this->cantidad}";
    }
}

// Instanciación: crear un objeto a partir de la clase
$laptop = new Producto();
$laptop->establecerDatos("Laptop Gamer", 1299.99, 5);

echo $laptop->mostrarInfo() . "\n";
// Producto: Laptop Gamer | Precio: $1299.99 | Cantidad: 5

echo "Valor total en inventario: $" . $laptop->valorTotal() . "\n";
// Valor total en inventario: $6499.95


// --- Ejemplo 2: Clase con valores por defecto y múltiples instancias ---
// Cada instancia es independiente y mantiene su propio estado

class CuentaBancaria {
    public string $titular = "Sin titular";
    public float $saldo = 0.0;
    public string $moneda = "USD";

    public function depositar(float $monto): void {
        if ($monto > 0) {
            $this->saldo += $monto;
            echo "Depósito de \${$monto} realizado. Saldo actual: \${$this->saldo}\n";
        }
    }

    public function retirar(float $monto): bool {
        if ($monto > 0 && $monto <= $this->saldo) {
            $this->saldo -= $monto;
            echo "Retiro de \${$monto} realizado. Saldo actual: \${$this->saldo}\n";
            return true;
        }
        echo "Fondos insuficientes para retirar \${$monto}\n";
        return false;
    }
}

// Creamos dos instancias independientes
$cuentaCarlos = new CuentaBancaria();
$cuentaCarlos->titular = "Carlos Méndez";
$cuentaCarlos->depositar(1000.00);  // Depósito de $1000 realizado. Saldo actual: $1000
$cuentaCarlos->retirar(250.00);     // Retiro de $250 realizado. Saldo actual: $750

$cuentaMaria = new CuentaBancaria();
$cuentaMaria->titular = "María López";
$cuentaMaria->depositar(500.00);    // Depósito de $500 realizado. Saldo actual: $500

// Cada cuenta es independiente
echo "{$cuentaCarlos->titular}: \${$cuentaCarlos->saldo}\n"; // Carlos Méndez: $750
echo "{$cuentaMaria->titular}: \${$cuentaMaria->saldo}\n";   // María López: $500


// --- Ejemplo 3: Clase con métodos que retornan $this (fluent interface) ---
// El patrón "fluent interface" permite encadenar llamadas a métodos

class ConsultaSQL {
    private string $tabla = "";
    private array $condiciones = [];
    private string $orden = "";
    private int $limite = 0;

    // Cada método retorna $this para permitir encadenamiento
    public function seleccionar(string $tabla): self {
        $this->tabla = $tabla;
        return $this;
    }

    public function donde(string $condicion): self {
        $this->condiciones[] = $condicion;
        return $this;
    }

    public function ordenarPor(string $campo): self {
        $this->orden = $campo;
        return $this;
    }

    public function limitar(int $cantidad): self {
        $this->limite = $cantidad;
        return $this;
    }

    public function construir(): string {
        $sql = "SELECT * FROM {$this->tabla}";

        if (!empty($this->condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $this->condiciones);
        }
        if (!empty($this->orden)) {
            $sql .= " ORDER BY {$this->orden}";
        }
        if ($this->limite > 0) {
            $sql .= " LIMIT {$this->limite}";
        }

        return $sql;
    }
}

// Encadenamiento de métodos gracias a retornar $this
$consulta = (new ConsultaSQL())
    ->seleccionar("usuarios")
    ->donde("edad > 18")
    ->donde("activo = 1")
    ->ordenarPor("nombre ASC")
    ->limitar(10)
    ->construir();

echo $consulta . "\n";
// SELECT * FROM usuarios WHERE edad > 18 AND activo = 1 ORDER BY nombre ASC LIMIT 10


// --- Ejemplo 4: Acceso a propiedades y métodos desde fuera del objeto ---
// Demostración de cómo interactuar con objetos externamente

class Rectangulo {
    public float $ancho;
    public float $alto;

    public function calcularArea(): float {
        return $this->ancho * $this->alto;
    }

    public function calcularPerimetro(): float {
        return 2 * ($this->ancho + $this->alto);
    }

    public function esQuadrado(): bool {
        return $this->ancho === $this->alto;
    }
}

// Asignación directa de propiedades públicas
$rect = new Rectangulo();
$rect->ancho = 15.5;
$rect->alto = 10.0;

echo "Área: " . $rect->calcularArea() . "\n";           // Área: 155
echo "Perímetro: " . $rect->calcularPerimetro() . "\n";  // Perímetro: 51
echo "¿Es cuadrado? " . ($rect->esQuadrado() ? "Sí" : "No") . "\n"; // No

// Podemos crear un arreglo de objetos
$figuras = [];
for ($i = 1; $i <= 3; $i++) {
    $fig = new Rectangulo();
    $fig->ancho = $i * 5.0;
    $fig->alto = $i * 3.0;
    $figuras[] = $fig;
}

foreach ($figuras as $indice => $figura) {
    echo "Rectángulo {$indice}: {$figura->ancho}x{$figura->alto} = área {$figura->calcularArea()}\n";
}
// Rectángulo 0: 5x3 = área 15
// Rectángulo 1: 10x6 = área 60
// Rectángulo 2: 15x9 = área 135

?>
