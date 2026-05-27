<?php

declare(strict_types=1);

namespace Tests\Integration;

use MiniProject\Database;
use MiniProject\NotFoundException;
use MiniProject\Priority;
use MiniProject\Status;
use MiniProject\Task;
use MiniProject\TaskRepository;
use MiniProject\TaskService;
use MiniProject\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests de integracion que verifican el flujo completo de trabajo
 * desde la creacion hasta la eliminacion de tareas, pasando por
 * busqueda, filtrado y estadisticas.
 *
 * Usa base de datos SQLite en memoria para aislamiento total.
 *
 * @covers \MiniProject\TaskService
 * @covers \MiniProject\TaskRepository
 * @covers \MiniProject\Database
 * @covers \MiniProject\Task
 */
class TaskWorkflowTest extends TestCase
{
    private TaskService $service;
    private TaskRepository $repository;

    protected function setUp(): void
    {
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
    //  Test de flujo completo: crear -> buscar -> completar -> verificar -> eliminar
    // ---------------------------------------------------------------

    public function testFlujoCompletoDeUnaTarea(): void
    {
        // 1. Crear una tarea
        $tareaCreada = $this->service->crearTarea(
            titulo: 'Implementar autenticacion',
            descripcion: 'Agregar login con JWT',
            prioridad: 'alta',
        );

        $this->assertNotNull($tareaCreada->id);
        $this->assertSame('Implementar autenticacion', $tareaCreada->titulo);
        $this->assertSame(Status::Pendiente, $tareaCreada->estado);
        $this->assertNull($tareaCreada->fechaCompletada);

        $idTarea = $tareaCreada->id;

        // 2. Buscar la tarea por ID usando el repositorio
        $tareaEncontrada = $this->repository->findById($idTarea);

        $this->assertSame($idTarea, $tareaEncontrada->id);
        $this->assertSame('Implementar autenticacion', $tareaEncontrada->titulo);
        $this->assertSame('Agregar login con JWT', $tareaEncontrada->descripcion);
        $this->assertSame(Priority::Alta, $tareaEncontrada->prioridad);

        // 3. Completar la tarea
        $tareaCompletada = $this->service->completarTarea($idTarea);

        $this->assertSame(Status::Completada, $tareaCompletada->estado);
        $this->assertNotNull($tareaCompletada->fechaCompletada);

        // 4. Verificar que el estado persiste al volver a buscar
        $tareaVerificada = $this->repository->findById($idTarea);

        $this->assertSame(Status::Completada, $tareaVerificada->estado);
        $this->assertNotNull($tareaVerificada->fechaCompletada);

        // 5. Eliminar la tarea
        $eliminada = $this->service->eliminarTarea($idTarea);

        $this->assertTrue($eliminada);

        // 6. Verificar que la tarea ya no existe
        $this->expectException(NotFoundException::class);
        $this->repository->findById($idTarea);
    }

    // ---------------------------------------------------------------
    //  Test de busqueda
    // ---------------------------------------------------------------

    public function testBusquedaEncuentraTareasCoincidentes(): void
    {
        $this->service->crearTarea('Revisar codigo PHP', 'Refactorizar modulos', 'alta');
        $this->service->crearTarea('Escribir documentacion', 'Documentar la API REST', 'media');
        $this->service->crearTarea('Configurar CI/CD', 'Pipeline de PHP con GitHub Actions', 'baja');
        $this->service->crearTarea('Comprar monitor', 'Para la oficina', 'baja');

        // Buscar por palabra clave en titulo
        $resultadosPHP = $this->service->buscarTareas('PHP');
        $this->assertCount(2, $resultadosPHP);

        // Buscar por palabra clave en descripcion
        $resultadosAPI = $this->service->buscarTareas('API');
        $this->assertCount(1, $resultadosAPI);
        $this->assertSame('Escribir documentacion', $resultadosAPI[0]->titulo);
    }

    public function testBusquedaSinCoincidenciasRetornaVacio(): void
    {
        $this->service->crearTarea('Tarea de prueba', 'Descripcion simple', 'media');

        $resultados = $this->service->buscarTareas('JavaScript');

        $this->assertCount(0, $resultados);
        $this->assertIsArray($resultados);
    }

    public function testBusquedaEsCaseInsensitive(): void
    {
        $this->service->crearTarea('Estudiar PHP avanzado', '', 'alta');

        // SQLite LIKE es case-insensitive por defecto para ASCII
        $resultados = $this->service->buscarTareas('php');

        $this->assertCount(1, $resultados);
    }

    // ---------------------------------------------------------------
    //  Tests de estadisticas
    // ---------------------------------------------------------------

    public function testEstadisticasRetornanConteosCorrectos(): void
    {
        // Crear tareas con distintas prioridades
        $this->service->crearTarea('Alta 1', '', 'alta');
        $this->service->crearTarea('Alta 2', '', 'alta');
        $this->service->crearTarea('Media 1', '', 'media');
        $this->service->crearTarea('Baja 1', '', 'baja');
        $this->service->crearTarea('Baja 2', '', 'baja');

        // Completar algunas
        $this->service->completarTarea(1); // Alta 1
        $this->service->completarTarea(3); // Media 1

        $stats = $this->service->obtenerEstadisticas();

        // Totales
        $this->assertSame(5, $stats['total']);
        $this->assertSame(2, $stats['completadas']);
        $this->assertSame(3, $stats['pendientes']);

        // Por prioridad
        $this->assertSame(2, $stats['por_prioridad']['alta']);
        $this->assertSame(1, $stats['por_prioridad']['media']);
        $this->assertSame(2, $stats['por_prioridad']['baja']);
    }

    public function testEstadisticasConBaseDeDatosVacia(): void
    {
        $stats = $this->service->obtenerEstadisticas();

        $this->assertSame(0, $stats['total']);
        $this->assertSame(0, $stats['completadas']);
        $this->assertSame(0, $stats['pendientes']);
        $this->assertSame(0, $stats['por_prioridad']['alta']);
        $this->assertSame(0, $stats['por_prioridad']['media']);
        $this->assertSame(0, $stats['por_prioridad']['baja']);
    }

    public function testEstadisticasTodasCompletadas(): void
    {
        $this->service->crearTarea('T1', '', 'alta');
        $this->service->crearTarea('T2', '', 'media');
        $this->service->completarTarea(1);
        $this->service->completarTarea(2);

        $stats = $this->service->obtenerEstadisticas();

        $this->assertSame(2, $stats['total']);
        $this->assertSame(2, $stats['completadas']);
        $this->assertSame(0, $stats['pendientes']);
    }

    // ---------------------------------------------------------------
    //  Tests de filtrado por estado
    // ---------------------------------------------------------------

    public function testFiltrarPorEstadoPendiente(): void
    {
        $this->service->crearTarea('Pendiente 1', '', 'alta');
        $this->service->crearTarea('Pendiente 2', '', 'media');
        $this->service->crearTarea('Sera completada', '', 'baja');
        $this->service->completarTarea(3);

        $pendientes = $this->service->listarTareas('pendiente');

        $this->assertCount(2, $pendientes);
        foreach ($pendientes as $tarea) {
            $this->assertSame(Status::Pendiente, $tarea->estado);
        }
    }

    public function testFiltrarPorEstadoCompletada(): void
    {
        $this->service->crearTarea('T1', '', 'alta');
        $this->service->crearTarea('T2', '', 'media');
        $this->service->crearTarea('T3', '', 'baja');
        $this->service->completarTarea(1);
        $this->service->completarTarea(2);

        $completadas = $this->service->listarTareas('completada');

        $this->assertCount(2, $completadas);
        foreach ($completadas as $tarea) {
            $this->assertSame(Status::Completada, $tarea->estado);
        }
    }

    public function testFiltrarTodas(): void
    {
        $this->service->crearTarea('T1', '', 'alta');
        $this->service->crearTarea('T2', '', 'media');
        $this->service->completarTarea(1);

        $todas = $this->service->listarTareas('todas');

        $this->assertCount(2, $todas);
    }

    public function testFiltrarAceptaVariantesDeEstado(): void
    {
        $this->service->crearTarea('T1', '', 'alta');

        // Las variantes 'pendientes', 'pending' tambien deben funcionar
        $resultadoPendientes = $this->service->listarTareas('pendientes');
        $this->assertCount(1, $resultadoPendientes);

        $resultadoPending = $this->service->listarTareas('pending');
        $this->assertCount(1, $resultadoPending);

        // Completar y probar variantes de completada
        $this->service->completarTarea(1);

        $resultadoCompletadas = $this->service->listarTareas('completadas');
        $this->assertCount(1, $resultadoCompletadas);

        $resultadoCompleted = $this->service->listarTareas('completed');
        $this->assertCount(1, $resultadoCompleted);
    }

    // ---------------------------------------------------------------
    //  Tests de flujo con multiples operaciones
    // ---------------------------------------------------------------

    public function testCrearMultiplesTareasYEliminarAlgunas(): void
    {
        $t1 = $this->service->crearTarea('Tarea 1', '', 'alta');
        $t2 = $this->service->crearTarea('Tarea 2', '', 'media');
        $t3 = $this->service->crearTarea('Tarea 3', '', 'baja');

        // Eliminar la tarea del medio
        $this->service->eliminarTarea($t2->id);

        $todas = $this->service->listarTareas('todas');
        $this->assertCount(2, $todas);

        // Verificar que las tareas correctas sobrevivieron
        $ids = array_map(fn(Task $t) => $t->id, $todas);
        $this->assertContains($t1->id, $ids);
        $this->assertContains($t3->id, $ids);
        $this->assertNotContains($t2->id, $ids);
    }

    public function testCompletarYVerificarCambioEnEstadisticas(): void
    {
        $this->service->crearTarea('T1', '', 'alta');
        $this->service->crearTarea('T2', '', 'media');

        // Antes de completar
        $statsBefore = $this->service->obtenerEstadisticas();
        $this->assertSame(0, $statsBefore['completadas']);
        $this->assertSame(2, $statsBefore['pendientes']);

        // Completar una tarea
        $this->service->completarTarea(1);

        // Despues de completar
        $statsAfter = $this->service->obtenerEstadisticas();
        $this->assertSame(1, $statsAfter['completadas']);
        $this->assertSame(1, $statsAfter['pendientes']);
    }

    public function testEliminarTareaInexistenteLanzaExcepcion(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service->eliminarTarea(999);
    }

    public function testCompletarTareaInexistenteLanzaExcepcion(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service->completarTarea(999);
    }

    public function testNotFoundExceptionContieneDatosDelRecurso(): void
    {
        try {
            $this->repository->findById(42);
            $this->fail('Deberia haber lanzado NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('tarea', $e->recurso);
            $this->assertSame(42, $e->identificador);
            $this->assertStringContainsString('42', $e->getMessage());
        }
    }

    public function testFlujoCompletoConVariasTareas(): void
    {
        // Crear varias tareas
        $comprar = $this->service->crearTarea('Comprar viveres', 'Lista del super', 'alta');
        $estudiar = $this->service->crearTarea('Estudiar para examen', 'Capitulos 5 al 8', 'alta');
        $limpiar = $this->service->crearTarea('Limpiar casa', 'Aspirar y trapear', 'media');
        $ejercicio = $this->service->crearTarea('Hacer ejercicio', '30 min cardio', 'baja');

        // Verificar que hay 4 tareas pendientes
        $this->assertCount(4, $this->service->listarTareas('pendiente'));
        $this->assertCount(0, $this->service->listarTareas('completada'));

        // Completar dos tareas
        $this->service->completarTarea($comprar->id);
        $this->service->completarTarea($limpiar->id);

        // Verificar filtrado
        $this->assertCount(2, $this->service->listarTareas('pendiente'));
        $this->assertCount(2, $this->service->listarTareas('completada'));
        $this->assertCount(4, $this->service->listarTareas('todas'));

        // Buscar tareas
        $resultados = $this->service->buscarTareas('examen');
        $this->assertCount(1, $resultados);
        $this->assertSame('Estudiar para examen', $resultados[0]->titulo);

        // Estadisticas finales
        $stats = $this->service->obtenerEstadisticas();
        $this->assertSame(4, $stats['total']);
        $this->assertSame(2, $stats['completadas']);
        $this->assertSame(2, $stats['pendientes']);
        $this->assertSame(2, $stats['por_prioridad']['alta']);
        $this->assertSame(1, $stats['por_prioridad']['media']);
        $this->assertSame(1, $stats['por_prioridad']['baja']);

        // Eliminar una tarea completada
        $this->service->eliminarTarea($comprar->id);

        // Verificar despues de eliminar
        $this->assertCount(3, $this->service->listarTareas('todas'));
        $statsPostDelete = $this->service->obtenerEstadisticas();
        $this->assertSame(3, $statsPostDelete['total']);
        $this->assertSame(1, $statsPostDelete['completadas']);
        $this->assertSame(1, $statsPostDelete['por_prioridad']['alta']);
    }
}
