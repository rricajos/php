<?php
// ============================================================================
// AUTOLOADING EN PHP
// ============================================================================
// Autoloading resuelve el problema de cargar manualmente cada archivo PHP
// con require/include. En vez de escribir decenas de require al inicio de
// cada archivo, PHP puede cargar clases automaticamente cuando se usan
// por primera vez, gracias a spl_autoload_register().
//
// El estandar PSR-4 define como mapear namespaces a directorios, permitiendo
// que herramientas como Composer generen autoloaders eficientes.
// ============================================================================

// ============================================================================
// Ejemplo 1: El problema sin autoloading
// ============================================================================
// Sin autoloading, necesitamos require/include para cada clase.
// Esto no escala en proyectos grandes.

echo "=== Ejemplo 1: El problema sin autoloading ===\n\n";

// Imaginemos una aplicacion con esta estructura de archivos:
//
//   proyecto/
//   ├── Models/
//   │   ├── Usuario.php          (clase App\Models\Usuario)
//   │   ├── Producto.php         (clase App\Models\Producto)
//   │   └── Pedido.php           (clase App\Models\Pedido)
//   ├── Services/
//   │   ├── AuthService.php      (clase App\Services\AuthService)
//   │   ├── EmailService.php     (clase App\Services\EmailService)
//   │   └── PagoService.php      (clase App\Services\PagoService)
//   ├── Controllers/
//   │   ├── HomeController.php
//   │   ├── UserController.php
//   │   └── ApiController.php
//   └── index.php

// SIN autoloading, index.php tendria algo como esto:
echo "  Sin autoloading, necesitariamos:\n";
echo "    require 'Models/Usuario.php';\n";
echo "    require 'Models/Producto.php';\n";
echo "    require 'Models/Pedido.php';\n";
echo "    require 'Services/AuthService.php';\n";
echo "    require 'Services/EmailService.php';\n";
echo "    require 'Services/PagoService.php';\n";
echo "    require 'Controllers/HomeController.php';\n";
echo "    require 'Controllers/UserController.php';\n";
echo "    require 'Controllers/ApiController.php';\n";
echo "    // ... y asi para CADA clase del proyecto\n\n";

echo "  Problemas:\n";
echo "    1. Mantener la lista de requires es tedioso y propenso a errores\n";
echo "    2. Cargar TODO aunque solo usemos 2-3 clases desperdicia memoria\n";
echo "    3. El orden importa: si B depende de A, A debe cargarse antes\n";
echo "    4. Agregar una clase nueva requiere modificar multiples archivos\n";
echo "    5. En proyectos con 100+ clases, es completamente inmanejable\n\n";


// ============================================================================
// Ejemplo 2: spl_autoload_register() - El mecanismo basico
// ============================================================================
// spl_autoload_register() registra funciones que PHP llama automaticamente
// cuando se intenta usar una clase que aun no esta cargada.

echo "=== Ejemplo 2: spl_autoload_register() ===\n\n";

// Registro de autoloaders internos para demostracion
// En un proyecto real, estos buscarian archivos en el filesystem

// Simularemos clases "cargadas" con un registro para demostrar el mecanismo
$clasesSimuladas = [];
$logAutoload = [];

// Primer autoloader: busca en el directorio 'src'
spl_autoload_register(function (string $clase) use (&$clasesSimuladas, &$logAutoload) {
    $logAutoload[] = "[Autoloader 1] Buscando clase: {$clase}";

    // Convertir namespace a ruta de archivo
    // App\Models\Usuario -> src/Models/Usuario.php
    $archivo = 'src/' . str_replace('\\', '/', $clase) . '.php';
    $logAutoload[] = "[Autoloader 1] Ruta calculada: {$archivo}";

    // En un proyecto real aqui hariamos: if (file_exists($archivo)) require $archivo;
    // Para la demostracion, solo registramos que se intento cargar
    $clasesSimuladas[$clase] = $archivo;

    // Retornar sin cargar realmente (demostracion)
    return false; // false = no se encontro, intentar siguiente autoloader
});

// Segundo autoloader: busca en el directorio 'vendor'
spl_autoload_register(function (string $clase) use (&$logAutoload) {
    $logAutoload[] = "[Autoloader 2] Buscando en vendor: {$clase}";

    // Mapeo de vendor
    $archivo = 'vendor/' . str_replace('\\', '/', $clase) . '.php';
    $logAutoload[] = "[Autoloader 2] Ruta calculada: {$archivo}";

    return false;
});

echo "  Se registraron " . count(spl_autoload_functions()) . " autoloaders\n\n";

// Demostrar como se encadenan los autoloaders
echo "  Los autoloaders se ejecutan en orden hasta que uno cargue la clase.\n";
echo "  Si ninguno la encuentra, PHP lanza un error fatal.\n\n";

echo "  Autoloaders registrados:\n";
foreach (spl_autoload_functions() as $indice => $autoloader) {
    $tipo = is_array($autoloader) ? get_class($autoloader[0]) . '::' . $autoloader[1] : 'Closure';
    echo "    [{$indice}] {$tipo}\n";
}
echo "\n";

// Limpiar los autoloaders de demostracion para no interferir con el resto
foreach (spl_autoload_functions() as $autoloader) {
    spl_autoload_unregister($autoloader);
}


// ============================================================================
// Ejemplo 3: El estandar PSR-4 explicado
// ============================================================================
// PSR-4 define una convencion para mapear namespaces a directorios.
// La regla fundamental: el namespace completo de la clase determina
// la ruta del archivo donde esta definida.

echo "=== Ejemplo 3: El estandar PSR-4 explicado ===\n\n";

echo "  Reglas de PSR-4:\n";
echo "  ────────────────\n\n";

echo "  1. Cada clase DEBE estar en su propio archivo\n";
echo "  2. El nombre del archivo DEBE coincidir con el nombre de la clase\n";
echo "     Clase 'Usuario' -> archivo 'Usuario.php'\n\n";

echo "  3. El namespace mapea a la estructura de directorios:\n";
echo "     Prefijo del namespace -> Directorio base\n";
echo "     Sub-namespaces -> Sub-directorios\n\n";

echo "  Ejemplo de mapeo PSR-4:\n";
echo "  ┌──────────────────────────────────┬─────────────────────────────┐\n";
echo "  │ Nombre completo de la clase      │ Ruta del archivo            │\n";
echo "  ├──────────────────────────────────┼─────────────────────────────┤\n";
echo "  │ App\\Models\\Usuario               │ src/Models/Usuario.php      │\n";
echo "  │ App\\Models\\Producto              │ src/Models/Producto.php     │\n";
echo "  │ App\\Services\\AuthService         │ src/Services/AuthService.php│\n";
echo "  │ App\\Http\\Controllers\\HomeCtrl    │ src/Http/Controllers/       │\n";
echo "  │                                  │     HomeCtrl.php            │\n";
echo "  │ Vendor\\Paquete\\Logger            │ vendor/paquete/src/         │\n";
echo "  │                                  │     Logger.php              │\n";
echo "  └──────────────────────────────────┴─────────────────────────────┘\n\n";

echo "  En este ejemplo el prefijo 'App\\' se mapea al directorio 'src/'.\n";
echo "  Asi, 'App\\Models\\Usuario' se busca en 'src/Models/Usuario.php'.\n\n";

// Demostrar el algoritmo de resolucion
function resolverRutaPSR4(string $clase, array $prefijos): ?string {
    foreach ($prefijos as $prefijo => $directorioBase) {
        // Verificar si la clase comienza con este prefijo
        $longitudPrefijo = strlen($prefijo);

        if (strncmp($prefijo, $clase, $longitudPrefijo) !== 0) {
            continue; // Este prefijo no aplica
        }

        // Obtener el nombre relativo de la clase (sin el prefijo)
        $claseRelativa = substr($clase, $longitudPrefijo);

        // Construir la ruta del archivo
        $archivo = $directorioBase . str_replace('\\', '/', $claseRelativa) . '.php';

        return $archivo;
    }

    return null;
}

// Configuracion de prefijos (como en composer.json)
$prefijos = [
    'App\\' => 'src/',
    'App\\Tests\\' => 'tests/',          // Mas especifico primero
    'Vendor\\Logger\\' => 'vendor/logger/src/',
    'Vendor\\Database\\' => 'vendor/database/src/',
];

// Resolver varias clases
$clases = [
    'App\\Models\\Usuario',
    'App\\Services\\EmailService',
    'App\\Http\\Middleware\\AuthMiddleware',
    'App\\Tests\\Unit\\UsuarioTest',
    'Vendor\\Logger\\FileLogger',
    'Vendor\\Database\\Connection',
    'ClaseGlobal', // Sin namespace, no coincide con ningun prefijo
];

echo "  Resolucion de rutas PSR-4:\n";
foreach ($clases as $clase) {
    $ruta = resolverRutaPSR4($clase, $prefijos);
    $rutaStr = $ruta ?? '(no resuelto - sin prefijo coincidente)';
    echo "    {$clase}\n      -> {$rutaStr}\n";
}
echo "\n";


// ============================================================================
// Ejemplo 4: Construir un autoloader PSR-4 desde cero
// ============================================================================
// Implementacion completa de un autoloader PSR-4 funcional.
// Este es el tipo de autoloader que Composer genera automaticamente.

echo "=== Ejemplo 4: Autoloader PSR-4 desde cero ===\n\n";

class AutoloaderPSR4 {
    // Mapeo de prefijos de namespace a directorios base
    // Formato: ['App\\' => ['/ruta/a/src/', '/otra/ruta/'], ...]
    private array $prefijos = [];

    // Registro de clases cargadas (para demostracion)
    private array $clasesResueltas = [];

    // Modo de depuracion
    private bool $debug;

    public function __construct(bool $debug = false) {
        $this->debug = $debug;
    }

    // Registrar un directorio base para un prefijo de namespace
    // Un mismo prefijo puede tener multiples directorios (se buscan en orden)
    public function agregarNamespace(string $prefijo, string $directorioBase, bool $prepend = false): void {
        // Normalizar el prefijo: debe terminar con \
        $prefijo = trim($prefijo, '\\') . '\\';

        // Normalizar el directorio: debe terminar con /
        $directorioBase = rtrim($directorioBase, '/') . '/';

        // Inicializar el array de directorios si no existe
        if (!isset($this->prefijos[$prefijo])) {
            $this->prefijos[$prefijo] = [];
        }

        // Agregar al inicio o al final de la lista
        if ($prepend) {
            array_unshift($this->prefijos[$prefijo], $directorioBase);
        } else {
            $this->prefijos[$prefijo][] = $directorioBase;
        }
    }

    // Registrar este autoloader con spl_autoload_register
    public function registrar(): void {
        spl_autoload_register([$this, 'cargarClase']);
    }

    // Desregistrar el autoloader
    public function desregistrar(): void {
        spl_autoload_unregister([$this, 'cargarClase']);
    }

    // Intentar cargar una clase
    public function cargarClase(string $clase): ?string {
        if ($this->debug) {
            echo "    [PSR-4] Intentando cargar: {$clase}\n";
        }

        // Intentar con cada prefijo registrado
        $prefijo = $clase;

        // Iterar hacia atras por los segmentos del namespace
        while (false !== $pos = strrpos($prefijo, '\\')) {
            // Obtener el prefijo hasta el ultimo separador (incluyendo \)
            $prefijo = substr($clase, 0, $pos + 1);

            // El resto es el nombre relativo de la clase
            $claseRelativa = substr($clase, $pos + 1);

            // Intentar cargar desde los directorios mapeados
            $archivo = $this->cargarClaseMapeada($prefijo, $claseRelativa);

            if ($archivo !== null) {
                return $archivo;
            }

            // Quitar el ultimo segmento y buscar con un prefijo mas corto
            $prefijo = rtrim($prefijo, '\\');
        }

        // Ningun prefijo coincidio
        if ($this->debug) {
            echo "    [PSR-4] No se encontro: {$clase}\n";
        }
        return null;
    }

    // Buscar el archivo en los directorios mapeados para un prefijo
    private function cargarClaseMapeada(string $prefijo, string $claseRelativa): ?string {
        if (!isset($this->prefijos[$prefijo])) {
            return null;
        }

        foreach ($this->prefijos[$prefijo] as $directorioBase) {
            // Construir ruta del archivo
            $archivo = $directorioBase . str_replace('\\', '/', $claseRelativa) . '.php';

            if ($this->debug) {
                echo "    [PSR-4] Buscando en: {$archivo}\n";
            }

            // En un proyecto real: if (file_exists($archivo)) { require $archivo; ... }
            // Para demostracion, registramos la resolucion
            $this->clasesResueltas[$prefijo . $claseRelativa] = $archivo;

            // Simulamos que el archivo existe
            if ($this->debug) {
                echo "    [PSR-4] Resuelto: {$archivo}\n";
            }
            return $archivo;
        }

        return null;
    }

    // Obtener todas las clases resueltas (para demostracion)
    public function getClasesResueltas(): array {
        return $this->clasesResueltas;
    }

    // Obtener los prefijos registrados
    public function getPrefijosRegistrados(): array {
        return $this->prefijos;
    }
}

// Crear y configurar el autoloader
$autoloader = new AutoloaderPSR4(debug: true);

// Registrar namespaces (como se haria en composer.json autoload)
$autoloader->agregarNamespace('App', 'src');
$autoloader->agregarNamespace('App\\Tests', 'tests');
$autoloader->agregarNamespace('Vendor\\Logger', 'vendor/logger/src');

echo "  Prefijos registrados:\n";
foreach ($autoloader->getPrefijosRegistrados() as $prefijo => $dirs) {
    echo "    {$prefijo} -> " . implode(', ', $dirs) . "\n";
}
echo "\n";

// Simular carga de clases
echo "  Simulando carga de clases:\n";
$autoloader->cargarClase('App\\Models\\Usuario');
echo "\n";
$autoloader->cargarClase('App\\Http\\Controllers\\ApiController');
echo "\n";
$autoloader->cargarClase('Vendor\\Logger\\FileLogger');
echo "\n";

echo "  Resumen de clases resueltas:\n";
foreach ($autoloader->getClasesResueltas() as $clase => $archivo) {
    echo "    {$clase} => {$archivo}\n";
}
echo "\n";


// ============================================================================
// Ejemplo 5: Configuracion de Composer autoload (explicado con comentarios)
// ============================================================================
// Composer es la herramienta estandar de PHP para manejar dependencias.
// Su configuracion de autoload en composer.json automatiza todo lo anterior.

echo "=== Ejemplo 5: Configuracion de Composer autoload ===\n\n";

// Asi se ve un composer.json tipico con autoloading:
$composerJsonExplicado = <<<'JSON'
{
    "name": "miempresa/miproyecto",
    "description": "Ejemplo de configuracion de autoload en Composer",
    "require": {
        "php": ">=8.1",
        "monolog/monolog": "^3.0",
        "guzzlehttp/guzzle": "^7.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },

    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "App\\Database\\": "src/DB/"
        },
        "classmap": [
            "legacy/",
            "lib/ClaseVieja.php"
        ],
        "files": [
            "src/helpers.php",
            "src/constantes.php"
        ]
    },

    "autoload-dev": {
        "psr-4": {
            "App\\Tests\\": "tests/"
        }
    }
}
JSON;

echo "  composer.json de ejemplo:\n";
echo "  " . str_replace("\n", "\n  ", $composerJsonExplicado) . "\n\n";

echo "  Explicacion de cada seccion de autoload:\n";
echo "  ─────────────────────────────────────────\n\n";

echo "  psr-4: (RECOMENDADO - estandar moderno)\n";
echo "    Mapea prefijos de namespace a directorios.\n";
echo "    'App\\\\' => 'src/' significa:\n";
echo "      App\\Models\\Usuario  ->  src/Models/Usuario.php\n";
echo "      App\\Http\\Request    ->  src/Http/Request.php\n\n";

echo "  classmap: (para codigo legacy sin namespaces)\n";
echo "    Composer escanea los directorios/archivos listados y crea un\n";
echo "    mapa directo clase->archivo. Util para integrar codigo antiguo.\n";
echo "    Ejemplo: ClaseVieja (sin namespace) -> legacy/ClaseVieja.php\n\n";

echo "  files: (para funciones y constantes globales)\n";
echo "    Archivos que se cargan SIEMPRE, en cada peticion.\n";
echo "    Ideal para archivos con funciones helper y constantes.\n";
echo "    Ejemplo: helpers.php con funciones como dd(), env(), config()\n\n";

echo "  autoload-dev: (solo para desarrollo/testing)\n";
echo "    Solo se carga con 'composer install' (no con --no-dev).\n";
echo "    Perfecto para clases de prueba.\n\n";

// Simular la estructura de directorios que Composer esperaria
echo "  Estructura de directorios esperada:\n";
$estructura = <<<'TEXT'
    proyecto/
    ├── composer.json
    ├── composer.lock
    ├── vendor/                          # Dependencias (generado por Composer)
    │   ├── autoload.php                 # <-- Incluir este archivo
    │   ├── composer/
    │   │   ├── autoload_classmap.php    # Mapa de clases generado
    │   │   ├── autoload_namespaces.php  # Namespaces PSR-0
    │   │   ├── autoload_psr4.php        # Namespaces PSR-4
    │   │   ├── autoload_files.php       # Archivos a cargar siempre
    │   │   └── autoload_real.php        # Autoloader principal
    │   ├── monolog/monolog/             # Dependencia instalada
    │   └── guzzlehttp/guzzle/           # Dependencia instalada
    ├── src/                             # Codigo fuente (mapeado a App\)
    │   ├── Models/
    │   │   ├── Usuario.php              # App\Models\Usuario
    │   │   └── Producto.php             # App\Models\Producto
    │   ├── Services/
    │   │   └── AuthService.php          # App\Services\AuthService
    │   ├── Http/
    │   │   └── Controllers/
    │   │       └── HomeController.php   # App\Http\Controllers\HomeController
    │   ├── helpers.php                  # Funciones globales
    │   └── constantes.php               # Constantes globales
    ├── tests/                           # Tests (mapeado a App\Tests\)
    │   ├── Unit/
    │   │   └── UsuarioTest.php          # App\Tests\Unit\UsuarioTest
    │   └── Feature/
    │       └── AuthTest.php             # App\Tests\Feature\AuthTest
    └── legacy/                          # Codigo viejo (classmap)
        └── ClaseVieja.php
TEXT;

echo $estructura . "\n\n";

echo "  Comandos de Composer:\n";
echo "    composer dump-autoload           # Regenerar archivos de autoload\n";
echo "    composer dump-autoload -o        # Optimizado: genera classmap para todo\n";
echo "    composer install                 # Instalar dependencias + autoload\n";
echo "    composer install --no-dev        # Sin autoload-dev (produccion)\n\n";

echo "  Para usar el autoloader de Composer en tu aplicacion:\n";
echo "    require __DIR__ . '/vendor/autoload.php';\n";
echo "    // Listo! Todas las clases se cargan automaticamente.\n\n";


// ============================================================================
// Ejemplo 6: Mini aplicacion con clases autoloaded (simulada)
// ============================================================================
// Simulamos una aplicacion completa donde las clases se cargan
// automaticamente. Definimos las clases aqui para que sea ejecutable,
// pero en un proyecto real cada una estaria en su propio archivo.

echo "=== Ejemplo 6: Mini aplicacion con autoloading simulado ===\n\n";

// --- En un proyecto real, estas clases estarian en archivos separados ---
// --- Aqui las definimos juntas solo para que el ejemplo sea ejecutable ---

// src/Config/Database.php
class DatabaseConfig {
    public function __construct(
        public readonly string $host = 'localhost',
        public readonly int $port = 3306,
        public readonly string $database = 'mi_app',
        public readonly string $username = 'root',
        public readonly string $password = ''
    ) {}

    public function getDsn(): string {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
    }
}

// src/Models/Tarea.php
class Tarea {
    private static int $siguienteId = 1;
    public readonly int $id;

    public function __construct(
        public string $titulo,
        public string $descripcion = '',
        public string $estado = 'pendiente',
        public string $prioridad = 'media',
        public ?\DateTime $fechaLimite = null
    ) {
        $this->id = self::$siguienteId++;
    }

    public function completar(): void {
        $this->estado = 'completada';
    }

    public function estaVencida(): bool {
        if ($this->fechaLimite === null) return false;
        return $this->fechaLimite < new \DateTime();
    }

    public function __toString(): string {
        $vencida = $this->estaVencida() ? ' [VENCIDA]' : '';
        $fecha = $this->fechaLimite ? $this->fechaLimite->format('d/m/Y') : 'sin fecha';
        return "[{$this->id}] {$this->titulo} ({$this->estado}, {$this->prioridad}) - {$fecha}{$vencida}";
    }
}

// src/Repositories/TareaRepository.php
class TareaRepository {
    private array $tareas = [];

    public function guardar(Tarea $tarea): Tarea {
        $this->tareas[$tarea->id] = $tarea;
        return $tarea;
    }

    public function buscarPorId(int $id): ?Tarea {
        return $this->tareas[$id] ?? null;
    }

    public function buscarTodas(): array {
        return array_values($this->tareas);
    }

    public function buscarPorEstado(string $estado): array {
        return array_values(array_filter(
            $this->tareas,
            fn(Tarea $t) => $t->estado === $estado
        ));
    }

    public function buscarPorPrioridad(string $prioridad): array {
        return array_values(array_filter(
            $this->tareas,
            fn(Tarea $t) => $t->prioridad === $prioridad
        ));
    }

    public function eliminar(int $id): bool {
        if (isset($this->tareas[$id])) {
            unset($this->tareas[$id]);
            return true;
        }
        return false;
    }

    public function contar(): int {
        return count($this->tareas);
    }
}

// src/Services/TareaService.php
class TareaService {
    public function __construct(
        private TareaRepository $repositorio
    ) {}

    public function crearTarea(string $titulo, string $descripcion = '',
                                string $prioridad = 'media', ?string $fechaLimite = null): Tarea {
        $fecha = $fechaLimite ? new \DateTime($fechaLimite) : null;
        $tarea = new Tarea($titulo, $descripcion, 'pendiente', $prioridad, $fecha);
        return $this->repositorio->guardar($tarea);
    }

    public function completarTarea(int $id): ?Tarea {
        $tarea = $this->repositorio->buscarPorId($id);
        if ($tarea === null) {
            return null;
        }
        $tarea->completar();
        return $tarea;
    }

    public function obtenerResumen(): array {
        $todas = $this->repositorio->buscarTodas();
        $pendientes = $this->repositorio->buscarPorEstado('pendiente');
        $completadas = $this->repositorio->buscarPorEstado('completada');
        $vencidas = array_filter($pendientes, fn(Tarea $t) => $t->estaVencida());

        return [
            'total' => count($todas),
            'pendientes' => count($pendientes),
            'completadas' => count($completadas),
            'vencidas' => count($vencidas),
            'porcentaje_completado' => count($todas) > 0
                ? round(count($completadas) / count($todas) * 100, 1)
                : 0,
        ];
    }

    public function listarTodas(): array {
        return $this->repositorio->buscarTodas();
    }
}

// --- Punto de entrada de la aplicacion ---
// En un proyecto real, esto seria algo como:
//   require __DIR__ . '/vendor/autoload.php';
//   // Todas las clases se cargan automaticamente al usarse
// Aqui no necesitamos require porque ya estan definidas arriba.

echo "  En un proyecto real, el punto de entrada seria:\n";
echo "    <?php\n";
echo "    require __DIR__ . '/vendor/autoload.php';\n";
echo "    // Las clases se cargan automaticamente al usarlas\n";
echo "    \$servicio = new App\\Services\\TareaService(...);\n\n";

// Inicializar la aplicacion
$dbConfig = new DatabaseConfig(
    host: 'localhost',
    database: 'tareas_app'
);
echo "  Configuracion BD: {$dbConfig->getDsn()}\n\n";

$repositorio = new TareaRepository();
$servicio = new TareaService($repositorio);

// Crear tareas
echo "  --- Creando tareas ---\n";
$t1 = $servicio->crearTarea(
    'Disenar base de datos',
    'Crear esquema ER para el modulo de inventario',
    'alta',
    '2025-12-15'
);
echo "  Creada: {$t1}\n";

$t2 = $servicio->crearTarea(
    'Implementar API REST',
    'Endpoints para CRUD de productos',
    'alta',
    '2025-12-20'
);
echo "  Creada: {$t2}\n";

$t3 = $servicio->crearTarea(
    'Escribir documentacion',
    'Documentar endpoints de la API',
    'baja',
    '2026-01-15'
);
echo "  Creada: {$t3}\n";

$t4 = $servicio->crearTarea(
    'Configurar CI/CD',
    'Pipeline de Github Actions',
    'media'
);
echo "  Creada: {$t4}\n";

$t5 = $servicio->crearTarea(
    'Reunion de planificacion',
    'Sprint planning Q1 2026',
    'media',
    '2025-01-10' // Fecha pasada - estara vencida
);
echo "  Creada: {$t5}\n";

// Completar algunas tareas
echo "\n  --- Completando tareas ---\n";
$servicio->completarTarea(1);
echo "  Tarea 1 completada\n";
$servicio->completarTarea(2);
echo "  Tarea 2 completada\n";

// Listar todas las tareas
echo "\n  --- Listado de tareas ---\n";
foreach ($servicio->listarTodas() as $tarea) {
    echo "  {$tarea}\n";
}

// Resumen
echo "\n  --- Resumen ---\n";
$resumen = $servicio->obtenerResumen();
echo "  Total: {$resumen['total']}\n";
echo "  Pendientes: {$resumen['pendientes']}\n";
echo "  Completadas: {$resumen['completadas']}\n";
echo "  Vencidas: {$resumen['vencidas']}\n";
echo "  Progreso: {$resumen['porcentaje_completado']}%\n";

echo "\n  NOTA: En un proyecto real con Composer, cada clase estaria en su\n";
echo "  propio archivo siguiendo PSR-4, y el autoloader las cargaria\n";
echo "  automaticamente. No necesitarias ningun require/include manual.\n";
?>
