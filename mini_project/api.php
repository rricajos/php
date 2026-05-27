<?php

declare(strict_types=1);

/**
 * API REST del Gestor de Tareas - Punto de entrada.
 *
 * Servidor HTTP para la API REST que expone los endpoints de
 * autenticacion y gestion de tareas. Diseñado para usarse con
 * el servidor integrado de PHP:
 *
 *   php -S localhost:8080 mini_project/api.php
 *
 * Caracteristicas:
 * - Autenticacion JWT con registro y login
 * - CRUD completo de tareas
 * - Busqueda, estadisticas y exportacion
 * - CORS habilitado para desarrollo
 * - Respuestas JSON consistentes con codigos HTTP apropiados
 *
 * Caracteristicas PHP 8: named arguments, match
 */

// ---------------------------------------------------------------
//  Autoloader (identico al de app.php para independencia)
// ---------------------------------------------------------------

/**
 * Registra el autoloader basado en namespaces.
 * Mapea MiniProject\ al directorio src/ del proyecto.
 */
spl_autoload_register(function (string $className): void {
    $prefix = 'MiniProject\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }

    $relativeClass = substr($className, $len);

    // Mapa de clases que comparten archivo
    $classMap = [
        // Enums definidos en Task.php
        'Priority'             => 'Task.php',
        'Status'               => 'Task.php',
        // Excepciones definidas en AppException.php
        'ValidationException'  => 'AppException.php',
        'NotFoundException'    => 'AppException.php',
        // Interfaz y exportadores definidos en ExportService.php
        'ExporterInterface'    => 'ExportService.php',
        'JsonExporter'         => 'ExportService.php',
        'CsvExporter'          => 'ExportService.php',
        // Clases del Router definidas en Router.php
        'Route'                => 'Router.php',
        'RouteMatch'           => 'Router.php',
    ];

    if (isset($classMap[$relativeClass])) {
        $file = $baseDir . $classMap[$relativeClass];
    } else {
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    }

    if (file_exists($file)) {
        require_once $file;
    }
});

// Importar las clases necesarias
use MiniProject\Database;
use MiniProject\TaskRepository;
use MiniProject\TaskService;
use MiniProject\ExportService;
use MiniProject\AuthService;
use MiniProject\ApiController;
use MiniProject\Router;
use MiniProject\JsonResponse;
use MiniProject\AppException;

// ---------------------------------------------------------------
//  Cabeceras CORS para desarrollo
// ---------------------------------------------------------------

/**
 * Establecer cabeceras CORS para permitir peticiones desde
 * cualquier origen durante desarrollo. En produccion, restringir
 * el origen a dominios especificos.
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 3600');

// Responder inmediatamente a preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------------------------------------------------------------
//  Inicializacion de servicios
// ---------------------------------------------------------------

try {
    // Inicializar la base de datos (Singleton — reutiliza la existente)
    Database::getInstance();

    // Crear servicios con inyeccion de dependencias (reutilizando las clases existentes)
    $repository = new TaskRepository();
    $taskService = new TaskService(repository: $repository);
    $exportService = new ExportService(
        outputDir: __DIR__ . '/data',
    );
    $authService = new AuthService();

    // Crear el controlador con todos los servicios inyectados
    $controller = new ApiController(
        taskService: $taskService,
        exportService: $exportService,
        authService: $authService,
    );
} catch (AppException $e) {
    JsonResponse::error(
        message: "Error al inicializar la API: {$e->getMessage()}",
        code: 500,
    );
}

// ---------------------------------------------------------------
//  Funciones auxiliares
// ---------------------------------------------------------------

/**
 * Obtiene el cuerpo de la peticion como array asociativo.
 *
 * Lee el body de la peticion HTTP, lo decodifica como JSON
 * y retorna un array. Si el body esta vacio o no es JSON valido,
 * retorna un array vacio.
 *
 * @return array<string, mixed> Datos del cuerpo de la peticion
 */
function obtenerBodyJson(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || $raw === '') {
        return [];
    }

    $data = json_decode($raw, associative: true);

    if (!is_array($data)) {
        JsonResponse::error(
            message: 'El cuerpo de la peticion debe ser JSON valido',
            code: 400,
        );
    }

    return $data;
}

/**
 * Middleware de autenticacion: verifica el token JWT.
 *
 * Extrae y valida el token Bearer del header Authorization.
 * Si el token es invalido o no esta presente, responde con 401.
 *
 * @param AuthService $authService Servicio de autenticacion
 * @return array{user_id: int, username: string} Datos del usuario autenticado
 */
function requireAuth(AuthService $authService): array
{
    try {
        return $authService->autenticar();
    } catch (AppException $e) {
        JsonResponse::error(
            message: $e->getMessage(),
            code: 401,
        );
    }
}

// ---------------------------------------------------------------
//  Configuracion de rutas
// ---------------------------------------------------------------

$router = new Router();

// --- Rutas publicas de autenticacion ---

$router->post('/auth/register', function () use ($controller): void {
    $body = obtenerBodyJson();
    $controller->registrar($body);
});

$router->post('/auth/login', function () use ($controller): void {
    $body = obtenerBodyJson();
    $controller->login($body);
});

// --- Rutas protegidas de tareas ---
// Nota: las rutas mas especificas (search, stats, export) van ANTES
// de las parametrizadas ({id}) para evitar que {id} capture "search", etc.

$router->get('/tasks/search', function () use ($controller, $authService): void {
    requireAuth($authService);
    $controller->buscarTareas();
});

$router->get('/tasks/stats', function () use ($controller, $authService): void {
    requireAuth($authService);
    $controller->estadisticas();
});

$router->get('/tasks/export', function () use ($controller, $authService): void {
    requireAuth($authService);
    $controller->exportarTareas();
});

$router->get('/tasks', function () use ($controller, $authService): void {
    requireAuth($authService);
    $controller->listarTareas();
});

$router->get('/tasks/{id}', function (array $params) use ($controller, $authService): void {
    requireAuth($authService);
    $controller->obtenerTarea($params);
});

$router->post('/tasks', function () use ($controller, $authService): void {
    requireAuth($authService);
    $body = obtenerBodyJson();
    $controller->crearTarea($body);
});

$router->patch('/tasks/{id}/complete', function (array $params) use ($controller, $authService): void {
    requireAuth($authService);
    $controller->completarTarea($params);
});

$router->delete('/tasks/{id}', function (array $params) use ($controller, $authService): void {
    requireAuth($authService);
    $controller->eliminarTarea($params);
});

// ---------------------------------------------------------------
//  Despachar la peticion
// ---------------------------------------------------------------

try {
    // Obtener metodo y URI de la peticion actual
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Resolver la ruta y ejecutar el handler
    $match = $router->resolve(method: $method, uri: $uri);

    // Ejecutar el handler con los parametros extraidos de la URI
    ($match->handler)($match->params);
} catch (AppException $e) {
    JsonResponse::error(
        message: $e->getMessage(),
        code: 500,
    );
} catch (\Throwable $e) {
    JsonResponse::error(
        message: 'Error interno del servidor: ' . $e->getMessage(),
        code: 500,
    );
}
