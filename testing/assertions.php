<?php
/**
 * ============================================================================
 * CATALOGO COMPLETO DE ASERCIONES EN PHPUNIT
 * ============================================================================
 *
 * PHPUnit ofrece mas de 60 metodos de asercion. Este archivo cubre
 * los mas importantes y frecuentemente utilizados, con ejemplos
 * practicos y su equivalente independiente ejecutable.
 *
 * Cada asercion verifica una condicion. Si la condicion no se cumple,
 * la prueba falla inmediatamente con un mensaje descriptivo.
 */

// ============================================================================
// MINI-FRAMEWORK DE ASERCIONES INDEPENDIENTE
// ============================================================================

class Afirmar {
    private static int $total = 0;
    private static int $ok = 0;
    private static int $fallos = 0;
    private static array $errores = [];

    public static function ejecutar(string $nombre, callable $prueba): void {
        self::$total++;
        try {
            $prueba();
            self::$ok++;
            echo "  [OK] {$nombre}\n";
        } catch (\Throwable $e) {
            self::$fallos++;
            self::$errores[] = "{$nombre}: {$e->getMessage()}";
            echo "  [FALLO] {$nombre}: {$e->getMessage()}\n";
        }
    }

    // --- Igualdad ---
    public static function assertEquals(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($esperado != $actual) {
            throw new \RuntimeException(
                $msg ?: "assertEquals fallo: esperado " . var_export($esperado, true)
                      . ", obtenido " . var_export($actual, true)
            );
        }
    }

    public static function assertSame(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($esperado !== $actual) {
            throw new \RuntimeException(
                $msg ?: "assertSame fallo: esperado " . var_export($esperado, true)
                      . " (tipo " . gettype($esperado) . "), obtenido "
                      . var_export($actual, true) . " (tipo " . gettype($actual) . ")"
            );
        }
    }

    public static function assertNotEquals(mixed $noEsperado, mixed $actual, string $msg = ''): void {
        if ($noEsperado == $actual) {
            throw new \RuntimeException(
                $msg ?: "assertNotEquals fallo: los valores NO deberian ser iguales"
            );
        }
    }

    // --- Booleanos y Nulos ---
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

    public static function assertNull(mixed $valor, string $msg = ''): void {
        if ($valor !== null) {
            throw new \RuntimeException($msg ?: "Se esperaba NULL");
        }
    }

    public static function assertNotNull(mixed $valor, string $msg = ''): void {
        if ($valor === null) {
            throw new \RuntimeException($msg ?: "Se esperaba un valor NO nulo");
        }
    }

    // --- Arreglos ---
    public static function assertContains(mixed $aguja, array $pajar, string $msg = ''): void {
        if (!in_array($aguja, $pajar, true)) {
            throw new \RuntimeException(
                $msg ?: "assertContains fallo: " . var_export($aguja, true) . " no encontrado en el arreglo"
            );
        }
    }

    public static function assertArrayHasKey(string|int $clave, array $arreglo, string $msg = ''): void {
        if (!array_key_exists($clave, $arreglo)) {
            throw new \RuntimeException($msg ?: "La clave '{$clave}' no existe en el arreglo");
        }
    }

    public static function assertCount(int $esperado, array|Countable $coleccion, string $msg = ''): void {
        $actual = count($coleccion);
        if ($esperado !== $actual) {
            throw new \RuntimeException($msg ?: "Se esperaban {$esperado} elementos, hay {$actual}");
        }
    }

    public static function assertEmpty(mixed $valor, string $msg = ''): void {
        if (!empty($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un valor vacio");
        }
    }

    public static function assertNotEmpty(mixed $valor, string $msg = ''): void {
        if (empty($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un valor NO vacio");
        }
    }

    // --- Cadenas ---
    public static function assertStringContainsString(string $aguja, string $pajar, string $msg = ''): void {
        if (strpos($pajar, $aguja) === false) {
            throw new \RuntimeException(
                $msg ?: "La cadena no contiene '{$aguja}'"
            );
        }
    }

    public static function assertStringStartsWith(string $prefijo, string $cadena, string $msg = ''): void {
        if (!str_starts_with($cadena, $prefijo)) {
            throw new \RuntimeException($msg ?: "La cadena no empieza con '{$prefijo}'");
        }
    }

    public static function assertStringEndsWith(string $sufijo, string $cadena, string $msg = ''): void {
        if (!str_ends_with($cadena, $sufijo)) {
            throw new \RuntimeException($msg ?: "La cadena no termina con '{$sufijo}'");
        }
    }

    public static function assertMatchesRegularExpression(string $patron, string $cadena, string $msg = ''): void {
        if (!preg_match($patron, $cadena)) {
            throw new \RuntimeException(
                $msg ?: "La cadena '{$cadena}' no coincide con el patron '{$patron}'"
            );
        }
    }

    // --- Tipos ---
    public static function assertInstanceOf(string $clase, mixed $objeto, string $msg = ''): void {
        if (!($objeto instanceof $clase)) {
            $tipoActual = is_object($objeto) ? get_class($objeto) : gettype($objeto);
            throw new \RuntimeException(
                $msg ?: "Se esperaba instancia de {$clase}, se obtuvo {$tipoActual}"
            );
        }
    }

    public static function assertIsArray(mixed $valor, string $msg = ''): void {
        if (!is_array($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un arreglo, se obtuvo " . gettype($valor));
        }
    }

    public static function assertIsString(mixed $valor, string $msg = ''): void {
        if (!is_string($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba una cadena, se obtuvo " . gettype($valor));
        }
    }

    public static function assertIsBool(mixed $valor, string $msg = ''): void {
        if (!is_bool($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un booleano, se obtuvo " . gettype($valor));
        }
    }

    public static function assertIsInt(mixed $valor, string $msg = ''): void {
        if (!is_int($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un entero, se obtuvo " . gettype($valor));
        }
    }

    public static function assertIsFloat(mixed $valor, string $msg = ''): void {
        if (!is_float($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba un flotante, se obtuvo " . gettype($valor));
        }
    }

    // --- Comparacion numerica ---
    public static function assertGreaterThan(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($actual <= $esperado) {
            throw new \RuntimeException(
                $msg ?: "{$actual} no es mayor que {$esperado}"
            );
        }
    }

    public static function assertLessThan(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($actual >= $esperado) {
            throw new \RuntimeException(
                $msg ?: "{$actual} no es menor que {$esperado}"
            );
        }
    }

    public static function assertGreaterThanOrEqual(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($actual < $esperado) {
            throw new \RuntimeException($msg ?: "{$actual} no es >= {$esperado}");
        }
    }

    public static function assertLessThanOrEqual(mixed $esperado, mixed $actual, string $msg = ''): void {
        if ($actual > $esperado) {
            throw new \RuntimeException($msg ?: "{$actual} no es <= {$esperado}");
        }
    }

    // --- Excepciones ---
    public static function assertThrows(string $claseExcepcion, callable $codigo, string $msg = ''): void {
        try {
            $codigo();
            throw new \RuntimeException(
                $msg ?: "Se esperaba la excepcion {$claseExcepcion} pero no se lanzo"
            );
        } catch (\Throwable $e) {
            if (!($e instanceof $claseExcepcion)) {
                throw new \RuntimeException(
                    $msg ?: "Se esperaba {$claseExcepcion} pero se lanzo " . get_class($e)
                );
            }
        }
    }

    public static function resumen(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "RESULTADOS: " . self::$total . " pruebas, "
           . self::$ok . " exitosas, "
           . self::$fallos . " fallidas\n";
        if (!empty(self::$errores)) {
            echo "\nDETALLE DE FALLOS:\n";
            foreach (self::$errores as $error) {
                echo "  - {$error}\n";
            }
        }
        echo str_repeat("=", 60) . "\n";
    }
}


// ============================================================================
// Ejemplo 1: assertEquals vs assertSame (comparacion flexible vs estricta)
// ============================================================================
echo "=== Ejemplo 1: assertEquals vs assertSame (== vs ===) ===\n\n";

/**
 * assertEquals  usa == (comparacion flexible / type juggling)
 * assertSame    usa === (comparacion estricta, mismo tipo y valor)
 *
 * Esta diferencia es CRITICA en PHP por las conversiones automaticas de tipos.
 *
 * VERSION PHPUNIT:
 *
 * class ComparacionTest extends TestCase
 * {
 *     public function testAssertEqualsEsFlexible(): void
 *     {
 *         $this->assertEquals(0, '0');       // PASA (0 == '0')
 *         $this->assertEquals(0, false);     // PASA (0 == false)
 *         $this->assertEquals('1', true);    // PASA ('1' == true)
 *         $this->assertEquals(100, '100');   // PASA (100 == '100')
 *     }
 *
 *     public function testAssertSameEsEstricta(): void
 *     {
 *         $this->assertSame(100, 100);       // PASA (mismo tipo y valor)
 *         // $this->assertSame(100, '100');   // FALLA (int !== string)
 *         // $this->assertSame(0, false);     // FALLA (int !== bool)
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
Afirmar::ejecutar("assertEquals: 0 == '0' (coercion de tipo)", function() {
    Afirmar::assertEquals(0, '0'); // Pasa porque PHP convierte '0' a 0
});

Afirmar::ejecutar("assertEquals: 100 == '100' (flexible)", function() {
    Afirmar::assertEquals(100, '100'); // Pasa por coercion de tipo
});

Afirmar::ejecutar("assertSame: 100 === 100 (mismo tipo y valor)", function() {
    Afirmar::assertSame(100, 100); // Pasa: ambos son int con valor 100
});

Afirmar::ejecutar("assertSame: int vs string son DIFERENTES", function() {
    // assertSame(100, '100') FALLARIA porque int !== string
    // Demostramos que SON diferentes con estricto
    $sonIgualesEstricto = (100 === '100');
    Afirmar::assertFalse($sonIgualesEstricto);

    // Pero SON iguales con flexible
    $sonIgualesFlexible = (100 == '100');
    Afirmar::assertTrue($sonIgualesFlexible);
});

Afirmar::ejecutar("assertNotEquals: valores diferentes", function() {
    Afirmar::assertNotEquals('hola', 'mundo');
    Afirmar::assertNotEquals(42, 99);
});

echo "\n";


// ============================================================================
// Ejemplo 2: Aserciones de cadenas y expresiones regulares
// ============================================================================
echo "=== Ejemplo 2: Aserciones de cadenas (String Assertions) ===\n\n";

/**
 * PHPUnit ofrece aserciones especializadas para cadenas:
 *   assertStringContainsString()    - Contiene subcadena
 *   assertStringStartsWith()        - Empieza con prefijo
 *   assertStringEndsWith()          - Termina con sufijo
 *   assertMatchesRegularExpression() - Coincide con regex
 *
 * VERSION PHPUNIT:
 *
 * class CadenasTest extends TestCase
 * {
 *     public function testContieneSubcadena(): void
 *     {
 *         $mensaje = "Error: el archivo no fue encontrado en el sistema";
 *         $this->assertStringContainsString('no fue encontrado', $mensaje);
 *     }
 *
 *     public function testEmpiezaConPrefijo(): void
 *     {
 *         $url = "https://api.ejemplo.com/usuarios";
 *         $this->assertStringStartsWith('https://', $url);
 *     }
 *
 *     public function testExpresionRegular(): void
 *     {
 *         $telefono = "+52-55-1234-5678";
 *         $this->assertMatchesRegularExpression('/^\+\d{2}-\d{2}-\d{4}-\d{4}$/', $telefono);
 *     }
 * }
 */

// Clase de ejemplo: Generador de URLs
class GeneradorURL {
    public function crearSlug(string $titulo): string {
        $slug = mb_strtolower($titulo);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    public function construirURL(string $base, string $ruta, array $params = []): string {
        $url = rtrim($base, '/') . '/' . ltrim($ruta, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    public function formatearTelefono(string $numero): string {
        // Eliminar todo excepto digitos
        $digitos = preg_replace('/\D/', '', $numero);
        if (strlen($digitos) === 10) {
            return sprintf("(%s) %s-%s",
                substr($digitos, 0, 3),
                substr($digitos, 3, 3),
                substr($digitos, 6, 4)
            );
        }
        return $numero; // Devolver sin cambios si no tiene 10 digitos
    }
}

// VERSION INDEPENDIENTE:
$gen = new GeneradorURL();

Afirmar::ejecutar("assertStringContainsString - Slug contiene palabras clave", function() use ($gen) {
    $slug = $gen->crearSlug("Como Aprender PHP en 30 Dias");
    Afirmar::assertStringContainsString('aprender', $slug);
    Afirmar::assertStringContainsString('php', $slug);
});

Afirmar::ejecutar("assertStringStartsWith - URL empieza con base correcta", function() use ($gen) {
    $url = $gen->construirURL('https://api.ejemplo.com', '/usuarios', ['page' => 1]);
    Afirmar::assertStringStartsWith('https://api.ejemplo.com', $url);
});

Afirmar::ejecutar("assertStringEndsWith - URL termina con parametros", function() use ($gen) {
    $url = $gen->construirURL('https://api.ejemplo.com', '/buscar', ['q' => 'php']);
    Afirmar::assertStringEndsWith('q=php', $url);
});

Afirmar::ejecutar("assertMatchesRegularExpression - Telefono formateado", function() use ($gen) {
    $formateado = $gen->formatearTelefono('5512345678');
    // Debe tener formato (XXX) XXX-XXXX
    Afirmar::assertMatchesRegularExpression('/^\(\d{3}\) \d{3}-\d{4}$/', $formateado);
});

Afirmar::ejecutar("Slug no contiene caracteres especiales", function() use ($gen) {
    $slug = $gen->crearSlug("!Hola Mundo! @2024 #PHP");
    // El slug solo debe tener letras minusculas, numeros y guiones
    Afirmar::assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);
});

echo "\n";


// ============================================================================
// Ejemplo 3: Aserciones de arreglos (assertContains, assertArrayHasKey, etc.)
// ============================================================================
echo "=== Ejemplo 3: Aserciones de arreglos ===\n\n";

/**
 * Aserciones para trabajar con arreglos y colecciones:
 *   assertContains()     - Un valor esta en el arreglo
 *   assertArrayHasKey()  - Una clave existe en el arreglo
 *   assertCount()        - El arreglo tiene N elementos
 *   assertEmpty()        - El arreglo esta vacio
 *   assertIsArray()      - El valor es un arreglo
 *
 * VERSION PHPUNIT:
 *
 * class ArreglosTest extends TestCase
 * {
 *     public function testRolesDeUsuario(): void
 *     {
 *         $roles = ['admin', 'editor', 'viewer'];
 *         $this->assertContains('admin', $roles);
 *         $this->assertNotContains('superuser', $roles);
 *         $this->assertCount(3, $roles);
 *     }
 *
 *     public function testEstructuraDeRespuesta(): void
 *     {
 *         $respuesta = ['status' => 200, 'data' => [], 'message' => 'OK'];
 *         $this->assertArrayHasKey('status', $respuesta);
 *         $this->assertArrayHasKey('data', $respuesta);
 *     }
 * }
 */

// Clase de ejemplo: Inventario de productos
class Inventario {
    private array $productos = [];

    public function agregar(string $nombre, int $cantidad, string $categoria): void {
        $this->productos[$nombre] = [
            'cantidad'  => $cantidad,
            'categoria' => $categoria,
        ];
    }

    public function obtenerPorCategoria(string $categoria): array {
        return array_filter(
            $this->productos,
            fn($p) => $p['categoria'] === $categoria
        );
    }

    public function obtenerNombres(): array {
        return array_keys($this->productos);
    }

    public function obtenerProducto(string $nombre): ?array {
        return $this->productos[$nombre] ?? null;
    }

    public function obtenerCategorias(): array {
        return array_unique(array_column($this->productos, 'categoria'));
    }
}

// VERSION INDEPENDIENTE:
$inv = new Inventario();
$inv->agregar('Laptop Dell', 15, 'electronica');
$inv->agregar('Monitor LG', 8, 'electronica');
$inv->agregar('Silla Ergonomica', 20, 'muebles');
$inv->agregar('Escritorio', 5, 'muebles');
$inv->agregar('Mouse Logitech', 50, 'electronica');

Afirmar::ejecutar("assertContains - Producto existe en la lista", function() use ($inv) {
    $nombres = $inv->obtenerNombres();
    Afirmar::assertContains('Laptop Dell', $nombres);
    Afirmar::assertContains('Silla Ergonomica', $nombres);
});

Afirmar::ejecutar("assertArrayHasKey - Producto tiene estructura correcta", function() use ($inv) {
    $producto = $inv->obtenerProducto('Laptop Dell');
    Afirmar::assertNotNull($producto);
    Afirmar::assertArrayHasKey('cantidad', $producto);
    Afirmar::assertArrayHasKey('categoria', $producto);
});

Afirmar::ejecutar("assertCount - Filtrar por categoria electronica", function() use ($inv) {
    $electronica = $inv->obtenerPorCategoria('electronica');
    Afirmar::assertCount(3, $electronica); // Laptop, Monitor, Mouse
});

Afirmar::ejecutar("assertIsArray - obtenerNombres retorna arreglo", function() use ($inv) {
    $nombres = $inv->obtenerNombres();
    Afirmar::assertIsArray($nombres);
    Afirmar::assertNotEmpty($nombres);
});

Afirmar::ejecutar("assertEmpty - Categoria inexistente retorna vacio", function() use ($inv) {
    $resultado = $inv->obtenerPorCategoria('ropa');
    Afirmar::assertEmpty($resultado);
});

Afirmar::ejecutar("assertNull - Producto inexistente retorna null", function() use ($inv) {
    $producto = $inv->obtenerProducto('Producto Fantasma');
    Afirmar::assertNull($producto);
});

echo "\n";


// ============================================================================
// Ejemplo 4: Aserciones de tipo e instanceof
// ============================================================================
echo "=== Ejemplo 4: Aserciones de tipo (assertInstanceOf, assertIs*) ===\n\n";

/**
 * Verificar el tipo de los valores retornados es esencial
 * para mantener contratos claros entre componentes.
 *
 * VERSION PHPUNIT:
 *
 * class TiposTest extends TestCase
 * {
 *     public function testInstanceOf(): void
 *     {
 *         $usuario = UsuarioFactory::crear('admin');
 *         $this->assertInstanceOf(Usuario::class, $usuario);
 *         $this->assertInstanceOf(EntidadBase::class, $usuario); // herencia
 *     }
 *
 *     public function testTiposDeRetorno(): void
 *     {
 *         $config = new Configuracion();
 *         $this->assertIsString($config->obtener('nombre_app'));
 *         $this->assertIsInt($config->obtener('puerto'));
 *         $this->assertIsBool($config->obtener('modo_debug'));
 *         $this->assertIsArray($config->obtener('idiomas_soportados'));
 *     }
 * }
 */

// Clases de ejemplo: Sistema de notificaciones
interface Notificacion {
    public function obtenerMensaje(): string;
    public function obtenerPrioridad(): int;
}

class NotificacionEmail implements Notificacion {
    public function __construct(
        private string $destinatario,
        private string $asunto,
        private string $cuerpo,
        private int $prioridad = 1
    ) {}

    public function obtenerMensaje(): string {
        return "Email a {$this->destinatario}: {$this->asunto}";
    }

    public function obtenerPrioridad(): int {
        return $this->prioridad;
    }

    public function obtenerDestinatario(): string {
        return $this->destinatario;
    }
}

class NotificacionSMS implements Notificacion {
    public function __construct(
        private string $telefono,
        private string $texto,
        private int $prioridad = 2
    ) {}

    public function obtenerMensaje(): string {
        return "SMS a {$this->telefono}: {$this->texto}";
    }

    public function obtenerPrioridad(): int {
        return $this->prioridad;
    }
}

class FabricaNotificaciones {
    public static function crear(string $tipo, array $datos): Notificacion {
        return match($tipo) {
            'email' => new NotificacionEmail(
                $datos['destinatario'],
                $datos['asunto'],
                $datos['cuerpo']
            ),
            'sms' => new NotificacionSMS(
                $datos['telefono'],
                $datos['texto']
            ),
            default => throw new \InvalidArgumentException("Tipo desconocido: {$tipo}")
        };
    }

    public static function obtenerEstadisticas(array $notificaciones): array {
        return [
            'total'    => count($notificaciones),
            'tipos'    => array_unique(array_map(fn($n) => get_class($n), $notificaciones)),
            'urgentes' => count(array_filter($notificaciones, fn($n) => $n->obtenerPrioridad() > 3)),
            'activo'   => true,
        ];
    }
}

// VERSION INDEPENDIENTE:
Afirmar::ejecutar("assertInstanceOf - Fabrica crea NotificacionEmail", function() {
    $notif = FabricaNotificaciones::crear('email', [
        'destinatario' => 'usuario@ejemplo.com',
        'asunto'       => 'Bienvenido',
        'cuerpo'       => 'Gracias por registrarte',
    ]);
    // Verificar que es instancia de la clase concreta
    Afirmar::assertInstanceOf(NotificacionEmail::class, $notif);
    // Verificar que implementa la interfaz
    Afirmar::assertInstanceOf(Notificacion::class, $notif);
});

Afirmar::ejecutar("assertInstanceOf - Fabrica crea NotificacionSMS", function() {
    $notif = FabricaNotificaciones::crear('sms', [
        'telefono' => '+52-55-1234-5678',
        'texto'    => 'Codigo de verificacion: 4821',
    ]);
    Afirmar::assertInstanceOf(NotificacionSMS::class, $notif);
    Afirmar::assertInstanceOf(Notificacion::class, $notif);
});

Afirmar::ejecutar("assertIs* - Verificar tipos de retorno en estadisticas", function() {
    $notificaciones = [
        FabricaNotificaciones::crear('email', [
            'destinatario' => 'a@b.com', 'asunto' => 'Test', 'cuerpo' => 'Hola',
        ]),
        FabricaNotificaciones::crear('sms', [
            'telefono' => '1234567890', 'texto' => 'Hola',
        ]),
    ];

    $stats = FabricaNotificaciones::obtenerEstadisticas($notificaciones);

    Afirmar::assertIsArray($stats);
    Afirmar::assertIsInt($stats['total']);
    Afirmar::assertIsArray($stats['tipos']);
    Afirmar::assertIsInt($stats['urgentes']);
    Afirmar::assertIsBool($stats['activo']);
});

Afirmar::ejecutar("assertIsString - Mensaje de notificacion es cadena", function() {
    $notif = FabricaNotificaciones::crear('email', [
        'destinatario' => 'test@test.com', 'asunto' => 'Hola', 'cuerpo' => 'Mundo',
    ]);
    Afirmar::assertIsString($notif->obtenerMensaje());
    Afirmar::assertStringContainsString('test@test.com', $notif->obtenerMensaje());
});

echo "\n";


// ============================================================================
// Ejemplo 5: Comparaciones numericas y assertGreaterThan/assertLessThan
// ============================================================================
echo "=== Ejemplo 5: Comparaciones numericas ===\n\n";

/**
 * Aserciones para comparar valores numericos:
 *   assertGreaterThan()        - Mayor que
 *   assertLessThan()           - Menor que
 *   assertGreaterThanOrEqual() - Mayor o igual que
 *   assertLessThanOrEqual()    - Menor o igual que
 *
 * VERSION PHPUNIT:
 *
 * class RendimientoTest extends TestCase
 * {
 *     public function testTiempoDeRespuesta(): void
 *     {
 *         $inicio = microtime(true);
 *         // ... ejecutar operacion ...
 *         $duracion = microtime(true) - $inicio;
 *
 *         $this->assertLessThan(1.0, $duracion, "La operacion tardo mas de 1 segundo");
 *     }
 *
 *     public function testPuntuacion(): void
 *     {
 *         $this->assertGreaterThanOrEqual(0, $puntuacion);
 *         $this->assertLessThanOrEqual(100, $puntuacion);
 *     }
 * }
 */

// Clase de ejemplo: Calculadora de metricas
class CalculadoraMetricas {
    public function calcularPromedio(array $valores): float {
        if (empty($valores)) {
            throw new \InvalidArgumentException("El arreglo no puede estar vacio");
        }
        return array_sum($valores) / count($valores);
    }

    public function calcularDesviacionEstandar(array $valores): float {
        $promedio = $this->calcularPromedio($valores);
        $sumaCuadrados = array_sum(array_map(
            fn($v) => pow($v - $promedio, 2),
            $valores
        ));
        return sqrt($sumaCuadrados / count($valores));
    }

    public function calcularPercentil(array $valores, float $percentil): float {
        sort($valores);
        $indice = ($percentil / 100) * (count($valores) - 1);
        $inferior = (int) floor($indice);
        $superior = (int) ceil($indice);
        $fraccion = $indice - $inferior;

        if ($inferior === $superior) {
            return $valores[$inferior];
        }
        return $valores[$inferior] + $fraccion * ($valores[$superior] - $valores[$inferior]);
    }

    public function normalizarEntre0y1(float $valor, float $min, float $max): float {
        if ($min === $max) {
            return 0.0;
        }
        return ($valor - $min) / ($max - $min);
    }
}

// VERSION INDEPENDIENTE:
$metricas = new CalculadoraMetricas();

Afirmar::ejecutar("assertGreaterThan - Promedio mayor que el minimo", function() use ($metricas) {
    $valores = [10, 20, 30, 40, 50];
    $promedio = $metricas->calcularPromedio($valores);
    Afirmar::assertGreaterThan(min($valores), $promedio);
});

Afirmar::ejecutar("assertLessThan - Promedio menor que el maximo", function() use ($metricas) {
    $valores = [10, 20, 30, 40, 50];
    $promedio = $metricas->calcularPromedio($valores);
    Afirmar::assertLessThan(max($valores), $promedio);
});

Afirmar::ejecutar("assertGreaterThanOrEqual - Desviacion estandar >= 0", function() use ($metricas) {
    $valores = [5, 5, 5, 5];  // Todos iguales: desviacion = 0
    $desviacion = $metricas->calcularDesviacionEstandar($valores);
    Afirmar::assertGreaterThanOrEqual(0, $desviacion);
    Afirmar::assertEquals(0.0, $desviacion); // Exactamente 0 si todos son iguales
});

Afirmar::ejecutar("assertLessThanOrEqual - Normalizacion entre 0 y 1", function() use ($metricas) {
    $normalizado = $metricas->normalizarEntre0y1(75, 0, 100);
    Afirmar::assertGreaterThanOrEqual(0, $normalizado);
    Afirmar::assertLessThanOrEqual(1, $normalizado);
    Afirmar::assertEquals(0.75, $normalizado);
});

Afirmar::ejecutar("Percentil 50 es la mediana", function() use ($metricas) {
    $valores = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    $mediana = $metricas->calcularPercentil($valores, 50);
    // La mediana de 1-10 es 5.5
    Afirmar::assertEquals(5.5, $mediana);
    Afirmar::assertGreaterThan(0, $mediana);
});

echo "\n";


// ============================================================================
// Ejemplo 6: expectException - Probando que se lanzan excepciones
// ============================================================================
echo "=== Ejemplo 6: Probando excepciones (expectException) ===\n\n";

/**
 * Verificar que el codigo lanza excepciones en situaciones invalidas
 * es tan importante como verificar que funciona correctamente.
 *
 * PHPUnit ofrece:
 *   expectException(ClaseExcepcion::class)    - Verifica la clase
 *   expectExceptionMessage('texto')           - Verifica el mensaje
 *   expectExceptionCode(404)                  - Verifica el codigo
 *
 * VERSION PHPUNIT:
 *
 * class ValidadorTest extends TestCase
 * {
 *     public function testEdadNegativaLanzaExcepcion(): void
 *     {
 *         $this->expectException(\InvalidArgumentException::class);
 *         $this->expectExceptionMessage('La edad no puede ser negativa');
 *
 *         $validador = new ValidadorRegistro();
 *         $validador->validarEdad(-5);
 *     }
 *
 *     public function testEmailVacioLanzaExcepcion(): void
 *     {
 *         $this->expectException(\InvalidArgumentException::class);
 *
 *         $validador = new ValidadorRegistro();
 *         $validador->validarEmail('');
 *     }
 * }
 */

// Clase de ejemplo: Validador de registro
class ValidadorRegistro {
    public function validarEdad(int $edad): bool {
        if ($edad < 0) {
            throw new \InvalidArgumentException("La edad no puede ser negativa");
        }
        if ($edad < 13) {
            throw new \InvalidArgumentException("Debe tener al menos 13 anios");
        }
        if ($edad > 150) {
            throw new \InvalidArgumentException("Edad no realista");
        }
        return true;
    }

    public function validarEmail(string $email): bool {
        if (empty(trim($email))) {
            throw new \InvalidArgumentException("El email no puede estar vacio");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Formato de email invalido: {$email}");
        }
        return true;
    }

    public function validarContrasenia(string $contrasenia): bool {
        if (strlen($contrasenia) < 8) {
            throw new \LengthException("La contrasenia debe tener al menos 8 caracteres");
        }
        if (!preg_match('/[A-Z]/', $contrasenia)) {
            throw new \InvalidArgumentException("La contrasenia debe contener al menos una mayuscula");
        }
        if (!preg_match('/[0-9]/', $contrasenia)) {
            throw new \InvalidArgumentException("La contrasenia debe contener al menos un numero");
        }
        return true;
    }
}

// VERSION INDEPENDIENTE:
$validador = new ValidadorRegistro();

Afirmar::ejecutar("expectException - Edad negativa lanza InvalidArgumentException", function() use ($validador) {
    Afirmar::assertThrows(\InvalidArgumentException::class, function() use ($validador) {
        $validador->validarEdad(-5);
    });
});

Afirmar::ejecutar("expectException - Menor de 13 anios lanza excepcion", function() use ($validador) {
    $excepcionLanzada = false;
    $mensajeCorrecto = false;

    try {
        $validador->validarEdad(10);
    } catch (\InvalidArgumentException $e) {
        $excepcionLanzada = true;
        // Verificamos tambien el MENSAJE de la excepcion
        $mensajeCorrecto = str_contains($e->getMessage(), '13');
    }

    Afirmar::assertTrue($excepcionLanzada, "Debio lanzar excepcion");
    Afirmar::assertTrue($mensajeCorrecto, "El mensaje debe mencionar la edad minima");
});

Afirmar::ejecutar("expectException - Email vacio lanza excepcion", function() use ($validador) {
    Afirmar::assertThrows(\InvalidArgumentException::class, function() use ($validador) {
        $validador->validarEmail('');
    });
});

Afirmar::ejecutar("expectException - Contrasenia corta lanza LengthException", function() use ($validador) {
    // Notar que es LengthException, no InvalidArgumentException
    Afirmar::assertThrows(\LengthException::class, function() use ($validador) {
        $validador->validarContrasenia('abc');
    });
});

Afirmar::ejecutar("expectException - Contrasenia sin mayusculas lanza excepcion", function() use ($validador) {
    Afirmar::assertThrows(\InvalidArgumentException::class, function() use ($validador) {
        $validador->validarContrasenia('solopequenias123');
    });
});

Afirmar::ejecutar("Validacion exitosa no lanza excepcion", function() use ($validador) {
    // Verificar que datos validos NO lanzan excepciones
    $edadValida = $validador->validarEdad(25);
    Afirmar::assertTrue($edadValida);

    $emailValido = $validador->validarEmail('usuario@ejemplo.com');
    Afirmar::assertTrue($emailValido);

    $contraseniaValida = $validador->validarContrasenia('MiClave123');
    Afirmar::assertTrue($contraseniaValida);
});

// ============================================================================
// RESUMEN FINAL
// ============================================================================
Afirmar::resumen();

/**
 * REFERENCIA RAPIDA DE ASERCIONES PHPUNIT:
 *
 * IGUALDAD:
 *   assertEquals($esperado, $actual)     - Igualdad flexible (==)
 *   assertSame($esperado, $actual)       - Igualdad estricta (===)
 *   assertNotEquals($val, $actual)       - Desigualdad flexible
 *   assertNotSame($val, $actual)         - Desigualdad estricta
 *
 * BOOLEANOS/NULOS:
 *   assertTrue($valor)                   - Es exactamente true
 *   assertFalse($valor)                  - Es exactamente false
 *   assertNull($valor)                   - Es null
 *   assertNotNull($valor)                - No es null
 *
 * CADENAS:
 *   assertStringContainsString($a, $b)   - $b contiene $a
 *   assertStringStartsWith($pre, $str)   - Empieza con prefijo
 *   assertStringEndsWith($suf, $str)     - Termina con sufijo
 *   assertMatchesRegularExpression()     - Coincide con regex
 *
 * ARREGLOS:
 *   assertContains($val, $arr)           - Valor en arreglo
 *   assertArrayHasKey($key, $arr)        - Clave existe
 *   assertCount($n, $arr)               - Tiene N elementos
 *   assertEmpty($val)                    - Esta vacio
 *
 * TIPOS:
 *   assertInstanceOf($clase, $obj)       - Es instancia de clase
 *   assertIsArray($val)                  - Es arreglo
 *   assertIsString($val)                 - Es cadena
 *   assertIsInt($val)                    - Es entero
 *   assertIsFloat($val)                  - Es flotante
 *   assertIsBool($val)                   - Es booleano
 *
 * NUMERICAS:
 *   assertGreaterThan($val, $actual)     - Mayor que
 *   assertLessThan($val, $actual)        - Menor que
 *   assertGreaterThanOrEqual()           - Mayor o igual
 *   assertLessThanOrEqual()              - Menor o igual
 *
 * EXCEPCIONES:
 *   expectException($clase)              - Espera excepcion
 *   expectExceptionMessage($msg)         - Verifica mensaje
 *   expectExceptionCode($code)           - Verifica codigo
 */
?>
