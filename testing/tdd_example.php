<?php
/**
 * ============================================================================
 * TDD - DESARROLLO GUIADO POR PRUEBAS (TEST-DRIVEN DEVELOPMENT)
 * ============================================================================
 *
 * TDD es una metodologia de desarrollo donde las PRUEBAS se escriben
 * ANTES que el codigo de produccion. Sigue un ciclo de tres pasos:
 *
 *   1. ROJO (Red):     Escribir una prueba que FALLE
 *   2. VERDE (Green):  Escribir el MINIMO codigo para que pase
 *   3. REFACTORIZAR:   Mejorar el codigo sin romper las pruebas
 *
 * Reglas de TDD (Robert C. Martin):
 *   - No escribir codigo de produccion sin una prueba que falle
 *   - No escribir mas de una prueba que falle a la vez
 *   - No escribir mas codigo del necesario para hacer pasar la prueba
 *
 * Este archivo muestra el proceso completo de TDD paso a paso
 * con dos ejemplos: Calculator (simple) y ShoppingCart (complejo).
 */

// ============================================================================
// MINI-FRAMEWORK DE PRUEBAS PARA TDD
// ============================================================================

class TDD {
    private static int $total = 0;
    private static int $ok = 0;
    private static int $fallos = 0;
    private static array $errores = [];
    private static string $faseActual = '';

    /** Marca la fase actual del ciclo TDD */
    public static function fase(string $fase): void {
        self::$faseActual = $fase;
    }

    public static function ejecutar(string $nombre, callable $prueba): void {
        self::$total++;
        $prefijo = self::$faseActual ? "[{$this->faseActual}] " : '';
        // Correccion: usar self:: en contexto estatico
        $prefijo = self::$faseActual ? "[" . self::$faseActual . "] " : '';
        try {
            $prueba();
            self::$ok++;
            echo "  [OK] {$prefijo}{$nombre}\n";
        } catch (\Throwable $e) {
            self::$fallos++;
            self::$errores[] = "{$prefijo}{$nombre}: {$e->getMessage()}";
            echo "  [FALLO] {$prefijo}{$nombre}: {$e->getMessage()}\n";
        }
    }

    public static function assertEquals(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($esperado != $actual) {
            throw new \RuntimeException(
                $msg ?: "Esperado: " . var_export($esperado, true)
                      . " | Obtenido: " . var_export($actual, true)
            );
        }
    }

    public static function assertSame(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($esperado !== $actual) {
            throw new \RuntimeException(
                $msg ?: "Esperado (estricto): " . var_export($esperado, true)
                      . " | Obtenido: " . var_export($actual, true)
            );
        }
    }

    public static function assertTrue(mixed $valor, string $msg = ''): void {
        if ($valor !== true) {
            throw new \RuntimeException($msg ?: "Se esperaba TRUE");
        }
    }

    public static function assertFalse(mixed $valor, string $msg = ''): void {
        if ($valor !== false) {
            throw new \RuntimeException($msg ?: "Se esperaba FALSE");
        }
    }

    public static function assertCount(int $esperado, array|Countable $col, string $msg = ''): void {
        if (count($col) !== $esperado) {
            throw new \RuntimeException(
                $msg ?: "Cantidad: esperado {$esperado}, obtenido " . count($col)
            );
        }
    }

    public static function assertEmpty(mixed $valor, string $msg = ''): void {
        if (!empty($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba vacio");
        }
    }

    public static function assertThrows(string $clase, callable $fn, string $msg = ''): void {
        try {
            $fn();
            throw new \RuntimeException(
                $msg ?: "Se esperaba excepcion {$clase} pero no se lanzo"
            );
        } catch (\Throwable $e) {
            if (!($e instanceof $clase)) {
                throw new \RuntimeException(
                    $msg ?: "Se esperaba {$clase}, se lanzo " . get_class($e)
                );
            }
        }
    }

    public static function resumen(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "RESULTADOS TDD: " . self::$total . " pruebas, "
           . self::$ok . " exitosas, "
           . self::$fallos . " fallidas\n";
        if (!empty(self::$errores)) {
            echo "\nFALLOS:\n";
            foreach (self::$errores as $e) {
                echo "  - {$e}\n";
            }
        }
        echo str_repeat("=", 60) . "\n";
    }
}


// ============================================================================
// ============================================================================
//
//     EJEMPLO 1: TDD PASO A PASO - CONSTRUIR UNA CALCULADORA
//
// ============================================================================
// ============================================================================
echo str_repeat("*", 60) . "\n";
echo "  EJEMPLO 1: TDD - CONSTRUIR UNA CALCULADORA\n";
echo str_repeat("*", 60) . "\n\n";


// ============================================================================
// Ejemplo 1, Iteracion 1: Suma basica
// ============================================================================
echo "=== Iteracion 1: Suma basica ===\n\n";

/**
 * PASO 1 - ROJO: Escribir la prueba primero.
 * La clase Calculator NO EXISTE todavia.
 *
 * // Este codigo FALLARIA:
 * // $calc = new Calculator();
 * // $resultado = $calc->add(2, 3);
 * // assertEquals(5, $resultado);
 *
 * PASO 2 - VERDE: Escribir el MINIMO codigo para que pase.
 */

// Empezamos con la implementacion minima
class Calculator {
    // Iteracion 1: Solo suma
    public function add(float $a, float $b): float {
        return $a + $b;
    }

    // Iteracion 2: Resta (se agrega despues)
    public function subtract(float $a, float $b): float {
        return $a - $b;
    }

    // Iteracion 3: Multiplicacion
    public function multiply(float $a, float $b): float {
        return $a * $b;
    }

    // Iteracion 4: Division con manejo de errores
    public function divide(float $a, float $b): float {
        if ($b == 0) {
            throw new \DivisionByZeroError("No se puede dividir entre cero");
        }
        return $a / $b;
    }

    // Iteracion 5: Memoria (refactorizacion)
    private float $memoria = 0;

    public function guardarEnMemoria(float $valor): void {
        $this->memoria = $valor;
    }

    public function recuperarMemoria(): float {
        return $this->memoria;
    }

    public function limpiarMemoria(): void {
        $this->memoria = 0;
    }

    // Iteracion 6: Operaciones con historial
    private array $historial = [];

    public function ejecutar(string $operacion, float $a, float $b): float {
        $resultado = match($operacion) {
            'sumar'       => $this->add($a, $b),
            'restar'      => $this->subtract($a, $b),
            'multiplicar' => $this->multiply($a, $b),
            'dividir'     => $this->divide($a, $b),
            default       => throw new \InvalidArgumentException("Operacion desconocida: {$operacion}"),
        };

        $this->historial[] = [
            'operacion' => $operacion,
            'a'         => $a,
            'b'         => $b,
            'resultado' => $resultado,
        ];

        return $resultado;
    }

    public function obtenerHistorial(): array {
        return $this->historial;
    }

    public function limpiarHistorial(): void {
        $this->historial = [];
    }
}

/*
 * Flujo TDD para la Iteracion 1:
 *
 * ROJO:   Escribimos test testAdd() -> FALLA (clase no existe)
 * VERDE:  Creamos Calculator con add() -> PASA
 * REFACTORIZAR: El codigo es tan simple que no necesita refactorizacion
 */

TDD::ejecutar("ROJO->VERDE: Sumar 2 + 3 = 5", function() {
    $calc = new Calculator();
    TDD::assertEquals(5, $calc->add(2, 3));
});

TDD::ejecutar("VERDE: Sumar numeros negativos -3 + -7 = -10", function() {
    $calc = new Calculator();
    TDD::assertEquals(-10, $calc->add(-3, -7));
});

TDD::ejecutar("VERDE: Sumar con cero 5 + 0 = 5", function() {
    $calc = new Calculator();
    TDD::assertEquals(5, $calc->add(5, 0));
});

echo "\n";


// ============================================================================
// Ejemplo 1, Iteracion 2: Resta
// ============================================================================
echo "=== Iteracion 2: Resta ===\n\n";

/**
 * PASO 1 - ROJO: Nueva prueba para resta
 *   $calc->subtract(10, 4) deberia retornar 6
 *   Pero el metodo subtract() no existe -> FALLA
 *
 * PASO 2 - VERDE: Agregar metodo subtract()
 *
 * PASO 3 - REFACTORIZAR: Todo bien, siguiente iteracion
 */

TDD::ejecutar("ROJO->VERDE: Restar 10 - 4 = 6", function() {
    $calc = new Calculator();
    TDD::assertEquals(6, $calc->subtract(10, 4));
});

TDD::ejecutar("VERDE: Restar dando negativo 3 - 8 = -5", function() {
    $calc = new Calculator();
    TDD::assertEquals(-5, $calc->subtract(3, 8));
});

echo "\n";


// ============================================================================
// Ejemplo 1, Iteracion 3: Multiplicacion
// ============================================================================
echo "=== Iteracion 3: Multiplicacion ===\n\n";

TDD::ejecutar("ROJO->VERDE: Multiplicar 3 * 7 = 21", function() {
    $calc = new Calculator();
    TDD::assertEquals(21, $calc->multiply(3, 7));
});

TDD::ejecutar("VERDE: Multiplicar por cero 999 * 0 = 0", function() {
    $calc = new Calculator();
    TDD::assertEquals(0, $calc->multiply(999, 0));
});

TDD::ejecutar("VERDE: Multiplicar negativos -3 * -4 = 12", function() {
    $calc = new Calculator();
    TDD::assertEquals(12, $calc->multiply(-3, -4));
});

echo "\n";


// ============================================================================
// Ejemplo 1, Iteracion 4: Division con manejo de errores
// ============================================================================
echo "=== Iteracion 4: Division (con manejo de errores) ===\n\n";

/**
 * PASO 1 - ROJO: Prueba para division normal
 *   $calc->divide(10, 2) deberia retornar 5.0
 *
 * PASO 2 - ROJO: Prueba para division por cero
 *   $calc->divide(10, 0) deberia lanzar DivisionByZeroError
 *
 * PASO 3 - VERDE: Implementar con validacion
 *
 * Nota TDD: Primero escribimos la prueba del caso feliz,
 * luego la del caso de error.
 */

TDD::ejecutar("ROJO->VERDE: Dividir 10 / 2 = 5", function() {
    $calc = new Calculator();
    TDD::assertEquals(5, $calc->divide(10, 2));
});

TDD::ejecutar("VERDE: Division con decimales 7 / 2 = 3.5", function() {
    $calc = new Calculator();
    TDD::assertEquals(3.5, $calc->divide(7, 2));
});

TDD::ejecutar("ROJO->VERDE: Division por cero lanza DivisionByZeroError", function() {
    $calc = new Calculator();
    TDD::assertThrows(\DivisionByZeroError::class, function() use ($calc) {
        $calc->divide(10, 0);
    });
});

echo "\n";


// ============================================================================
// Ejemplo 1, Iteracion 5: Memoria (nueva funcionalidad via TDD)
// ============================================================================
echo "=== Iteracion 5: Funcionalidad de memoria ===\n\n";

/**
 * NUEVOS REQUISITOS:
 *   - La calculadora debe tener memoria
 *   - Guardar un valor en memoria
 *   - Recuperar valor de memoria
 *   - Limpiar memoria (volver a 0)
 *
 * Siguiendo TDD, escribimos las pruebas PRIMERO:
 *
 * VERSION PHPUNIT (lo que escribiriamos primero):
 *
 * public function testMemoriaIniciaEnCero(): void
 * {
 *     $calc = new Calculator();
 *     $this->assertEquals(0, $calc->recuperarMemoria());
 * }
 *
 * public function testGuardarYRecuperarMemoria(): void
 * {
 *     $calc = new Calculator();
 *     $calc->guardarEnMemoria(42);
 *     $this->assertEquals(42, $calc->recuperarMemoria());
 * }
 */

TDD::ejecutar("ROJO->VERDE: Memoria inicia en cero", function() {
    $calc = new Calculator();
    TDD::assertEquals(0, $calc->recuperarMemoria());
});

TDD::ejecutar("ROJO->VERDE: Guardar y recuperar de memoria", function() {
    $calc = new Calculator();
    $calc->guardarEnMemoria(42);
    TDD::assertEquals(42, $calc->recuperarMemoria());
});

TDD::ejecutar("VERDE: Limpiar memoria vuelve a cero", function() {
    $calc = new Calculator();
    $calc->guardarEnMemoria(100);
    $calc->limpiarMemoria();
    TDD::assertEquals(0, $calc->recuperarMemoria());
});

TDD::ejecutar("VERDE: Guardar resultado de operacion en memoria", function() {
    $calc = new Calculator();
    $resultado = $calc->add(15, 25);
    $calc->guardarEnMemoria($resultado);
    TDD::assertEquals(40, $calc->recuperarMemoria());
});

echo "\n";


// ============================================================================
// Ejemplo 1, Iteracion 6: Historial de operaciones (REFACTORIZAR)
// ============================================================================
echo "=== Iteracion 6: Historial + Refactorizacion ===\n\n";

/**
 * REFACTORIZACION: Agregamos un metodo ejecutar() que:
 *   1. Realiza la operacion
 *   2. Guarda en el historial
 *   3. Retorna el resultado
 *
 * Las pruebas anteriores SIGUEN PASANDO (no rompimos nada).
 * Agregamos pruebas nuevas para la funcionalidad de historial.
 */

TDD::ejecutar("REFACTOR: ejecutar() realiza suma correctamente", function() {
    $calc = new Calculator();
    TDD::assertEquals(8, $calc->ejecutar('sumar', 3, 5));
});

TDD::ejecutar("ROJO->VERDE: ejecutar() registra en historial", function() {
    $calc = new Calculator();
    $calc->ejecutar('sumar', 3, 5);
    $calc->ejecutar('multiplicar', 4, 6);

    $historial = $calc->obtenerHistorial();
    TDD::assertCount(2, $historial);
    TDD::assertEquals('sumar', $historial[0]['operacion']);
    TDD::assertEquals(8, $historial[0]['resultado']);
    TDD::assertEquals('multiplicar', $historial[1]['operacion']);
    TDD::assertEquals(24, $historial[1]['resultado']);
});

TDD::ejecutar("VERDE: Limpiar historial", function() {
    $calc = new Calculator();
    $calc->ejecutar('sumar', 1, 1);
    $calc->ejecutar('restar', 5, 3);
    TDD::assertCount(2, $calc->obtenerHistorial());

    $calc->limpiarHistorial();
    TDD::assertEmpty($calc->obtenerHistorial());
});

TDD::ejecutar("VERDE: Operacion desconocida lanza excepcion", function() {
    $calc = new Calculator();
    TDD::assertThrows(\InvalidArgumentException::class, function() use ($calc) {
        $calc->ejecutar('raiz_cuadrada', 9, 0);
    });
});

echo "\n";


// ============================================================================
// ============================================================================
//
//     EJEMPLO 2: TDD COMPLEJO - CONSTRUIR UN CARRITO DE COMPRAS
//
// ============================================================================
// ============================================================================
echo str_repeat("*", 60) . "\n";
echo "  EJEMPLO 2: TDD - CARRITO DE COMPRAS (ShoppingCart)\n";
echo str_repeat("*", 60) . "\n\n";

/**
 * Requisitos del carrito de compras (obtenidos del cliente):
 *
 * 1. Agregar productos con nombre, precio y cantidad
 * 2. Eliminar productos del carrito
 * 3. Actualizar la cantidad de un producto
 * 4. Calcular el subtotal
 * 5. Aplicar cupones de descuento (porcentaje y monto fijo)
 * 6. Calcular impuestos basados en el pais
 * 7. Calcular el total final (subtotal - descuento + impuesto)
 * 8. Vaciar el carrito
 *
 * Vamos a construir TODO esto con TDD, iteracion por iteracion.
 */


// ============================================================================
// Ejemplo 2, Iteracion 1: Estructura basica del carrito
// ============================================================================
echo "=== Iteracion 1: Carrito vacio y agregar productos ===\n\n";

/**
 * ROJO: Escribimos las pruebas primero:
 *   - El carrito nuevo esta vacio
 *   - Se pueden agregar productos
 *   - El carrito reporta la cantidad de items
 *
 * VERDE: Implementamos el minimo necesario.
 */

// Producto simple (Value Object)
class CartItem {
    public function __construct(
        public readonly string $sku,
        public readonly string $nombre,
        public readonly float $precio,
        public int $cantidad = 1
    ) {
        if ($precio < 0) {
            throw new \InvalidArgumentException("El precio no puede ser negativo");
        }
        if ($cantidad < 1) {
            throw new \InvalidArgumentException("La cantidad debe ser al menos 1");
        }
    }

    public function getSubtotal(): float {
        return round($this->precio * $this->cantidad, 2);
    }
}

// Cupones de descuento
abstract class Cupon {
    public function __construct(
        public readonly string $codigo,
        public readonly float $minimoCompra = 0
    ) {}

    abstract public function calcularDescuento(float $subtotal): float;

    public function esAplicable(float $subtotal): bool {
        return $subtotal >= $this->minimoCompra;
    }
}

class CuponPorcentaje extends Cupon {
    public function __construct(
        string $codigo,
        public readonly float $porcentaje,
        float $minimoCompra = 0
    ) {
        parent::__construct($codigo, $minimoCompra);
        if ($porcentaje < 0 || $porcentaje > 100) {
            throw new \InvalidArgumentException("Porcentaje debe estar entre 0 y 100");
        }
    }

    public function calcularDescuento(float $subtotal): float {
        if (!$this->esAplicable($subtotal)) {
            return 0;
        }
        return round($subtotal * ($this->porcentaje / 100), 2);
    }
}

class CuponMontoFijo extends Cupon {
    public function __construct(
        string $codigo,
        public readonly float $monto,
        float $minimoCompra = 0
    ) {
        parent::__construct($codigo, $minimoCompra);
    }

    public function calcularDescuento(float $subtotal): float {
        if (!$this->esAplicable($subtotal)) {
            return 0;
        }
        // El descuento no puede ser mayor que el subtotal
        return min($this->monto, $subtotal);
    }
}

// El carrito de compras (construido iterativamente con TDD)
class ShoppingCart {
    /** @var array<string, CartItem> */
    private array $items = [];
    private ?Cupon $cupon = null;
    private float $tasaImpuesto = 0;

    public function isEmpty(): bool {
        return empty($this->items);
    }

    public function getItemCount(): int {
        return array_sum(array_map(
            fn(CartItem $item) => $item->cantidad,
            $this->items
        ));
    }

    public function getUniqueItemCount(): int {
        return count($this->items);
    }

    public function addItem(CartItem $item): void {
        $sku = $item->sku;
        if (isset($this->items[$sku])) {
            // Si ya existe, sumar cantidades
            $existing = $this->items[$sku];
            $this->items[$sku] = new CartItem(
                $existing->sku,
                $existing->nombre,
                $existing->precio,
                $existing->cantidad + $item->cantidad
            );
        } else {
            $this->items[$sku] = $item;
        }
    }

    public function removeItem(string $sku): void {
        if (!isset($this->items[$sku])) {
            throw new \RuntimeException("Producto no encontrado en el carrito: {$sku}");
        }
        unset($this->items[$sku]);
    }

    public function updateQuantity(string $sku, int $cantidad): void {
        if (!isset($this->items[$sku])) {
            throw new \RuntimeException("Producto no encontrado: {$sku}");
        }
        if ($cantidad < 1) {
            throw new \InvalidArgumentException("La cantidad debe ser al menos 1");
        }
        $item = $this->items[$sku];
        $this->items[$sku] = new CartItem(
            $item->sku,
            $item->nombre,
            $item->precio,
            $cantidad
        );
    }

    public function getSubtotal(): float {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->getSubtotal();
        }
        return round($subtotal, 2);
    }

    public function applyCoupon(Cupon $cupon): void {
        $this->cupon = $cupon;
    }

    public function removeCoupon(): void {
        $this->cupon = null;
    }

    public function getDiscount(): float {
        if ($this->cupon === null) {
            return 0;
        }
        return $this->cupon->calcularDescuento($this->getSubtotal());
    }

    public function setTaxRate(float $tasa): void {
        if ($tasa < 0 || $tasa > 1) {
            throw new \InvalidArgumentException("La tasa debe estar entre 0 y 1");
        }
        $this->tasaImpuesto = $tasa;
    }

    public function getTax(): float {
        $baseImponible = $this->getSubtotal() - $this->getDiscount();
        return round($baseImponible * $this->tasaImpuesto, 2);
    }

    public function getTotal(): float {
        $subtotal = $this->getSubtotal();
        $descuento = $this->getDiscount();
        $impuesto = $this->getTax();
        return round($subtotal - $descuento + $impuesto, 2);
    }

    public function getItems(): array {
        return array_values($this->items);
    }

    public function clear(): void {
        $this->items = [];
        $this->cupon = null;
    }

    public function getResumen(): array {
        return [
            'items'       => $this->getItemCount(),
            'subtotal'    => $this->getSubtotal(),
            'descuento'   => $this->getDiscount(),
            'impuesto'    => $this->getTax(),
            'total'       => $this->getTotal(),
            'cupon'       => $this->cupon?->codigo,
        ];
    }
}

// PRUEBAS TDD - Iteracion 1:
TDD::ejecutar("ROJO->VERDE: Carrito nuevo esta vacio", function() {
    $cart = new ShoppingCart();
    TDD::assertTrue($cart->isEmpty());
    TDD::assertEquals(0, $cart->getItemCount());
});

TDD::ejecutar("ROJO->VERDE: Agregar producto al carrito", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop HP', 15999.99));

    TDD::assertFalse($cart->isEmpty());
    TDD::assertEquals(1, $cart->getItemCount());
});

TDD::ejecutar("VERDE: Agregar multiples productos", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99));
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 499.50, 2));

    TDD::assertEquals(2, $cart->getUniqueItemCount()); // 2 productos distintos
    TDD::assertEquals(3, $cart->getItemCount());        // 1 + 2 = 3 unidades
});

TDD::ejecutar("VERDE: Agregar mismo producto suma cantidades", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99, 1));
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99, 2));

    TDD::assertEquals(1, $cart->getUniqueItemCount()); // Sigue siendo 1 producto
    TDD::assertEquals(3, $cart->getItemCount());        // 1 + 2 = 3 unidades
});

echo "\n";


// ============================================================================
// Ejemplo 2, Iteracion 2: Eliminar y actualizar
// ============================================================================
echo "=== Iteracion 2: Eliminar y actualizar cantidades ===\n\n";

TDD::ejecutar("ROJO->VERDE: Eliminar producto del carrito", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99));
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 499.50));

    $cart->removeItem('LAP-001');
    TDD::assertEquals(1, $cart->getUniqueItemCount());
    TDD::assertEquals(1, $cart->getItemCount());
});

TDD::ejecutar("VERDE: Eliminar producto inexistente lanza excepcion", function() {
    $cart = new ShoppingCart();
    TDD::assertThrows(\RuntimeException::class, function() use ($cart) {
        $cart->removeItem('NOEXISTE');
    });
});

TDD::ejecutar("ROJO->VERDE: Actualizar cantidad de producto", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99, 1));

    $cart->updateQuantity('LAP-001', 3);
    TDD::assertEquals(3, $cart->getItemCount());
});

TDD::ejecutar("VERDE: Cantidad menor a 1 lanza excepcion", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99));

    TDD::assertThrows(\InvalidArgumentException::class, function() use ($cart) {
        $cart->updateQuantity('LAP-001', 0);
    });
});

echo "\n";


// ============================================================================
// Ejemplo 2, Iteracion 3: Calcular subtotal
// ============================================================================
echo "=== Iteracion 3: Calcular subtotal ===\n\n";

TDD::ejecutar("ROJO->VERDE: Subtotal de carrito vacio es 0", function() {
    $cart = new ShoppingCart();
    TDD::assertEquals(0, $cart->getSubtotal());
});

TDD::ejecutar("ROJO->VERDE: Subtotal con un producto", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99));
    TDD::assertEquals(15999.99, $cart->getSubtotal());
});

TDD::ejecutar("VERDE: Subtotal con multiples productos y cantidades", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 15999.99, 1)); // 15999.99
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 499.50, 2));     // 999.00
    $cart->addItem(new CartItem('TEC-001', 'Teclado', 1299.00, 1));  // 1299.00

    // 15999.99 + 999.00 + 1299.00 = 18297.99
    TDD::assertEquals(18297.99, $cart->getSubtotal());
});

echo "\n";


// ============================================================================
// Ejemplo 2, Iteracion 4: Cupones de descuento
// ============================================================================
echo "=== Iteracion 4: Cupones de descuento ===\n\n";

TDD::ejecutar("ROJO->VERDE: Cupon de porcentaje 10%", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 1000.00));

    $cupon = new CuponPorcentaje('DESC10', 10);
    $cart->applyCoupon($cupon);

    TDD::assertEquals(100.00, $cart->getDiscount()); // 10% de 1000
});

TDD::ejecutar("ROJO->VERDE: Cupon de monto fijo $200", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 1000.00));

    $cupon = new CuponMontoFijo('FIJO200', 200);
    $cart->applyCoupon($cupon);

    TDD::assertEquals(200.00, $cart->getDiscount());
});

TDD::ejecutar("VERDE: Cupon con minimo de compra no aplicable", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 100.00));

    // Cupon requiere minimo $500 de compra
    $cupon = new CuponPorcentaje('VIP20', 20, 500);
    $cart->applyCoupon($cupon);

    // No se aplica porque el subtotal (100) < minimo (500)
    TDD::assertEquals(0, $cart->getDiscount());
});

TDD::ejecutar("VERDE: Cupon fijo no excede el subtotal", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 100.00));

    // Cupon de $500 pero el carrito solo tiene $100
    $cupon = new CuponMontoFijo('MEGA500', 500);
    $cart->applyCoupon($cupon);

    // El descuento maximo es el subtotal
    TDD::assertEquals(100.00, $cart->getDiscount());
});

TDD::ejecutar("VERDE: Remover cupon elimina descuento", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 1000.00));
    $cart->applyCoupon(new CuponPorcentaje('DESC10', 10));

    TDD::assertEquals(100.00, $cart->getDiscount());

    $cart->removeCoupon();
    TDD::assertEquals(0, $cart->getDiscount());
});

echo "\n";


// ============================================================================
// Ejemplo 2, Iteracion 5: Impuestos
// ============================================================================
echo "=== Iteracion 5: Impuestos ===\n\n";

TDD::ejecutar("ROJO->VERDE: Impuesto sin descuento", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 1000.00));
    $cart->setTaxRate(0.16); // 16% IVA Mexico

    TDD::assertEquals(160.00, $cart->getTax());
});

TDD::ejecutar("VERDE: Impuesto se calcula DESPUES del descuento", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 1000.00));
    $cart->applyCoupon(new CuponPorcentaje('DESC10', 10)); // -100
    $cart->setTaxRate(0.16); // 16% sobre $900

    // Base imponible: 1000 - 100 = 900
    // Impuesto: 900 * 0.16 = 144
    TDD::assertEquals(144.00, $cart->getTax());
});

TDD::ejecutar("VERDE: Tasa invalida lanza excepcion", function() {
    $cart = new ShoppingCart();
    TDD::assertThrows(\InvalidArgumentException::class, function() use ($cart) {
        $cart->setTaxRate(1.5); // 150%? No valido
    });
});

echo "\n";


// ============================================================================
// Ejemplo 2, Iteracion 6: Total final y resumen
// ============================================================================
echo "=== Iteracion 6: Total final y resumen completo ===\n\n";

TDD::ejecutar("ROJO->VERDE: Total = subtotal - descuento + impuesto", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 1000.00));
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 500.00));

    // Subtotal: 1500.00
    $cart->applyCoupon(new CuponPorcentaje('DESC10', 10)); // Descuento: 150.00
    $cart->setTaxRate(0.16);

    // Base imponible: 1500 - 150 = 1350
    // Impuesto: 1350 * 0.16 = 216
    // Total: 1500 - 150 + 216 = 1566
    TDD::assertEquals(1500.00, $cart->getSubtotal());
    TDD::assertEquals(150.00, $cart->getDiscount());
    TDD::assertEquals(216.00, $cart->getTax());
    TDD::assertEquals(1566.00, $cart->getTotal());
});

TDD::ejecutar("VERDE: Total sin descuento ni impuesto", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 999.99));

    // Sin cupon ni impuesto, el total = subtotal
    TDD::assertEquals(999.99, $cart->getTotal());
});

TDD::ejecutar("VERDE: Resumen completo del carrito", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 2000.00, 1));
    $cart->addItem(new CartItem('MOU-001', 'Mouse', 300.00, 2));
    $cart->applyCoupon(new CuponMontoFijo('PROMO100', 100));
    $cart->setTaxRate(0.16);

    $resumen = $cart->getResumen();

    TDD::assertEquals(3, $resumen['items']);        // 1 + 2 unidades
    TDD::assertEquals(2600.00, $resumen['subtotal']); // 2000 + 600
    TDD::assertEquals(100.00, $resumen['descuento']); // Cupon fijo
    TDD::assertEquals(400.00, $resumen['impuesto']);   // (2600 - 100) * 0.16
    TDD::assertEquals(2900.00, $resumen['total']);     // 2600 - 100 + 400
    TDD::assertEquals('PROMO100', $resumen['cupon']);
});

TDD::ejecutar("VERDE: Vaciar carrito resetea todo", function() {
    $cart = new ShoppingCart();
    $cart->addItem(new CartItem('LAP-001', 'Laptop', 5000.00));
    $cart->applyCoupon(new CuponPorcentaje('MEGA', 50));

    $cart->clear();

    TDD::assertTrue($cart->isEmpty());
    TDD::assertEquals(0, $cart->getSubtotal());
    TDD::assertEquals(0, $cart->getDiscount());
    TDD::assertEquals(0, $cart->getTotal());
});

TDD::ejecutar("REFACTOR: Escenario completo de compra", function() {
    $cart = new ShoppingCart();

    // 1. El cliente agrega productos
    $cart->addItem(new CartItem('TEL-001', 'Smartphone Samsung', 12999.00, 1));
    $cart->addItem(new CartItem('FUN-001', 'Funda protectora', 299.00, 2));
    $cart->addItem(new CartItem('AUR-001', 'Auriculares Bluetooth', 1599.00, 1));

    // 2. Cambia de opinion y quiere 3 fundas
    $cart->updateQuantity('FUN-001', 3);

    // 3. Aplica un cupon de bienvenida
    $cart->applyCoupon(new CuponPorcentaje('BIENVENIDO15', 15));

    // 4. Configurar impuestos de Mexico
    $cart->setTaxRate(0.16);

    // Calculos esperados:
    // Subtotal: 12999 + (299 * 3) + 1599 = 12999 + 897 + 1599 = 15495.00
    // Descuento: 15495 * 0.15 = 2324.25
    // Base imponible: 15495 - 2324.25 = 13170.75
    // Impuesto: 13170.75 * 0.16 = 2107.32
    // Total: 15495 - 2324.25 + 2107.32 = 15278.07

    TDD::assertEquals(15495.00, $cart->getSubtotal());
    TDD::assertEquals(2324.25, $cart->getDiscount());
    TDD::assertEquals(2107.32, $cart->getTax());
    TDD::assertEquals(15278.07, $cart->getTotal());

    // Verificar resumen
    $resumen = $cart->getResumen();
    TDD::assertEquals(5, $resumen['items']); // 1 + 3 + 1
    TDD::assertEquals('BIENVENIDO15', $resumen['cupon']);
});

// ============================================================================
// RESUMEN FINAL
// ============================================================================
TDD::resumen();

/**
 * RESUMEN DEL CICLO TDD:
 *
 * +-------------------------------------------------------------------+
 * |                     CICLO ROJO-VERDE-REFACTOR                      |
 * +-------------------------------------------------------------------+
 * |                                                                     |
 * |   1. ROJO (Red)                                                     |
 * |      - Escribir una prueba que FALLE                                |
 * |      - La prueba define el COMPORTAMIENTO esperado                  |
 * |      - NO escribir codigo de produccion todavia                     |
 * |                                                                     |
 * |   2. VERDE (Green)                                                  |
 * |      - Escribir el MINIMO codigo para que la prueba PASE            |
 * |      - No optimizar, no pensar en "el futuro"                       |
 * |      - Solo hacer que la prueba pase                                |
 * |                                                                     |
 * |   3. REFACTORIZAR (Refactor)                                        |
 * |      - Mejorar el codigo sin cambiar el comportamiento              |
 * |      - Eliminar duplicacion, mejorar nombres                        |
 * |      - Las pruebas DEBEN seguir pasando                             |
 * |                                                                     |
 * |   Repetir el ciclo para cada nueva funcionalidad                    |
 * |                                                                     |
 * +-------------------------------------------------------------------+
 *
 * BENEFICIOS DE TDD:
 *   1. Codigo siempre cubierto por pruebas (cobertura alta)
 *   2. Diseno emerge de las necesidades reales (no especulativo)
 *   3. Deteccion temprana de errores
 *   4. Documentacion viva (las pruebas explican que hace el codigo)
 *   5. Confianza para refactorizar (las pruebas protegen)
 *   6. Codigo mas modular y desacoplado
 *
 * ERRORES COMUNES:
 *   1. Escribir demasiado codigo en el paso VERDE
 *   2. No refactorizar (acumular deuda tecnica)
 *   3. Escribir pruebas DESPUES del codigo (no es TDD)
 *   4. Pruebas que dependen de detalles de implementacion
 *   5. Saltarse el paso ROJO (no verificar que la prueba falla)
 */
?>
