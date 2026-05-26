<?php
// ============================================
// HERENCIA EN PHP - extends, parent::, sobreescritura de métodos
// ============================================

// --- Ejemplo 1: Herencia básica con extends ---
// Una clase hija hereda propiedades y métodos de la clase padre

class Animal {
    protected string $nombre;
    protected int $edad;

    public function __construct(string $nombre, int $edad) {
        $this->nombre = $nombre;
        $this->edad = $edad;
    }

    public function hablar(): string {
        return "{$this->nombre} hace un sonido";
    }

    public function descripcion(): string {
        return "{$this->nombre} tiene {$this->edad} años";
    }
}

// 'extends' establece la relación de herencia
class Perro extends Animal {
    private string $raza;

    public function __construct(string $nombre, int $edad, string $raza) {
        // parent:: llama al constructor de la clase padre
        parent::__construct($nombre, $edad);
        $this->raza = $raza;
    }

    // Sobreescritura del método hablar()
    public function hablar(): string {
        return "{$this->nombre} dice: ¡Guau guau!";
    }

    public function buscar(string $objeto): string {
        return "{$this->nombre} ({$this->raza}) busca el {$objeto}";
    }
}

class Gato extends Animal {
    private bool $esInterior;

    public function __construct(string $nombre, int $edad, bool $esInterior = true) {
        parent::__construct($nombre, $edad);
        $this->esInterior = $esInterior;
    }

    public function hablar(): string {
        return "{$this->nombre} dice: ¡Miau!";
    }

    public function ronronear(): string {
        return "{$this->nombre} está ronroneando... prrr";
    }
}

$firulais = new Perro("Firulais", 5, "Labrador");
$michi = new Gato("Michi", 3);

echo $firulais->hablar() . "\n";       // Firulais dice: ¡Guau guau!
echo $firulais->descripcion() . "\n";  // Firulais tiene 5 años (heredado)
echo $firulais->buscar("hueso") . "\n"; // Firulais (Labrador) busca el hueso

echo $michi->hablar() . "\n";          // Michi dice: ¡Miau!
echo $michi->ronronear() . "\n";       // Michi está ronroneando... prrr

// instanceof verifica la jerarquía de herencia
echo ($firulais instanceof Animal) ? "Firulais es un Animal\n" : "";
echo ($firulais instanceof Perro) ? "Firulais es un Perro\n" : "";


// --- Ejemplo 2: Uso de parent:: para extender comportamiento ---
// No solo reemplazamos el método del padre, sino que lo extendemos

class Vehiculo {
    protected string $marca;
    protected string $modelo;
    protected int $velocidadActual = 0;

    public function __construct(string $marca, string $modelo) {
        $this->marca = $marca;
        $this->modelo = $modelo;
    }

    public function acelerar(int $incremento): string {
        $this->velocidadActual += $incremento;
        return "{$this->marca} {$this->modelo} acelera a {$this->velocidadActual} km/h";
    }

    public function obtenerInfo(): array {
        return [
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'velocidad' => $this->velocidadActual,
        ];
    }
}

class VehiculoElectrico extends Vehiculo {
    protected int $bateria = 100; // porcentaje

    public function __construct(string $marca, string $modelo, int $bateria = 100) {
        parent::__construct($marca, $modelo);
        $this->bateria = $bateria;
    }

    // Extendemos el comportamiento del padre usando parent::
    public function acelerar(int $incremento): string {
        if ($this->bateria <= 0) {
            return "¡Sin batería! No se puede acelerar.";
        }

        // Llamamos al método del padre para la lógica base
        $resultado = parent::acelerar($incremento);

        // Añadimos lógica adicional
        $this->bateria -= intval($incremento * 0.5);
        $this->bateria = max(0, $this->bateria);

        return $resultado . " (Batería: {$this->bateria}%)";
    }

    // Extendemos la información con datos adicionales
    public function obtenerInfo(): array {
        $info = parent::obtenerInfo();
        $info['bateria'] = $this->bateria;
        $info['tipo'] = 'eléctrico';
        return $info;
    }
}

$tesla = new VehiculoElectrico("Tesla", "Model 3");
echo $tesla->acelerar(30) . "\n";
// Tesla Model 3 acelera a 30 km/h (Batería: 85%)
echo $tesla->acelerar(50) . "\n";
// Tesla Model 3 acelera a 80 km/h (Batería: 60%)

print_r($tesla->obtenerInfo());
// Array ( [marca] => Tesla [modelo] => Model 3 [velocidad] => 80 [bateria] => 60 [tipo] => eléctrico )


// --- Ejemplo 3: Cadena de herencia (múltiples niveles) ---
// PHP permite herencia en cadena: A -> B -> C

class Forma {
    protected string $color;

    public function __construct(string $color = "negro") {
        $this->color = $color;
    }

    public function obtenerTipo(): string {
        return "Forma genérica";
    }

    public function describir(): string {
        return $this->obtenerTipo() . " de color {$this->color}";
    }
}

class Poligono extends Forma {
    protected int $lados;

    public function __construct(int $lados, string $color = "negro") {
        parent::__construct($color);
        $this->lados = $lados;
    }

    public function obtenerTipo(): string {
        return "Polígono de {$this->lados} lados";
    }
}

class Triangulo extends Poligono {
    private float $base;
    private float $altura;

    public function __construct(float $base, float $altura, string $color = "negro") {
        // Llamamos al constructor de Poligono (que a su vez llama al de Forma)
        parent::__construct(3, $color);
        $this->base = $base;
        $this->altura = $altura;
    }

    public function obtenerTipo(): string {
        return "Triángulo ({$this->base}x{$this->altura})";
    }

    public function area(): float {
        return ($this->base * $this->altura) / 2;
    }
}

$triangulo = new Triangulo(10, 8, "azul");
echo $triangulo->describir() . "\n";
// Triángulo (10x8) de color azul

echo "Área: " . $triangulo->area() . "\n";
// Área: 40

// La cadena de herencia se mantiene
echo ($triangulo instanceof Forma) ? "Es una Forma\n" : "";
echo ($triangulo instanceof Poligono) ? "Es un Polígono\n" : "";
echo ($triangulo instanceof Triangulo) ? "Es un Triángulo\n" : "";


// --- Ejemplo 4: Sobreescritura con lógica condicional y final ---
// Podemos marcar métodos como 'final' para prevenir sobreescritura

class ProcesadorPagos {
    protected float $comision = 0.029; // 2.9% por defecto

    // 'final' impide que las clases hijas sobreescriban este método
    final public function calcularComision(float $monto): float {
        return round($monto * $this->comision, 2);
    }

    public function procesar(float $monto, string $moneda = "USD"): array {
        $comision = $this->calcularComision($monto);
        return [
            'monto_original' => $monto,
            'comision' => $comision,
            'total' => $monto + $comision,
            'moneda' => $moneda,
            'procesador' => static::class, // Nombre de la clase actual
        ];
    }
}

class ProcesadorPayPal extends ProcesadorPagos {
    protected float $comision = 0.035; // PayPal cobra 3.5%

    public function procesar(float $monto, string $moneda = "USD"): array {
        // Llamamos al procesamiento base y añadimos datos de PayPal
        $resultado = parent::procesar($monto, $moneda);
        $resultado['metodo'] = "PayPal";
        $resultado['email_paypal'] = "pagos@tienda.com";
        return $resultado;
    }
}

class ProcesadorStripe extends ProcesadorPagos {
    protected float $comision = 0.025; // Stripe cobra 2.5%

    public function procesar(float $monto, string $moneda = "USD"): array {
        $resultado = parent::procesar($monto, $moneda);
        $resultado['metodo'] = "Stripe";
        $resultado['clave_publica'] = "pk_live_xxx";
        return $resultado;
    }
}

$paypal = new ProcesadorPayPal();
$stripe = new ProcesadorStripe();

print_r($paypal->procesar(100.00));
// Array ( [monto_original] => 100 [comision] => 3.5 [total] => 103.5
//         [moneda] => USD [procesador] => ProcesadorPayPal [metodo] => PayPal ... )

print_r($stripe->procesar(100.00));
// Array ( [monto_original] => 100 [comision] => 2.5 [total] => 102.5
//         [moneda] => USD [procesador] => ProcesadorStripe [metodo] => Stripe ... )

?>
