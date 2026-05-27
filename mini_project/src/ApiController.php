<?php

declare(strict_types=1);

namespace MiniProject;

/**
 * Controlador de la API REST para el gestor de tareas.
 *
 * Recibe datos de peticiones HTTP, delega al TaskService existente
 * y devuelve respuestas JSON estructuradas. Actua como puente entre
 * el Router HTTP y la capa de logica de negocio.
 *
 * Reutiliza completamente: TaskService, ExportService, AuthService,
 * y todas las excepciones personalizadas existentes.
 *
 * Caracteristicas PHP 8: constructor promotion, readonly, named arguments,
 * match, union types
 */
class ApiController
{
    /**
     * Inicializa el controlador con los servicios necesarios.
     *
     * @param TaskService $taskService Servicio de logica de negocio de tareas
     * @param ExportService $exportService Servicio de exportacion (JSON/CSV)
     * @param AuthService $authService Servicio de autenticacion y JWT
     */
    public function __construct(
        private readonly TaskService $taskService,
        private readonly ExportService $exportService,
        private readonly AuthService $authService,
    ) {}

    // ---------------------------------------------------------------
    //  Endpoints de autenticacion (publicos, sin token)
    // ---------------------------------------------------------------

    /**
     * POST /auth/register - Registra un nuevo usuario.
     *
     * Espera un cuerpo JSON con 'username' y 'password'.
     * Retorna los datos del usuario creado con codigo 201.
     *
     * @param array<string, mixed> $body Cuerpo de la peticion (username, password)
     */
    public function registrar(array $body): void
    {
        try {
            $username = $body['username'] ?? '';
            $password = $body['password'] ?? '';

            $usuario = $this->authService->registrar(
                username: (string) $username,
                password: (string) $password,
            );

            JsonResponse::success(
                data: $usuario,
                code: 201,
                message: 'Usuario registrado exitosamente',
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        } catch (AppException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 409,
            );
        }
    }

    /**
     * POST /auth/login - Autentica un usuario y retorna un token JWT.
     *
     * Espera un cuerpo JSON con 'username' y 'password'.
     * Retorna el token JWT, tipo, tiempo de expiracion y datos del usuario.
     *
     * @param array<string, mixed> $body Cuerpo de la peticion (username, password)
     */
    public function login(array $body): void
    {
        try {
            $username = $body['username'] ?? '';
            $password = $body['password'] ?? '';

            $resultado = $this->authService->login(
                username: (string) $username,
                password: (string) $password,
            );

            JsonResponse::success(
                data: $resultado,
                message: 'Login exitoso',
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        } catch (AppException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 401,
            );
        }
    }

    // ---------------------------------------------------------------
    //  Endpoints de tareas (protegidos, requieren token)
    // ---------------------------------------------------------------

    /**
     * GET /tasks - Lista tareas con filtro opcional por estado.
     *
     * Soporta query parameter ?status=pendiente|completada para filtrar.
     * Sin parametro, retorna todas las tareas.
     */
    public function listarTareas(): void
    {
        try {
            // Obtener filtro de estado del query string
            $filtro = $_GET['status'] ?? 'todas';

            $tareas = $this->taskService->listarTareas((string) $filtro);

            // Convertir las tareas a arrays para la respuesta JSON
            $data = array_map(
                callback: fn(Task $t): array => $t->toArray(),
                array: $tareas,
            );

            JsonResponse::success(
                data: [
                    'total' => count($data),
                    'tareas' => $data,
                ],
                message: 'Tareas obtenidas exitosamente',
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        }
    }

    /**
     * GET /tasks/{id} - Obtiene una tarea por su ID.
     *
     * @param array<string, string> $params Parametros de la ruta (contiene 'id')
     */
    public function obtenerTarea(array $params): void
    {
        try {
            $id = (int) ($params['id'] ?? 0);

            // Reutilizar el repositorio a traves del servicio
            // TaskService no tiene findById directo, usamos el repository
            $repository = new TaskRepository();
            $tarea = $repository->findById($id);

            JsonResponse::success(
                data: $tarea->toArray(),
                message: 'Tarea obtenida exitosamente',
            );
        } catch (NotFoundException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 404,
            );
        } catch (AppException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }

    /**
     * POST /tasks - Crea una nueva tarea.
     *
     * Espera un cuerpo JSON con:
     * - title (string, obligatorio): Titulo de la tarea
     * - description (string, opcional): Descripcion detallada
     * - priority (string, opcional): 'alta', 'media' o 'baja' (defecto: 'media')
     *
     * @param array<string, mixed> $body Cuerpo de la peticion
     */
    public function crearTarea(array $body): void
    {
        try {
            $titulo = $body['title'] ?? '';
            $descripcion = $body['description'] ?? '';
            $prioridad = $body['priority'] ?? 'media';

            $tarea = $this->taskService->crearTarea(
                titulo: (string) $titulo,
                descripcion: (string) $descripcion,
                prioridad: (string) $prioridad,
            );

            JsonResponse::success(
                data: $tarea->toArray(),
                code: 201,
                message: 'Tarea creada exitosamente',
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        }
    }

    /**
     * PATCH /tasks/{id}/complete - Marca una tarea como completada.
     *
     * @param array<string, string> $params Parametros de la ruta (contiene 'id')
     */
    public function completarTarea(array $params): void
    {
        try {
            $id = $params['id'] ?? '0';

            $tarea = $this->taskService->completarTarea($id);

            JsonResponse::success(
                data: $tarea->toArray(),
                message: 'Tarea marcada como completada',
            );
        } catch (NotFoundException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 404,
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        }
    }

    /**
     * DELETE /tasks/{id} - Elimina una tarea permanentemente.
     *
     * @param array<string, string> $params Parametros de la ruta (contiene 'id')
     */
    public function eliminarTarea(array $params): void
    {
        try {
            $id = $params['id'] ?? '0';

            $this->taskService->eliminarTarea($id);

            JsonResponse::success(
                message: "Tarea #{$id} eliminada permanentemente",
            );
        } catch (NotFoundException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 404,
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        }
    }

    /**
     * GET /tasks/search?q=keyword - Busca tareas por palabra clave.
     *
     * El parametro de busqueda se pasa como query string ?q=keyword.
     * Busca en titulo y descripcion de las tareas.
     */
    public function buscarTareas(): void
    {
        try {
            $keyword = $_GET['q'] ?? '';

            $tareas = $this->taskService->buscarTareas((string) $keyword);

            $data = array_map(
                callback: fn(Task $t): array => $t->toArray(),
                array: $tareas,
            );

            JsonResponse::success(
                data: [
                    'keyword' => $keyword,
                    'total' => count($data),
                    'tareas' => $data,
                ],
                message: 'Busqueda completada',
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        }
    }

    /**
     * GET /tasks/stats - Obtiene estadisticas de las tareas.
     *
     * Retorna totales, desglose por estado y por prioridad,
     * ademas del porcentaje de completado.
     */
    public function estadisticas(): void
    {
        try {
            $stats = $this->taskService->obtenerEstadisticas();

            // Calcular porcentaje de completado
            $porcentaje = $stats['total'] > 0
                ? round(($stats['completadas'] / $stats['total']) * 100, 1)
                : 0.0;

            $stats['porcentaje_completado'] = $porcentaje;

            JsonResponse::success(
                data: $stats,
                message: 'Estadisticas obtenidas exitosamente',
            );
        } catch (AppException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }

    /**
     * GET /tasks/export?format=json|csv - Exporta tareas a archivo.
     *
     * El formato se especifica con el query parameter ?format=json|csv.
     * Por defecto exporta en JSON. Retorna la ruta del archivo generado.
     */
    public function exportarTareas(): void
    {
        try {
            $formato = $_GET['format'] ?? 'json';

            $tareas = $this->taskService->obtenerTodasParaExportar();

            if (empty($tareas)) {
                JsonResponse::success(
                    data: null,
                    message: 'No hay tareas para exportar',
                );
            }

            $ruta = $this->exportService->exportar(
                tasks: $tareas,
                formato: (string) $formato,
            );

            JsonResponse::success(
                data: [
                    'formato' => $formato,
                    'archivo' => $ruta,
                    'total_exportadas' => count($tareas),
                ],
                message: 'Tareas exportadas exitosamente',
            );
        } catch (ValidationException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 422,
                errors: ['campo' => $e->campo],
            );
        } catch (AppException $e) {
            JsonResponse::error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }
}
