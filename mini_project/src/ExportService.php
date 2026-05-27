<?php

declare(strict_types=1);

namespace MiniProject;

/**
 * Interfaz para exportadores de tareas - Patron Strategy.
 *
 * Define el contrato que deben cumplir todos los exportadores.
 * Cada implementacion representa una estrategia de exportacion diferente.
 *
 * Patron: Strategy
 */
interface ExporterInterface
{
    /**
     * Exporta una lista de tareas al formato especifico.
     *
     * @param Task[] $tasks Lista de tareas a exportar
     * @return string Ruta del archivo generado
     */
    public function export(array $tasks): string;

    /**
     * Devuelve el nombre descriptivo del formato de exportacion.
     *
     * @return string Nombre del formato (ej: 'JSON', 'CSV')
     */
    public function getFormatName(): string;
}

/**
 * Exportador de tareas a formato JSON.
 *
 * Implementa la estrategia de exportacion JSON con formato legible
 * (pretty print) y soporte Unicode para caracteres en espanol.
 *
 * Patron: Strategy (implementacion concreta)
 * Caracteristicas PHP 8: constructor promotion, readonly
 */
class JsonExporter implements ExporterInterface
{
    /**
     * @param string $outputDir Directorio donde se guardaran los archivos exportados
     */
    public function __construct(
        private readonly string $outputDir,
    ) {}

    /**
     * Exporta las tareas a un archivo JSON con timestamp en el nombre.
     *
     * El archivo se genera con formato legible (JSON_PRETTY_PRINT) y
     * sin escapar caracteres Unicode (JSON_UNESCAPED_UNICODE) para
     * preservar tildes y caracteres especiales del espanol.
     *
     * @param Task[] $tasks Lista de tareas a exportar
     * @return string Ruta absoluta del archivo JSON generado
     * @throws AppException Si no se puede escribir el archivo
     */
    public function export(array $tasks): string
    {
        // Asegurar que el directorio de salida exista
        $this->asegurarDirectorio();

        // Convertir tareas a arrays para serializacion JSON
        $data = [
            'exportado_en' => date('Y-m-d H:i:s'),
            'total_tareas' => count($tasks),
            'tareas' => array_map(
                callback: fn(Task $t): array => $t->toArray(),
                array: $tasks,
            ),
        ];

        // Generar nombre de archivo con timestamp
        $filename = sprintf('tareas_%s.json', date('Y-m-d_His'));
        $filepath = $this->outputDir . '/' . $filename;

        // Codificar a JSON con formato legible y soporte Unicode
        $json = json_encode(
            value: $data,
            flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        $result = file_put_contents($filepath, $json);

        if ($result === false) {
            throw new AppException(
                message: "No se pudo escribir el archivo: {$filepath}",
                code: AppException::ERROR_FILESYSTEM,
            );
        }

        return $filepath;
    }

    /**
     * Devuelve el nombre del formato de exportacion.
     *
     * @return string 'JSON'
     */
    public function getFormatName(): string
    {
        return 'JSON';
    }

    /**
     * Asegura que el directorio de salida exista, creandolo si es necesario.
     *
     * @throws AppException Si no se puede crear el directorio
     */
    private function asegurarDirectorio(): void
    {
        if (!is_dir($this->outputDir)) {
            if (!mkdir($this->outputDir, 0755, true)) {
                throw new AppException(
                    message: "No se pudo crear el directorio: {$this->outputDir}",
                    code: AppException::ERROR_FILESYSTEM,
                );
            }
        }
    }
}

/**
 * Exportador de tareas a formato CSV.
 *
 * Implementa la estrategia de exportacion CSV compatible con
 * hojas de calculo (Excel, LibreOffice Calc, etc).
 *
 * Patron: Strategy (implementacion concreta)
 * Caracteristicas PHP 8: constructor promotion, readonly
 */
class CsvExporter implements ExporterInterface
{
    /**
     * @param string $outputDir Directorio donde se guardaran los archivos exportados
     */
    public function __construct(
        private readonly string $outputDir,
    ) {}

    /**
     * Exporta las tareas a un archivo CSV con encabezados.
     *
     * Incluye BOM UTF-8 para compatibilidad con Excel y usa
     * las funciones nativas de PHP para generar CSV valido.
     *
     * @param Task[] $tasks Lista de tareas a exportar
     * @return string Ruta absoluta del archivo CSV generado
     * @throws AppException Si no se puede escribir el archivo
     */
    public function export(array $tasks): string
    {
        // Asegurar que el directorio de salida exista
        $this->asegurarDirectorio();

        // Generar nombre de archivo con timestamp
        $filename = sprintf('tareas_%s.csv', date('Y-m-d_His'));
        $filepath = $this->outputDir . '/' . $filename;

        // Abrir archivo para escritura
        $handle = fopen($filepath, 'w');

        if ($handle === false) {
            throw new AppException(
                message: "No se pudo crear el archivo: {$filepath}",
                code: AppException::ERROR_FILESYSTEM,
            );
        }

        // Escribir BOM UTF-8 para compatibilidad con Excel
        fwrite($handle, "\xEF\xBB\xBF");

        // Escribir encabezados
        fputcsv($handle, [
            'ID',
            'Titulo',
            'Descripcion',
            'Prioridad',
            'Estado',
            'Fecha Creacion',
            'Fecha Completada',
        ]);

        // Escribir cada tarea como una fila CSV
        foreach ($tasks as $task) {
            fputcsv($handle, [
                $task->id,
                $task->titulo,
                $task->descripcion,
                $task->prioridad->value,
                $task->estado->value,
                $task->fechaCreacion,
                $task->fechaCompletada ?? '',
            ]);
        }

        fclose($handle);

        return $filepath;
    }

    /**
     * Devuelve el nombre del formato de exportacion.
     *
     * @return string 'CSV'
     */
    public function getFormatName(): string
    {
        return 'CSV';
    }

    /**
     * Asegura que el directorio de salida exista, creandolo si es necesario.
     *
     * @throws AppException Si no se puede crear el directorio
     */
    private function asegurarDirectorio(): void
    {
        if (!is_dir($this->outputDir)) {
            if (!mkdir($this->outputDir, 0755, true)) {
                throw new AppException(
                    message: "No se pudo crear el directorio: {$this->outputDir}",
                    code: AppException::ERROR_FILESYSTEM,
                );
            }
        }
    }
}

/**
 * Servicio de exportacion que gestiona las estrategias disponibles.
 *
 * Actua como contexto del patron Strategy, permitiendo seleccionar
 * dinamicamente el exportador a utilizar.
 *
 * Patron: Strategy (contexto)
 * Caracteristicas PHP 8: constructor promotion, readonly, match, named arguments
 */
class ExportService
{
    /** @var array<string, ExporterInterface> Exportadores registrados por nombre */
    private array $exporters = [];

    /**
     * Inicializa el servicio con el directorio de salida por defecto
     * y registra los exportadores disponibles.
     *
     * @param string $outputDir Directorio base para archivos exportados
     */
    public function __construct(
        private readonly string $outputDir,
    ) {
        // Registrar exportadores disponibles
        $this->registrarExportador('json', new JsonExporter(outputDir: $this->outputDir));
        $this->registrarExportador('csv', new CsvExporter(outputDir: $this->outputDir));
    }

    /**
     * Registra un nuevo exportador con un nombre clave.
     *
     * @param string $nombre Nombre identificador del exportador
     * @param ExporterInterface $exporter Instancia del exportador
     */
    public function registrarExportador(string $nombre, ExporterInterface $exporter): void
    {
        $this->exporters[strtolower($nombre)] = $exporter;
    }

    /**
     * Exporta tareas usando el exportador especificado.
     *
     * @param Task[] $tasks Lista de tareas a exportar
     * @param string $formato Formato deseado: 'json' o 'csv'
     * @return string Ruta del archivo generado
     * @throws ValidationException Si el formato no esta registrado
     */
    public function exportar(array $tasks, string $formato = 'json'): string
    {
        $formato = strtolower(trim($formato));

        if (!isset($this->exporters[$formato])) {
            $disponibles = implode(', ', array_keys($this->exporters));
            throw new ValidationException(
                message: "Formato de exportacion '{$formato}' no disponible. Formatos disponibles: {$disponibles}",
                code: ValidationException::ERROR_CAMPO_VACIO,
                campo: 'formato',
            );
        }

        $exporter = $this->exporters[$formato];

        return $exporter->export($tasks);
    }

    /**
     * Devuelve los nombres de los formatos de exportacion disponibles.
     *
     * @return string[] Lista de formatos registrados
     */
    public function formatosDisponibles(): array
    {
        return array_keys($this->exporters);
    }
}
