<?php
/**
 * ============================================================================
 * MOCKS, STUBS Y DOBLES DE PRUEBA EN PHPUNIT
 * ============================================================================
 *
 * Los "dobles de prueba" (test doubles) son objetos que reemplazan
 * dependencias reales durante las pruebas. Permiten probar una clase
 * de forma AISLADA sin necesitar bases de datos, APIs externas, etc.
 *
 * TIPOS DE DOBLES:
 *   - Stub: Retorna valores predefinidos (no verifica comportamiento)
 *   - Mock: Verifica que ciertos metodos fueron llamados correctamente
 *   - Fake: Implementacion funcional pero simplificada (ej: BD en memoria)
 *   - Spy:  Registra las llamadas para verificarlas despues
 *   - Dummy: Se pasa como argumento pero nunca se usa
 *
 * En PHPUnit, createMock() puede crear tanto stubs como mocks dependiendo
 * de como se configure.
 */

// ============================================================================
// MINI-FRAMEWORK DE PRUEBAS
// ============================================================================

class Verificar {
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

    public static function assertCount(int $esperado, array|Countable $col, string $msg = ''): void {
        if (count($col) !== $esperado) {
            throw new \RuntimeException($msg ?: "Cantidad incorrecta");
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
// INTERFACES Y CLASES BASE PARA LOS EJEMPLOS
// ============================================================================

/**
 * Interfaz del repositorio de usuarios (contrato para acceso a datos).
 * En produccion, se implementaria con MySQL, PostgreSQL, etc.
 * En pruebas, se reemplaza con un mock o fake.
 */
interface RepositorioUsuarios {
    public function buscarPorId(int $id): ?array;
    public function buscarPorEmail(string $email): ?array;
    public function guardar(array $datos): int;
    public function eliminar(int $id): bool;
    public function listarTodos(): array;
    public function contarActivos(): int;
}

/**
 * Interfaz del servicio de correo electronico.
 * No queremos enviar emails reales durante las pruebas.
 */
interface ServicioCorreo {
    public function enviar(string $destinatario, string $asunto, string $cuerpo): bool;
    public function enviarMasivo(array $destinatarios, string $asunto, string $cuerpo): int;
}

/**
 * Interfaz del servicio de cache.
 */
interface ServicioCache {
    public function obtener(string $clave): mixed;
    public function guardar(string $clave, mixed $valor, int $ttl = 3600): bool;
    public function eliminar(string $clave): bool;
    public function existe(string $clave): bool;
}

/**
 * Clase de servicio que DEPENDE de las interfaces anteriores.
 * Esta es la clase que queremos probar de forma aislada.
 */
class ServicioUsuarios {
    public function __construct(
        private RepositorioUsuarios $repositorio,
        private ServicioCorreo $correo,
        private ?ServicioCache $cache = null
    ) {}

    public function registrar(string $nombre, string $email, string $contrasenia): int {
        // Verificar que el email no este en uso
        $existente = $this->repositorio->buscarPorEmail($email);
        if ($existente !== null) {
            throw new \RuntimeException("El email {$email} ya esta registrado");
        }

        // Guardar en la base de datos
        $id = $this->repositorio->guardar([
            'nombre'      => $nombre,
            'email'       => $email,
            'contrasenia' => password_hash($contrasenia, PASSWORD_DEFAULT),
            'activo'      => true,
            'creado_en'   => date('Y-m-d H:i:s'),
        ]);

        // Enviar email de bienvenida
        $this->correo->enviar(
            $email,
            'Bienvenido a nuestra plataforma',
            "Hola {$nombre}, tu cuenta ha sido creada exitosamente."
        );

        // Limpiar cache si existe
        if ($this->cache) {
            $this->cache->eliminar('usuarios_activos_count');
        }

        return $id;
    }

    public function obtenerPerfil(int $id): ?array {
        // Intentar obtener del cache primero
        if ($this->cache) {
            $cacheado = $this->cache->obtener("usuario_{$id}");
            if ($cacheado !== null) {
                return $cacheado;
            }
        }

        // Si no esta en cache, buscar en repositorio
        $usuario = $this->repositorio->buscarPorId($id);

        // Guardar en cache para futuras consultas
        if ($usuario !== null && $this->cache) {
            $this->cache->guardar("usuario_{$id}", $usuario, 1800);
        }

        return $usuario;
    }

    public function contarUsuariosActivos(): int {
        return $this->repositorio->contarActivos();
    }

    public function notificarATodos(string $asunto, string $mensaje): int {
        $usuarios = $this->repositorio->listarTodos();
        $emails = array_column($usuarios, 'email');
        return $this->correo->enviarMasivo($emails, $asunto, $mensaje);
    }
}


// ============================================================================
// Ejemplo 1: Que son los Stubs - Retornar valores predefinidos
// ============================================================================
echo "=== Ejemplo 1: Stubs - Retornar valores predefinidos ===\n\n";

/**
 * Un STUB reemplaza una dependencia para retornar valores controlados.
 * No verifica comportamiento, solo proporciona datos para que
 * el codigo bajo prueba pueda ejecutarse.
 *
 * VERSION PHPUNIT:
 *
 * class ServicioUsuariosTest extends TestCase
 * {
 *     public function testObtenerPerfilRetornaDatosDelRepositorio(): void
 *     {
 *         // Crear stub del repositorio
 *         $repoStub = $this->createStub(RepositorioUsuarios::class);
 *
 *         // Configurar que retorne datos especificos
 *         $repoStub->method('buscarPorId')
 *                  ->willReturn([
 *                      'id' => 1,
 *                      'nombre' => 'Ana Garcia',
 *                      'email' => 'ana@ejemplo.com',
 *                  ]);
 *
 *         $correoStub = $this->createStub(ServicioCorreo::class);
 *
 *         $servicio = new ServicioUsuarios($repoStub, $correoStub);
 *         $perfil = $servicio->obtenerPerfil(1);
 *
 *         $this->assertEquals('Ana Garcia', $perfil['nombre']);
 *         $this->assertEquals('ana@ejemplo.com', $perfil['email']);
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Creamos stubs manualmente implementando la interfaz

/**
 * STUB del repositorio: Retorna datos fijos sin base de datos real.
 * Esto es exactamente lo que hace createStub() internamente.
 */
class RepositorioUsuariosStub implements RepositorioUsuarios {
    /** @var array<int, array> Datos falsos en memoria */
    private array $usuarios = [];

    public function __construct(array $usuarios = []) {
        foreach ($usuarios as $usuario) {
            $this->usuarios[$usuario['id']] = $usuario;
        }
    }

    public function buscarPorId(int $id): ?array {
        return $this->usuarios[$id] ?? null;
    }

    public function buscarPorEmail(string $email): ?array {
        foreach ($this->usuarios as $usuario) {
            if ($usuario['email'] === $email) {
                return $usuario;
            }
        }
        return null;
    }

    public function guardar(array $datos): int {
        $id = count($this->usuarios) + 1;
        $datos['id'] = $id;
        $this->usuarios[$id] = $datos;
        return $id;
    }

    public function eliminar(int $id): bool {
        if (isset($this->usuarios[$id])) {
            unset($this->usuarios[$id]);
            return true;
        }
        return false;
    }

    public function listarTodos(): array {
        return array_values($this->usuarios);
    }

    public function contarActivos(): int {
        return count(array_filter($this->usuarios, fn($u) => $u['activo'] ?? false));
    }
}

/** STUB del servicio de correo: No envia nada, solo simula exito */
class ServicioCorreoStub implements ServicioCorreo {
    public function enviar(string $destinatario, string $asunto, string $cuerpo): bool {
        return true; // Siempre "exitoso" sin enviar nada
    }

    public function enviarMasivo(array $destinatarios, string $asunto, string $cuerpo): int {
        return count($destinatarios); // Simula que todos se enviaron
    }
}

// Pruebas usando stubs:
$datosUsuario = [
    ['id' => 1, 'nombre' => 'Ana Garcia', 'email' => 'ana@ejemplo.com', 'activo' => true],
    ['id' => 2, 'nombre' => 'Carlos Lopez', 'email' => 'carlos@ejemplo.com', 'activo' => true],
    ['id' => 3, 'nombre' => 'Maria Ruiz', 'email' => 'maria@ejemplo.com', 'activo' => false],
];

Verificar::ejecutar("Stub: obtenerPerfil retorna datos del repositorio", function() use ($datosUsuario) {
    $repoStub = new RepositorioUsuariosStub($datosUsuario);
    $correoStub = new ServicioCorreoStub();
    $servicio = new ServicioUsuarios($repoStub, $correoStub);

    $perfil = $servicio->obtenerPerfil(1);

    Verificar::assertEquals('Ana Garcia', $perfil['nombre']);
    Verificar::assertEquals('ana@ejemplo.com', $perfil['email']);
});

Verificar::ejecutar("Stub: obtenerPerfil con ID inexistente retorna null", function() use ($datosUsuario) {
    $repoStub = new RepositorioUsuariosStub($datosUsuario);
    $correoStub = new ServicioCorreoStub();
    $servicio = new ServicioUsuarios($repoStub, $correoStub);

    $perfil = $servicio->obtenerPerfil(999);

    Verificar::assertNull($perfil);
});

Verificar::ejecutar("Stub: contarUsuariosActivos cuenta correctamente", function() use ($datosUsuario) {
    $repoStub = new RepositorioUsuariosStub($datosUsuario);
    $correoStub = new ServicioCorreoStub();
    $servicio = new ServicioUsuarios($repoStub, $correoStub);

    // 2 activos de 3 usuarios
    Verificar::assertEquals(2, $servicio->contarUsuariosActivos());
});

echo "\n";


// ============================================================================
// Ejemplo 2: Mocks - Verificar que metodos fueron llamados
// ============================================================================
echo "=== Ejemplo 2: Mocks - Verificar comportamiento ===\n\n";

/**
 * Un MOCK verifica que ciertos metodos fueron llamados con los
 * parametros correctos el numero correcto de veces.
 *
 * La diferencia clave:
 *   STUB = "cuando me llames, retorno esto" (configura respuestas)
 *   MOCK = "verifico que me llamaste asi" (verifica interacciones)
 *
 * VERSION PHPUNIT:
 *
 * class ServicioUsuariosTest extends TestCase
 * {
 *     public function testRegistrarEnviaEmailDeBienvenida(): void
 *     {
 *         $repoMock = $this->createMock(RepositorioUsuarios::class);
 *         $repoMock->method('buscarPorEmail')->willReturn(null);
 *         $repoMock->method('guardar')->willReturn(42);
 *
 *         // Crear mock del correo y VERIFICAR que se llame
 *         $correoMock = $this->createMock(ServicioCorreo::class);
 *         $correoMock->expects($this->once())    // Exactamente 1 vez
 *                    ->method('enviar')
 *                    ->with(
 *                        'nuevo@ejemplo.com',                      // destinatario
 *                        'Bienvenido a nuestra plataforma',        // asunto
 *                        $this->stringContains('Pedro')            // cuerpo contiene nombre
 *                    )
 *                    ->willReturn(true);
 *
 *         $servicio = new ServicioUsuarios($repoMock, $correoMock);
 *         $id = $servicio->registrar('Pedro', 'nuevo@ejemplo.com', 'MiClave123');
 *
 *         $this->assertEquals(42, $id);
 *     }
 *
 *     public function testRegistrarGuardaEnRepositorio(): void
 *     {
 *         $repoMock = $this->createMock(RepositorioUsuarios::class);
 *         $repoMock->method('buscarPorEmail')->willReturn(null);
 *
 *         // Verificar que guardar() se llama UNA vez con datos correctos
 *         $repoMock->expects($this->once())
 *                  ->method('guardar')
 *                  ->with($this->callback(function($datos) {
 *                      return $datos['nombre'] === 'Pedro'
 *                          && $datos['email'] === 'nuevo@ejemplo.com';
 *                  }))
 *                  ->willReturn(42);
 *
 *         $correoMock = $this->createStub(ServicioCorreo::class);
 *         $correoMock->method('enviar')->willReturn(true);
 *
 *         $servicio = new ServicioUsuarios($repoMock, $correoMock);
 *         $servicio->registrar('Pedro', 'nuevo@ejemplo.com', 'MiClave123');
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Mock manual que registra llamadas

/**
 * MOCK del servicio de correo: Registra todas las llamadas
 * para verificarlas despues.
 */
class ServicioCorreoMock implements ServicioCorreo {
    /** @var array Registro de todas las llamadas a enviar() */
    public array $enviosRealizados = [];
    public int $vecesLlamadoEnviar = 0;

    /** @var array Registro de llamadas a enviarMasivo() */
    public array $enviosMasivos = [];
    public int $vecesLlamadoEnviarMasivo = 0;

    /** Valor que retornara enviar() */
    private bool $retornoEnviar;

    public function __construct(bool $retornoEnviar = true) {
        $this->retornoEnviar = $retornoEnviar;
    }

    public function enviar(string $destinatario, string $asunto, string $cuerpo): bool {
        $this->vecesLlamadoEnviar++;
        $this->enviosRealizados[] = [
            'destinatario' => $destinatario,
            'asunto'       => $asunto,
            'cuerpo'       => $cuerpo,
        ];
        return $this->retornoEnviar;
    }

    public function enviarMasivo(array $destinatarios, string $asunto, string $cuerpo): int {
        $this->vecesLlamadoEnviarMasivo++;
        $this->enviosMasivos[] = [
            'destinatarios' => $destinatarios,
            'asunto'        => $asunto,
            'cuerpo'        => $cuerpo,
        ];
        return count($destinatarios);
    }

    // Metodos de verificacion del mock
    public function verificarEnviadoUnaVez(): bool {
        return $this->vecesLlamadoEnviar === 1;
    }

    public function verificarDestinatario(string $email): bool {
        foreach ($this->enviosRealizados as $envio) {
            if ($envio['destinatario'] === $email) {
                return true;
            }
        }
        return false;
    }

    public function verificarAsuntoContiene(string $texto): bool {
        foreach ($this->enviosRealizados as $envio) {
            if (str_contains($envio['asunto'], $texto)) {
                return true;
            }
        }
        return false;
    }
}

// Pruebas usando mocks:
Verificar::ejecutar("Mock: Registrar envia exactamente un email", function() {
    $repoStub = new RepositorioUsuariosStub([]); // Vacio, ningun email en uso
    $correoMock = new ServicioCorreoMock();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);
    $servicio->registrar('Pedro Sanchez', 'pedro@ejemplo.com', 'MiClave123');

    // Verificar que enviar() se llamo EXACTAMENTE una vez
    Verificar::assertTrue(
        $correoMock->verificarEnviadoUnaVez(),
        "enviar() debio llamarse exactamente 1 vez"
    );
});

Verificar::ejecutar("Mock: Email enviado al destinatario correcto", function() {
    $repoStub = new RepositorioUsuariosStub([]);
    $correoMock = new ServicioCorreoMock();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);
    $servicio->registrar('Pedro', 'pedro@ejemplo.com', 'MiClave123');

    // Verificar que se envio al email correcto
    Verificar::assertTrue(
        $correoMock->verificarDestinatario('pedro@ejemplo.com'),
        "El email debio enviarse a pedro@ejemplo.com"
    );
});

Verificar::ejecutar("Mock: Asunto del email contiene 'Bienvenido'", function() {
    $repoStub = new RepositorioUsuariosStub([]);
    $correoMock = new ServicioCorreoMock();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);
    $servicio->registrar('Ana', 'ana@ejemplo.com', 'Clave456');

    // Verificar que el asunto contiene la palabra esperada
    Verificar::assertTrue(
        $correoMock->verificarAsuntoContiene('Bienvenido'),
        "El asunto debe contener 'Bienvenido'"
    );
});

Verificar::ejecutar("Mock: Cuerpo del email contiene el nombre", function() {
    $repoStub = new RepositorioUsuariosStub([]);
    $correoMock = new ServicioCorreoMock();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);
    $servicio->registrar('Laura Martinez', 'laura@ejemplo.com', 'Segura789');

    // Verificar el contenido del cuerpo del email
    $ultimoEnvio = end($correoMock->enviosRealizados);
    Verificar::assertStringContainsString('Laura Martinez', $ultimoEnvio['cuerpo']);
});

echo "\n";


// ============================================================================
// Ejemplo 3: Fakes - Implementaciones simplificadas funcionales
// ============================================================================
echo "=== Ejemplo 3: Fakes - Implementaciones simplificadas ===\n\n";

/**
 * Un FAKE es una implementacion funcional pero simplificada.
 * A diferencia de un stub que retorna valores fijos, un fake
 * tiene logica real pero usa recursos mas simples
 * (memoria en lugar de BD, arreglo en lugar de Redis, etc.)
 *
 * Los fakes son utiles cuando necesitas comportamiento real
 * pero no quieres depender de infraestructura externa.
 */

/** FAKE del servicio de cache: Usa un arreglo en lugar de Redis/Memcached */
class ServicioCacheFake implements ServicioCache {
    private array $almacen = [];
    private array $tiempos = [];

    public function obtener(string $clave): mixed {
        if (!$this->existe($clave)) {
            return null;
        }
        return $this->almacen[$clave];
    }

    public function guardar(string $clave, mixed $valor, int $ttl = 3600): bool {
        $this->almacen[$clave] = $valor;
        $this->tiempos[$clave] = time() + $ttl;
        return true;
    }

    public function eliminar(string $clave): bool {
        unset($this->almacen[$clave], $this->tiempos[$clave]);
        return true;
    }

    public function existe(string $clave): bool {
        if (!isset($this->almacen[$clave])) {
            return false;
        }
        // Verificar que no haya expirado (logica real simplificada)
        if (isset($this->tiempos[$clave]) && $this->tiempos[$clave] < time()) {
            $this->eliminar($clave);
            return false;
        }
        return true;
    }

    // Metodo adicional solo para pruebas
    public function obtenerTodo(): array {
        return $this->almacen;
    }
}

Verificar::ejecutar("Fake Cache: Perfil se guarda en cache tras primera consulta", function() {
    $datosUsuario = [
        ['id' => 1, 'nombre' => 'Elena', 'email' => 'elena@ejemplo.com', 'activo' => true],
    ];
    $repoStub = new RepositorioUsuariosStub($datosUsuario);
    $correoStub = new ServicioCorreoStub();
    $cacheFake = new ServicioCacheFake();

    $servicio = new ServicioUsuarios($repoStub, $correoStub, $cacheFake);

    // Primera consulta: debe ir al repositorio y guardar en cache
    $perfil1 = $servicio->obtenerPerfil(1);
    Verificar::assertEquals('Elena', $perfil1['nombre']);

    // Verificar que se guardo en cache
    $enCache = $cacheFake->obtener('usuario_1');
    Verificar::assertEquals('Elena', $enCache['nombre']);
});

Verificar::ejecutar("Fake Cache: Segunda consulta usa cache, no repositorio", function() {
    // Repositorio vacio, pero cache tiene datos
    $repoStub = new RepositorioUsuariosStub([]); // Sin datos
    $correoStub = new ServicioCorreoStub();
    $cacheFake = new ServicioCacheFake();

    // Pre-cargar el cache manualmente
    $cacheFake->guardar('usuario_5', [
        'id' => 5, 'nombre' => 'Desde Cache', 'email' => 'cache@ejemplo.com'
    ]);

    $servicio = new ServicioUsuarios($repoStub, $correoStub, $cacheFake);

    // Debe retornar del cache aunque el repositorio no tenga el dato
    $perfil = $servicio->obtenerPerfil(5);
    Verificar::assertEquals('Desde Cache', $perfil['nombre']);
});

Verificar::ejecutar("Fake Cache: Registro invalida el cache de conteo", function() {
    $repoStub = new RepositorioUsuariosStub([]);
    $correoStub = new ServicioCorreoStub();
    $cacheFake = new ServicioCacheFake();

    // Pre-cargar un conteo en cache
    $cacheFake->guardar('usuarios_activos_count', 10);
    Verificar::assertTrue($cacheFake->existe('usuarios_activos_count'));

    $servicio = new ServicioUsuarios($repoStub, $correoStub, $cacheFake);
    $servicio->registrar('Nuevo Usuario', 'nuevo@ejemplo.com', 'Clave123');

    // El registro debe haber invalidado el cache de conteo
    Verificar::assertFalse($cacheFake->existe('usuarios_activos_count'));
});

echo "\n";


// ============================================================================
// Ejemplo 4: willReturn y willReturnMap en PHPUnit (con equivalente)
// ============================================================================
echo "=== Ejemplo 4: Configurar retornos (willReturn / willReturnMap) ===\n\n";

/**
 * PHPUnit permite configurar que retorna un mock segun los argumentos:
 *
 * willReturn($valor)         - Siempre retorna lo mismo
 * willReturnMap($mapa)       - Retorna segun los argumentos recibidos
 * willReturnCallback($fn)    - Retorna segun una funcion personalizada
 * willReturnOnConsecutiveCalls($v1, $v2, ...) - Retornos en secuencia
 * willThrowException($e)     - Lanza una excepcion
 *
 * VERSION PHPUNIT:
 *
 * class PreciosTest extends TestCase
 * {
 *     public function testWillReturnMap(): void
 *     {
 *         $repoMock = $this->createMock(RepositorioPrecios::class);
 *
 *         // Configurar retornos segun argumentos
 *         $repoMock->method('obtenerPrecio')
 *                  ->willReturnMap([
 *                      ['laptop', 15999.99],
 *                      ['mouse', 499.50],
 *                      ['teclado', 1299.00],
 *                  ]);
 *
 *         $this->assertEquals(15999.99, $repoMock->obtenerPrecio('laptop'));
 *         $this->assertEquals(499.50, $repoMock->obtenerPrecio('mouse'));
 *     }
 *
 *     public function testWillReturnOnConsecutiveCalls(): void
 *     {
 *         $mock = $this->createMock(ServicioAPI::class);
 *
 *         // Cada llamada retorna un valor diferente en secuencia
 *         $mock->method('obtenerEstado')
 *              ->willReturnOnConsecutiveCalls('pendiente', 'procesando', 'completado');
 *
 *         $this->assertEquals('pendiente', $mock->obtenerEstado());
 *         $this->assertEquals('procesando', $mock->obtenerEstado());
 *         $this->assertEquals('completado', $mock->obtenerEstado());
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Simulando willReturnMap con un stub configurable

interface RepositorioPrecios {
    public function obtenerPrecio(string $producto): ?float;
    public function obtenerPrecioConDescuento(string $producto, string $cupon): float;
}

/**
 * Stub configurable que simula willReturnMap():
 * Retorna diferentes valores segun los argumentos recibidos.
 */
class RepositorioPreciosConfigurable implements RepositorioPrecios {
    private array $precios = [];
    private array $descuentos = [];

    /** Simula willReturnMap para obtenerPrecio */
    public function configurarPrecios(array $mapa): void {
        $this->precios = $mapa;
    }

    /** Simula willReturnMap para obtenerPrecioConDescuento */
    public function configurarDescuentos(array $mapa): void {
        $this->descuentos = $mapa;
    }

    public function obtenerPrecio(string $producto): ?float {
        return $this->precios[$producto] ?? null;
    }

    public function obtenerPrecioConDescuento(string $producto, string $cupon): float {
        $clave = "{$producto}|{$cupon}";
        return $this->descuentos[$clave] ?? $this->obtenerPrecio($producto) ?? 0.0;
    }
}

// Clase que usa el repositorio de precios
class CalculadoraCarrito {
    public function __construct(private RepositorioPrecios $repositorio) {}

    public function calcularTotal(array $items): float {
        $total = 0;
        foreach ($items as $producto => $cantidad) {
            $precio = $this->repositorio->obtenerPrecio($producto);
            if ($precio === null) {
                throw new \RuntimeException("Producto no encontrado: {$producto}");
            }
            $total += $precio * $cantidad;
        }
        return round($total, 2);
    }

    public function calcularTotalConCupon(array $items, string $cupon): float {
        $total = 0;
        foreach ($items as $producto => $cantidad) {
            $precio = $this->repositorio->obtenerPrecioConDescuento($producto, $cupon);
            $total += $precio * $cantidad;
        }
        return round($total, 2);
    }
}

// Pruebas:
Verificar::ejecutar("willReturnMap: Precios mapeados por producto", function() {
    $repo = new RepositorioPreciosConfigurable();
    $repo->configurarPrecios([
        'laptop'  => 15999.99,
        'mouse'   => 499.50,
        'teclado' => 1299.00,
    ]);

    $calculadora = new CalculadoraCarrito($repo);
    $total = $calculadora->calcularTotal([
        'laptop' => 1,     // 15999.99
        'mouse'  => 2,     // 499.50 * 2 = 999.00
    ]);

    Verificar::assertEquals(16998.99, $total);
});

Verificar::ejecutar("willReturnMap: Precios con cupon de descuento", function() {
    $repo = new RepositorioPreciosConfigurable();
    $repo->configurarPrecios([
        'laptop' => 15999.99,
        'mouse'  => 499.50,
    ]);
    $repo->configurarDescuentos([
        'laptop|VERANO20' => 12799.99,   // 20% de descuento
        'mouse|VERANO20'  => 399.60,     // 20% de descuento
    ]);

    $calculadora = new CalculadoraCarrito($repo);
    $total = $calculadora->calcularTotalConCupon(
        ['laptop' => 1, 'mouse' => 1],
        'VERANO20'
    );

    Verificar::assertEquals(13199.59, $total);
});

// Simulando willReturnOnConsecutiveCalls
Verificar::ejecutar("willReturnOnConsecutiveCalls: Retornos en secuencia", function() {
    // Simulamos un servicio que retorna estados en secuencia
    $estados = ['pendiente', 'procesando', 'completado'];
    $indice = 0;

    $obtenerEstado = function() use ($estados, &$indice): string {
        return $estados[$indice++] ?? 'desconocido';
    };

    Verificar::assertEquals('pendiente', $obtenerEstado());
    Verificar::assertEquals('procesando', $obtenerEstado());
    Verificar::assertEquals('completado', $obtenerEstado());
});

Verificar::ejecutar("willThrowException: Simular fallo del repositorio", function() {
    // Simular que el repositorio lanza una excepcion
    $repo = new RepositorioPreciosConfigurable();
    // No configuramos precios, asi que obtenerPrecio retorna null
    // y calcularTotal lanza RuntimeException

    $calculadora = new CalculadoraCarrito($repo);
    $lanzoExcepcion = false;

    try {
        $calculadora->calcularTotal(['producto_inexistente' => 1]);
    } catch (\RuntimeException $e) {
        $lanzoExcepcion = true;
        Verificar::assertStringContainsString('no encontrado', $e->getMessage());
    }

    Verificar::assertTrue($lanzoExcepcion, "Debio lanzar excepcion");
});

echo "\n";


// ============================================================================
// Ejemplo 5: Verificar llamadas con expects() (conteo y parametros)
// ============================================================================
echo "=== Ejemplo 5: expects() - Verificar llamadas ===\n\n";

/**
 * En PHPUnit, expects() configura cuantas veces debe llamarse un metodo:
 *
 *   expects($this->once())       - Exactamente 1 vez
 *   expects($this->exactly(3))   - Exactamente 3 veces
 *   expects($this->never())      - Nunca (0 veces)
 *   expects($this->atLeastOnce()) - 1 o mas veces
 *   expects($this->atMost(5))    - Como maximo 5 veces
 *   expects($this->any())        - Cualquier cantidad
 *
 * VERSION PHPUNIT:
 *
 * class NotificacionTest extends TestCase
 * {
 *     public function testNotificarATodosLlamaEnviarMasivo(): void
 *     {
 *         $repo = $this->createStub(RepositorioUsuarios::class);
 *         $repo->method('listarTodos')->willReturn([
 *             ['email' => 'a@test.com'],
 *             ['email' => 'b@test.com'],
 *         ]);
 *
 *         $correo = $this->createMock(ServicioCorreo::class);
 *         $correo->expects($this->once())        // Se llama una vez
 *                ->method('enviarMasivo')
 *                ->with(
 *                    ['a@test.com', 'b@test.com'],  // destinatarios
 *                    'Aviso importante',              // asunto
 *                    $this->anything()               // cuerpo (cualquier valor)
 *                )
 *                ->willReturn(2);
 *
 *         $servicio = new ServicioUsuarios($repo, $correo);
 *         $enviados = $servicio->notificarATodos('Aviso importante', 'Contenido');
 *
 *         $this->assertEquals(2, $enviados);
 *     }
 *
 *     public function testNoEnviaCorreoSiRegistroFalla(): void
 *     {
 *         $repo = $this->createStub(RepositorioUsuarios::class);
 *         $repo->method('buscarPorEmail')->willReturn(['existente']); // Email ya existe
 *
 *         $correo = $this->createMock(ServicioCorreo::class);
 *         $correo->expects($this->never())  // NUNCA se debe llamar
 *                ->method('enviar');
 *
 *         $servicio = new ServicioUsuarios($repo, $correo);
 *
 *         $this->expectException(\RuntimeException::class);
 *         $servicio->registrar('Test', 'existente@test.com', 'Clave123');
 *     }
 * }
 */

// VERSION INDEPENDIENTE - Mock con contador

/** Mock avanzado que registra todas las interacciones */
class ServicioCorreoMockAvanzado implements ServicioCorreo {
    private array $llamadas = [];

    public function enviar(string $destinatario, string $asunto, string $cuerpo): bool {
        $this->llamadas[] = [
            'metodo'       => 'enviar',
            'destinatario' => $destinatario,
            'asunto'       => $asunto,
            'cuerpo'       => $cuerpo,
        ];
        return true;
    }

    public function enviarMasivo(array $destinatarios, string $asunto, string $cuerpo): int {
        $this->llamadas[] = [
            'metodo'        => 'enviarMasivo',
            'destinatarios' => $destinatarios,
            'asunto'        => $asunto,
            'cuerpo'        => $cuerpo,
        ];
        return count($destinatarios);
    }

    // Verificaciones estilo expects()
    public function contarLlamadas(string $metodo): int {
        return count(array_filter(
            $this->llamadas,
            fn($l) => $l['metodo'] === $metodo
        ));
    }

    public function obtenerLlamada(string $metodo, int $indice = 0): ?array {
        $llamadasMetodo = array_values(array_filter(
            $this->llamadas,
            fn($l) => $l['metodo'] === $metodo
        ));
        return $llamadasMetodo[$indice] ?? null;
    }

    public function verificarNuncaLlamado(string $metodo): bool {
        return $this->contarLlamadas($metodo) === 0;
    }
}

Verificar::ejecutar("expects(once) - notificarATodos llama enviarMasivo una vez", function() {
    $repoStub = new RepositorioUsuariosStub([
        ['id' => 1, 'nombre' => 'Ana', 'email' => 'ana@test.com', 'activo' => true],
        ['id' => 2, 'nombre' => 'Carlos', 'email' => 'carlos@test.com', 'activo' => true],
    ]);
    $correoMock = new ServicioCorreoMockAvanzado();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);
    $enviados = $servicio->notificarATodos('Aviso', 'Contenido del aviso');

    // Verificar: expects($this->once())->method('enviarMasivo')
    Verificar::assertSame(1, $correoMock->contarLlamadas('enviarMasivo'));

    // Verificar los argumentos
    $llamada = $correoMock->obtenerLlamada('enviarMasivo');
    Verificar::assertCount(2, $llamada['destinatarios']);
    Verificar::assertEquals('Aviso', $llamada['asunto']);

    // Verificar valor de retorno
    Verificar::assertEquals(2, $enviados);
});

Verificar::ejecutar("expects(never) - No envia correo si el email ya existe", function() {
    $repoStub = new RepositorioUsuariosStub([
        ['id' => 1, 'nombre' => 'Existente', 'email' => 'ya@existe.com', 'activo' => true],
    ]);
    $correoMock = new ServicioCorreoMockAvanzado();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);

    $lanzoExcepcion = false;
    try {
        $servicio->registrar('Test', 'ya@existe.com', 'Clave123');
    } catch (\RuntimeException $e) {
        $lanzoExcepcion = true;
    }

    Verificar::assertTrue($lanzoExcepcion, "Debio lanzar excepcion por email duplicado");

    // expects($this->never())->method('enviar')
    Verificar::assertTrue(
        $correoMock->verificarNuncaLlamado('enviar'),
        "enviar() NUNCA debio llamarse cuando el registro falla"
    );
});

Verificar::ejecutar("expects(exactly) - Registro llama enviar exactamente una vez", function() {
    $repoStub = new RepositorioUsuariosStub([]);
    $correoMock = new ServicioCorreoMockAvanzado();

    $servicio = new ServicioUsuarios($repoStub, $correoMock);
    $servicio->registrar('Nuevo', 'nuevo@test.com', 'MiClave123');

    // Verificar que enviar se llamo exactamente 1 vez
    Verificar::assertSame(1, $correoMock->contarLlamadas('enviar'));

    // Y que enviarMasivo NUNCA se llamo
    Verificar::assertTrue($correoMock->verificarNuncaLlamado('enviarMasivo'));
});

echo "\n";


// ============================================================================
// Ejemplo 6: Escenario completo - Servicio de pedidos con multiples dobles
// ============================================================================
echo "=== Ejemplo 6: Escenario completo - Servicio de pedidos ===\n\n";

/**
 * Escenario realista: Un ServicioPedidos depende de varios componentes.
 * Usamos diferentes tipos de dobles segun la necesidad.
 */

interface RepositorioPedidos {
    public function guardar(array $pedido): int;
    public function buscarPorId(int $id): ?array;
}

interface PasarelaPago {
    public function procesarPago(float $monto, string $tarjeta): array;
}

interface ServicioInventario {
    public function verificarDisponibilidad(string $sku, int $cantidad): bool;
    public function reservar(string $sku, int $cantidad): bool;
}

class ServicioPedidos {
    public function __construct(
        private RepositorioPedidos $repositorio,
        private PasarelaPago $pasarela,
        private ServicioInventario $inventario,
        private ServicioCorreo $correo
    ) {}

    public function crearPedido(
        array $items,
        string $tarjeta,
        string $emailCliente
    ): array {
        // 1. Verificar disponibilidad de todos los items
        foreach ($items as $item) {
            if (!$this->inventario->verificarDisponibilidad($item['sku'], $item['cantidad'])) {
                throw new \RuntimeException("Producto {$item['sku']} no disponible");
            }
        }

        // 2. Calcular total
        $total = array_sum(array_map(
            fn($i) => $i['precio'] * $i['cantidad'],
            $items
        ));

        // 3. Procesar pago
        $resultadoPago = $this->pasarela->procesarPago($total, $tarjeta);
        if ($resultadoPago['estado'] !== 'aprobado') {
            throw new \RuntimeException("Pago rechazado: " . ($resultadoPago['razon'] ?? 'desconocida'));
        }

        // 4. Reservar inventario
        foreach ($items as $item) {
            $this->inventario->reservar($item['sku'], $item['cantidad']);
        }

        // 5. Guardar pedido
        $idPedido = $this->repositorio->guardar([
            'items'           => $items,
            'total'           => $total,
            'transaccion_id'  => $resultadoPago['transaccion_id'],
            'email'           => $emailCliente,
            'estado'          => 'confirmado',
        ]);

        // 6. Enviar confirmacion
        $this->correo->enviar(
            $emailCliente,
            "Pedido #{$idPedido} confirmado",
            "Su pedido por \${$total} ha sido confirmado."
        );

        return [
            'id'              => $idPedido,
            'total'           => $total,
            'transaccion_id'  => $resultadoPago['transaccion_id'],
            'estado'          => 'confirmado',
        ];
    }
}

// Implementaciones fake y mock para la prueba completa:

class RepositorioPedidosFake implements RepositorioPedidos {
    private array $pedidos = [];
    private int $siguienteId = 1000;

    public function guardar(array $pedido): int {
        $id = $this->siguienteId++;
        $pedido['id'] = $id;
        $this->pedidos[$id] = $pedido;
        return $id;
    }

    public function buscarPorId(int $id): ?array {
        return $this->pedidos[$id] ?? null;
    }
}

class PasarelaPagoStub implements PasarelaPago {
    private string $estado;
    private string $razon;

    public function __construct(string $estado = 'aprobado', string $razon = '') {
        $this->estado = $estado;
        $this->razon = $razon;
    }

    public function procesarPago(float $monto, string $tarjeta): array {
        return [
            'estado'          => $this->estado,
            'transaccion_id'  => 'TXN-' . uniqid(),
            'monto'           => $monto,
            'razon'           => $this->razon,
        ];
    }
}

class ServicioInventarioStub implements ServicioInventario {
    private array $disponibilidad = [];

    public function configurar(string $sku, bool $disponible): void {
        $this->disponibilidad[$sku] = $disponible;
    }

    public function verificarDisponibilidad(string $sku, int $cantidad): bool {
        return $this->disponibilidad[$sku] ?? true;
    }

    public function reservar(string $sku, int $cantidad): bool {
        return true;
    }
}

// Pruebas del escenario completo:
Verificar::ejecutar("Pedido exitoso: todo funciona correctamente", function() {
    $repo = new RepositorioPedidosFake();
    $pago = new PasarelaPagoStub('aprobado');
    $inventario = new ServicioInventarioStub();
    $correo = new ServicioCorreoMockAvanzado();

    $servicio = new ServicioPedidos($repo, $pago, $inventario, $correo);
    $resultado = $servicio->crearPedido(
        [
            ['sku' => 'LAP-001', 'precio' => 15999.99, 'cantidad' => 1],
            ['sku' => 'MOU-001', 'precio' => 499.50, 'cantidad' => 2],
        ],
        '4111111111111111',
        'cliente@ejemplo.com'
    );

    Verificar::assertEquals('confirmado', $resultado['estado']);
    Verificar::assertEquals(16998.99, $resultado['total']);

    // Verificar que se envio confirmacion por correo
    Verificar::assertSame(1, $correo->contarLlamadas('enviar'));
    $llamada = $correo->obtenerLlamada('enviar');
    Verificar::assertEquals('cliente@ejemplo.com', $llamada['destinatario']);
});

Verificar::ejecutar("Pedido falla: Producto no disponible", function() {
    $repo = new RepositorioPedidosFake();
    $pago = new PasarelaPagoStub('aprobado');
    $inventario = new ServicioInventarioStub();
    $inventario->configurar('AGOTADO-001', false); // No disponible
    $correo = new ServicioCorreoMockAvanzado();

    $servicio = new ServicioPedidos($repo, $pago, $inventario, $correo);

    $lanzoExcepcion = false;
    try {
        $servicio->crearPedido(
            [['sku' => 'AGOTADO-001', 'precio' => 100, 'cantidad' => 1]],
            '4111111111111111',
            'cliente@ejemplo.com'
        );
    } catch (\RuntimeException $e) {
        $lanzoExcepcion = true;
        Verificar::assertStringContainsString('no disponible', $e->getMessage());
    }

    Verificar::assertTrue($lanzoExcepcion);
    // El correo NUNCA se envia si el pedido falla
    Verificar::assertTrue($correo->verificarNuncaLlamado('enviar'));
});

Verificar::ejecutar("Pedido falla: Pago rechazado", function() {
    $repo = new RepositorioPedidosFake();
    $pago = new PasarelaPagoStub('rechazado', 'Fondos insuficientes');
    $inventario = new ServicioInventarioStub();
    $correo = new ServicioCorreoMockAvanzado();

    $servicio = new ServicioPedidos($repo, $pago, $inventario, $correo);

    $lanzoExcepcion = false;
    try {
        $servicio->crearPedido(
            [['sku' => 'LAP-001', 'precio' => 15999.99, 'cantidad' => 1]],
            '4111111111111111',
            'cliente@ejemplo.com'
        );
    } catch (\RuntimeException $e) {
        $lanzoExcepcion = true;
        Verificar::assertStringContainsString('rechazado', $e->getMessage());
    }

    Verificar::assertTrue($lanzoExcepcion);
    // No se envia correo si el pago falla
    Verificar::assertTrue($correo->verificarNuncaLlamado('enviar'));
});

Verificar::ejecutar("Pedido exitoso se guarda en repositorio", function() {
    $repo = new RepositorioPedidosFake();
    $pago = new PasarelaPagoStub('aprobado');
    $inventario = new ServicioInventarioStub();
    $correo = new ServicioCorreoStub();

    $servicio = new ServicioPedidos($repo, $pago, $inventario, $correo);
    $resultado = $servicio->crearPedido(
        [['sku' => 'TEC-001', 'precio' => 1299, 'cantidad' => 1]],
        '4111111111111111',
        'buyer@ejemplo.com'
    );

    // Verificar que el pedido se persiste en el repositorio
    $pedidoGuardado = $repo->buscarPorId($resultado['id']);
    Verificar::assertEquals('confirmado', $pedidoGuardado['estado']);
    Verificar::assertEquals('buyer@ejemplo.com', $pedidoGuardado['email']);
    Verificar::assertEquals(1299, $pedidoGuardado['total']);
});

// ============================================================================
// RESUMEN FINAL
// ============================================================================
Verificar::resumen();

/**
 * RESUMEN DE DOBLES DE PRUEBA:
 *
 * +--------+------------------+---------------------------+------------------+
 * | Tipo   | Comportamiento   | Cuando Usar               | PHPUnit          |
 * +--------+------------------+---------------------------+------------------+
 * | Dummy  | Sin logica       | Solo llenar parametros    | createStub()     |
 * | Stub   | Retorna valores  | Proporcionar datos fijos  | createStub()     |
 * |        | predefinidos     |                           | + method()->     |
 * |        |                  |                           |   willReturn()   |
 * +--------+------------------+---------------------------+------------------+
 * | Mock   | Verifica         | Verificar interacciones   | createMock()     |
 * |        | llamadas         | (se llamo?, cuantas       | + expects()      |
 * |        |                  |  veces?, con que args?)   |                  |
 * +--------+------------------+---------------------------+------------------+
 * | Fake   | Implementacion   | Necesitas logica real     | Clase manual     |
 * |        | simplificada     | sin infraestructura       | implementando    |
 * |        |                  | (BD en memoria, etc.)     | la interfaz      |
 * +--------+------------------+---------------------------+------------------+
 * | Spy    | Registra         | Verificar despues de la   | Mock con         |
 * |        | llamadas         | ejecucion                 | expects() +      |
 * |        |                  |                           | callback         |
 * +--------+------------------+---------------------------+------------------+
 *
 * BUENAS PRACTICAS:
 *   1. Preferir stubs sobre mocks cuando sea posible (menos fragiles)
 *   2. Solo mockear lo que NO controlas (no mockees tu propio codigo)
 *   3. Inyectar dependencias por constructor facilita el testing
 *   4. Programar contra interfaces, no contra implementaciones
 *   5. Un mock que verifica demasiado hace la prueba fragil
 */
?>
