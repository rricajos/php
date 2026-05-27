<?php

declare(strict_types=1);

namespace Tests\Unit;

use MiniProject\CsvExporter;
use MiniProject\ExporterInterface;
use MiniProject\ExportService;
use MiniProject\JsonExporter;
use MiniProject\Priority;
use MiniProject\Status;
use MiniProject\Task;
use MiniProject\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para el servicio de exportacion y las estrategias
 * de exportacion (JsonExporter, CsvExporter, ExportService).
 *
 * @covers \MiniProject\JsonExporter
 * @covers \MiniProject\CsvExporter
 * @covers \MiniProject\ExportService
 */
class ExportServiceTest extends TestCase
{
    private string $tempDir;

    /** @var Task[] */
    private array $tareasEjemplo;

    protected function setUp(): void
    {
        // Crear un directorio temporal unico para cada test
        $this->tempDir = sys_get_temp_dir() . '/mini_project_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);

        // Preparar tareas de ejemplo para los tests
        $this->tareasEjemplo = [
            new Task(
                id: 1,
                titulo: 'Comprar leche',
                descripcion: 'En el supermercado',
                prioridad: Priority::Alta,
                estado: Status::Pendiente,
                fechaCreacion: '2026-05-01 10:00:00',
                fechaCompletada: null,
            ),
            new Task(
                id: 2,
                titulo: 'Estudiar PHP',
                descripcion: 'Repasar patrones',
                prioridad: Priority::Media,
                estado: Status::Completada,
                fechaCreacion: '2026-05-01 08:00:00',
                fechaCompletada: '2026-05-01 12:00:00',
            ),
            new Task(
                id: 3,
                titulo: 'Hacer ejercicio',
                descripcion: '',
                prioridad: Priority::Baja,
                estado: Status::Pendiente,
                fechaCreacion: '2026-05-02 07:00:00',
                fechaCompletada: null,
            ),
        ];
    }

    protected function tearDown(): void
    {
        // Limpiar archivos generados en el directorio temporal
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempDir);
        }
    }

    // ---------------------------------------------------------------
    //  Tests de JsonExporter
    // ---------------------------------------------------------------

    public function testJsonExporterProduceJsonValido(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);

        // Verificar que el archivo se creo
        $this->assertFileExists($filepath);

        // Leer y decodificar el JSON
        $contenido = file_get_contents($filepath);
        $this->assertNotFalse($contenido);

        $data = json_decode($contenido, true);
        $this->assertNotNull($data, 'El JSON generado debe ser valido');
        $this->assertIsArray($data);
    }

    public function testJsonExporterContieneEstructuraCorrecta(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);
        $data = json_decode(file_get_contents($filepath), true);

        // Verificar claves de nivel superior
        $this->assertArrayHasKey('exportado_en', $data);
        $this->assertArrayHasKey('total_tareas', $data);
        $this->assertArrayHasKey('tareas', $data);

        // Verificar conteo de tareas
        $this->assertSame(3, $data['total_tareas']);
        $this->assertCount(3, $data['tareas']);
    }

    public function testJsonExporterContieneDetallesDeTareas(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);
        $data = json_decode(file_get_contents($filepath), true);

        $primeraTarea = $data['tareas'][0];

        $this->assertSame(1, $primeraTarea['id']);
        $this->assertSame('Comprar leche', $primeraTarea['titulo']);
        $this->assertSame('En el supermercado', $primeraTarea['descripcion']);
        $this->assertSame('alta', $primeraTarea['prioridad']);
        $this->assertSame('pendiente', $primeraTarea['estado']);
    }

    public function testJsonExporterConListaVacia(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export([]);
        $data = json_decode(file_get_contents($filepath), true);

        $this->assertSame(0, $data['total_tareas']);
        $this->assertCount(0, $data['tareas']);
    }

    public function testJsonExporterGeneraArchivoConExtensionJson(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);

        $this->assertStringEndsWith('.json', $filepath);
    }

    public function testJsonExporterGetFormatName(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $this->assertSame('JSON', $exporter->getFormatName());
    }

    public function testJsonExporterImplementaInterface(): void
    {
        $exporter = new JsonExporter(outputDir: $this->tempDir);

        $this->assertInstanceOf(ExporterInterface::class, $exporter);
    }

    // ---------------------------------------------------------------
    //  Tests de CsvExporter
    // ---------------------------------------------------------------

    public function testCsvExporterProduceCsvValidoConEncabezados(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);

        $this->assertFileExists($filepath);

        // Leer el contenido del CSV
        $contenido = file_get_contents($filepath);
        $this->assertNotFalse($contenido);

        // Eliminar BOM UTF-8 si esta presente para analizar el contenido
        $contenidoSinBom = ltrim($contenido, "\xEF\xBB\xBF");
        $lineas = explode("\n", trim($contenidoSinBom));

        // La primera linea debe ser los encabezados
        $encabezados = str_getcsv($lineas[0]);
        $this->assertContains('ID', $encabezados);
        $this->assertContains('Titulo', $encabezados);
        $this->assertContains('Descripcion', $encabezados);
        $this->assertContains('Prioridad', $encabezados);
        $this->assertContains('Estado', $encabezados);
        $this->assertContains('Fecha Creacion', $encabezados);
        $this->assertContains('Fecha Completada', $encabezados);
    }

    public function testCsvExporterContieneFilasDeDatos(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);

        $contenido = file_get_contents($filepath);
        $contenidoSinBom = ltrim($contenido, "\xEF\xBB\xBF");
        $lineas = explode("\n", trim($contenidoSinBom));

        // 1 linea de encabezados + 3 lineas de datos
        $this->assertCount(4, $lineas);

        // Verificar primera fila de datos
        $primeraFila = str_getcsv($lineas[1]);
        $this->assertSame('1', $primeraFila[0]); // ID
        $this->assertSame('Comprar leche', $primeraFila[1]); // Titulo
    }

    public function testCsvExporterConListaVacia(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export([]);

        $contenido = file_get_contents($filepath);
        $contenidoSinBom = ltrim($contenido, "\xEF\xBB\xBF");
        $lineas = explode("\n", trim($contenidoSinBom));

        // Solo encabezados, sin datos
        $this->assertCount(1, $lineas);
    }

    public function testCsvExporterGeneraArchivoConExtensionCsv(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);

        $this->assertStringEndsWith('.csv', $filepath);
    }

    public function testCsvExporterGetFormatName(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $this->assertSame('CSV', $exporter->getFormatName());
    }

    public function testCsvExporterImplementaInterface(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $this->assertInstanceOf(ExporterInterface::class, $exporter);
    }

    public function testCsvExporterIncluyeBomUtf8(): void
    {
        $exporter = new CsvExporter(outputDir: $this->tempDir);

        $filepath = $exporter->export($this->tareasEjemplo);

        $contenidoCrudo = file_get_contents($filepath);

        // Verificar que los primeros 3 bytes son el BOM UTF-8
        $this->assertSame("\xEF\xBB\xBF", substr($contenidoCrudo, 0, 3));
    }

    // ---------------------------------------------------------------
    //  Tests de ExportService (contexto del patron Strategy)
    // ---------------------------------------------------------------

    public function testExportServiceRegistraExportadoresPorDefecto(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        $formatos = $service->formatosDisponibles();

        $this->assertContains('json', $formatos);
        $this->assertContains('csv', $formatos);
    }

    public function testExportServiceRegistraYRecuperaExportadorCustom(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        // Crear un exportador personalizado con una clase anonima
        $customExporter = new class implements ExporterInterface {
            public function export(array $tasks): string
            {
                return '/tmp/custom_export.txt';
            }

            public function getFormatName(): string
            {
                return 'Custom';
            }
        };

        $service->registrarExportador('custom', $customExporter);

        $formatos = $service->formatosDisponibles();
        $this->assertContains('custom', $formatos);
    }

    public function testExportServiceExportarEnFormatoJson(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        $filepath = $service->exportar($this->tareasEjemplo, 'json');

        $this->assertFileExists($filepath);
        $this->assertStringEndsWith('.json', $filepath);

        // Verificar que el JSON es valido
        $data = json_decode(file_get_contents($filepath), true);
        $this->assertNotNull($data);
    }

    public function testExportServiceExportarEnFormatoCsv(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        $filepath = $service->exportar($this->tareasEjemplo, 'csv');

        $this->assertFileExists($filepath);
        $this->assertStringEndsWith('.csv', $filepath);
    }

    public function testExportServiceExportarCreaElArchivo(): void
    {
        $subDir = $this->tempDir . '/exportaciones';
        $service = new ExportService(outputDir: $subDir);

        $filepath = $service->exportar($this->tareasEjemplo, 'json');

        $this->assertFileExists($filepath);
        $this->assertDirectoryExists($subDir);

        // Limpiar subdirectorio
        if (is_file($filepath)) {
            unlink($filepath);
        }
        if (is_dir($subDir)) {
            rmdir($subDir);
        }
    }

    public function testExportServiceRechazaFormatoNoRegistrado(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('no disponible');

        $service->exportar($this->tareasEjemplo, 'xml');
    }

    public function testExportServiceFormatoEsCaseInsensitive(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        // El formato se convierte a minusculas internamente
        $filepath = $service->exportar($this->tareasEjemplo, 'JSON');

        $this->assertFileExists($filepath);
    }

    public function testExportServiceFormatosDisponiblesRetornaArray(): void
    {
        $service = new ExportService(outputDir: $this->tempDir);

        $formatos = $service->formatosDisponibles();

        $this->assertIsArray($formatos);
        $this->assertCount(2, $formatos);
    }
}
