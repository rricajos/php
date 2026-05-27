<?php

declare(strict_types=1);

namespace MiniProject;

use PDO;
use PDOException;

/**
 * Conexion a base de datos usando el patron Singleton.
 *
 * Garantiza una unica instancia de conexion PDO a SQLite durante
 * todo el ciclo de vida de la aplicacion. Crea la tabla de tareas
 * automaticamente si no existe.
 *
 * Patron: Singleton
 * Caracteristicas PHP 8: constructor promotion, readonly, match
 */
class Database
{
    /** Instancia unica (Singleton) */
    private static ?self $instance = null;

    /** Conexion PDO a SQLite */
    private readonly PDO $pdo;

    /**
     * Constructor privado para prevenir instanciacion directa.
     *
     * @param string $dbPath Ruta al archivo de base de datos SQLite
     */
    private function __construct(
        private readonly string $dbPath,
    ) {
        try {
            // Asegurar que el directorio de datos exista
            $dir = dirname($this->dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Crear conexion PDO con SQLite
            $this->pdo = new PDO(
                dsn: "sqlite:{$this->dbPath}",
            );

            // Configurar PDO para lanzar excepciones en errores
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Habilitar claves foraneas en SQLite
            $this->pdo->exec('PRAGMA foreign_keys = ON');

            // Inicializar la estructura de la base de datos
            $this->inicializarTablas();
        } catch (PDOException $e) {
            throw new AppException(
                message: "Error al conectar con la base de datos: {$e->getMessage()}",
                code: AppException::ERROR_DATABASE,
                previous: $e,
            );
        }
    }

    /**
     * Obtiene la instancia unica de la base de datos (Singleton).
     *
     * @param string|null $dbPath Ruta al archivo SQLite (solo se usa en la primera llamada)
     * @return self Instancia unica de Database
     */
    public static function getInstance(?string $dbPath = null): self
    {
        if (self::$instance === null) {
            // Ruta por defecto: data/tasks.db relativo al directorio del proyecto
            $path = $dbPath ?? dirname(__DIR__) . '/data/tasks.db';
            self::$instance = new self($path);
        }

        return self::$instance;
    }

    /**
     * Obtiene la conexion PDO subyacente.
     *
     * @return PDO Instancia de conexion PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Crea las tablas necesarias si no existen.
     * Estructura de la tabla tasks:
     * - id: clave primaria autoincremental
     * - titulo: titulo de la tarea (obligatorio)
     * - descripcion: descripcion detallada (opcional)
     * - prioridad: alta, media o baja
     * - estado: pendiente o completada
     * - fecha_creacion: timestamp de creacion
     * - fecha_completada: timestamp de cuando se completo (nullable)
     */
    private function inicializarTablas(): void
    {
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo TEXT NOT NULL,
                descripcion TEXT DEFAULT '',
                prioridad TEXT NOT NULL DEFAULT 'media' CHECK(prioridad IN ('alta', 'media', 'baja')),
                estado TEXT NOT NULL DEFAULT 'pendiente' CHECK(estado IN ('pendiente', 'completada')),
                fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                fecha_completada DATETIME DEFAULT NULL
            )
        SQL;

        $this->pdo->exec($sql);
    }

    /**
     * Resetea la instancia Singleton (util para testing).
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Prevenir clonacion del Singleton.
     */
    private function __clone(): void {}

    /**
     * Prevenir deserializacion del Singleton.
     */
    public function __wakeup(): void
    {
        throw new AppException('No se puede deserializar un Singleton');
    }
}
