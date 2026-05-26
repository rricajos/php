<?php
/**
 * SplPriorityQueue - Cola de prioridad
 *
 * Implementa una cola donde los elementos se extraen según su prioridad
 * (mayor prioridad primero). Internamente usa un heap (montículo).
 */

// ============================================
// Ejemplo 1: Operaciones básicas - insert y extract
// ============================================

echo "=== Ejemplo 1: Operaciones básicas ===\n";

$cola = new SplPriorityQueue();

// insert(dato, prioridad) - mayor número = mayor prioridad
$cola->insert('Tarea baja', 1);
$cola->insert('Tarea alta', 10);
$cola->insert('Tarea media', 5);
$cola->insert('Tarea crítica', 20);
$cola->insert('Tarea normal', 3);

echo "Cantidad de elementos: " . $cola->count() . "\n";

// extract() devuelve y remueve el elemento con mayor prioridad
echo "Extraer el de mayor prioridad: " . $cola->extract() . "\n"; // Tarea crítica
echo "Siguiente en prioridad: " . $cola->extract() . "\n";        // Tarea alta

// top() muestra el siguiente sin removerlo
echo "Siguiente (sin remover): " . $cola->top() . "\n";           // Tarea media
echo "Elementos restantes: " . $cola->count() . "\n";

// ============================================
// Ejemplo 2: Modos de extracción (flags)
// ============================================

echo "\n=== Ejemplo 2: Modos de extracción ===\n";

// EXTR_DATA - solo devuelve los datos (por defecto)
$colaData = new SplPriorityQueue();
$colaData->setExtractFlags(SplPriorityQueue::EXTR_DATA);
$colaData->insert('Dato A', 3);
$colaData->insert('Dato B', 1);
$colaData->insert('Dato C', 5);

echo "EXTR_DATA (solo datos):\n";
while (!$colaData->isEmpty()) {
    echo "  " . $colaData->extract() . "\n";
}

// EXTR_PRIORITY - solo devuelve la prioridad
$colaPrio = new SplPriorityQueue();
$colaPrio->setExtractFlags(SplPriorityQueue::EXTR_PRIORITY);
$colaPrio->insert('Dato X', 3);
$colaPrio->insert('Dato Y', 1);
$colaPrio->insert('Dato Z', 5);

echo "\nEXTR_PRIORITY (solo prioridad):\n";
while (!$colaPrio->isEmpty()) {
    echo "  Prioridad: " . $colaPrio->extract() . "\n";
}

// EXTR_BOTH - devuelve un array con 'data' y 'priority'
$colaAmbos = new SplPriorityQueue();
$colaAmbos->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
$colaAmbos->insert('Emergencia', 10);
$colaAmbos->insert('Normal', 3);
$colaAmbos->insert('Urgente', 7);

echo "\nEXTR_BOTH (datos y prioridad):\n";
while (!$colaAmbos->isEmpty()) {
    $elemento = $colaAmbos->extract();
    echo "  [{$elemento['data']}] con prioridad {$elemento['priority']}\n";
}

// ============================================
// Ejemplo 3: Cola de prioridad personalizada
// ============================================

echo "\n=== Ejemplo 3: Cola de prioridad personalizada ===\n";

/**
 * Cola de prioridad invertida (menor número = mayor prioridad).
 * Útil para algoritmos como Dijkstra donde menor costo = mayor prioridad.
 */
class ColaPrioridadMinima extends SplPriorityQueue
{
    /**
     * Invertir la comparación: menor prioridad se extrae primero
     */
    public function compare(mixed $prioridad1, mixed $prioridad2): int
    {
        // Invertir: retornar negativo cuando prioridad1 > prioridad2
        return $prioridad2 <=> $prioridad1;
    }
}

$colaMin = new ColaPrioridadMinima();
$colaMin->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

$colaMin->insert('Nodo A', 15);
$colaMin->insert('Nodo B', 3);
$colaMin->insert('Nodo C', 8);
$colaMin->insert('Nodo D', 1);

echo "Extracción por prioridad mínima:\n";
while (!$colaMin->isEmpty()) {
    $elem = $colaMin->extract();
    echo "  {$elem['data']} (costo: {$elem['priority']})\n";
}

// ============================================
// Ejemplo 4: Caso práctico - Sistema de tickets de soporte
// ============================================

echo "\n=== Ejemplo 4: Sistema de tickets de soporte ===\n";

/**
 * Sistema de gestión de tickets con prioridad.
 * Las prioridades son: crítico(4), alto(3), medio(2), bajo(1).
 */
class SistemaTickets
{
    private SplPriorityQueue $cola;
    private int $siguienteId;

    // Mapeo de niveles de prioridad
    private const PRIORIDADES = [
        'crítico' => 4,
        'alto'    => 3,
        'medio'   => 2,
        'bajo'    => 1,
    ];

    public function __construct()
    {
        $this->cola = new SplPriorityQueue();
        $this->cola->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
        $this->siguienteId = 1;
    }

    public function crearTicket(string $titulo, string $nivel): void
    {
        $prioridad = self::PRIORIDADES[$nivel] ?? 1;
        $ticket = [
            'id'     => $this->siguienteId++,
            'titulo' => $titulo,
            'nivel'  => $nivel,
            'hora'   => date('H:i:s'),
        ];
        $this->cola->insert($ticket, $prioridad);
        echo "  Ticket #{$ticket['id']} creado: '$titulo' [$nivel]\n";
    }

    public function atenderSiguiente(): ?array
    {
        if ($this->cola->isEmpty()) {
            echo "  No hay tickets pendientes.\n";
            return null;
        }
        $resultado = $this->cola->extract();
        $ticket = $resultado['data'];
        echo "  Atendiendo Ticket #{$ticket['id']}: '{$ticket['titulo']}' [{$ticket['nivel']}]\n";
        return $ticket;
    }

    public function pendientes(): int
    {
        return $this->cola->count();
    }
}

$soporte = new SistemaTickets();

// Crear tickets con distintas prioridades
$soporte->crearTicket('Botón no funciona', 'bajo');
$soporte->crearTicket('Servidor caído', 'crítico');
$soporte->crearTicket('Error en reporte', 'medio');
$soporte->crearTicket('Fuga de datos', 'crítico');
$soporte->crearTicket('Interfaz desalineada', 'bajo');
$soporte->crearTicket('Lentitud en consultas', 'alto');

echo "\nAtendiendo tickets por prioridad:\n";
while ($soporte->pendientes() > 0) {
    $soporte->atenderSiguiente();
}

// ============================================
// Ejemplo 5: Prioridad con desempate por timestamp
// ============================================

echo "\n=== Ejemplo 5: Prioridad con desempate ===\n";

/**
 * Cuando dos elementos tienen la misma prioridad, se desempata
 * usando un segundo criterio (orden de inserción con timestamp).
 * Se usa un array [prioridad, secuencia] como valor de prioridad.
 */
$colaTiempo = new SplPriorityQueue();
$colaTiempo->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

$secuencia = 0;

// Insertar con prioridad compuesta: [nivel, orden_insercion]
// El segundo valor desempata por orden de llegada
$colaTiempo->insert('Tarea A', [5, $secuencia++]); // prioridad 5, llegó 1ro
$colaTiempo->insert('Tarea B', [5, $secuencia++]); // prioridad 5, llegó 2do
$colaTiempo->insert('Tarea C', [3, $secuencia++]); // prioridad 3
$colaTiempo->insert('Tarea D', [5, $secuencia++]); // prioridad 5, llegó 3ro

echo "Extracción con desempate por orden de llegada:\n";
while (!$colaTiempo->isEmpty()) {
    $elem = $colaTiempo->extract();
    $priNivel = $elem['priority'][0];
    $priOrden = $elem['priority'][1];
    echo "  {$elem['data']} - Nivel: $priNivel, Orden: $priOrden\n";
}

?>
