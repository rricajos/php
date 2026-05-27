<?php
/**
 * ============================================================================
 * DATA PROVIDERS - PRUEBAS PARAMETRIZADAS EN PHPUNIT
 * ============================================================================
 *
 * Los Data Providers permiten ejecutar la MISMA prueba con MULTIPLES
 * conjuntos de datos. En lugar de escribir una prueba por cada caso,
 * se escribe una sola vez y se alimenta con datos diferentes.
 *
 * Ventajas:
 *   - Elimina duplicacion de codigo de pruebas
 *   - Facilita agregar nuevos casos (solo agregar datos)
 *   - Cada conjunto de datos se reporta como prueba independiente
 *   - Nombres descriptivos ayudan a identificar fallos rapidamente
 *
 * En PHPUnit se usa la anotacion @dataProvider o el atributo #[DataProvider].
 */

// ============================================================================
// MINI-FRAMEWORK DE PRUEBAS
// ============================================================================

class Probar {
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

    /**
     * Ejecuta una prueba parametrizada con multiples conjuntos de datos.
     * Equivalente a @dataProvider en PHPUnit.
     *
     * @param string   $nombre   Nombre base de la prueba
     * @param array    $datos    Arreglo de conjuntos de datos [nombre => [args...]]
     * @param callable $prueba   Funcion que recibe los argumentos del conjunto
     */
    public static function conDatos(string $nombre, array $datos, callable $prueba): void {
        foreach ($datos as $caso => $argumentos) {
            $etiqueta = is_string($caso) ? "{$nombre} [{$caso}]" : "{$nombre} [caso #{$caso}]";
            self::$total++;
            try {
                $prueba(...$argumentos);
                self::$ok++;
                echo "  [OK] {$etiqueta}\n";
            } catch (\Throwable $e) {
                self::$fallos++;
                self::$errores[] = "{$etiqueta}: {$e->getMessage()}";
                echo "  [FALLO] {$etiqueta}: {$e->getMessage()}\n";
            }
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

    public static function assertNull(mixed $valor, string $msg = ''): void {
        if ($valor !== null) {
            throw new \RuntimeException($msg ?: "Se esperaba NULL");
        }
    }

    public static function assertStringContainsString(string $aguja, string $pajar, string $msg = ''): void {
        if (!str_contains($pajar, $aguja)) {
            throw new \RuntimeException($msg ?: "No contiene '{$aguja}'");
        }
    }

    public static function assertMatchesRegularExpression(string $patron, string $cadena, string $msg = ''): void {
        if (!preg_match($patron, $cadena)) {
            throw new \RuntimeException($msg ?: "No coincide con patron '{$patron}'");
        }
    }

    public static function resumen(): void {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "RESULTADOS: " . self::$total . " pruebas, "
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
// CLASES A PROBAR
// ============================================================================

/** Validador de formularios con multiples reglas */
class ValidadorFormulario {
    public function validarEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validarContrasenia(string $contrasenia): array {
        $errores = [];
        if (strlen($contrasenia) < 8) {
            $errores[] = 'minimo_8_caracteres';
        }
        if (!preg_match('/[A-Z]/', $contrasenia)) {
            $errores[] = 'requiere_mayuscula';
        }
        if (!preg_match('/[a-z]/', $contrasenia)) {
            $errores[] = 'requiere_minuscula';
        }
        if (!preg_match('/[0-9]/', $contrasenia)) {
            $errores[] = 'requiere_numero';
        }
        if (!preg_match('/[!@#$%^&*]/', $contrasenia)) {
            $errores[] = 'requiere_especial';
        }
        return $errores;
    }

    public function validarEdad(mixed $valor): ?string {
        if (!is_numeric($valor)) {
            return 'no_es_numero';
        }
        $edad = (int) $valor;
        if ($edad < 0) {
            return 'negativa';
        }
        if ($edad < 13) {
            return 'menor_de_edad';
        }
        if ($edad > 150) {
            return 'no_realista';
        }
        return null; // null = valido
    }

    public function validarTelefono(string $telefono): bool {
        // Acepta formatos: +XX-XX-XXXX-XXXX, (XXX) XXX-XXXX, XXXXXXXXXX
        $limpio = preg_replace('/[\s\-\(\)\+]/', '', $telefono);
        return preg_match('/^\d{10,13}$/', $limpio) === 1;
    }
}

/** Convertidor de monedas con tasas fijas */
class ConvertidorMoneda {
    private array $tasas = [
        'USD_MXN' => 17.15,
        'USD_EUR' => 0.92,
        'USD_GBP' => 0.79,
        'EUR_MXN' => 18.64,
        'EUR_USD' => 1.09,
        'MXN_USD' => 0.058,
    ];

    public function convertir(float $monto, string $origen, string $destino): float {
        if ($origen === $destino) {
            return $monto;
        }
        $clave = "{$origen}_{$destino}";
        if (!isset($this->tasas[$clave])) {
            throw new \InvalidArgumentException("Tasa no disponible: {$clave}");
        }
        return round($monto * $this->tasas[$clave], 2);
    }

    public function formatear(float $monto, string $moneda): string {
        return match($moneda) {
            'USD' => '$' . number_format($monto, 2),
            'EUR' => number_format($monto, 2) . ' EUR',
            'MXN' => '$' . number_format($monto, 2) . ' MXN',
            'GBP' => '£' . number_format($monto, 2),
            default => number_format($monto, 2) . " {$moneda}",
        };
    }
}

/** Calculadora de impuestos por pais */
class CalculadoraImpuestos {
    private array $tasasIVA = [
        'MX' => 0.16,    // Mexico: 16%
        'ES' => 0.21,    // Espana: 21%
        'AR' => 0.21,    // Argentina: 21%
        'CO' => 0.19,    // Colombia: 19%
        'CL' => 0.19,    // Chile: 19%
        'US' => 0.0,     // Estados Unidos: vaira por estado (0 federal)
    ];

    public function calcularIVA(float $monto, string $pais): float {
        $tasa = $this->tasasIVA[$pais] ?? null;
        if ($tasa === null) {
            throw new \InvalidArgumentException("Pais no soportado: {$pais}");
        }
        return round($monto * $tasa, 2);
    }

    public function calcularTotal(float $monto, string $pais): float {
        return round($monto + $this->calcularIVA($monto, $pais), 2);
    }

    public function obtenerTasa(string $pais): float {
        return $this->tasasIVA[$pais]
            ?? throw new \InvalidArgumentException("Pais no soportado: {$pais}");
    }
}


// ============================================================================
// Ejemplo 1: Data Provider basico - Validacion de emails
// ============================================================================
echo "=== Ejemplo 1: Data Provider basico - Validacion de emails ===\n\n";

/**
 * VERSION PHPUNIT:
 *
 * class ValidadorFormularioTest extends TestCase
 * {
 *     /**
 *      * @dataProvider emailsValidosProvider
 *      * /
 *     public function testEmailsValidos(string $email): void
 *     {
 *         $validador = new ValidadorFormulario();
 *         $this->assertTrue($validador->validarEmail($email));
 *     }
 *
 *     public static function emailsValidosProvider(): array
 *     {
 *         return [
 *             'email simple'        => ['usuario@ejemplo.com'],
 *             'con subdominio'      => ['user@mail.ejemplo.com'],
 *             'con puntos'          => ['nombre.apellido@empresa.com'],
 *             'con numeros'         => ['user123@test.org'],
 *             'con guion bajo'      => ['mi_correo@dominio.net'],
 *             'con mas/tags'        => ['user+tag@gmail.com'],
 *         ];
 *     }
 *
 *     /**
 *      * @dataProvider emailsInvalidosProvider
 *      * /
 *     public function testEmailsInvalidos(string $email): void
 *     {
 *         $validador = new ValidadorFormulario();
 *         $this->assertFalse($validador->validarEmail($email));
 *     }
 *
 *     public static function emailsInvalidosProvider(): array
 *     {
 *         return [
 *             'sin arroba'          => ['usuarioejemplo.com'],
 *             'sin dominio'         => ['usuario@'],
 *             'sin usuario'         => ['@ejemplo.com'],
 *             'con espacios'        => ['usuario @ejemplo.com'],
 *             'doble arroba'        => ['user@@ejemplo.com'],
 *             'cadena vacia'        => [''],
 *         ];
 *     }
 * }
 */

// VERSION INDEPENDIENTE usando nuestro conDatos():
$validador = new ValidadorFormulario();

// Emails validos - cada caso tiene un nombre descriptivo
$emailsValidos = [
    'email simple'        => ['usuario@ejemplo.com'],
    'con subdominio'      => ['user@mail.ejemplo.com'],
    'con puntos'          => ['nombre.apellido@empresa.com'],
    'con numeros'         => ['user123@test.org'],
    'con guion bajo'      => ['mi_correo@dominio.net'],
    'con tags'            => ['user+tag@gmail.com'],
];

Probar::conDatos(
    "Email valido",
    $emailsValidos,
    function(string $email) use ($validador) {
        Probar::assertTrue($validador->validarEmail($email));
    }
);

echo "\n";

// Emails invalidos
$emailsInvalidos = [
    'sin arroba'          => ['usuarioejemplo.com'],
    'sin dominio'         => ['usuario@'],
    'sin usuario'         => ['@ejemplo.com'],
    'con espacios'        => ['usuario @ejemplo.com'],
    'doble arroba'        => ['user@@ejemplo.com'],
];

Probar::conDatos(
    "Email invalido",
    $emailsInvalidos,
    function(string $email) use ($validador) {
        Probar::assertFalse($validador->validarEmail($email));
    }
);

echo "\n";


// ============================================================================
// Ejemplo 2: Data Provider con multiples argumentos - Conversion de monedas
// ============================================================================
echo "=== Ejemplo 2: Data Provider con multiples argumentos ===\n\n";

/**
 * Los data providers pueden enviar multiples argumentos a cada prueba.
 * Cada entrada del arreglo es un sub-arreglo con los parametros.
 *
 * VERSION PHPUNIT:
 *
 * class ConvertidorMonedaTest extends TestCase
 * {
 *     /**
 *      * @dataProvider conversionesProvider
 *      * /
 *     public function testConvertir(
 *         float $monto,
 *         string $origen,
 *         string $destino,
 *         float $esperado
 *     ): void {
 *         $convertidor = new ConvertidorMoneda();
 *         $resultado = $convertidor->convertir($monto, $origen, $destino);
 *         $this->assertEquals($esperado, $resultado);
 *     }
 *
 *     public static function conversionesProvider(): array
 *     {
 *         return [
 *             '100 USD a MXN' => [100.0, 'USD', 'MXN', 1715.0],
 *             '100 USD a EUR' => [100.0, 'USD', 'EUR', 92.0],
 *             '50 EUR a MXN'  => [50.0, 'EUR', 'MXN', 932.0],
 *             'misma moneda'  => [100.0, 'USD', 'USD', 100.0],
 *         ];
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
$convertidor = new ConvertidorMoneda();

$conversiones = [
    '100 USD a MXN'        => [100.0, 'USD', 'MXN', 1715.0],
    '100 USD a EUR'        => [100.0, 'USD', 'EUR', 92.0],
    '100 USD a GBP'        => [100.0, 'USD', 'GBP', 79.0],
    '50 EUR a MXN'         => [50.0,  'EUR', 'MXN', 932.0],
    '1000 MXN a USD'       => [1000.0, 'MXN', 'USD', 58.0],
    'misma moneda USD'     => [100.0, 'USD', 'USD', 100.0],
];

Probar::conDatos(
    "Conversion de moneda",
    $conversiones,
    function(float $monto, string $origen, string $destino, float $esperado) use ($convertidor) {
        $resultado = $convertidor->convertir($monto, $origen, $destino);
        Probar::assertEquals($esperado, $resultado);
    }
);

echo "\n";

// Data provider para formateo
$formateos = [
    'dolares USD'     => [1234.56, 'USD', '$1,234.56'],
    'euros EUR'       => [1234.56, 'EUR', '1,234.56 EUR'],
    'pesos MXN'       => [1234.56, 'MXN', '$1,234.56 MXN'],
    'libras GBP'      => [1234.56, 'GBP', '£1,234.56'],
];

Probar::conDatos(
    "Formateo de moneda",
    $formateos,
    function(float $monto, string $moneda, string $esperado) use ($convertidor) {
        $resultado = $convertidor->formatear($monto, $moneda);
        Probar::assertEquals($esperado, $resultado);
    }
);

echo "\n";


// ============================================================================
// Ejemplo 3: Data Provider con generadores (yield) - Validacion de contrasenia
// ============================================================================
echo "=== Ejemplo 3: Data Providers con generadores (yield) ===\n\n";

/**
 * En PHPUnit, los data providers pueden usar generadores con yield
 * para producir datos de forma perezosa (lazy). Esto es util cuando
 * los datos son muchos o se calculan dinamicamente.
 *
 * VERSION PHPUNIT:
 *
 * class ContraseniaTest extends TestCase
 * {
 *     /**
 *      * @dataProvider contraseniasDebilesProvider
 *      * /
 *     public function testContraseniasDebilesTienenErrores(
 *         string $contrasenia,
 *         array $erroresEsperados
 *     ): void {
 *         $validador = new ValidadorFormulario();
 *         $errores = $validador->validarContrasenia($contrasenia);
 *
 *         foreach ($erroresEsperados as $error) {
 *             $this->assertContains($error, $errores);
 *         }
 *     }
 *
 *     // Generador con yield: mas eficiente en memoria
 *     public static function contraseniasDebilesProvider(): \Generator
 *     {
 *         yield 'solo minusculas' => ['abcdefgh', ['requiere_mayuscula', 'requiere_numero', 'requiere_especial']];
 *         yield 'muy corta'       => ['Ab1!', ['minimo_8_caracteres']];
 *         yield 'sin numeros'     => ['AbcDefgh!', ['requiere_numero']];
 *         yield 'sin especiales'  => ['AbcDef12', ['requiere_especial']];
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Simulamos generadores con funciones que retornan arreglos:

/**
 * Funcion generadora: produce conjuntos de datos de prueba.
 * En PHPUnit esto seria un metodo statico con yield.
 */
function generarCasosContrasenia(): Generator {
    yield 'solo minusculas' => [
        'abcdefgh',
        ['requiere_mayuscula', 'requiere_numero', 'requiere_especial']
    ];
    yield 'muy corta' => [
        'Ab1!',
        ['minimo_8_caracteres']
    ];
    yield 'sin numeros' => [
        'AbcDefgh!',
        ['requiere_numero']
    ];
    yield 'sin especiales' => [
        'AbcDef123',
        ['requiere_especial']
    ];
    yield 'sin mayusculas' => [
        'abcdef1!x',
        ['requiere_mayuscula']
    ];
    yield 'solo numeros' => [
        '12345678',
        ['requiere_mayuscula', 'requiere_minuscula', 'requiere_especial']
    ];
}

// Convertir generador a arreglo para conDatos()
$casosContrasenia = iterator_to_array(generarCasosContrasenia());

Probar::conDatos(
    "Contrasenia debil tiene errores esperados",
    $casosContrasenia,
    function(string $contrasenia, array $erroresEsperados) use ($validador) {
        $errores = $validador->validarContrasenia($contrasenia);
        foreach ($erroresEsperados as $errorEsperado) {
            if (!in_array($errorEsperado, $errores)) {
                throw new \RuntimeException(
                    "Error '{$errorEsperado}' no encontrado en: " . implode(', ', $errores)
                );
            }
        }
    }
);

echo "\n";

// Contrasenia valida (sin errores)
Probar::ejecutar("Contrasenia valida no tiene errores", function() use ($validador) {
    $errores = $validador->validarContrasenia('MiClave@123');
    Probar::assertEquals([], $errores);
});

echo "\n";


// ============================================================================
// Ejemplo 4: Datasets con nombre para mejores mensajes de error
// ============================================================================
echo "=== Ejemplo 4: Datasets con nombre descriptivo ===\n\n";

/**
 * Los nombres descriptivos en los data providers son CRUCIALES porque
 * cuando una prueba falla, el nombre aparece en el reporte de errores.
 *
 * Sin nombre:  "testValidarEdad with data set #3"    (poco util)
 * Con nombre:  "testValidarEdad with data set 'edad negativa'"  (claro)
 *
 * VERSION PHPUNIT (con atributo PHP 8.1+):
 *
 * use PHPUnit\Framework\Attributes\DataProvider;
 *
 * class EdadTest extends TestCase
 * {
 *     #[DataProvider('edadesProvider')]
 *     public function testValidarEdad(
 *         mixed $entrada,
 *         ?string $errorEsperado,
 *         string $descripcion
 *     ): void {
 *         $validador = new ValidadorFormulario();
 *         $resultado = $validador->validarEdad($entrada);
 *         $this->assertSame($errorEsperado, $resultado, $descripcion);
 *     }
 *
 *     public static function edadesProvider(): array
 *     {
 *         return [
 *             'edad valida 25'          => [25, null, 'Edad normal debe ser valida'],
 *             'edad minima 13'          => [13, null, 'Justo en el limite minimo'],
 *             'edad maxima 150'         => [150, null, 'Justo en el limite maximo'],
 *             'menor de 13'             => [12, 'menor_de_edad', 'Por debajo del limite'],
 *             'edad negativa'           => [-1, 'negativa', 'Numeros negativos no validos'],
 *             'edad no realista 200'    => [200, 'no_realista', 'Por encima de 150'],
 *             'texto no numerico'       => ['abc', 'no_es_numero', 'Letras no son validas'],
 *             'cadena vacia'            => ['', 'no_es_numero', 'Cadena vacia no valida'],
 *         ];
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
$casosEdad = [
    // Formato: 'nombre descriptivo' => [entrada, error_esperado]
    'edad valida 25'              => [25, null],
    'edad minima aceptable (13)'  => [13, null],
    'edad maxima aceptable (150)' => [150, null],
    'justo debajo del minimo'     => [12, 'menor_de_edad'],
    'edad negativa'               => [-1, 'negativa'],
    'edad no realista (200)'      => [200, 'no_realista'],
    'texto no numerico'           => ['abc', 'no_es_numero'],
    'edad cero'                   => [0, 'menor_de_edad'],
    'edad como string numerico'   => ['25', null],
    'edad limite superior + 1'    => [151, 'no_realista'],
];

Probar::conDatos(
    "Validar edad",
    $casosEdad,
    function(mixed $entrada, ?string $errorEsperado) use ($validador) {
        $resultado = $validador->validarEdad($entrada);
        Probar::assertSame($errorEsperado, $resultado);
    }
);

echo "\n";


// ============================================================================
// Ejemplo 5: Practico - Validacion de telefonos con docenas de combinaciones
// ============================================================================
echo "=== Ejemplo 5: Validacion de telefonos con muchos casos ===\n\n";

/**
 * Este ejemplo muestra el verdadero poder de los data providers:
 * probar docenas de formatos de telefono con una sola funcion de prueba.
 *
 * VERSION PHPUNIT:
 *
 * class TelefonoTest extends TestCase
 * {
 *     /**
 *      * @dataProvider telefonosValidosProvider
 *      * /
 *     public function testTelefonosValidos(string $telefono): void
 *     {
 *         $validador = new ValidadorFormulario();
 *         $this->assertTrue(
 *             $validador->validarTelefono($telefono),
 *             "El telefono '{$telefono}' deberia ser valido"
 *         );
 *     }
 *
 *     public static function telefonosValidosProvider(): array
 *     {
 *         return [
 *             'formato local MX'    => ['5512345678'],
 *             'con codigo pais'     => ['+525512345678'],
 *             'con guiones'         => ['55-1234-5678'],
 *             'formato US'          => ['(555) 123-4567'],
 *             'con espacios'        => ['55 1234 5678'],
 *             'mixto'               => ['+52 (55) 1234-5678'],
 *         ];
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
$telefonosValidos = [
    'formato local 10 digitos'          => ['5512345678'],
    'con codigo de pais +52'            => ['+525512345678'],
    'con guiones'                       => ['55-1234-5678'],
    'formato norteamericano'            => ['(555) 123-4567'],
    'con espacios'                      => ['55 1234 5678'],
    'formato mixto con codigo pais'     => ['+52 (55) 1234-5678'],
    'guiones y parentesis'              => ['(55) 1234-5678'],
];

$telefonosInvalidos = [
    'muy corto 5 digitos'               => ['12345'],
    'muy largo 15 digitos'              => ['123456789012345'],
    'con letras'                        => ['55-ABCD-1234'],
    'solo texto'                        => ['telefono'],
    'vacio'                             => [''],
];

Probar::conDatos(
    "Telefono valido",
    $telefonosValidos,
    function(string $telefono) use ($validador) {
        Probar::assertTrue(
            $validador->validarTelefono($telefono),
            "'{$telefono}' deberia ser valido"
        );
    }
);

echo "\n";

Probar::conDatos(
    "Telefono invalido",
    $telefonosInvalidos,
    function(string $telefono) use ($validador) {
        Probar::assertFalse(
            $validador->validarTelefono($telefono),
            "'{$telefono}' deberia ser invalido"
        );
    }
);

echo "\n";


// ============================================================================
// Ejemplo 6: Practico completo - Calculadora de impuestos con data providers
// ============================================================================
echo "=== Ejemplo 6: Calculadora de impuestos parametrizada ===\n\n";

/**
 * Escenario realista: Probar una calculadora de impuestos para
 * multiples paises con diferentes tasas y montos.
 *
 * VERSION PHPUNIT:
 *
 * class CalculadoraImpuestosTest extends TestCase
 * {
 *     private CalculadoraImpuestos $calc;
 *
 *     protected function setUp(): void
 *     {
 *         $this->calc = new CalculadoraImpuestos();
 *     }
 *
 *     /**
 *      * @dataProvider ivaProvider
 *      * /
 *     public function testCalcularIVA(float $monto, string $pais, float $ivaEsperado): void
 *     {
 *         $this->assertEquals($ivaEsperado, $this->calc->calcularIVA($monto, $pais));
 *     }
 *
 *     public static function ivaProvider(): array
 *     {
 *         return [
 *             'Mexico 100'   => [100.0, 'MX', 16.0],
 *             'Espana 100'   => [100.0, 'ES', 21.0],
 *             'Colombia 100' => [100.0, 'CO', 19.0],
 *             'Mexico 250'   => [250.0, 'MX', 40.0],
 *             'EEUU 100'     => [100.0, 'US', 0.0],
 *         ];
 *     }
 *
 *     /**
 *      * @dataProvider paisesNoSoportadosProvider
 *      * /
 *     public function testPaisNoSoportadoLanzaExcepcion(string $pais): void
 *     {
 *         $this->expectException(\InvalidArgumentException::class);
 *         $this->calc->calcularIVA(100, $pais);
 *     }
 *
 *     public static function paisesNoSoportadosProvider(): array
 *     {
 *         return [
 *             'Brasil'    => ['BR'],
 *             'Japon'     => ['JP'],
 *             'Alemania'  => ['DE'],
 *             'Invalido'  => ['XX'],
 *         ];
 *     }
 * }
 */

// VERSION INDEPENDIENTE:
$calc = new CalculadoraImpuestos();

// Data provider: Calculos de IVA
$casosIVA = [
    'Mexico $100 (16% = $16)'             => [100.0, 'MX', 16.0],
    'Espana $100 (21% = $21)'             => [100.0, 'ES', 21.0],
    'Argentina $100 (21% = $21)'          => [100.0, 'AR', 21.0],
    'Colombia $100 (19% = $19)'           => [100.0, 'CO', 19.0],
    'Chile $100 (19% = $19)'              => [100.0, 'CL', 19.0],
    'EEUU $100 (0% = $0)'                 => [100.0, 'US', 0.0],
    'Mexico $250 (16% = $40)'             => [250.0, 'MX', 40.0],
    'Espana $1500 (21% = $315)'           => [1500.0, 'ES', 315.0],
    'Mexico $0 (16% = $0)'                => [0.0, 'MX', 0.0],
    'Colombia $99.99 (19% = $19)'         => [99.99, 'CO', 19.0],
];

Probar::conDatos(
    "Calcular IVA",
    $casosIVA,
    function(float $monto, string $pais, float $ivaEsperado) use ($calc) {
        $resultado = $calc->calcularIVA($monto, $pais);
        Probar::assertEquals($ivaEsperado, $resultado);
    }
);

echo "\n";

// Data provider: Total con IVA incluido
$casosTotal = [
    'Mexico $100 -> $116'                 => [100.0, 'MX', 116.0],
    'Espana $100 -> $121'                 => [100.0, 'ES', 121.0],
    'EEUU $100 -> $100 (sin IVA federal)' => [100.0, 'US', 100.0],
    'Chile $500 -> $595'                  => [500.0, 'CL', 595.0],
];

Probar::conDatos(
    "Total con IVA",
    $casosTotal,
    function(float $monto, string $pais, float $totalEsperado) use ($calc) {
        $resultado = $calc->calcularTotal($monto, $pais);
        Probar::assertEquals($totalEsperado, $resultado);
    }
);

echo "\n";

// Data provider: Paises no soportados (debe lanzar excepcion)
$paisesInvalidos = [
    'Brasil (BR)'     => ['BR'],
    'Japon (JP)'      => ['JP'],
    'Alemania (DE)'   => ['DE'],
    'Invalido (XX)'   => ['XX'],
    'Vacio'           => [''],
];

Probar::conDatos(
    "Pais no soportado lanza excepcion",
    $paisesInvalidos,
    function(string $pais) use ($calc) {
        $lanzoExcepcion = false;
        try {
            $calc->calcularIVA(100, $pais);
        } catch (\InvalidArgumentException $e) {
            $lanzoExcepcion = true;
        }
        Probar::assertTrue($lanzoExcepcion, "Debio lanzar excepcion para pais '{$pais}'");
    }
);

echo "\n";

// Data provider: Tasas de IVA por pais
$tasasPorPais = [
    'Mexico 16%'     => ['MX', 0.16],
    'Espana 21%'     => ['ES', 0.21],
    'Argentina 21%'  => ['AR', 0.21],
    'Colombia 19%'   => ['CO', 0.19],
    'Chile 19%'      => ['CL', 0.19],
    'EEUU 0%'        => ['US', 0.0],
];

Probar::conDatos(
    "Tasa de IVA por pais",
    $tasasPorPais,
    function(string $pais, float $tasaEsperada) use ($calc) {
        Probar::assertSame($tasaEsperada, $calc->obtenerTasa($pais));
    }
);

// ============================================================================
// RESUMEN FINAL
// ============================================================================
Probar::resumen();

/**
 * REFERENCIA DE DATA PROVIDERS EN PHPUNIT:
 *
 * SINTAXIS CLASICA (anotacion):
 *   /**
 *    * @dataProvider nombreDelProvider
 *    * /
 *   public function testAlgo($arg1, $arg2): void { ... }
 *
 *   public static function nombreDelProvider(): array { ... }
 *
 * SINTAXIS MODERNA (atributo PHP 8.1+):
 *   use PHPUnit\Framework\Attributes\DataProvider;
 *
 *   #[DataProvider('nombreDelProvider')]
 *   public function testAlgo($arg1, $arg2): void { ... }
 *
 * RETORNO DEL PROVIDER:
 *   - Array de arrays: [[arg1, arg2], [arg1, arg2], ...]
 *   - Array nombrado: ['caso1' => [arg1], 'caso2' => [arg1], ...]
 *   - Generator con yield: yield 'nombre' => [arg1, arg2];
 *
 * BUENAS PRACTICAS:
 *   1. SIEMPRE usar nombres descriptivos en los datasets
 *   2. Los providers deben ser metodos STATIC
 *   3. Incluir casos limite (0, vacio, null, maximo)
 *   4. Incluir tanto casos validos como invalidos
 *   5. Usar generadores para datasets muy grandes (eficiencia de memoria)
 *   6. Un provider por concepto; no mezclar validaciones diferentes
 */
?>
