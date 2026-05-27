<?php

declare(strict_types=1);

namespace Tests\Unit;

use MiniProject\Database;
use MiniProject\Priority;
use MiniProject\Status;
use MiniProject\Task;
use MiniProject\TaskRepository;
use MiniProject\TaskService;
use MiniProject\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para TaskService con base de datos SQLite en memoria.
 *
 * Usa una base de datos real en memoria en lugar de mocks para mayor
 * fidelidad y simplicidad, dado que el repositorio depende del Singleton Database.
 *
 * @covers \MiniProject\TaskService
 * @covers \MiniProject\TaskRepository
 * @covers \MiniProject\Database
 */
class TaskServiceTest extends TestCase
{
    private TaskService $service;
    private TaskRepository $repository;

    protected function setUp(): void
    {
        // Resetear el singleton para que cada test tenga su propia BD en memoria
        Database::resetInstance();
        Database::getInstance(':memory:');

        $this->repository = new TaskRepository();
        $this->service = new TaskService($this->repository);
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    // ---------------------------------------------------------------
    //  Tests de crearTarea con datos validos
    // ---------------------------------------------------------------

    public function testCrearTareaConDatosValidos(): void
    {
        $task = $this->service->crearTarea(
            titulo: 'Comprar pan',
            descripcion: 'En la panaderia de la esquina',
            prioridad: 'alta',
        );

        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotNull($task->id);
        $this->assertSame('Comprar pan', $task->titulo);
        $this->assertSame('En la panaderia de la esquina', $task->descripcion);
        $this->assertSame(Priority::Alta, $task->prioridad);
        $this->assertSame(Status::Pendiente, $task->estado);
        $this->assertNotEmpty($task->fechaCreacion);
    }

    public function testCrearTareaConPrioridadMedia(): void
    {
        $task = $this->service->crearTarea(
            titulo: 'Estudiar PHP',
            descripcion: 'Repasar patrones de diseno',
            prioridad: 'media',
        );

        $this->assertSame(Priority::Media, $task->prioridad);
    }

    public function testCrearTareaConPrioridadBaja(): void
    {
        $task = $this->service->crearTarea(
            titulo: 'Ordenar escritorio',
            descripcion: '',
            prioridad: 'baja',
        );

        $this->assertSame(Priority::Baja, $task->prioridad);
    }

    public function testCrearTareaConDescripcionVacia(): void
    {
        $task = $this->service->crearTarea(
            titulo: 'Tarea sin descripcion',
            descripcion: '',
            prioridad: 'media',
        );

        $this->assertSame('', $task->descripcion);
    }

    public function testCrearTareaRecortaEspaciosDelTitulo(): void
    {
        $task = $this->service->crearTarea(
            titulo: '   Titulo con espacios   ',
            descripcion: '',
            prioridad: 'baja',
        );

        $this->assertSame('Titulo con espacios', $task->titulo);
    }

    public function testCrearVariasTareasAsignaIdsConsecutivos(): void
    {
        $tarea1 = $this->service->crearTarea('Primera tarea', '', 'alta');
        $tarea2 = $this->service->crearTarea('Segunda tarea', '', 'media');
        $tarea3 = $this->service->crearTarea('Tercera tarea', '', 'baja');

        $this->assertSame(1, $tarea1->id);
        $this->assertSame(2, $tarea2->id);
        $this->assertSame(3, $tarea3->id);
    }

    // ---------------------------------------------------------------
    //  Tests de validacion: titulo vacio
    // ---------------------------------------------------------------

    public function testCrearTareaRechazaTituloVacio(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El titulo de la tarea no puede estar vacio');

        $this->service->crearTarea(
            titulo: '',
            descripcion: 'Tiene descripcion pero no titulo',
            prioridad: 'alta',
        );
    }

    public function testCrearTareaRechazaTituloSoloEspacios(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El titulo de la tarea no puede estar vacio');

        $this->service->crearTarea(
            titulo: '     ',
            descripcion: '',
            prioridad: 'media',
        );
    }

    public function testCrearTareaValidacionTituloVacioTieneCampoCorrecto(): void
    {
        try {
            $this->service->crearTarea('', '', 'alta');
            $this->fail('Deberia haber lanzado ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('titulo', $e->campo);
            $this->assertSame(ValidationException::ERROR_CAMPO_VACIO, $e->getCode());
        }
    }

    // ---------------------------------------------------------------
    //  Tests de validacion: titulo demasiado largo
    // ---------------------------------------------------------------

    public function testCrearTareaRechazaTituloMayorA100Caracteres(): void
    {
        $tituloLargo = str_repeat('a', 101);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El titulo no puede superar los 100 caracteres');

        $this->service->crearTarea(
            titulo: $tituloLargo,
            descripcion: '',
            prioridad: 'alta',
        );
    }

    public function testCrearTareaAceptaTituloDe100Caracteres(): void
    {
        $tituloExacto = str_repeat('x', 100);

        $task = $this->service->crearTarea(
            titulo: $tituloExacto,
            descripcion: '',
            prioridad: 'media',
        );

        $this->assertSame(100, mb_strlen($task->titulo));
    }

    // ---------------------------------------------------------------
    //  Tests de validacion: prioridad invalida
    // ---------------------------------------------------------------

    public function testCrearTareaRechazaPrioridadInvalida(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Prioridad invalida');

        $this->service->crearTarea(
            titulo: 'Tarea con prioridad invalida',
            descripcion: '',
            prioridad: 'urgente',
        );
    }

    public function testCrearTareaRechazaPrioridadVacia(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->crearTarea(
            titulo: 'Tarea sin prioridad',
            descripcion: '',
            prioridad: '',
        );
    }

    public function testCrearTareaValidacionPrioridadTieneCampoCorrecto(): void
    {
        try {
            $this->service->crearTarea('Tarea', '', 'inexistente');
            $this->fail('Deberia haber lanzado ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('prioridad', $e->campo);
            $this->assertSame(ValidationException::ERROR_PRIORIDAD_INVALIDA, $e->getCode());
        }
    }

    // ---------------------------------------------------------------
    //  Tests de completarTarea
    // ---------------------------------------------------------------

    public function testCompletarTareaPendiente(): void
    {
        $creada = $this->service->crearTarea('Tarea por completar', '', 'alta');

        $completada = $this->service->completarTarea($creada->id);

        $this->assertSame(Status::Completada, $completada->estado);
        $this->assertNotNull($completada->fechaCompletada);
    }

    public function testCompletarTareaYaCompletadaLanzaExcepcion(): void
    {
        $creada = $this->service->crearTarea('Tarea doble completar', '', 'media');
        $this->service->completarTarea($creada->id);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('ya esta completada');

        $this->service->completarTarea($creada->id);
    }

    public function testCompletarTareaConIdString(): void
    {
        $creada = $this->service->crearTarea('Tarea id string', '', 'baja');

        $completada = $this->service->completarTarea((string) $creada->id);

        $this->assertSame(Status::Completada, $completada->estado);
    }

    // ---------------------------------------------------------------
    //  Tests de buscarTareas
    // ---------------------------------------------------------------

    public function testBuscarTareasConPalabraClave(): void
    {
        $this->service->crearTarea('Comprar leche', 'En el supermercado', 'alta');
        $this->service->crearTarea('Comprar pan', 'En la panaderia', 'media');
        $this->service->crearTarea('Estudiar PHP', 'Repasar enums', 'baja');

        $resultados = $this->service->buscarTareas('Comprar');

        $this->assertCount(2, $resultados);
    }

    public function testBuscarTareasConPalabraClaveVaciaLanzaExcepcion(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('La palabra clave de busqueda no puede estar vacia');

        $this->service->buscarTareas('');
    }

    public function testBuscarTareasConSoloEspaciosLanzaExcepcion(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->buscarTareas('   ');
    }

    public function testBuscarTareasPorDescripcion(): void
    {
        $this->service->crearTarea('Tarea 1', 'Comprar material de oficina', 'alta');
        $this->service->crearTarea('Tarea 2', 'Revisar documentos', 'media');

        $resultados = $this->service->buscarTareas('oficina');

        $this->assertCount(1, $resultados);
        $this->assertSame('Tarea 1', $resultados[0]->titulo);
    }

    public function testBuscarTareasSinResultados(): void
    {
        $this->service->crearTarea('Estudiar PHP', '', 'alta');

        $resultados = $this->service->buscarTareas('Python');

        $this->assertCount(0, $resultados);
    }

    // ---------------------------------------------------------------
    //  Tests de listarTareas
    // ---------------------------------------------------------------

    public function testListarTodasLasTareas(): void
    {
        $this->service->crearTarea('Tarea 1', '', 'alta');
        $this->service->crearTarea('Tarea 2', '', 'media');

        $tareas = $this->service->listarTareas('todas');

        $this->assertCount(2, $tareas);
    }

    public function testListarTareasPendientes(): void
    {
        $tarea = $this->service->crearTarea('Tarea pendiente', '', 'alta');
        $this->service->crearTarea('Tarea completar', '', 'media');
        $this->service->completarTarea(2);

        $pendientes = $this->service->listarTareas('pendiente');

        $this->assertCount(1, $pendientes);
        $this->assertSame(Status::Pendiente, $pendientes[0]->estado);
    }

    public function testListarTareasCompletadas(): void
    {
        $this->service->crearTarea('Tarea 1', '', 'alta');
        $this->service->crearTarea('Tarea 2', '', 'media');
        $this->service->completarTarea(1);

        $completadas = $this->service->listarTareas('completada');

        $this->assertCount(1, $completadas);
        $this->assertSame(Status::Completada, $completadas[0]->estado);
    }

    public function testListarTareasConFiltroInvalidoLanzaExcepcion(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Filtro de estado invalido');

        $this->service->listarTareas('cancelada');
    }

    // ---------------------------------------------------------------
    //  Tests de eliminarTarea
    // ---------------------------------------------------------------

    public function testEliminarTareaExistente(): void
    {
        $this->service->crearTarea('Tarea a eliminar', '', 'alta');

        $resultado = $this->service->eliminarTarea(1);

        $this->assertTrue($resultado);

        // Verificar que la lista esta vacia
        $tareas = $this->service->listarTareas('todas');
        $this->assertCount(0, $tareas);
    }

    // ---------------------------------------------------------------
    //  Tests de obtenerEstadisticas
    // ---------------------------------------------------------------

    public function testObtenerEstadisticasConTareas(): void
    {
        $this->service->crearTarea('Alta 1', '', 'alta');
        $this->service->crearTarea('Media 1', '', 'media');
        $this->service->crearTarea('Baja 1', '', 'baja');
        $this->service->completarTarea(1);

        $stats = $this->service->obtenerEstadisticas();

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['completadas']);
        $this->assertSame(2, $stats['pendientes']);
        $this->assertSame(1, $stats['por_prioridad']['alta']);
        $this->assertSame(1, $stats['por_prioridad']['media']);
        $this->assertSame(1, $stats['por_prioridad']['baja']);
    }

    public function testObtenerEstadisticasSinTareas(): void
    {
        $stats = $this->service->obtenerEstadisticas();

        $this->assertSame(0, $stats['total']);
        $this->assertSame(0, $stats['completadas']);
        $this->assertSame(0, $stats['pendientes']);
        $this->assertSame(0, $stats['por_prioridad']['alta']);
        $this->assertSame(0, $stats['por_prioridad']['media']);
        $this->assertSame(0, $stats['por_prioridad']['baja']);
    }
}
