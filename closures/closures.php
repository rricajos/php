<?php
// ============================================================================
// CLOSURES (FUNCIONES ANONIMAS) EN PHP
// ============================================================================
// Las closures son funciones sin nombre que pueden capturar variables del
// ambito circundante. Son fundamentales para callbacks, middleware, eventos
// y programacion funcional en PHP.
// ============================================================================

// ============================================================================
// Ejemplo 1: Funciones anonimas vs funciones nombradas
// ============================================================================
// Las funciones anonimas (closures) no tienen nombre y se pueden asignar
// a variables, pasar como argumentos o retornar desde otras funciones.

echo "=== Ejemplo 1: Funciones anonimas vs funciones nombradas ===\n\n";

// Funcion nombrada tradicional
function sumar(int $a, int $b): int {
    return $a + $b;
}

// Funcion anonima asignada a una variable
$restar = function (int $a, int $b): int {
    return $a - $b;
};

// Funcion anonima asignada a otra variable (las funciones son ciudadanos de primera clase)
$multiplicar = function (int $a, int $b): int {
    return $a * $b;
};

echo "Funcion nombrada sumar(5, 3): " . sumar(5, 3) . "\n";
echo "Closure restar(10, 4): " . $restar(10, 4) . "\n";
echo "Closure multiplicar(6, 7): " . $multiplicar(6, 7) . "\n";

// Reasignar una closure a otra variable
$operacion = $multiplicar;
echo "Reasignada multiplicar a \$operacion(3, 3): " . $operacion(3, 3) . "\n";

// Verificar que es un objeto Closure
echo "Tipo de \$restar: " . get_class($restar) . "\n";         // Closure
echo "Es callable: " . (is_callable($restar) ? 'si' : 'no') . "\n";

// Pasar closures como argumentos (callbacks)
function aplicarOperacion(int $a, int $b, callable $operacion): int {
    return $operacion($a, $b);
}

echo "aplicarOperacion(10, 5, \$restar): " . aplicarOperacion(10, 5, $restar) . "\n";

// Retornar closures desde funciones (funciones de orden superior)
function crearMultiplicador(int $factor): Closure {
    return function (int $numero) use ($factor): int {
        return $numero * $factor;
    };
}

$triplicar = crearMultiplicador(3);
$quintuplicar = crearMultiplicador(5);
echo "triplicar(7): " . $triplicar(7) . "\n";
echo "quintuplicar(4): " . $quintuplicar(4) . "\n\n";


// ============================================================================
// Ejemplo 2: Arrow functions (fn =>) vs closures regulares
// ============================================================================
// Las arrow functions (PHP 7.4+) son una sintaxis compacta para closures
// simples. Capturan variables automaticamente por valor (sin necesidad de use).

echo "=== Ejemplo 2: Arrow functions vs closures regulares ===\n\n";

$impuesto = 0.16;

// Closure regular: requiere 'use' para acceder a $impuesto
$calcularPrecioConImpuesto = function (float $precio) use ($impuesto): float {
    return $precio * (1 + $impuesto);
};

// Arrow function: captura $impuesto automaticamente por valor
$calcularPrecioConImpuestoArrow = fn(float $precio): float => $precio * (1 + $impuesto);

echo "Closure regular - Precio con impuesto de \$100: \$" . $calcularPrecioConImpuesto(100) . "\n";
echo "Arrow function - Precio con impuesto de \$100: \$" . $calcularPrecioConImpuestoArrow(100) . "\n";

// Las arrow functions capturan por VALOR, no por referencia
$contador = 0;
$incrementarArrow = fn() => $contador + 1; // Captura $contador = 0
$contador = 100;
echo "Arrow function captura valor al definir (espera 1): " . $incrementarArrow() . "\n";

// Arrow functions anidadas (cada nivel captura del anterior)
$base = 10;
$anidada = fn($x) => fn($y) => $base + $x + $y;
echo "Arrow anidada(5)(3) con base=10: " . $anidada(5)(3) . "\n"; // 18

// Limitacion: las arrow functions solo permiten una expresion
// Para logica compleja, se necesita una closure regular
$procesarPedido = function (array $items) use ($impuesto): array {
    $subtotal = array_sum(array_column($items, 'precio'));
    $impuestoTotal = $subtotal * $impuesto;
    $total = $subtotal + $impuestoTotal;
    return [
        'subtotal' => $subtotal,
        'impuesto' => $impuestoTotal,
        'total' => $total
    ];
};

$items = [
    ['nombre' => 'Laptop', 'precio' => 15000],
    ['nombre' => 'Mouse', 'precio' => 350],
    ['nombre' => 'Teclado', 'precio' => 750],
];

$resultado = $procesarPedido($items);
echo "Pedido - Subtotal: \${$resultado['subtotal']}, ";
echo "Impuesto: \${$resultado['impuesto']}, ";
echo "Total: \${$resultado['total']}\n";

// Comparacion de uso con array_map
$precios = [100, 250, 500, 1000];
$conImpuestoRegular = array_map(function ($p) use ($impuesto) {
    return $p * (1 + $impuesto);
}, $precios);
$conImpuestoArrow = array_map(fn($p) => $p * (1 + $impuesto), $precios);

echo "Precios con impuesto (arrow): " . implode(', ', array_map(fn($p) => "\$$p", $conImpuestoArrow)) . "\n\n";


// ============================================================================
// Ejemplo 3: Closure::bind, Closure::bindTo y Closure::fromCallable
// ============================================================================
// Estos metodos permiten cambiar el contexto ($this) de una closure y
// convertir funciones nombradas en closures.

echo "=== Ejemplo 3: Closure::bind, bindTo y fromCallable ===\n\n";

class CuentaBancaria {
    private float $saldo;
    private string $titular;
    private array $transacciones = [];

    public function __construct(string $titular, float $saldoInicial) {
        $this->titular = $titular;
        $this->saldo = $saldoInicial;
    }

    public function getSaldo(): float {
        return $this->saldo;
    }

    public function getTitular(): string {
        return $this->titular;
    }
}

// Closure que accede a propiedades privadas al ser enlazada a un objeto
$depositar = function (float $monto): string {
    if ($monto <= 0) {
        return "Error: El monto debe ser positivo";
    }
    $this->saldo += $monto;
    $this->transacciones[] = "+\${$monto}";
    return "Deposito de \${$monto} realizado. Saldo: \${$this->saldo}";
};

$retirar = function (float $monto): string {
    if ($monto > $this->saldo) {
        return "Error: Fondos insuficientes (saldo: \${$this->saldo})";
    }
    $this->saldo -= $monto;
    $this->transacciones[] = "-\${$monto}";
    return "Retiro de \${$monto} realizado. Saldo: \${$this->saldo}";
};

$cuenta = new CuentaBancaria('Maria Lopez', 5000.00);

// Closure::bind (metodo estatico) - crea nueva closure enlazada
// Parametros: closure, objeto para $this, clase para acceso a propiedades privadas
$depositarEnCuenta = Closure::bind($depositar, $cuenta, CuentaBancaria::class);
$retirarDeCuenta = Closure::bind($retirar, $cuenta, CuentaBancaria::class);

echo $depositarEnCuenta(1500) . "\n";
echo $retirarDeCuenta(2000) . "\n";
echo $retirarDeCuenta(10000) . "\n"; // Fondos insuficientes

// bindTo (metodo de instancia) - alternativa orientada a objetos
$verTransacciones = function (): string {
    return "Transacciones de {$this->titular}: " . implode(', ', $this->transacciones);
};

$verTransaccionesCuenta = $verTransacciones->bindTo($cuenta, CuentaBancaria::class);
echo $verTransaccionesCuenta() . "\n";

// Closure::fromCallable - convierte un callable en una Closure
// Util para normalizar callbacks y obtener verificacion de tipos temprana
function calcularInteres(float $capital, float $tasa, int $anios): float {
    return $capital * pow(1 + $tasa, $anios) - $capital;
}

$interesComoClousure = Closure::fromCallable('calcularInteres');
echo "Interes de \$10,000 al 5% por 3 anios: \$" .
    number_format($interesComoClousure(10000, 0.05, 3), 2) . "\n";

// fromCallable con metodos de clase
class Validador {
    public static function esEmailValido(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function esPositivo(float $numero): bool {
        return $numero > 0;
    }
}

$validarEmail = Closure::fromCallable([Validador::class, 'esEmailValido']);
$validador = new Validador();
$validarPositivo = Closure::fromCallable([$validador, 'esPositivo']);

$emails = ['user@example.com', 'invalido', 'otro@dominio.mx', '@roto.com'];
$validos = array_filter($emails, $validarEmail);
echo "Emails validos: " . implode(', ', $validos) . "\n";

// Desde PHP 8.1, se puede usar la sintaxis de primera clase para callables
// $validarEmail = Validador::esEmailValido(...);
// $validarPositivo = $validador->esPositivo(...);
echo "\n";


// ============================================================================
// Ejemplo 4: Sistema de eventos con closures
// ============================================================================
// Patron Observer/EventEmitter implementado con closures. Cada listener
// es una closure que se ejecuta cuando se dispara el evento correspondiente.

echo "=== Ejemplo 4: Sistema de eventos con closures ===\n\n";

class EventEmitter {
    // Almacena closures agrupadas por nombre de evento
    private array $listeners = [];

    // Registrar un listener (closure) para un evento
    public function on(string $evento, Closure $listener): self {
        $this->listeners[$evento][] = $listener;
        return $this; // Permite encadenamiento
    }

    // Registrar un listener que se ejecuta solo una vez
    public function once(string $evento, Closure $listener): self {
        // Envolvemos el listener original en una closure que se auto-elimina
        $wrapper = function () use ($evento, $listener, &$wrapper) {
            $listener(...func_get_args());
            $this->off($evento, $wrapper);
        };
        $this->on($evento, $wrapper);
        return $this;
    }

    // Eliminar un listener especifico
    public function off(string $evento, Closure $listener): self {
        if (isset($this->listeners[$evento])) {
            $this->listeners[$evento] = array_filter(
                $this->listeners[$evento],
                fn($l) => $l !== $listener
            );
        }
        return $this;
    }

    // Disparar un evento, ejecutando todos sus listeners
    public function emit(string $evento, mixed ...$args): self {
        $listeners = $this->listeners[$evento] ?? [];
        foreach ($listeners as $listener) {
            $resultado = $listener(...$args);
            // Si un listener retorna false, detener la propagacion
            if ($resultado === false) {
                break;
            }
        }
        return $this;
    }

    // Obtener cantidad de listeners para un evento
    public function contarListeners(string $evento): int {
        return count($this->listeners[$evento] ?? []);
    }
}

$emitter = new EventEmitter();

// Registrar listeners para el evento 'usuario.creado'
$emitter->on('usuario.creado', function (array $usuario) {
    echo "  [LOG] Usuario creado: {$usuario['nombre']} ({$usuario['email']})\n";
});

$emitter->on('usuario.creado', function (array $usuario) {
    echo "  [EMAIL] Enviando bienvenida a {$usuario['email']}\n";
});

// Listener que solo se ejecuta una vez
$emitter->once('usuario.creado', function (array $usuario) {
    echo "  [PROMO] Primer usuario! Cupon de descuento enviado a {$usuario['email']}\n";
});

// Listener para errores
$emitter->on('error', function (string $mensaje, int $codigo) {
    echo "  [ERROR #{$codigo}] {$mensaje}\n";
});

echo "Listeners para 'usuario.creado': " . $emitter->contarListeners('usuario.creado') . "\n";

echo "--- Primer usuario ---\n";
$emitter->emit('usuario.creado', [
    'nombre' => 'Carlos Ruiz',
    'email' => 'carlos@example.com'
]);

echo "--- Segundo usuario (sin promo, era 'once') ---\n";
$emitter->emit('usuario.creado', [
    'nombre' => 'Ana Torres',
    'email' => 'ana@example.com'
]);

echo "--- Error ---\n";
$emitter->emit('error', 'Conexion a base de datos fallida', 500);
echo "\n";


// ============================================================================
// Ejemplo 5: Cadena de middleware con closures
// ============================================================================
// Los middlewares son closures que procesan una solicitud en cadena.
// Cada middleware puede modificar la solicitud, la respuesta, o detener
// el flujo. Este patron es usado por frameworks como Laravel y Slim.

echo "=== Ejemplo 5: Cadena de middleware con closures ===\n\n";

class MiddlewarePipeline {
    private array $middlewares = [];

    // Agregar un middleware a la cadena
    public function pipe(Closure $middleware): self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    // Ejecutar la cadena de middlewares con una solicitud
    public function procesar(array $solicitud): array {
        // Construimos la cadena desde el final hacia el inicio
        // El ultimo "handler" simplemente retorna la solicitud procesada
        $handler = fn(array $solicitud): array => $solicitud;

        // Envolvemos cada middleware alrededor del anterior (de atras hacia adelante)
        foreach (array_reverse($this->middlewares) as $middleware) {
            $handler = function (array $solicitud) use ($middleware, $handler): array {
                return $middleware($solicitud, $handler);
            };
        }

        return $handler($solicitud);
    }
}

$pipeline = new MiddlewarePipeline();

// Middleware 1: Autenticacion
$pipeline->pipe(function (array $solicitud, Closure $siguiente): array {
    echo "  [Middleware] Verificando autenticacion...\n";
    if (empty($solicitud['token'])) {
        return ['error' => 'No autorizado', 'codigo' => 401];
    }
    $solicitud['usuario'] = 'admin'; // Simular decodificacion del token
    return $siguiente($solicitud);
});

// Middleware 2: Validacion de datos
$pipeline->pipe(function (array $solicitud, Closure $siguiente): array {
    echo "  [Middleware] Validando datos de entrada...\n";
    if (empty($solicitud['datos']['nombre'])) {
        return ['error' => 'El nombre es obligatorio', 'codigo' => 422];
    }
    // Sanitizar datos
    $solicitud['datos']['nombre'] = trim($solicitud['datos']['nombre']);
    return $siguiente($solicitud);
});

// Middleware 3: Registro de actividad (logging)
$pipeline->pipe(function (array $solicitud, Closure $siguiente): array {
    $inicio = microtime(true);
    echo "  [Middleware] Registrando solicitud de '{$solicitud['usuario']}'...\n";
    $respuesta = $siguiente($solicitud);
    $duracion = round((microtime(true) - $inicio) * 1000, 2);
    $respuesta['tiempo_ms'] = $duracion;
    return $respuesta;
});

// Middleware 4: Controlador final (la logica de negocio)
$pipeline->pipe(function (array $solicitud, Closure $siguiente): array {
    echo "  [Controlador] Procesando solicitud...\n";
    return [
        'exito' => true,
        'mensaje' => "Usuario '{$solicitud['datos']['nombre']}' creado por {$solicitud['usuario']}",
        'codigo' => 201
    ];
});

// Caso exitoso
echo "--- Solicitud valida ---\n";
$respuesta = $pipeline->procesar([
    'token' => 'abc123',
    'datos' => ['nombre' => '  Roberto Sanchez  '],
]);
echo "  Respuesta: " . json_encode($respuesta, JSON_UNESCAPED_UNICODE) . "\n\n";

// Caso sin autenticacion
echo "--- Solicitud sin token ---\n";
$pipelineSinAuth = new MiddlewarePipeline();
$pipelineSinAuth->pipe(function (array $solicitud, Closure $siguiente): array {
    if (empty($solicitud['token'])) {
        return ['error' => 'No autorizado', 'codigo' => 401];
    }
    return $siguiente($solicitud);
});
$respuesta = $pipelineSinAuth->procesar(['datos' => ['nombre' => 'Test']]);
echo "  Respuesta: " . json_encode($respuesta, JSON_UNESCAPED_UNICODE) . "\n\n";


// ============================================================================
// Ejemplo 6: Filtrado de colecciones con closures personalizadas
// ============================================================================
// Closures como predicados, transformadores y reductores para procesar
// colecciones de datos de forma expresiva y reutilizable.

echo "=== Ejemplo 6: Filtrado de colecciones con closures ===\n\n";

class Coleccion {
    private array $items;

    public function __construct(array $items) {
        $this->items = $items;
    }

    // Filtrar items segun un predicado (closure que retorna bool)
    public function filtrar(Closure $predicado): self {
        return new self(array_values(array_filter($this->items, $predicado)));
    }

    // Transformar cada item con una closure
    public function transformar(Closure $transformador): self {
        return new self(array_map($transformador, $this->items));
    }

    // Reducir la coleccion a un unico valor
    public function reducir(Closure $reductor, mixed $inicial = null): mixed {
        return array_reduce($this->items, $reductor, $inicial);
    }

    // Ordenar con una closure de comparacion
    public function ordenar(Closure $comparador): self {
        $items = $this->items;
        usort($items, $comparador);
        return new self($items);
    }

    // Agrupar items segun una clave generada por una closure
    public function agrupar(Closure $generadorClave): array {
        $grupos = [];
        foreach ($this->items as $item) {
            $clave = $generadorClave($item);
            $grupos[$clave][] = $item;
        }
        return $grupos;
    }

    // Obtener el primer item que cumpla la condicion
    public function primero(Closure $predicado): mixed {
        foreach ($this->items as $item) {
            if ($predicado($item)) {
                return $item;
            }
        }
        return null;
    }

    public function toArray(): array {
        return $this->items;
    }

    public function contar(): int {
        return count($this->items);
    }
}

// Datos de ejemplo: catalogo de productos
$productos = new Coleccion([
    ['nombre' => 'Laptop HP',       'precio' => 18500, 'categoria' => 'electronica', 'stock' => 15],
    ['nombre' => 'Mouse Logitech',  'precio' => 450,   'categoria' => 'electronica', 'stock' => 200],
    ['nombre' => 'Escritorio Roble','precio' => 8900,   'categoria' => 'muebles',     'stock' => 5],
    ['nombre' => 'Silla Ergonomica','precio' => 6500,   'categoria' => 'muebles',     'stock' => 0],
    ['nombre' => 'Monitor 27"',     'precio' => 7200,   'categoria' => 'electronica', 'stock' => 30],
    ['nombre' => 'Teclado Mecanico','precio' => 1800,   'categoria' => 'electronica', 'stock' => 85],
    ['nombre' => 'Lampara LED',     'precio' => 350,    'categoria' => 'iluminacion', 'stock' => 150],
    ['nombre' => 'Webcam HD',       'precio' => 1200,   'categoria' => 'electronica', 'stock' => 0],
]);

// Closures reutilizables como predicados
$enStock = fn(array $p): bool => $p['stock'] > 0;
$esElectronica = fn(array $p): bool => $p['categoria'] === 'electronica';
$precioMenorA = fn(float $max): Closure => fn(array $p): bool => $p['precio'] < $max;

// Encadenar operaciones: productos de electronica en stock, baratos, ordenados por precio
$resultado = $productos
    ->filtrar($enStock)
    ->filtrar($esElectronica)
    ->filtrar($precioMenorA(5000))
    ->ordenar(fn($a, $b) => $a['precio'] <=> $b['precio'])
    ->transformar(fn($p) => "{$p['nombre']}: \${$p['precio']}");

echo "Electronica en stock bajo \$5,000:\n";
foreach ($resultado->toArray() as $item) {
    echo "  - {$item}\n";
}

// Calcular el valor total del inventario
$valorInventario = $productos->reducir(
    fn(float $acumulado, array $p): float => $acumulado + ($p['precio'] * $p['stock']),
    0.0
);
echo "\nValor total del inventario: \$" . number_format($valorInventario, 2) . "\n";

// Agrupar por categoria
$porCategoria = $productos->agrupar(fn($p) => $p['categoria']);
echo "\nProductos por categoria:\n";
foreach ($porCategoria as $categoria => $items) {
    $nombres = array_map(fn($p) => $p['nombre'], $items);
    echo "  {$categoria}: " . implode(', ', $nombres) . "\n";
}

// Encontrar el primer producto sin stock
$sinStock = $productos->primero(fn($p) => $p['stock'] === 0);
echo "\nPrimer producto sin stock: {$sinStock['nombre']} (\${$sinStock['precio']})\n";
echo "Total de productos: " . $productos->contar() . "\n";
?>
