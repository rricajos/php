<?php

declare(strict_types=1);

namespace MiniProject;

/**
 * Servicio de logica de negocio para tareas.
 *
 * Capa intermedia entre la interfaz de usuario y el repositorio.
 * Se encarga de validar datos de entrada, delegar operaciones
 * al repositorio y formatear la salida.
 *
 * Patron: Inyeccion de Dependencias (recibe el repositorio en el constructor)
 * Caracteristicas PHP 8: constructor promotion, readonly, named arguments,
 * union types, match
 */
class TaskService
{
    /**
     * Inicializa el servicio con inyeccion de dependencias.
     *
     * @param TaskRepository $repository Repositorio de acceso a datos
     */
    public function __construct(
        private readonly TaskRepository $repository,
    ) {}

    /**
     * Crea y guarda una nueva tarea validando los datos de entrada.
     *
     * @param string $titulo Titulo de la tarea (no puede estar vacio)
     * @param string $descripcion Descripcion opcional de la tarea
     * @param string $prioridad Prioridad como string: 'alta', 'media' o 'baja'
     * @return Task La tarea creada con su id asignado
     * @throws ValidationException Si los datos no son validos
     */
    public function crearTarea(
        string $titulo,
        string $descripcion,
        string $prioridad,
    ): Task {
        // Validar titulo (obligatorio, no vacio)
        $titulo = trim($titulo);
        if ($titulo === '') {
            throw new ValidationException(
                message: 'El titulo de la tarea no puede estar vacio',
                code: ValidationException::ERROR_CAMPO_VACIO,
                campo: 'titulo',
            );
        }

        // Validar longitud maxima del titulo
        if (mb_strlen($titulo) > 100) {
            throw new ValidationException(
                message: 'El titulo no puede superar los 100 caracteres',
                code: ValidationException::ERROR_CAMPO_VACIO,
                campo: 'titulo',
            );
        }

        // Validar y convertir prioridad de string a enum
        $prioridadEnum = $this->validarPrioridad($prioridad);

        // Sanitizar descripcion
        $descripcion = trim($descripcion);

        // Crear la entidad Task con named arguments
        $task = new Task(
            id: null,
            titulo: $titulo,
            descripcion: $descripcion,
            prioridad: $prioridadEnum,
            estado: Status::Pendiente,
        );

        // Delegar al repositorio para persistir
        return $this->repository->save($task);
    }

    /**
     * Lista tareas con filtro opcional por estado.
     *
     * @param string $filtro Filtro de estado: 'pendiente', 'completada' o 'todas'
     * @return Task[] Lista de tareas filtradas
     * @throws ValidationException Si el filtro no es valido
     */
    public function listarTareas(string $filtro = 'todas'): array
    {
        $filtro = strtolower(trim($filtro));

        return match ($filtro) {
            'todas', 'all' => $this->repository->findAll(),
            'pendiente', 'pendientes', 'pending' => $this->repository->findByStatus(Status::Pendiente),
            'completada', 'completadas', 'completed' => $this->repository->findByStatus(Status::Completada),
            default => throw new ValidationException(
                message: "Filtro de estado invalido: '{$filtro}'. Usa: todas, pendiente o completada",
                code: ValidationException::ERROR_ESTADO_INVALIDO,
                campo: 'estado',
            ),
        };
    }

    /**
     * Marca una tarea como completada por su ID.
     *
     * @param int|string $id Identificador de la tarea (se valida como entero positivo)
     * @return Task Tarea actualizada con estado completada
     * @throws ValidationException Si el ID no es valido
     * @throws NotFoundException Si la tarea no existe
     */
    public function completarTarea(int|string $id): Task
    {
        $idValidado = $this->validarId($id);

        // Verificar que la tarea no este ya completada
        $tarea = $this->repository->findById($idValidado);
        if ($tarea->estado === Status::Completada) {
            throw new ValidationException(
                message: "La tarea #{$idValidado} ya esta completada",
                code: ValidationException::ERROR_ESTADO_INVALIDO,
                campo: 'estado',
            );
        }

        return $this->repository->complete($idValidado);
    }

    /**
     * Elimina una tarea por su ID.
     *
     * @param int|string $id Identificador de la tarea
     * @return bool true si se elimino correctamente
     * @throws ValidationException Si el ID no es valido
     * @throws NotFoundException Si la tarea no existe
     */
    public function eliminarTarea(int|string $id): bool
    {
        $idValidado = $this->validarId($id);

        return $this->repository->delete($idValidado);
    }

    /**
     * Busca tareas por palabra clave en titulo y descripcion.
     *
     * @param string $keyword Palabra clave de busqueda
     * @return Task[] Lista de tareas que coinciden
     * @throws ValidationException Si la palabra clave esta vacia
     */
    public function buscarTareas(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            throw new ValidationException(
                message: 'La palabra clave de busqueda no puede estar vacia',
                code: ValidationException::ERROR_CAMPO_VACIO,
                campo: 'keyword',
            );
        }

        return $this->repository->search($keyword);
    }

    /**
     * Obtiene estadisticas formateadas para mostrar en la terminal.
     *
     * @return array<string, int|array<string, int>> Datos estadisticos
     */
    public function obtenerEstadisticas(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Obtiene todas las tareas (sin filtro) para exportacion.
     *
     * @return Task[] Todas las tareas en la base de datos
     */
    public function obtenerTodasParaExportar(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Formatea las estadisticas como texto con colores ANSI para la terminal.
     *
     * @param array<string, mixed> $stats Datos estadisticos del repositorio
     * @return string Texto formateado con colores
     */
    public function formatearEstadisticas(array $stats): string
    {
        $porcentaje = $stats['total'] > 0
            ? round(($stats['completadas'] / $stats['total']) * 100, 1)
            : 0;

        // Construir barra de progreso visual
        $barraAncho = 30;
        $llenos = $stats['total'] > 0
            ? (int) round(($stats['completadas'] / $stats['total']) * $barraAncho)
            : 0;
        $vacios = $barraAncho - $llenos;
        $barra = "\033[42m" . str_repeat(' ', $llenos) . "\033[0m"
               . "\033[47m" . str_repeat(' ', $vacios) . "\033[0m";

        $lineas = [];
        $lineas[] = '';
        $lineas[] = "\033[1;35m========== ESTADISTICAS ==========\033[0m";
        $lineas[] = '';
        $lineas[] = "  Total de tareas:    \033[1m{$stats['total']}\033[0m";
        $lineas[] = "  Completadas:        \033[32m{$stats['completadas']}\033[0m";
        $lineas[] = "  Pendientes:         \033[33m{$stats['pendientes']}\033[0m";
        $lineas[] = '';
        $lineas[] = "  Progreso: [{$barra}] {$porcentaje}%";
        $lineas[] = '';
        $lineas[] = "\033[1;35m--- Por prioridad ---\033[0m";
        $lineas[] = "  \033[31mAlta:   {$stats['por_prioridad']['alta']}\033[0m";
        $lineas[] = "  \033[33mMedia:  {$stats['por_prioridad']['media']}\033[0m";
        $lineas[] = "  \033[32mBaja:   {$stats['por_prioridad']['baja']}\033[0m";
        $lineas[] = '';
        $lineas[] = "\033[1;35m==================================\033[0m";

        return implode(PHP_EOL, $lineas);
    }

    // ---------------------------------------------------------------
    //  Metodos privados de validacion
    // ---------------------------------------------------------------

    /**
     * Valida y convierte un string de prioridad al enum Priority.
     *
     * @param string $prioridad Valor de prioridad como texto
     * @return Priority Enum de prioridad validado
     * @throws ValidationException Si el valor no es una prioridad valida
     */
    private function validarPrioridad(string $prioridad): Priority
    {
        $prioridad = strtolower(trim($prioridad));

        $enum = Priority::tryFrom($prioridad);

        if ($enum === null) {
            $valoresValidos = implode(', ', array_map(
                fn(Priority $p): string => $p->value,
                Priority::cases(),
            ));

            throw new ValidationException(
                message: "Prioridad invalida: '{$prioridad}'. Valores validos: {$valoresValidos}",
                code: ValidationException::ERROR_PRIORIDAD_INVALIDA,
                campo: 'prioridad',
            );
        }

        return $enum;
    }

    /**
     * Valida que un ID sea un entero positivo.
     *
     * @param int|string $id Valor a validar como ID
     * @return int ID validado como entero positivo
     * @throws ValidationException Si el ID no es un entero positivo
     */
    private function validarId(int|string $id): int
    {
        // Si es string, intentar convertir a entero
        if (is_string($id)) {
            $id = trim($id);
            if (!ctype_digit($id)) {
                throw new ValidationException(
                    message: "El ID debe ser un numero entero positivo, se recibio: '{$id}'",
                    code: ValidationException::ERROR_ID_INVALIDO,
                    campo: 'id',
                );
            }
            $id = (int) $id;
        }

        if ($id <= 0) {
            throw new ValidationException(
                message: "El ID debe ser mayor que cero, se recibio: {$id}",
                code: ValidationException::ERROR_ID_INVALIDO,
                campo: 'id',
            );
        }

        return $id;
    }
}
