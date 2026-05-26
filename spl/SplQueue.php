<?php
/**
 * SplQueue - Cola FIFO (First In, First Out)
 *
 * La clase SplQueue implementa una cola donde el primer elemento
 * agregado es el primero en ser removido.
 */

// ============================================
// Ejemplo 1: Operaciones básicas - enqueue, dequeue, bottom
// ============================================

echo "=== Ejemplo 1: Operaciones básicas ===\n";

$cola = new SplQueue();

// enqueue() agrega elementos al final de la cola
$cola->enqueue('Cliente A');
$cola->enqueue('Cliente B');
$cola->enqueue('Cliente C');
$cola->enqueue('Cliente D');

// bottom() devuelve el primer elemento (frente) sin removerlo
echo "Primer cliente en la cola: " . $cola->bottom() . "\n"; // Cliente A

// top() devuelve el último elemento (final) sin removerlo
echo "Último cliente en la cola: " . $cola->top() . "\n"; // Cliente D

// dequeue() remueve y devuelve el primer elemento
$atendido = $cola->dequeue();
echo "Cliente atendido: " . $atendido . "\n"; // Cliente A

// Verificar nuevo frente de la cola
echo "Siguiente cliente: " . $cola->bottom() . "\n"; // Cliente B

// ============================================
// Ejemplo 2: count() e iteración
// ============================================

echo "\n=== Ejemplo 2: count() e iteración ===\n";

$colaTareas = new SplQueue();
$colaTareas->enqueue('Enviar correo');
$colaTareas->enqueue('Generar reporte');
$colaTareas->enqueue('Actualizar base de datos');
$colaTareas->enqueue('Notificar al usuario');

echo "Tareas pendientes: " . $colaTareas->count() . "\n"; // 4

// Iterar sobre la cola con foreach (orden FIFO)
echo "Lista de tareas pendientes:\n";
foreach ($colaTareas as $indice => $tarea) {
    echo "  [$indice] $tarea\n";
}

// La iteración NO remueve elementos
echo "Tareas después de iterar: " . $colaTareas->count() . "\n"; // 4

// ============================================
// Ejemplo 3: Procesamiento completo de una cola
// ============================================

echo "\n=== Ejemplo 3: Procesar cola completa ===\n";

$colaImpresion = new SplQueue();
$colaImpresion->enqueue('documento_informe.pdf');
$colaImpresion->enqueue('factura_001.pdf');
$colaImpresion->enqueue('contrato_2026.pdf');

echo "Documentos en cola de impresión: " . $colaImpresion->count() . "\n";

// Procesar todos los elementos hasta vaciar la cola
$orden = 1;
while (!$colaImpresion->isEmpty()) {
    $documento = $colaImpresion->dequeue();
    echo "  Imprimiendo ($orden): $documento\n";
    $orden++;
}

echo "¿Cola vacía? " . ($colaImpresion->isEmpty() ? 'Sí' : 'No') . "\n";

// ============================================
// Ejemplo 4: Caso práctico - Sistema de turnos
// ============================================

echo "\n=== Ejemplo 4: Sistema de turnos ===\n";

/**
 * Clase que implementa un sistema de turnos usando SplQueue.
 * Cada turno tiene un número, nombre y prioridad de atención.
 */
class SistemaTurnos
{
    private SplQueue $cola;
    private int $siguienteNumero;

    public function __construct()
    {
        $this->cola = new SplQueue();
        $this->siguienteNumero = 1;
    }

    /**
     * Registra un nuevo turno en la cola
     */
    public function registrarTurno(string $nombre, string $servicio): array
    {
        $turno = [
            'numero'   => $this->siguienteNumero++,
            'nombre'   => $nombre,
            'servicio' => $servicio,
            'hora'     => date('H:i:s'),
        ];
        $this->cola->enqueue($turno);
        return $turno;
    }

    /**
     * Atiende el siguiente turno en la cola
     */
    public function atenderSiguiente(): ?array
    {
        if ($this->cola->isEmpty()) {
            return null;
        }
        return $this->cola->dequeue();
    }

    /**
     * Muestra el siguiente turno sin atenderlo
     */
    public function verSiguiente(): ?array
    {
        if ($this->cola->isEmpty()) {
            return null;
        }
        return $this->cola->bottom();
    }

    public function turnosPendientes(): int
    {
        return $this->cola->count();
    }

    /**
     * Lista todos los turnos pendientes
     */
    public function listarPendientes(): void
    {
        if ($this->cola->isEmpty()) {
            echo "  No hay turnos pendientes.\n";
            return;
        }
        foreach ($this->cola as $turno) {
            echo "  Turno #{$turno['numero']} - {$turno['nombre']} ({$turno['servicio']})\n";
        }
    }
}

$sistema = new SistemaTurnos();

// Registrar turnos
$sistema->registrarTurno('María López', 'Consulta general');
$sistema->registrarTurno('Carlos Ruiz', 'Pago de factura');
$sistema->registrarTurno('Ana García', 'Reclamo');
$sistema->registrarTurno('Pedro Sánchez', 'Consulta general');

echo "Turnos pendientes: " . $sistema->turnosPendientes() . "\n";
echo "\nCola actual:\n";
$sistema->listarPendientes();

// Atender turnos
echo "\nAtendiendo turnos:\n";
$turno = $sistema->atenderSiguiente();
echo "  Atendido: Turno #{$turno['numero']} - {$turno['nombre']}\n";

$turno = $sistema->atenderSiguiente();
echo "  Atendido: Turno #{$turno['numero']} - {$turno['nombre']}\n";

echo "\nTurnos restantes: " . $sistema->turnosPendientes() . "\n";
$sistema->listarPendientes();

// ============================================
// Ejemplo 5: Cola con límite máximo (Buffer circular)
// ============================================

echo "\n=== Ejemplo 5: Cola con límite máximo ===\n";

/**
 * Cola con tamaño máximo. Al llenarse, descarta los elementos
 * más antiguos automáticamente (comportamiento de buffer circular).
 */
class ColaLimitada
{
    private SplQueue $cola;
    private int $limiteMaximo;

    public function __construct(int $limite)
    {
        $this->cola = new SplQueue();
        $this->limiteMaximo = $limite;
    }

    public function agregar(mixed $elemento): void
    {
        // Si la cola está llena, remover el más antiguo
        if ($this->cola->count() >= $this->limiteMaximo) {
            $descartado = $this->cola->dequeue();
            echo "  [Descartado por límite: $descartado]\n";
        }
        $this->cola->enqueue($elemento);
    }

    public function obtenerTodos(): array
    {
        $elementos = [];
        foreach ($this->cola as $elemento) {
            $elementos[] = $elemento;
        }
        return $elementos;
    }

    public function cantidad(): int
    {
        return $this->cola->count();
    }
}

// Cola de los últimos 3 mensajes de log
$logReciente = new ColaLimitada(3);

$logReciente->agregar('Inicio del sistema');
$logReciente->agregar('Usuario conectado');
$logReciente->agregar('Consulta realizada');
echo "Log actual: " . implode(' | ', $logReciente->obtenerTodos()) . "\n";

$logReciente->agregar('Error en base de datos');
$logReciente->agregar('Reintento exitoso');

echo "Log después de exceder límite:\n";
foreach ($logReciente->obtenerTodos() as $msg) {
    echo "  - $msg\n";
}
echo "Cantidad en cola: " . $logReciente->cantidad() . "\n";

?>
