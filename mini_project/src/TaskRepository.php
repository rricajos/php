<?php

declare(strict_types=1);

namespace MiniProject;

use PDO;
use PDOException;

/**
 * Repositorio de tareas - Patron Repository.
 *
 * Encapsula toda la logica de acceso a datos para la entidad Task.
 * Usa sentencias preparadas (prepared statements) para prevenir
 * inyeccion SQL y manejo de errores adecuado.
 *
 * Patron: Repository
 * Caracteristicas PHP 8: constructor promotion, readonly, named arguments, match
 */
class TaskRepository
{
    /** Conexion PDO obtenida del Singleton Database */
    private readonly PDO $pdo;

    /**
     * Inicializa el repositorio obteniendo la conexion desde el Singleton.
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las tareas ordenadas por fecha de creacion descendente.
     *
     * @return Task[] Lista de todas las tareas
     * @throws AppException Si ocurre un error de base de datos
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT * FROM tasks ORDER BY fecha_creacion DESC');
            $rows = $stmt->fetchAll();

            // Mapear cada fila a una instancia de Task usando el metodo de fabrica
            return array_map(
                callback: fn(array $row): Task => Task::fromRow($row),
                array: $rows,
            );
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al obtener tareas: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Busca una tarea por su identificador unico.
     *
     * @param int $id Identificador de la tarea
     * @return Task La tarea encontrada
     * @throws NotFoundException Si la tarea no existe
     * @throws AppException Si ocurre un error de base de datos
     */
    public function findById(int $id): Task
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            if ($row === false) {
                throw new NotFoundException(
                    recurso: 'tarea',
                    identificador: $id,
                );
            }

            return Task::fromRow($row);
        } catch (NotFoundException $e) {
            // Re-lanzar NotFoundException tal cual
            throw $e;
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al buscar tarea #{$id}: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Filtra tareas por estado (pendiente o completada).
     *
     * @param Status $estado Estado por el cual filtrar
     * @return Task[] Lista de tareas con el estado dado
     * @throws AppException Si ocurre un error de base de datos
     */
    public function findByStatus(Status $estado): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM tasks WHERE estado = :estado ORDER BY fecha_creacion DESC'
            );
            $stmt->execute([':estado' => $estado->value]);
            $rows = $stmt->fetchAll();

            return array_map(
                callback: fn(array $row): Task => Task::fromRow($row),
                array: $rows,
            );
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al filtrar tareas por estado: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Busca tareas cuyo titulo o descripcion contengan la palabra clave.
     *
     * @param string $keyword Palabra clave de busqueda
     * @return Task[] Lista de tareas que coinciden con la busqueda
     * @throws AppException Si ocurre un error de base de datos
     */
    public function search(string $keyword): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM tasks
                 WHERE titulo LIKE :keyword OR descripcion LIKE :keyword
                 ORDER BY fecha_creacion DESC'
            );
            $stmt->execute([':keyword' => "%{$keyword}%"]);
            $rows = $stmt->fetchAll();

            return array_map(
                callback: fn(array $row): Task => Task::fromRow($row),
                array: $rows,
            );
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al buscar tareas: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Guarda una nueva tarea en la base de datos.
     *
     * @param Task $task Tarea a guardar (sin id asignado)
     * @return Task Nueva instancia con el id generado por la BD
     * @throws AppException Si ocurre un error de base de datos
     */
    public function save(Task $task): Task
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO tasks (titulo, descripcion, prioridad, estado)
                 VALUES (:titulo, :descripcion, :prioridad, :estado)'
            );

            $stmt->execute([
                ':titulo' => $task->titulo,
                ':descripcion' => $task->descripcion,
                ':prioridad' => $task->prioridad->value,
                ':estado' => $task->estado->value,
            ]);

            // Obtener el id generado y retornar la tarea completa desde la BD
            $nuevoId = (int) $this->pdo->lastInsertId();

            return $this->findById($nuevoId);
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al guardar tarea: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Marca una tarea como completada y registra la fecha de completado.
     *
     * @param int $id Identificador de la tarea a completar
     * @return Task Tarea actualizada con estado completada
     * @throws NotFoundException Si la tarea no existe
     * @throws AppException Si ocurre un error de base de datos
     */
    public function complete(int $id): Task
    {
        // Verificar que la tarea existe antes de intentar actualizar
        $this->findById($id);

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE tasks
                 SET estado = :estado, fecha_completada = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );

            $stmt->execute([
                ':estado' => Status::Completada->value,
                ':id' => $id,
            ]);

            return $this->findById($id);
        } catch (NotFoundException $e) {
            throw $e;
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al completar tarea #{$id}: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Elimina una tarea de la base de datos de forma permanente.
     *
     * @param int $id Identificador de la tarea a eliminar
     * @return bool true si se elimino correctamente
     * @throws NotFoundException Si la tarea no existe
     * @throws AppException Si ocurre un error de base de datos
     */
    public function delete(int $id): bool
    {
        // Verificar que la tarea existe antes de eliminar
        $this->findById($id);

        try {
            $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al eliminar tarea #{$id}: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Obtiene estadisticas generales de las tareas.
     *
     * Retorna un array asociativo con:
     * - total: numero total de tareas
     * - completadas: tareas completadas
     * - pendientes: tareas pendientes
     * - por_prioridad: desglose por nivel de prioridad
     *
     * @return array<string, int|array<string, int>> Estadisticas calculadas
     * @throws AppException Si ocurre un error de base de datos
     */
    public function getStatistics(): array
    {
        try {
            // Conteo total
            $total = (int) $this->pdo->query('SELECT COUNT(*) FROM tasks')->fetchColumn();

            // Conteo por estado
            $stmtEstado = $this->pdo->query(
                'SELECT estado, COUNT(*) as cantidad FROM tasks GROUP BY estado'
            );
            $porEstado = [];
            foreach ($stmtEstado->fetchAll() as $row) {
                $porEstado[$row['estado']] = (int) $row['cantidad'];
            }

            // Conteo por prioridad
            $stmtPrioridad = $this->pdo->query(
                'SELECT prioridad, COUNT(*) as cantidad FROM tasks GROUP BY prioridad'
            );
            $porPrioridad = [];
            foreach ($stmtPrioridad->fetchAll() as $row) {
                $porPrioridad[$row['prioridad']] = (int) $row['cantidad'];
            }

            return [
                'total' => $total,
                'completadas' => $porEstado['completada'] ?? 0,
                'pendientes' => $porEstado['pendiente'] ?? 0,
                'por_prioridad' => [
                    'alta' => $porPrioridad['alta'] ?? 0,
                    'media' => $porPrioridad['media'] ?? 0,
                    'baja' => $porPrioridad['baja'] ?? 0,
                ],
            ];
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al obtener estadisticas: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }
}
