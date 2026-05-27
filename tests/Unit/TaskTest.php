<?php

declare(strict_types=1);

namespace Tests\Unit;

use MiniProject\Priority;
use MiniProject\Status;
use MiniProject\Task;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para la entidad Task y los enums Priority/Status.
 *
 * @covers \MiniProject\Task
 * @covers \MiniProject\Priority
 * @covers \MiniProject\Status
 */
class TaskTest extends TestCase
{
    // ---------------------------------------------------------------
    //  Tests de creacion de Task
    // ---------------------------------------------------------------

    public function testCrearTareaConTodasLasPropiedades(): void
    {
        $task = new Task(
            id: 1,
            titulo: 'Comprar leche',
            descripcion: 'Ir al supermercado',
            prioridad: Priority::Alta,
            estado: Status::Pendiente,
            fechaCreacion: '2026-01-15 10:00:00',
            fechaCompletada: null,
        );

        $this->assertSame(1, $task->id);
        $this->assertSame('Comprar leche', $task->titulo);
        $this->assertSame('Ir al supermercado', $task->descripcion);
        $this->assertSame(Priority::Alta, $task->prioridad);
        $this->assertSame(Status::Pendiente, $task->estado);
        $this->assertSame('2026-01-15 10:00:00', $task->fechaCreacion);
        $this->assertNull($task->fechaCompletada);
    }

    public function testCrearTareaConValoresPorDefecto(): void
    {
        $task = new Task(
            id: null,
            titulo: 'Tarea nueva',
            descripcion: '',
            prioridad: Priority::Media,
        );

        $this->assertNull($task->id);
        $this->assertSame(Status::Pendiente, $task->estado);
        $this->assertSame('', $task->fechaCreacion);
        $this->assertNull($task->fechaCompletada);
    }

    public function testCrearTareaCompletada(): void
    {
        $task = new Task(
            id: 5,
            titulo: 'Tarea terminada',
            descripcion: 'Ya esta lista',
            prioridad: Priority::Baja,
            estado: Status::Completada,
            fechaCreacion: '2026-01-10 08:00:00',
            fechaCompletada: '2026-01-12 14:30:00',
        );

        $this->assertSame(Status::Completada, $task->estado);
        $this->assertSame('2026-01-12 14:30:00', $task->fechaCompletada);
    }

    // ---------------------------------------------------------------
    //  Tests del metodo de fabrica fromRow()
    // ---------------------------------------------------------------

    public function testFromRowConDatosValidos(): void
    {
        $row = [
            'id' => '7',
            'titulo' => 'Estudiar PHP',
            'descripcion' => 'Repasar enums y readonly',
            'prioridad' => 'alta',
            'estado' => 'pendiente',
            'fecha_creacion' => '2026-05-01 09:00:00',
            'fecha_completada' => null,
        ];

        $task = Task::fromRow($row);

        $this->assertSame(7, $task->id);
        $this->assertSame('Estudiar PHP', $task->titulo);
        $this->assertSame('Repasar enums y readonly', $task->descripcion);
        $this->assertSame(Priority::Alta, $task->prioridad);
        $this->assertSame(Status::Pendiente, $task->estado);
        $this->assertSame('2026-05-01 09:00:00', $task->fechaCreacion);
        $this->assertNull($task->fechaCompletada);
    }

    public function testFromRowConTareaCompletada(): void
    {
        $row = [
            'id' => '3',
            'titulo' => 'Hacer ejercicio',
            'descripcion' => '30 minutos de cardio',
            'prioridad' => 'media',
            'estado' => 'completada',
            'fecha_creacion' => '2026-04-20 07:00:00',
            'fecha_completada' => '2026-04-20 07:35:00',
        ];

        $task = Task::fromRow($row);

        $this->assertSame(3, $task->id);
        $this->assertSame(Priority::Media, $task->prioridad);
        $this->assertSame(Status::Completada, $task->estado);
        $this->assertSame('2026-04-20 07:35:00', $task->fechaCompletada);
    }

    public function testFromRowConDescripcionNula(): void
    {
        $row = [
            'id' => '1',
            'titulo' => 'Sin descripcion',
            'descripcion' => null,
            'prioridad' => 'baja',
            'estado' => 'pendiente',
            'fecha_creacion' => null,
            'fecha_completada' => null,
        ];

        $task = Task::fromRow($row);

        $this->assertSame('', $task->descripcion);
        $this->assertSame('', $task->fechaCreacion);
    }

    // ---------------------------------------------------------------
    //  Tests del metodo toArray()
    // ---------------------------------------------------------------

    public function testToArrayRetornaEstructuraCorrecta(): void
    {
        $task = new Task(
            id: 10,
            titulo: 'Revisar codigo',
            descripcion: 'Code review del PR #42',
            prioridad: Priority::Alta,
            estado: Status::Pendiente,
            fechaCreacion: '2026-03-15 16:00:00',
            fechaCompletada: null,
        );

        $array = $task->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('titulo', $array);
        $this->assertArrayHasKey('descripcion', $array);
        $this->assertArrayHasKey('prioridad', $array);
        $this->assertArrayHasKey('estado', $array);
        $this->assertArrayHasKey('fecha_creacion', $array);
        $this->assertArrayHasKey('fecha_completada', $array);

        $this->assertSame(10, $array['id']);
        $this->assertSame('Revisar codigo', $array['titulo']);
        $this->assertSame('Code review del PR #42', $array['descripcion']);
        $this->assertSame('alta', $array['prioridad']);
        $this->assertSame('pendiente', $array['estado']);
        $this->assertSame('2026-03-15 16:00:00', $array['fecha_creacion']);
        $this->assertNull($array['fecha_completada']);
    }

    public function testToArrayConTareaCompletada(): void
    {
        $task = new Task(
            id: 2,
            titulo: 'Desplegar app',
            descripcion: 'Deploy a produccion',
            prioridad: Priority::Media,
            estado: Status::Completada,
            fechaCreacion: '2026-02-01 12:00:00',
            fechaCompletada: '2026-02-01 14:00:00',
        );

        $array = $task->toArray();

        $this->assertSame('completada', $array['estado']);
        $this->assertSame('media', $array['prioridad']);
        $this->assertSame('2026-02-01 14:00:00', $array['fecha_completada']);
    }

    public function testToArrayUsaValoresDeEnumComoString(): void
    {
        $task = new Task(
            id: 1,
            titulo: 'Test',
            descripcion: '',
            prioridad: Priority::Baja,
            estado: Status::Pendiente,
        );

        $array = $task->toArray();

        // Los valores del enum se almacenan como string en el array
        $this->assertSame('baja', $array['prioridad']);
        $this->assertSame('pendiente', $array['estado']);
    }

    // ---------------------------------------------------------------
    //  Tests del enum Priority
    // ---------------------------------------------------------------

    public function testPriorityTieneTresCasos(): void
    {
        $cases = Priority::cases();

        $this->assertCount(3, $cases);
        $this->assertSame(Priority::Alta, $cases[0]);
        $this->assertSame(Priority::Media, $cases[1]);
        $this->assertSame(Priority::Baja, $cases[2]);
    }

    public function testPriorityValoresBackedEnum(): void
    {
        $this->assertSame('alta', Priority::Alta->value);
        $this->assertSame('media', Priority::Media->value);
        $this->assertSame('baja', Priority::Baja->value);
    }

    public function testPriorityFromStringValido(): void
    {
        $this->assertSame(Priority::Alta, Priority::from('alta'));
        $this->assertSame(Priority::Media, Priority::from('media'));
        $this->assertSame(Priority::Baja, Priority::from('baja'));
    }

    public function testPriorityFromStringInvalidoLanzaExcepcion(): void
    {
        $this->expectException(\ValueError::class);
        Priority::from('urgente');
    }

    public function testPriorityTryFromRetornaNullParaValorInvalido(): void
    {
        $this->assertNull(Priority::tryFrom('urgente'));
        $this->assertNull(Priority::tryFrom(''));
        $this->assertNull(Priority::tryFrom('ALTA'));
    }

    public function testPriorityColorizadoContieneElValor(): void
    {
        // El metodo colorizado() envuelve el valor con codigos ANSI
        $this->assertStringContainsString('alta', Priority::Alta->colorizado());
        $this->assertStringContainsString('media', Priority::Media->colorizado());
        $this->assertStringContainsString('baja', Priority::Baja->colorizado());
    }

    public function testPriorityIndicadorRetornaSimbolosCorrecto(): void
    {
        $this->assertSame('!!!', Priority::Alta->indicador());
        $this->assertSame('!! ', Priority::Media->indicador());
        $this->assertSame('!  ', Priority::Baja->indicador());
    }

    // ---------------------------------------------------------------
    //  Tests del enum Status
    // ---------------------------------------------------------------

    public function testStatusTieneDosCasos(): void
    {
        $cases = Status::cases();

        $this->assertCount(2, $cases);
        $this->assertSame(Status::Pendiente, $cases[0]);
        $this->assertSame(Status::Completada, $cases[1]);
    }

    public function testStatusValoresBackedEnum(): void
    {
        $this->assertSame('pendiente', Status::Pendiente->value);
        $this->assertSame('completada', Status::Completada->value);
    }

    public function testStatusFromStringValido(): void
    {
        $this->assertSame(Status::Pendiente, Status::from('pendiente'));
        $this->assertSame(Status::Completada, Status::from('completada'));
    }

    public function testStatusFromStringInvalidoLanzaExcepcion(): void
    {
        $this->expectException(\ValueError::class);
        Status::from('cancelada');
    }

    public function testStatusColorizadoContieneElValor(): void
    {
        $this->assertStringContainsString('pendiente', Status::Pendiente->colorizado());
        $this->assertStringContainsString('completada', Status::Completada->colorizado());
    }

    public function testStatusCasillaRetornaFormato(): void
    {
        $this->assertSame('[ ]', Status::Pendiente->casilla());
        $this->assertSame('[x]', Status::Completada->casilla());
    }

    // ---------------------------------------------------------------
    //  Tests de metodos de formato
    // ---------------------------------------------------------------

    public function testFormatoLineaContieneIdYTitulo(): void
    {
        $task = new Task(
            id: 42,
            titulo: 'Tarea de prueba',
            descripcion: '',
            prioridad: Priority::Alta,
            estado: Status::Pendiente,
        );

        $linea = $task->formatoLinea();

        $this->assertStringContainsString('42', $linea);
        $this->assertStringContainsString('Tarea de prueba', $linea);
    }

    public function testFormatoDetalleContieneInformacionCompleta(): void
    {
        $task = new Task(
            id: 1,
            titulo: 'Mi tarea',
            descripcion: 'Una descripcion detallada',
            prioridad: Priority::Media,
            estado: Status::Completada,
            fechaCreacion: '2026-01-01 00:00:00',
            fechaCompletada: '2026-01-02 12:00:00',
        );

        $detalle = $task->formatoDetalle();

        $this->assertStringContainsString('Mi tarea', $detalle);
        $this->assertStringContainsString('Una descripcion detallada', $detalle);
        $this->assertStringContainsString('2026-01-01 00:00:00', $detalle);
        $this->assertStringContainsString('2026-01-02 12:00:00', $detalle);
    }

    public function testFormatoDetalleSinFechaCompletada(): void
    {
        $task = new Task(
            id: 1,
            titulo: 'Pendiente',
            descripcion: '',
            prioridad: Priority::Baja,
            estado: Status::Pendiente,
            fechaCreacion: '2026-01-01 00:00:00',
            fechaCompletada: null,
        );

        $detalle = $task->formatoDetalle();

        // No debe contener la linea "Completada:" si fechaCompletada es null
        $this->assertStringNotContainsString('Completada:', $detalle);
    }
}
