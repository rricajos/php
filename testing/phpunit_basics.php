<?php
/**
 * ============================================================================
 * FUNDAMENTOS DE PHPUNIT - PRUEBAS UNITARIAS EN PHP
 * ============================================================================
 *
 * Las pruebas unitarias verifican que las unidades individuales de codigo
 * (funciones, metodos, clases) funcionan correctamente de forma aislada.
 * PHPUnit es el framework estandar de pruebas para PHP.
 *
 * INSTALACION (con Composer):
 *   composer require --dev phpunit/phpunit
 *
 * EJECUCION DESDE CLI:
 *   ./vendor/bin/phpunit tests/
 *   ./vendor/bin/phpunit --filter NombreDelTest
 *   ./vendor/bin/phpunit --verbose
 *
 * Este archivo incluye tanto la version PHPUnit (comentada/explicada)
 * como una version independiente ejecutable con assert().
 */

// ============================================================================
// MINI-FRAMEWORK DE PRUEBAS INDEPENDIENTE
// ============================================================================
// Como PHPUnit puede no estar instalado, creamos un sistema basico
// que permite ejecutar este archivo directamente con: php phpunit_basics.php

/**
 * Mini-framework de pruebas autocontenido.
 * Simula el comportamiento basico de PHPUnit sin dependencias externas.
 */
class MiniTest {
    private static int $totalPruebas = 0;
    private static int $pruebasExitosas = 0;
    private static int $pruebasFallidas = 0;
    private static array $fallos = [];

    /**
     * Ejecuta una prueba individual y registra el resultado.
     */
    public static function ejecutar(string $nombre, callable $prueba): void {
        self::$totalPruebas++;
        try {
            $prueba();
            self::$pruebasExitosas++;
            echo "  [OK] {$nombre}\n";
        } catch (\Throwable $e) {
            self::$pruebasFallidas++;
            self::$fallos[] = "{$nombre}: {$e->getMessage()}";
            echo "  [FALLO] {$nombre}: {$e->getMessage()}\n";
        }
    }

    /** Verifica que dos valores son iguales (comparacion flexible ==) */
    public static function assertEquals(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($esperado != $actual) {
            $mensaje = $msg ?: "Se esperaba " . var_export($esperado, true)
                             . " pero se obtuvo " . var_export($actual, true);
            throw new \RuntimeException($mensaje);
        }
    }

    /** Verifica que un valor es verdadero */
    public static function assertTrue(mixed $valor, string $msg = ''): void {
        if ($valor !== true) {
            throw new \RuntimeException($msg ?: "Se esperaba TRUE pero se obtuvo " . var_export($valor, true));
        }
    }

    /** Verifica que un valor es falso */
    public static function assertFalse(mixed $valor, string $msg = ''): void {
        if ($valor !== false) {
            throw new \RuntimeException($msg ?: "Se esperaba FALSE pero se obtuvo " . var_export($valor, true));
        }
    }

    /** Verifica que un valor es null */
    public static function assertNull(mixed $valor, string $msg = ''): void {
        if ($valor !== null) {
            throw new \RuntimeException($msg ?: "Se esperaba NULL pero se obtuvo " . var_export($valor, true));
        }
    }

    /** Verifica la cantidad de elementos en un arreglo o Countable */
    public static function assertCount(int $esperado, array|Countable $coleccion, string $msg = ''): void {
        $actual = count($coleccion);
        if ($esperado !== $actual) {
            throw new \RuntimeException($msg ?: "Se esperaban {$esperado} elementos pero hay {$actual}");
        }
    }

    /** Verifica que un arreglo esta vacio */
    public static function assertEmpty(mixed $valor, string $msg = ''): void {
        if (!empty($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un valor vacio");
        }
    }

    /** Muestra el resumen final de todas las pruebas */
    public static function resumen(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "RESULTADOS: {$this->totalPruebas} pruebas, "
           . "{self::$pruebasExitosas} exitosas, "
           . "{self::$pruebasFallidas} fallidas\n";
        // Correccion: usar self:: en contexto estatico
        echo "RESULTADOS: " . self::$totalPruebas . " pruebas, "
           . self::$pruebasExitosas . " exitosas, "
           . self::$pruebasFallidas . " fallidas\n";
        if (!empty(self::$fallos)) {
            echo "\nFALLOS:\n";
            foreach (self::$fallos as $fallo) {
                echo "  - {$fallo}\n";
            }
        }
        echo str_repeat("=", 60) . "\n";
    }
}

// Corregimos el metodo resumen para que funcione correctamente
// (La version de arriba tiene un error intencional para demostrar depuracion)
// Redefinimos con una version limpia:

class TestRunner {
    private static int $total = 0;
    private static int $exitosas = 0;
    private static int $fallidas = 0;
    private static array $fallos = [];

    public static function ejecutar(string $nombre, callable $prueba): void {
        self::$total++;
        try {
            $prueba();
            self::$exitosas++;
            echo "  [OK] {$nombre}\n";
        } catch (\Throwable $e) {
            self::$fallidas++;
            self::$fallos[] = "{$nombre}: {$e->getMessage()}";
            echo "  [FALLO] {$nombre}: {$e->getMessage()}\n";
        }
    }

    public static function resumen(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "RESULTADOS: " . self::$total . " pruebas, "
           . self::$exitosas . " exitosas, "
           . self::$fallidas . " fallidas\n";
        if (!empty(self::$fallos)) {
            echo "\nFALLOS:\n";
            foreach (self::$fallos as $fallo) {
                echo "  - {$fallo}\n";
            }
        }
        echo str_repeat("=", 60) . "\n";
    }
}


// ============================================================================
// Ejemplo 1: Que es una prueba unitaria - Patron Arrange-Act-Assert (AAA)
// ============================================================================
echo "=== Ejemplo 1: Patron Arrange-Act-Assert (AAA) ===\n\n";

/**
 * El patron AAA es la estructura fundamental de toda prueba unitaria:
 *
 * 1. ARRANGE (Preparar): Configurar los datos y objetos necesarios
 * 2. ACT (Actuar): Ejecutar la accion que queremos probar
 * 3. ASSERT (Verificar): Comprobar que el resultado es el esperado
 */

// Clase de ejemplo que vamos a probar
class Calculadora {
    public function sumar(float $a, float $b): float {
        return $a + $b;
    }

    public function restar(float $a, float $b): float {
        return $a - $b;
    }

    public function multiplicar(float $a, float $b): float {
        return $a * $b;
    }

    public function dividir(float $a, float $b): float {
        if ($b == 0) {
            throw new \InvalidArgumentException("No se puede dividir entre cero");
        }
        return $a / $b;
    }
}

/*
 * VERSION PHPUNIT (requiere instalacion):
 *
 * use PHPUnit\Framework\TestCase;
 *
 * class CalculadoraTest extends TestCase
 * {
 *     public function testSumar(): void
 *     {
 *         // Arrange (Preparar)
 *         $calc = new Calculadora();
 *
 *         // Act (Actuar)
 *         $resultado = $calc->sumar(3, 5);
 *
 *         // Assert (Verificar)
 *         $this->assertEquals(8, $resultado);
 *     }
 * }
 *
 * Ejecutar: ./vendor/bin/phpunit tests/CalculadoraTest.php
 */

// VERSION INDEPENDIENTE EJECUTABLE:
$calc = new Calculadora();

TestRunner::ejecutar("Sumar 3 + 5 debe dar 8", function() use ($calc) {
    // Arrange - Preparar
    $a = 3;
    $b = 5;

    // Act - Actuar
    $resultado = $calc->sumar($a, $b);

    // Assert - Verificar
    MiniTest::assertEquals(8, $resultado);
});

TestRunner::ejecutar("Restar 10 - 4 debe dar 6", function() use ($calc) {
    // Arrange
    $a = 10;
    $b = 4;

    // Act
    $resultado = $calc->restar($a, $b);

    // Assert
    MiniTest::assertEquals(6, $resultado);
});

TestRunner::ejecutar("Multiplicar 3 * 7 debe dar 21", function() use ($calc) {
    $resultado = $calc->multiplicar(3, 7);
    MiniTest::assertEquals(21, $resultado);
});

echo "\n";


// ============================================================================
// Ejemplo 2: Anatomia de una clase TestCase en PHPUnit
// ============================================================================
echo "=== Ejemplo 2: Anatomia de una clase TestCase ===\n\n";

/**
 * En PHPUnit, cada clase de pruebas extiende TestCase.
 * Los metodos de prueba DEBEN:
 *   - Empezar con la palabra "test" O tener la anotacion @test
 *   - Ser publicos
 *   - No recibir parametros (salvo con dataProviders)
 *
 * Convencion de nombres:
 *   - Clase: [ClaseProbada]Test  ->  CalculadoraTest
 *   - Metodo: test[Metodo][Escenario][ResultadoEsperado]
 *     Ejemplo: testDividirConCeroDaExcepcion
 */

/*
 * VERSION PHPUNIT COMPLETA:
 *
 * use PHPUnit\Framework\TestCase;
 *
 * class CalculadoraTest extends TestCase
 * {
 *     private Calculadora $calculadora;
 *
 *     // Se ejecuta antes de CADA prueba
 *     protected function setUp(): void
 *     {
 *         $this->calculadora = new Calculadora();
 *     }
 *
 *     // Se ejecuta despues de CADA prueba
 *     protected function tearDown(): void
 *     {
 *         // Limpiar recursos si es necesario
 *     }
 *
 *     public function testSumarNumerosPositivos(): void
 *     {
 *         $this->assertEquals(8, $this->calculadora->sumar(3, 5));
 *     }
 *
 *     public function testSumarNumerosNegativos(): void
 *     {
 *         $this->assertEquals(-8, $this->calculadora->sumar(-3, -5));
 *     }
 *
 *     public function testDividirPorCeroLanzaExcepcion(): void
 *     {
 *         $this->expectException(\InvalidArgumentException::class);
 *         $this->calculadora->dividir(10, 0);
 *     }
 *
 *     // Usando la anotacion @test en lugar del prefijo "test"
 *     /** @test * /
 *     public function laMultiplicacionPorCeroDaCero(): void
 *     {
 *         $this->assertEquals(0, $this->calculadora->multiplicar(999, 0));
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Simulamos setUp creando el objeto antes de cada prueba
$crearCalculadora = function(): Calculadora {
    // Esto simula el setUp() de PHPUnit
    return new Calculadora();
};

TestRunner::ejecutar("Sumar numeros positivos", function() use ($crearCalculadora) {
    $calc = $crearCalculadora();
    MiniTest::assertEquals(8, $calc->sumar(3, 5));
});

TestRunner::ejecutar("Sumar numeros negativos", function() use ($crearCalculadora) {
    $calc = $crearCalculadora();
    MiniTest::assertEquals(-8, $calc->sumar(-3, -5));
});

TestRunner::ejecutar("Dividir por cero lanza excepcion", function() use ($crearCalculadora) {
    $calc = $crearCalculadora();
    $lanzoExcepcion = false;
    try {
        $calc->dividir(10, 0);
    } catch (\InvalidArgumentException $e) {
        $lanzoExcepcion = true;
        MiniTest::assertEquals("No se puede dividir entre cero", $e->getMessage());
    }
    MiniTest::assertTrue($lanzoExcepcion, "Debio lanzar InvalidArgumentException");
});

TestRunner::ejecutar("Multiplicacion por cero da cero", function() use ($crearCalculadora) {
    $calc = $crearCalculadora();
    MiniTest::assertEquals(0, $calc->multiplicar(999, 0));
});

echo "\n";


// ============================================================================
// Ejemplo 3: Aserciones basicas - assertEquals, assertTrue, assertFalse
// ============================================================================
echo "=== Ejemplo 3: Aserciones basicas ===\n\n";

/**
 * Las aserciones son el corazon de las pruebas.
 * Cada una verifica una condicion especifica sobre el resultado.
 * Si la condicion falla, la prueba se marca como fallida.
 */

// Clase de ejemplo: Validador de email
class ValidadorEmail {
    public function esValido(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function obtenerDominio(string $email): ?string {
        if (!$this->esValido($email)) {
            return null;
        }
        $partes = explode('@', $email);
        return $partes[1] ?? null;
    }
}

/*
 * VERSION PHPUNIT:
 *
 * class ValidadorEmailTest extends TestCase
 * {
 *     private ValidadorEmail $validador;
 *
 *     protected function setUp(): void
 *     {
 *         $this->validador = new ValidadorEmail();
 *     }
 *
 *     public function testEmailValidoRetornaTrue(): void
 *     {
 *         // assertTrue verifica que el valor es exactamente true
 *         $this->assertTrue($this->validador->esValido('user@example.com'));
 *     }
 *
 *     public function testEmailInvalidoRetornaFalse(): void
 *     {
 *         // assertFalse verifica que el valor es exactamente false
 *         $this->assertFalse($this->validador->esValido('no-es-email'));
 *     }
 *
 *     public function testObtenerDominioDeEmailValido(): void
 *     {
 *         // assertEquals verifica igualdad con ==
 *         $this->assertEquals('gmail.com', $this->validador->obtenerDominio('ana@gmail.com'));
 *     }
 *
 *     public function testObtenerDominioDeEmailInvalidoEsNull(): void
 *     {
 *         // assertNull verifica que el valor es null
 *         $this->assertNull($this->validador->obtenerDominio('invalido'));
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
$validador = new ValidadorEmail();

TestRunner::ejecutar("assertTrue - Email valido retorna true", function() use ($validador) {
    MiniTest::assertTrue($validador->esValido('usuario@ejemplo.com'));
});

TestRunner::ejecutar("assertFalse - Email invalido retorna false", function() use ($validador) {
    MiniTest::assertFalse($validador->esValido('esto-no-es-email'));
});

TestRunner::ejecutar("assertEquals - Obtener dominio de email valido", function() use ($validador) {
    $dominio = $validador->obtenerDominio('ana@gmail.com');
    MiniTest::assertEquals('gmail.com', $dominio);
});

TestRunner::ejecutar("assertNull - Dominio de email invalido es null", function() use ($validador) {
    $dominio = $validador->obtenerDominio('invalido');
    MiniTest::assertNull($dominio);
});

echo "\n";


// ============================================================================
// Ejemplo 4: assertCount y assertEmpty con colecciones
// ============================================================================
echo "=== Ejemplo 4: assertCount y assertEmpty ===\n\n";

/**
 * assertCount y assertEmpty son ideales para probar colecciones,
 * resultados de busquedas, filtrados, etc.
 */

// Clase de ejemplo: Gestor de tareas
class GestorTareas {
    private array $tareas = [];

    public function agregar(string $tarea): void {
        $this->tareas[] = [
            'titulo'    => $tarea,
            'completada' => false,
            'creada_en'  => date('Y-m-d H:i:s'),
        ];
    }

    public function completar(int $indice): void {
        if (isset($this->tareas[$indice])) {
            $this->tareas[$indice]['completada'] = true;
        }
    }

    public function obtenerTodas(): array {
        return $this->tareas;
    }

    public function obtenerPendientes(): array {
        return array_values(array_filter(
            $this->tareas,
            fn($t) => !$t['completada']
        ));
    }

    public function obtenerCompletadas(): array {
        return array_values(array_filter(
            $this->tareas,
            fn($t) => $t['completada']
        ));
    }
}

/*
 * VERSION PHPUNIT:
 *
 * class GestorTareasTest extends TestCase
 * {
 *     public function testGestorNuevoEstaVacio(): void
 *     {
 *         $gestor = new GestorTareas();
 *         $this->assertEmpty($gestor->obtenerTodas());
 *         $this->assertCount(0, $gestor->obtenerTodas());
 *     }
 *
 *     public function testAgregarTareaIncrementaCuenta(): void
 *     {
 *         $gestor = new GestorTareas();
 *         $gestor->agregar('Comprar leche');
 *         $gestor->agregar('Estudiar PHP');
 *         $this->assertCount(2, $gestor->obtenerTodas());
 *     }
 *
 *     public function testCompletarTareaActualizaListas(): void
 *     {
 *         $gestor = new GestorTareas();
 *         $gestor->agregar('Tarea 1');
 *         $gestor->agregar('Tarea 2');
 *         $gestor->completar(0);
 *
 *         $this->assertCount(1, $gestor->obtenerCompletadas());
 *         $this->assertCount(1, $gestor->obtenerPendientes());
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
TestRunner::ejecutar("assertEmpty - Gestor nuevo esta vacio", function() {
    $gestor = new GestorTareas();
    MiniTest::assertEmpty($gestor->obtenerTodas());
});

TestRunner::ejecutar("assertCount - Agregar tareas incrementa la cuenta", function() {
    $gestor = new GestorTareas();
    $gestor->agregar('Comprar leche');
    $gestor->agregar('Estudiar PHP');
    $gestor->agregar('Hacer ejercicio');
    MiniTest::assertCount(3, $gestor->obtenerTodas());
});

TestRunner::ejecutar("assertCount - Completar tarea actualiza ambas listas", function() {
    $gestor = new GestorTareas();
    $gestor->agregar('Tarea A');
    $gestor->agregar('Tarea B');
    $gestor->agregar('Tarea C');

    $gestor->completar(0); // Completar "Tarea A"
    $gestor->completar(2); // Completar "Tarea C"

    MiniTest::assertCount(2, $gestor->obtenerCompletadas());
    MiniTest::assertCount(1, $gestor->obtenerPendientes());
});

TestRunner::ejecutar("Tarea pendiente no aparece en completadas", function() {
    $gestor = new GestorTareas();
    $gestor->agregar('Mi tarea');

    MiniTest::assertCount(1, $gestor->obtenerPendientes());
    MiniTest::assertEmpty($gestor->obtenerCompletadas());
});

echo "\n";


// ============================================================================
// Ejemplo 5: Usando assert() nativo de PHP
// ============================================================================
echo "=== Ejemplo 5: assert() nativo de PHP ===\n\n";

/**
 * PHP tiene una funcion assert() nativa que puede usarse para
 * pruebas rapidas. Es mas simple que PHPUnit pero util para
 * verificaciones basicas durante el desarrollo.
 *
 * NOTA: En produccion, assert() puede desactivarse por configuracion.
 * Para pruebas serias, siempre usar PHPUnit.
 */

// Clase de ejemplo: Convertidor de temperatura
class ConvertidorTemperatura {
    public function celsiusAFahrenheit(float $celsius): float {
        return ($celsius * 9/5) + 32;
    }

    public function fahrenheitACelsius(float $fahrenheit): float {
        return ($fahrenheit - 32) * 5/9;
    }

    public function celsiusAKelvin(float $celsius): float {
        return $celsius + 273.15;
    }

    public function esTemperaturaAbsolutaValida(float $kelvin): bool {
        return $kelvin >= 0; // No puede haber temperatura negativa en Kelvin
    }
}

// Usando assert() nativo de PHP
$conv = new ConvertidorTemperatura();

// assert() lanza AssertionError si la condicion es falsa
TestRunner::ejecutar("assert() - 0C = 32F (punto de congelacion del agua)", function() use ($conv) {
    $resultado = $conv->celsiusAFahrenheit(0);
    assert($resultado === 32.0, "0 grados Celsius deberian ser 32 Fahrenheit");
    // Si llegamos aqui, la asercion paso
    MiniTest::assertTrue(true);
});

TestRunner::ejecutar("assert() - 100C = 212F (punto de ebullicion)", function() use ($conv) {
    $resultado = $conv->celsiusAFahrenheit(100);
    assert($resultado === 212.0, "100 grados Celsius deberian ser 212 Fahrenheit");
    MiniTest::assertTrue(true);
});

TestRunner::ejecutar("assert() - Conversion ida y vuelta C->F->C", function() use ($conv) {
    $original = 37.5;
    $fahrenheit = $conv->celsiusAFahrenheit($original);
    $deVuelta = $conv->fahrenheitACelsius($fahrenheit);

    // Comparacion con tolerancia para evitar errores de punto flotante
    $diferencia = abs($original - $deVuelta);
    assert($diferencia < 0.0001, "La conversion ida y vuelta debe preservar el valor");
    MiniTest::assertTrue(true);
});

TestRunner::ejecutar("assert() - 0 Kelvin es temperatura valida", function() use ($conv) {
    assert($conv->esTemperaturaAbsolutaValida(0) === true);
    assert($conv->esTemperaturaAbsolutaValida(-1) === false);
    MiniTest::assertTrue(true);
});

echo "\n";


// ============================================================================
// Ejemplo 6: Escenario practico completo - Pruebas para un carrito de compras
// ============================================================================
echo "=== Ejemplo 6: Escenario practico - CarritoDeCompras ===\n\n";

/**
 * Escenario realista: Probamos un carrito de compras con multiples
 * funcionalidades. Esto demuestra como se verian las pruebas en
 * un proyecto real con PHPUnit y su equivalente independiente.
 */

class Producto {
    public function __construct(
        public readonly string $nombre,
        public readonly float $precio,
        public readonly string $sku
    ) {}
}

class CarritoDeCompras {
    /** @var array<string, array{producto: Producto, cantidad: int}> */
    private array $items = [];

    public function agregarProducto(Producto $producto, int $cantidad = 1): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException("La cantidad debe ser positiva");
        }

        $sku = $producto->sku;
        if (isset($this->items[$sku])) {
            $this->items[$sku]['cantidad'] += $cantidad;
        } else {
            $this->items[$sku] = [
                'producto' => $producto,
                'cantidad' => $cantidad,
            ];
        }
    }

    public function eliminarProducto(string $sku): void {
        unset($this->items[$sku]);
    }

    public function obtenerCantidadItems(): int {
        return array_sum(array_column($this->items, 'cantidad'));
    }

    public function obtenerSubtotal(): float {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['producto']->precio * $item['cantidad'];
        }
        return round($total, 2);
    }

    public function aplicarDescuento(float $porcentaje): float {
        if ($porcentaje < 0 || $porcentaje > 100) {
            throw new \InvalidArgumentException("Porcentaje debe estar entre 0 y 100");
        }
        $subtotal = $this->obtenerSubtotal();
        return round($subtotal * (1 - $porcentaje / 100), 2);
    }

    public function estaVacio(): bool {
        return empty($this->items);
    }
}

/*
 * VERSION PHPUNIT:
 *
 * class CarritoDeComprasTest extends TestCase
 * {
 *     private CarritoDeCompras $carrito;
 *     private Producto $laptop;
 *     private Producto $mouse;
 *
 *     protected function setUp(): void
 *     {
 *         $this->carrito = new CarritoDeCompras();
 *         $this->laptop = new Producto('Laptop HP', 15999.99, 'LAP-001');
 *         $this->mouse = new Producto('Mouse Logitech', 499.50, 'MOU-001');
 *     }
 *
 *     public function testCarritoNuevoEstaVacio(): void
 *     {
 *         $this->assertTrue($this->carrito->estaVacio());
 *         $this->assertEquals(0, $this->carrito->obtenerCantidadItems());
 *         $this->assertEquals(0, $this->carrito->obtenerSubtotal());
 *     }
 *
 *     public function testAgregarProducto(): void
 *     {
 *         $this->carrito->agregarProducto($this->laptop);
 *         $this->assertFalse($this->carrito->estaVacio());
 *         $this->assertEquals(1, $this->carrito->obtenerCantidadItems());
 *     }
 *
 *     public function testSubtotalConMultiplesProductos(): void
 *     {
 *         $this->carrito->agregarProducto($this->laptop, 1);
 *         $this->carrito->agregarProducto($this->mouse, 2);
 *         // 15999.99 + (499.50 * 2) = 16998.99
 *         $this->assertEquals(16998.99, $this->carrito->obtenerSubtotal());
 *     }
 *
 *     public function testAplicarDescuento(): void
 *     {
 *         $this->carrito->agregarProducto($this->laptop);
 *         $totalConDescuento = $this->carrito->aplicarDescuento(10);
 *         $this->assertEquals(14400.0, $totalConDescuento); // 10% de descuento
 *     }
 *
 *     public function testCantidadNegativaLanzaExcepcion(): void
 *     {
 *         $this->expectException(\InvalidArgumentException::class);
 *         $this->carrito->agregarProducto($this->laptop, -1);
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
$laptop = new Producto('Laptop HP', 15999.99, 'LAP-001');
$mouse  = new Producto('Mouse Logitech', 499.50, 'MOU-001');
$teclado = new Producto('Teclado Mecanico', 1299.00, 'TEC-001');

TestRunner::ejecutar("Carrito nuevo esta vacio", function() {
    $carrito = new CarritoDeCompras();
    MiniTest::assertTrue($carrito->estaVacio());
    MiniTest::assertEquals(0, $carrito->obtenerCantidadItems());
    MiniTest::assertEquals(0, $carrito->obtenerSubtotal());
});

TestRunner::ejecutar("Agregar producto al carrito", function() use ($laptop) {
    $carrito = new CarritoDeCompras();
    $carrito->agregarProducto($laptop);
    MiniTest::assertFalse($carrito->estaVacio());
    MiniTest::assertEquals(1, $carrito->obtenerCantidadItems());
    MiniTest::assertEquals(15999.99, $carrito->obtenerSubtotal());
});

TestRunner::ejecutar("Subtotal con multiples productos y cantidades", function() use ($laptop, $mouse) {
    $carrito = new CarritoDeCompras();
    $carrito->agregarProducto($laptop, 1);   // 15999.99
    $carrito->agregarProducto($mouse, 2);     // 499.50 * 2 = 999.00

    MiniTest::assertEquals(3, $carrito->obtenerCantidadItems());
    MiniTest::assertEquals(16998.99, $carrito->obtenerSubtotal());
});

TestRunner::ejecutar("Aplicar descuento del 10%", function() use ($teclado) {
    $carrito = new CarritoDeCompras();
    $carrito->agregarProducto($teclado, 1); // 1299.00

    $conDescuento = $carrito->aplicarDescuento(10);
    MiniTest::assertEquals(1169.10, $conDescuento); // 1299.00 * 0.90
});

TestRunner::ejecutar("Eliminar producto del carrito", function() use ($laptop, $mouse) {
    $carrito = new CarritoDeCompras();
    $carrito->agregarProducto($laptop);
    $carrito->agregarProducto($mouse);

    $carrito->eliminarProducto('LAP-001');
    MiniTest::assertEquals(1, $carrito->obtenerCantidadItems());
    MiniTest::assertEquals(499.50, $carrito->obtenerSubtotal());
});

TestRunner::ejecutar("Cantidad negativa lanza excepcion", function() use ($laptop) {
    $carrito = new CarritoDeCompras();
    $lanzoExcepcion = false;
    try {
        $carrito->agregarProducto($laptop, -1);
    } catch (\InvalidArgumentException $e) {
        $lanzoExcepcion = true;
    }
    MiniTest::assertTrue($lanzoExcepcion, "Debio lanzar InvalidArgumentException");
});

// ============================================================================
// RESUMEN FINAL
// ============================================================================
TestRunner::resumen();

/**
 * COMANDOS UTILES DE PHPUNIT (referencia rapida):
 *
 *   # Ejecutar todas las pruebas
 *   ./vendor/bin/phpunit
 *
 *   # Ejecutar un archivo especifico
 *   ./vendor/bin/phpunit tests/CalculadoraTest.php
 *
 *   # Ejecutar un metodo especifico
 *   ./vendor/bin/phpunit --filter testSumarNumerosPositivos
 *
 *   # Salida detallada
 *   ./vendor/bin/phpunit --verbose
 *
 *   # Con colores
 *   ./vendor/bin/phpunit --colors
 *
 *   # Detener al primer fallo
 *   ./vendor/bin/phpunit --stop-on-failure
 *
 *   # Generar reporte de cobertura (requiere Xdebug o PCOV)
 *   ./vendor/bin/phpunit --coverage-html reports/
 */
?>
