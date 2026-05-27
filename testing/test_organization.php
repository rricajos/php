<?php
/**
 * ============================================================================
 * ORGANIZACION DE PRUEBAS EN PHPUNIT
 * ============================================================================
 *
 * Una buena organizacion de pruebas es esencial para mantener un proyecto
 * grande. Este archivo cubre:
 *   - Ciclo de vida: setUp, tearDown, setUpBeforeClass, tearDownAfterClass
 *   - Dependencias entre pruebas con @depends
 *   - Agrupacion con @group
 *   - Configuracion en phpunit.xml
 *   - Estructura de directorios convencional
 *   - Caso practico: organizando pruebas para un UserService
 */

// ============================================================================
// MINI-FRAMEWORK DE PRUEBAS
// ============================================================================

class TestOrg {
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
            throw new \RuntimeException($msg ?: "assertSame fallo");
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

    public static function assertNotNull(mixed $valor, string $msg = ''): void {
        if ($valor === null) {
            throw new \RuntimeException($msg ?: "No se esperaba NULL");
        }
    }

    public static function assertCount(int $esperado, array|Countable $col, string $msg = ''): void {
        if (count($col) !== $esperado) {
            throw new \RuntimeException($msg ?: "Cantidad incorrecta: esperado {$esperado}, obtenido " . count($col));
        }
    }

    public static function assertEmpty(mixed $valor, string $msg = ''): void {
        if (!empty($valor)) {
            throw new \RuntimeException($msg ?: "Se esperaba valor vacio");
        }
    }

    public static function assertArrayHasKey(string|int $clave, array $arr, string $msg = ''): void {
        if (!array_key_exists($clave, $arr)) {
            throw new \RuntimeException($msg ?: "Clave '{$clave}' no encontrada");
        }
    }

    public static function assertStringContainsString(string $aguja, string $pajar, string $msg = ''): void {
        if (!str_contains($pajar, $aguja)) {
            throw new \RuntimeException($msg ?: "No contiene '{$aguja}'");
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
// Ejemplo 1: Ciclo de vida setUp / tearDown
// ============================================================================
echo "=== Ejemplo 1: Ciclo de vida setUp / tearDown ===\n\n";

/**
 * PHPUnit ejecuta metodos del ciclo de vida en este orden:
 *
 *   setUpBeforeClass()    <-- Una vez antes de TODAS las pruebas de la clase
 *     setUp()             <-- Antes de CADA prueba
 *       testPrimero()
 *     tearDown()          <-- Despues de CADA prueba
 *     setUp()
 *       testSegundo()
 *     tearDown()
 *   tearDownAfterClass()  <-- Una vez despues de TODAS las pruebas
 *
 * setUp():    Crear objetos frescos, preparar estado limpio
 * tearDown(): Limpiar archivos temporales, cerrar conexiones
 * setUpBeforeClass(): Cargar fixtures costosos (BD de prueba, archivos grandes)
 * tearDownAfterClass(): Limpiar recursos compartidos
 *
 * VERSION PHPUNIT:
 *
 * class ConexionBDTest extends TestCase
 * {
 *     private static PDO $conexion;
 *     private $transaccion;
 *
 *     public static function setUpBeforeClass(): void
 *     {
 *         // Se ejecuta UNA vez: crear conexion a BD de prueba
 *         self::$conexion = new PDO('sqlite::memory:');
 *         self::$conexion->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre TEXT)');
 *     }
 *
 *     protected function setUp(): void
 *     {
 *         // Se ejecuta antes de CADA prueba: iniciar transaccion
 *         self::$conexion->beginTransaction();
 *     }
 *
 *     protected function tearDown(): void
 *     {
 *         // Se ejecuta despues de CADA prueba: revertir transaccion
 *         // Esto asegura que cada prueba empieza con BD limpia
 *         self::$conexion->rollBack();
 *     }
 *
 *     public static function tearDownAfterClass(): void
 *     {
 *         // Se ejecuta UNA vez al final: cerrar conexion
 *         self::$conexion = null;
 *     }
 *
 *     public function testInsertarUsuario(): void { ... }
 *     public function testBuscarUsuario(): void { ... }
 * }
 */

// VERSION INDEPENDIENTE - Simulamos el ciclo de vida:

/**
 * Simulador del ciclo de vida de PHPUnit TestCase.
 * Demuestra el orden de ejecucion de setUp/tearDown.
 */
class SimuladorCicloVida {
    private static array $log = [];

    public static function ejecutarSuite(string $nombre, array $metodosPrueba): void {
        echo "  Ejecutando suite: {$nombre}\n";
        self::$log = [];

        // setUpBeforeClass() - una vez al inicio
        self::registrar("setUpBeforeClass()");
        echo "    -> setUpBeforeClass()\n";

        foreach ($metodosPrueba as $nombreTest => $fn) {
            // setUp() - antes de cada prueba
            self::registrar("setUp() antes de {$nombreTest}");
            echo "    -> setUp()\n";

            try {
                $fn();
                echo "    -> [OK] {$nombreTest}\n";
            } catch (\Throwable $e) {
                echo "    -> [FALLO] {$nombreTest}: {$e->getMessage()}\n";
            }

            // tearDown() - despues de cada prueba
            self::registrar("tearDown() despues de {$nombreTest}");
            echo "    -> tearDown()\n";
            echo "    ---\n";
        }

        // tearDownAfterClass() - una vez al final
        self::registrar("tearDownAfterClass()");
        echo "    -> tearDownAfterClass()\n";

        return;
    }

    private static function registrar(string $evento): void {
        self::$log[] = $evento;
    }

    public static function obtenerLog(): array {
        return self::$log;
    }
}

// Clase que simula un repositorio con estado que necesita limpieza
class RepositorioEnMemoria {
    private array $datos = [];

    public function insertar(string $nombre): int {
        $id = count($this->datos) + 1;
        $this->datos[$id] = ['id' => $id, 'nombre' => $nombre];
        return $id;
    }

    public function buscar(int $id): ?array {
        return $this->datos[$id] ?? null;
    }

    public function contar(): int {
        return count($this->datos);
    }

    public function limpiar(): void {
        $this->datos = [];
    }
}

// Demostrar el ciclo de vida:
$repo = new RepositorioEnMemoria();

SimuladorCicloVida::ejecutarSuite("RepositorioTest", [
    'testInsertar' => function() use ($repo) {
        $repo->limpiar(); // Simula setUp
        $id = $repo->insertar('Ana');
        TestOrg::assertEquals(1, $id);
        TestOrg::assertEquals(1, $repo->contar());
    },
    'testBuscar' => function() use ($repo) {
        $repo->limpiar(); // Simula setUp - estado limpio
        $repo->insertar('Carlos');
        $encontrado = $repo->buscar(1);
        TestOrg::assertNotNull($encontrado);
        TestOrg::assertEquals('Carlos', $encontrado['nombre']);
    },
    'testContarVacio' => function() use ($repo) {
        $repo->limpiar(); // Simula setUp - cada test empieza limpio
        TestOrg::assertEquals(0, $repo->contar());
    },
]);

echo "\n";

// Verificar que el ciclo se ejecuto correctamente
TestOrg::ejecutar("Ciclo de vida tiene orden correcto", function() {
    $log = SimuladorCicloVida::obtenerLog();
    // Verificar que setUpBeforeClass fue primero
    TestOrg::assertStringContainsString('setUpBeforeClass', $log[0]);
    // Verificar que tearDownAfterClass fue ultimo
    TestOrg::assertStringContainsString('tearDownAfterClass', end($log));
});

echo "\n";


// ============================================================================
// Ejemplo 2: @depends - Dependencias entre pruebas
// ============================================================================
echo "=== Ejemplo 2: @depends - Dependencias entre pruebas ===\n\n";

/**
 * La anotacion @depends permite que una prueba dependa del resultado
 * de otra. El valor retornado por la prueba "padre" se pasa como
 * argumento a la prueba "hija".
 *
 * CUIDADO: Usar @depends con moderacion. Las pruebas idealmente
 * deben ser independientes. @depends es util para pruebas que
 * representan un flujo secuencial (crear -> modificar -> eliminar).
 *
 * VERSION PHPUNIT:
 *
 * class ArticuloTest extends TestCase
 * {
 *     public function testCrearArticulo(): int
 *     {
 *         $articulo = new Articulo('Mi titulo', 'Contenido...');
 *         $id = $articulo->guardar();
 *
 *         $this->assertGreaterThan(0, $id);
 *         return $id;  // Este valor se pasa a las pruebas dependientes
 *     }
 *
 *     /**
 *      * @depends testCrearArticulo
 *      * /
 *     public function testModificarArticulo(int $id): int
 *     {
 *         $articulo = Articulo::buscar($id);
 *         $articulo->setTitulo('Titulo modificado');
 *         $articulo->guardar();
 *
 *         $this->assertEquals('Titulo modificado', $articulo->getTitulo());
 *         return $id;
 *     }
 *
 *     /**
 *      * @depends testModificarArticulo
 *      * /
 *     public function testEliminarArticulo(int $id): void
 *     {
 *         $resultado = Articulo::eliminar($id);
 *         $this->assertTrue($resultado);
 *
 *         $eliminado = Articulo::buscar($id);
 *         $this->assertNull($eliminado);
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Simulamos dependencias pasando resultados:

class Articulo {
    private static array $almacen = [];
    private static int $siguienteId = 1;

    private int $id = 0;

    public function __construct(
        private string $titulo,
        private string $contenido,
        private string $estado = 'borrador'
    ) {}

    public function guardar(): int {
        if ($this->id === 0) {
            $this->id = self::$siguienteId++;
        }
        self::$almacen[$this->id] = [
            'id'        => $this->id,
            'titulo'    => $this->titulo,
            'contenido' => $this->contenido,
            'estado'    => $this->estado,
        ];
        return $this->id;
    }

    public static function buscar(int $id): ?self {
        if (!isset(self::$almacen[$id])) {
            return null;
        }
        $datos = self::$almacen[$id];
        $articulo = new self($datos['titulo'], $datos['contenido'], $datos['estado']);
        $articulo->id = $datos['id'];
        return $articulo;
    }

    public static function eliminar(int $id): bool {
        if (isset(self::$almacen[$id])) {
            unset(self::$almacen[$id]);
            return true;
        }
        return false;
    }

    public function getTitulo(): string { return $this->titulo; }
    public function setTitulo(string $titulo): void { $this->titulo = $titulo; }
    public function getEstado(): string { return $this->estado; }
    public function publicar(): void { $this->estado = 'publicado'; }
    public function getId(): int { return $this->id; }

    public static function reiniciar(): void {
        self::$almacen = [];
        self::$siguienteId = 1;
    }
}

// Simulando @depends: cada prueba usa el resultado de la anterior
Articulo::reiniciar();
$idCreado = null;

TestOrg::ejecutar("@depends Paso 1: Crear articulo", function() use (&$idCreado) {
    $articulo = new Articulo('Mi Primer Articulo', 'Contenido del articulo...');
    $idCreado = $articulo->guardar();
    TestOrg::assertTrue($idCreado > 0, "El ID debe ser positivo");
});

TestOrg::ejecutar("@depends Paso 2: Modificar articulo (depende de Paso 1)", function() use (&$idCreado) {
    // En PHPUnit, $idCreado vendria como parametro via @depends
    TestOrg::assertNotNull($idCreado, "Paso 1 debio proporcionar un ID");

    $articulo = Articulo::buscar($idCreado);
    TestOrg::assertNotNull($articulo, "El articulo debe existir");

    $articulo->setTitulo('Titulo Actualizado');
    $articulo->guardar();

    $recargar = Articulo::buscar($idCreado);
    TestOrg::assertEquals('Titulo Actualizado', $recargar->getTitulo());
});

TestOrg::ejecutar("@depends Paso 3: Publicar articulo (depende de Paso 2)", function() use (&$idCreado) {
    $articulo = Articulo::buscar($idCreado);
    TestOrg::assertEquals('borrador', $articulo->getEstado());

    $articulo->publicar();
    $articulo->guardar();

    $recargar = Articulo::buscar($idCreado);
    TestOrg::assertEquals('publicado', $recargar->getEstado());
});

TestOrg::ejecutar("@depends Paso 4: Eliminar articulo (depende de Paso 3)", function() use (&$idCreado) {
    $resultado = Articulo::eliminar($idCreado);
    TestOrg::assertTrue($resultado, "La eliminacion debe ser exitosa");

    $eliminado = Articulo::buscar($idCreado);
    TestOrg::assertNull($eliminado, "El articulo ya no debe existir");
});

echo "\n";


// ============================================================================
// Ejemplo 3: @group y categorias de pruebas
// ============================================================================
echo "=== Ejemplo 3: @group - Categorias de pruebas ===\n\n";

/**
 * @group permite categorizar pruebas para ejecutar solo subconjuntos.
 * Es util para separar pruebas rapidas de lentas, o por funcionalidad.
 *
 * VERSION PHPUNIT:
 *
 * /**
 *  * @group rapido
 *  * @group validacion
 *  * /
 * class ValidacionRapidaTest extends TestCase { ... }
 *
 * /**
 *  * @group lento
 *  * @group integracion
 *  * /
 * class BaseDeDatosTest extends TestCase { ... }
 *
 * COMANDOS:
 *   # Ejecutar solo pruebas de un grupo
 *   ./vendor/bin/phpunit --group rapido
 *
 *   # Excluir un grupo
 *   ./vendor/bin/phpunit --exclude-group lento
 *
 *   # Multiples grupos
 *   ./vendor/bin/phpunit --group rapido,validacion
 *
 * ATRIBUTOS PHP 8.1+:
 *   use PHPUnit\Framework\Attributes\Group;
 *
 *   #[Group('rapido')]
 *   class MiTest extends TestCase { ... }
 */

// VERSION INDEPENDIENTE - Sistema de grupos manual:

class SistemaGrupos {
    /** @var array<string, array<string, callable>> */
    private static array $grupos = [];

    public static function registrar(string $grupo, string $nombre, callable $prueba): void {
        self::$grupos[$grupo][$nombre] = $prueba;
    }

    public static function ejecutarGrupo(string $grupo): void {
        if (!isset(self::$grupos[$grupo])) {
            echo "  [!] Grupo '{$grupo}' no encontrado\n";
            return;
        }
        echo "  Ejecutando grupo: @{$grupo} (" . count(self::$grupos[$grupo]) . " pruebas)\n";
        foreach (self::$grupos[$grupo] as $nombre => $prueba) {
            TestOrg::ejecutar("[{$grupo}] {$nombre}", $prueba);
        }
    }

    public static function listarGrupos(): array {
        return array_map(fn($p) => count($p), self::$grupos);
    }
}

// Registrar pruebas en diferentes grupos:

// Grupo: unitaria (pruebas rapidas sin dependencias externas)
SistemaGrupos::registrar('unitaria', 'Sumar numeros positivos', function() {
    TestOrg::assertEquals(8, 3 + 5);
});

SistemaGrupos::registrar('unitaria', 'Concatenar cadenas', function() {
    TestOrg::assertEquals('HolaMundo', 'Hola' . 'Mundo');
});

SistemaGrupos::registrar('unitaria', 'Array push', function() {
    $arr = [1, 2];
    $arr[] = 3;
    TestOrg::assertCount(3, $arr);
});

// Grupo: validacion
SistemaGrupos::registrar('validacion', 'Email valido', function() {
    TestOrg::assertTrue(filter_var('test@ejemplo.com', FILTER_VALIDATE_EMAIL) !== false);
});

SistemaGrupos::registrar('validacion', 'Email invalido', function() {
    TestOrg::assertFalse(filter_var('no-es-email', FILTER_VALIDATE_EMAIL) !== false);
});

// Grupo: integracion (simuladas - normalmente serian lentas)
SistemaGrupos::registrar('integracion', 'Flujo completo de registro', function() {
    // Simular un flujo completo
    $datos = ['nombre' => 'Test', 'email' => 'test@test.com'];
    TestOrg::assertArrayHasKey('nombre', $datos);
    TestOrg::assertArrayHasKey('email', $datos);
});

// Ejecutar solo grupos especificos (como --group en PHPUnit):
echo "  Simulando: phpunit --group unitaria\n";
SistemaGrupos::ejecutarGrupo('unitaria');
echo "\n";

echo "  Simulando: phpunit --group validacion\n";
SistemaGrupos::ejecutarGrupo('validacion');
echo "\n";

// Mostrar resumen de grupos
$grupos = SistemaGrupos::listarGrupos();
echo "  Grupos disponibles:\n";
foreach ($grupos as $grupo => $cantidad) {
    echo "    @{$grupo}: {$cantidad} pruebas\n";
}

echo "\n";


// ============================================================================
// Ejemplo 4: Estructura de directorios y phpunit.xml
// ============================================================================
echo "=== Ejemplo 4: Estructura de directorios y phpunit.xml ===\n\n";

/**
 * ESTRUCTURA CONVENCIONAL DE UN PROYECTO PHP CON PRUEBAS:
 *
 *   mi-proyecto/
 *   |-- src/                          <-- Codigo fuente
 *   |   |-- Entity/
 *   |   |   |-- User.php
 *   |   |   |-- Product.php
 *   |   |-- Service/
 *   |   |   |-- UserService.php
 *   |   |   |-- OrderService.php
 *   |   |-- Repository/
 *   |   |   |-- UserRepository.php
 *   |   |   |-- ProductRepository.php
 *   |   |-- Validator/
 *   |       |-- UserValidator.php
 *   |
 *   |-- tests/                        <-- Pruebas (espejo de src/)
 *   |   |-- Unit/                     <-- Pruebas unitarias
 *   |   |   |-- Entity/
 *   |   |   |   |-- UserTest.php
 *   |   |   |   |-- ProductTest.php
 *   |   |   |-- Service/
 *   |   |   |   |-- UserServiceTest.php
 *   |   |   |   |-- OrderServiceTest.php
 *   |   |   |-- Validator/
 *   |   |       |-- UserValidatorTest.php
 *   |   |
 *   |   |-- Integration/             <-- Pruebas de integracion
 *   |   |   |-- Repository/
 *   |   |       |-- UserRepositoryTest.php
 *   |   |
 *   |   |-- Functional/              <-- Pruebas funcionales / E2E
 *   |   |   |-- UserRegistrationTest.php
 *   |   |
 *   |   |-- Fixtures/                <-- Datos de prueba
 *   |   |   |-- users.json
 *   |   |   |-- products.csv
 *   |   |
 *   |   |-- bootstrap.php            <-- Configuracion inicial de pruebas
 *   |
 *   |-- phpunit.xml                  <-- Configuracion de PHPUnit
 *   |-- composer.json
 *
 *
 * CONVENCIONES DE NOMBRES:
 *   - Archivo:  [ClaseOriginal]Test.php  ->  UserServiceTest.php
 *   - Clase:    [ClaseOriginal]Test      ->  UserServiceTest
 *   - Metodo:   test[Accion][Escenario]  ->  testCrearUsuarioConEmailDuplicado
 *   - El directorio tests/ refleja la estructura de src/
 */

// Mostramos como seria el phpunit.xml tipico:
$phpunitXmlEjemplo = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>

<!--
  ARCHIVO: phpunit.xml (en la raiz del proyecto)

  Este archivo configura PHPUnit: donde buscar pruebas,
  que reportes generar, variables de entorno, etc.
-->

<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true"
         stopOnFailure="false"
         cacheDirectory=".phpunit.cache">

    <!-- Donde estan las pruebas -->
    <testsuites>
        <!-- Suite principal: todas las pruebas -->
        <testsuite name="Todas">
            <directory>tests</directory>
        </testsuite>

        <!-- Suite solo unitarias (rapidas) -->
        <testsuite name="Unitarias">
            <directory>tests/Unit</directory>
        </testsuite>

        <!-- Suite de integracion (lentas, necesitan BD) -->
        <testsuite name="Integracion">
            <directory>tests/Integration</directory>
        </testsuite>

        <!-- Suite funcional -->
        <testsuite name="Funcional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>

    <!-- Configuracion de cobertura de codigo -->
    <source>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/Migrations</directory>
            <file>src/Kernel.php</file>
        </exclude>
    </source>

    <!-- Variables de entorno para pruebas -->
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_DATABASE" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="MAIL_MAILER" value="log"/>
    </php>

    <!-- Grupos a excluir por defecto -->
    <groups>
        <exclude>
            <group>lento</group>
            <group>external-api</group>
        </exclude>
    </groups>

</phpunit>
XML;

echo "  Ejemplo de phpunit.xml:\n";
echo "  " . str_repeat("-", 50) . "\n";
// Mostrar las primeras lineas
$lineas = explode("\n", $phpunitXmlEjemplo);
foreach (array_slice($lineas, 0, 15) as $linea) {
    echo "  {$linea}\n";
}
echo "  ... (ver archivo completo en los comentarios)\n";
echo "  " . str_repeat("-", 50) . "\n\n";

// Comandos utiles:
$comandos = [
    'Ejecutar todas las pruebas'           => './vendor/bin/phpunit',
    'Solo suite unitarias'                 => './vendor/bin/phpunit --testsuite Unitarias',
    'Solo suite integracion'               => './vendor/bin/phpunit --testsuite Integracion',
    'Solo un grupo'                        => './vendor/bin/phpunit --group rapido',
    'Excluir grupo lento'                  => './vendor/bin/phpunit --exclude-group lento',
    'Un archivo especifico'                => './vendor/bin/phpunit tests/Unit/Service/UserServiceTest.php',
    'Un metodo especifico'                 => './vendor/bin/phpunit --filter testCrearUsuario',
    'Con cobertura HTML'                   => './vendor/bin/phpunit --coverage-html reports/',
    'Formato detallado'                    => './vendor/bin/phpunit --verbose --colors',
    'Parar al primer fallo'                => './vendor/bin/phpunit --stop-on-failure',
];

echo "  Comandos utiles de PHPUnit:\n";
foreach ($comandos as $desc => $cmd) {
    echo "    {$desc}:\n      {$cmd}\n\n";
}


// ============================================================================
// Ejemplo 5: setUp/tearDown practico con archivos temporales
// ============================================================================
echo "=== Ejemplo 5: setUp/tearDown con recursos reales ===\n\n";

/**
 * Ejemplo practico donde setUp crea recursos y tearDown los limpia.
 * Este patron es comun cuando se trabaja con archivos, conexiones, etc.
 *
 * VERSION PHPUNIT:
 *
 * class ExportadorCSVTest extends TestCase
 * {
 *     private string $dirTemporal;
 *     private ExportadorCSV $exportador;
 *
 *     protected function setUp(): void
 *     {
 *         // Crear directorio temporal unico para esta prueba
 *         $this->dirTemporal = sys_get_temp_dir() . '/test_' . uniqid();
 *         mkdir($this->dirTemporal, 0777, true);
 *
 *         $this->exportador = new ExportadorCSV($this->dirTemporal);
 *     }
 *
 *     protected function tearDown(): void
 *     {
 *         // Limpiar: eliminar archivos temporales
 *         $archivos = glob($this->dirTemporal . '/*');
 *         foreach ($archivos as $archivo) {
 *             unlink($archivo);
 *         }
 *         rmdir($this->dirTemporal);
 *     }
 *
 *     public function testExportarUsuarios(): void
 *     {
 *         $usuarios = [['nombre' => 'Ana', 'email' => 'ana@test.com']];
 *         $archivo = $this->exportador->exportar($usuarios, 'usuarios.csv');
 *
 *         $this->assertFileExists($archivo);
 *         $contenido = file_get_contents($archivo);
 *         $this->assertStringContainsString('Ana', $contenido);
 *     }
 * }
 */

// VERSION INDEPENDIENTE:

class ExportadorCSV {
    public function __construct(private string $directorio) {}

    public function exportar(array $datos, string $nombreArchivo): string {
        $ruta = $this->directorio . '/' . $nombreArchivo;
        $fp = fopen($ruta, 'w');

        if (!empty($datos)) {
            // Encabezados desde las claves del primer registro
            fputcsv($fp, array_keys($datos[0]));
            // Datos
            foreach ($datos as $fila) {
                fputcsv($fp, $fila);
            }
        }

        fclose($fp);
        return $ruta;
    }

    public function contarArchivos(): int {
        $archivos = glob($this->directorio . '/*.csv');
        return $archivos ? count($archivos) : 0;
    }
}

// Simular ciclo setUp -> prueba -> tearDown
$dirTemporal = sys_get_temp_dir() . '/phpunit_ejemplo_' . uniqid();

// setUp: crear directorio temporal
$setUp = function() use ($dirTemporal) {
    if (!is_dir($dirTemporal)) {
        mkdir($dirTemporal, 0777, true);
    }
    return new ExportadorCSV($dirTemporal);
};

// tearDown: limpiar archivos temporales
$tearDown = function() use ($dirTemporal) {
    if (is_dir($dirTemporal)) {
        $archivos = glob($dirTemporal . '/*');
        if ($archivos) {
            foreach ($archivos as $archivo) {
                unlink($archivo);
            }
        }
        rmdir($dirTemporal);
    }
};

// Ejecutar pruebas con ciclo de vida:
TestOrg::ejecutar("setUp/tearDown: Exportar CSV crea archivo", function() use ($setUp, $tearDown) {
    $exportador = $setUp();
    try {
        $datos = [
            ['nombre' => 'Ana Garcia', 'email' => 'ana@ejemplo.com', 'rol' => 'admin'],
            ['nombre' => 'Carlos Lopez', 'email' => 'carlos@ejemplo.com', 'rol' => 'editor'],
        ];

        $archivo = $exportador->exportar($datos, 'usuarios.csv');
        TestOrg::assertTrue(file_exists($archivo), "El archivo CSV debe existir");

        $contenido = file_get_contents($archivo);
        TestOrg::assertStringContainsString('Ana Garcia', $contenido);
        TestOrg::assertStringContainsString('carlos@ejemplo.com', $contenido);
    } finally {
        $tearDown(); // Siempre limpiar, incluso si falla
    }
});

TestOrg::ejecutar("setUp/tearDown: Contar archivos exportados", function() use ($setUp, $tearDown) {
    $exportador = $setUp();
    try {
        TestOrg::assertEquals(0, $exportador->contarArchivos());

        $exportador->exportar([['a' => 1]], 'primero.csv');
        $exportador->exportar([['b' => 2]], 'segundo.csv');

        TestOrg::assertEquals(2, $exportador->contarArchivos());
    } finally {
        $tearDown();
    }
});

TestOrg::ejecutar("setUp/tearDown: Directorio limpio tras tearDown", function() use ($setUp, $tearDown, $dirTemporal) {
    $exportador = $setUp();
    $exportador->exportar([['x' => 'y']], 'temporal.csv');
    TestOrg::assertTrue(is_dir($dirTemporal));

    $tearDown();
    TestOrg::assertFalse(is_dir($dirTemporal), "tearDown debe eliminar el directorio");
});

echo "\n";


// ============================================================================
// Ejemplo 6: Practico completo - Organizando pruebas para UserService
// ============================================================================
echo "=== Ejemplo 6: Organizacion completa para UserService ===\n\n";

/**
 * Escenario realista: Como organizar las pruebas para un servicio
 * de usuarios en un proyecto real, mostrando la separacion por
 * tipo de prueba y el uso de todos los conceptos anteriores.
 */

// ---- Entidad ----
class User {
    private int $id = 0;

    public function __construct(
        private string $nombre,
        private string $email,
        private string $rol = 'user',
        private bool $activo = true
    ) {}

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getNombre(): string { return $this->nombre; }
    public function getEmail(): string { return $this->email; }
    public function getRol(): string { return $this->rol; }
    public function esActivo(): bool { return $this->activo; }
    public function desactivar(): void { $this->activo = false; }
    public function activar(): void { $this->activo = true; }
    public function promoverAAdmin(): void { $this->rol = 'admin'; }

    public function toArray(): array {
        return [
            'id'     => $this->id,
            'nombre' => $this->nombre,
            'email'  => $this->email,
            'rol'    => $this->rol,
            'activo' => $this->activo,
        ];
    }
}

// ---- Repositorio (interfaz) ----
interface UserRepositoryInterface {
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): int;
    public function delete(int $id): bool;
    public function findAll(): array;
    public function findByRole(string $rol): array;
}

// ---- Fake del repositorio ----
class InMemoryUserRepository implements UserRepositoryInterface {
    /** @var User[] */
    private array $users = [];
    private int $nextId = 1;

    public function findById(int $id): ?User {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(string $email): ?User {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): int {
        if ($user->getId() === 0) {
            $user->setId($this->nextId++);
        }
        $this->users[$user->getId()] = $user;
        return $user->getId();
    }

    public function delete(int $id): bool {
        if (isset($this->users[$id])) {
            unset($this->users[$id]);
            return true;
        }
        return false;
    }

    public function findAll(): array {
        return array_values($this->users);
    }

    public function findByRole(string $rol): array {
        return array_values(array_filter(
            $this->users,
            fn(User $u) => $u->getRol() === $rol
        ));
    }
}

// ---- Servicio ----
class UserService {
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function register(string $nombre, string $email): User {
        // Validar email unico
        if ($this->repository->findByEmail($email) !== null) {
            throw new \RuntimeException("Email ya registrado: {$email}");
        }
        // Validar formato
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email invalido: {$email}");
        }

        $user = new User($nombre, $email);
        $this->repository->save($user);
        return $user;
    }

    public function deactivate(int $id): bool {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new \RuntimeException("Usuario no encontrado: {$id}");
        }
        $user->desactivar();
        $this->repository->save($user);
        return true;
    }

    public function promoteToAdmin(int $id): User {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new \RuntimeException("Usuario no encontrado");
        }
        $user->promoverAAdmin();
        $this->repository->save($user);
        return $user;
    }

    public function getActiveUsers(): array {
        return array_filter(
            $this->repository->findAll(),
            fn(User $u) => $u->esActivo()
        );
    }

    public function getUserProfile(int $id): array {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new \RuntimeException("Usuario no encontrado");
        }
        return $user->toArray();
    }
}

/*
 * ASI SE ORGANIZARIAN LAS PRUEBAS EN PHPUNIT:
 *
 * tests/
 *   Unit/
 *     Entity/
 *       UserTest.php              <- Pruebas de la entidad User
 *     Service/
 *       UserServiceTest.php       <- Pruebas del servicio (con mocks)
 *   Integration/
 *     Repository/
 *       UserRepositoryTest.php    <- Pruebas con BD real
 *   Functional/
 *     UserRegistrationFlowTest.php <- Flujo completo
 */

// ------------------------------------------------------------------
// PRUEBAS UNITARIAS DE LA ENTIDAD (tests/Unit/Entity/UserTest.php)
// ------------------------------------------------------------------
echo "  --- tests/Unit/Entity/UserTest.php ---\n";

TestOrg::ejecutar("[Unit/Entity] User se crea con valores por defecto", function() {
    $user = new User('Ana Garcia', 'ana@ejemplo.com');
    TestOrg::assertEquals('Ana Garcia', $user->getNombre());
    TestOrg::assertEquals('ana@ejemplo.com', $user->getEmail());
    TestOrg::assertEquals('user', $user->getRol());     // Rol por defecto
    TestOrg::assertTrue($user->esActivo());              // Activo por defecto
});

TestOrg::ejecutar("[Unit/Entity] User se puede desactivar y reactivar", function() {
    $user = new User('Test', 'test@test.com');
    TestOrg::assertTrue($user->esActivo());

    $user->desactivar();
    TestOrg::assertFalse($user->esActivo());

    $user->activar();
    TestOrg::assertTrue($user->esActivo());
});

TestOrg::ejecutar("[Unit/Entity] User se puede promover a admin", function() {
    $user = new User('Test', 'test@test.com');
    TestOrg::assertEquals('user', $user->getRol());

    $user->promoverAAdmin();
    TestOrg::assertEquals('admin', $user->getRol());
});

TestOrg::ejecutar("[Unit/Entity] toArray retorna estructura correcta", function() {
    $user = new User('Ana', 'ana@test.com', 'admin', true);
    $user->setId(42);
    $arr = $user->toArray();

    TestOrg::assertArrayHasKey('id', $arr);
    TestOrg::assertArrayHasKey('nombre', $arr);
    TestOrg::assertArrayHasKey('email', $arr);
    TestOrg::assertArrayHasKey('rol', $arr);
    TestOrg::assertArrayHasKey('activo', $arr);
    TestOrg::assertEquals(42, $arr['id']);
});

echo "\n";

// ------------------------------------------------------------------
// PRUEBAS UNITARIAS DEL SERVICIO (tests/Unit/Service/UserServiceTest.php)
// ------------------------------------------------------------------
echo "  --- tests/Unit/Service/UserServiceTest.php ---\n";

// setUp equivalente: crear instancias frescas para cada prueba
$crearServicio = function(): array {
    $repo = new InMemoryUserRepository();
    $service = new UserService($repo);
    return [$service, $repo];
};

TestOrg::ejecutar("[Unit/Service] Registrar usuario exitosamente", function() use ($crearServicio) {
    [$service, $repo] = $crearServicio();

    $user = $service->register('Carlos Lopez', 'carlos@ejemplo.com');

    TestOrg::assertEquals('Carlos Lopez', $user->getNombre());
    TestOrg::assertEquals('carlos@ejemplo.com', $user->getEmail());
    TestOrg::assertTrue($user->getId() > 0);
});

TestOrg::ejecutar("[Unit/Service] Registrar con email duplicado lanza excepcion", function() use ($crearServicio) {
    [$service, $repo] = $crearServicio();

    $service->register('Ana', 'ana@ejemplo.com');

    $lanzoExcepcion = false;
    try {
        $service->register('Otra Ana', 'ana@ejemplo.com'); // Mismo email
    } catch (\RuntimeException $e) {
        $lanzoExcepcion = true;
        TestOrg::assertStringContainsString('ya registrado', $e->getMessage());
    }
    TestOrg::assertTrue($lanzoExcepcion);
});

TestOrg::ejecutar("[Unit/Service] Desactivar usuario existente", function() use ($crearServicio) {
    [$service, $repo] = $crearServicio();

    $user = $service->register('Maria', 'maria@test.com');
    $resultado = $service->deactivate($user->getId());

    TestOrg::assertTrue($resultado);
    // Verificar que realmente se desactivo en el repositorio
    $actualizado = $repo->findById($user->getId());
    TestOrg::assertFalse($actualizado->esActivo());
});

TestOrg::ejecutar("[Unit/Service] Obtener usuarios activos excluye inactivos", function() use ($crearServicio) {
    [$service, $repo] = $crearServicio();

    $user1 = $service->register('Activo1', 'activo1@test.com');
    $user2 = $service->register('Activo2', 'activo2@test.com');
    $user3 = $service->register('Inactivo', 'inactivo@test.com');

    $service->deactivate($user3->getId());

    $activos = $service->getActiveUsers();
    TestOrg::assertCount(2, $activos);
});

TestOrg::ejecutar("[Unit/Service] Promover usuario a administrador", function() use ($crearServicio) {
    [$service, $repo] = $crearServicio();

    $user = $service->register('Nuevo Admin', 'admin@test.com');
    TestOrg::assertEquals('user', $user->getRol());

    $promovido = $service->promoteToAdmin($user->getId());
    TestOrg::assertEquals('admin', $promovido->getRol());
});

TestOrg::ejecutar("[Unit/Service] Obtener perfil de usuario", function() use ($crearServicio) {
    [$service, $repo] = $crearServicio();

    $user = $service->register('Perfil Test', 'perfil@test.com');
    $perfil = $service->getUserProfile($user->getId());

    TestOrg::assertArrayHasKey('id', $perfil);
    TestOrg::assertEquals('Perfil Test', $perfil['nombre']);
    TestOrg::assertEquals('perfil@test.com', $perfil['email']);
});

// ============================================================================
// RESUMEN FINAL
// ============================================================================
TestOrg::resumen();

/**
 * RESUMEN DE BUENAS PRACTICAS DE ORGANIZACION:
 *
 * 1. ESTRUCTURA:
 *    - tests/ refleja la estructura de src/
 *    - Separar Unit/, Integration/, Functional/
 *    - Un archivo de prueba por clase probada
 *
 * 2. NOMBRES:
 *    - Clase: [Clase]Test
 *    - Metodo: test[Que][Cuando][Entonces]
 *    - Ejemplo: testRegistrarConEmailDuplicadoLanzaExcepcion
 *
 * 3. CICLO DE VIDA:
 *    - setUp(): Crear objetos frescos para cada prueba
 *    - tearDown(): Limpiar recursos (archivos, conexiones)
 *    - setUpBeforeClass(): Recursos compartidos costosos
 *    - tearDownAfterClass(): Limpieza final
 *
 * 4. GRUPOS Y SUITES:
 *    - @group para categorizar (rapido, lento, integracion)
 *    - phpunit.xml para definir suites por directorio
 *    - --exclude-group para omitir pruebas lentas en desarrollo
 *
 * 5. INDEPENDENCIA:
 *    - Cada prueba debe funcionar de forma aislada
 *    - No depender del orden de ejecucion
 *    - Usar @depends solo para flujos secuenciales explicitos
 *    - setUp() garantiza estado limpio
 */
?>
