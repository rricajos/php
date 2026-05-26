<?php
/**
 * SplHeap - Montículos (Heaps)
 *
 * SplHeap es una clase abstracta. PHP provee dos implementaciones concretas:
 * - SplMinHeap: el valor mínimo está en la cima (se extrae primero)
 * - SplMaxHeap: el valor máximo está en la cima (se extrae primero)
 *
 * Los heaps son ideales para obtener el mínimo/máximo eficientemente.
 */

// ============================================
// Ejemplo 1: SplMinHeap - Montículo mínimo
// ============================================

echo "=== Ejemplo 1: SplMinHeap ===\n";

$minHeap = new SplMinHeap();

// Insertar elementos (se reorganizan automáticamente)
$minHeap->insert(45);
$minHeap->insert(12);
$minHeap->insert(78);
$minHeap->insert(3);
$minHeap->insert(56);
$minHeap->insert(1);

echo "Cantidad de elementos: " . $minHeap->count() . "\n";

// top() muestra el mínimo sin removerlo
echo "Valor mínimo (top): " . $minHeap->top() . "\n"; // 1

// extract() remueve y devuelve el mínimo
echo "\nExtracción ordenada (menor a mayor):\n";
while (!$minHeap->isEmpty()) {
    echo "  " . $minHeap->extract() . "\n";
}

// ============================================
// Ejemplo 2: SplMaxHeap - Montículo máximo
// ============================================

echo "\n=== Ejemplo 2: SplMaxHeap ===\n";

$maxHeap = new SplMaxHeap();

$valores = [30, 10, 80, 50, 20, 90, 40];

foreach ($valores as $val) {
    $maxHeap->insert($val);
}

echo "Valores insertados: " . implode(', ', $valores) . "\n";
echo "Valor máximo (top): " . $maxHeap->top() . "\n"; // 90

// Extraer los 3 mayores
echo "\nTop 3 valores:\n";
for ($i = 1; $i <= 3 && !$maxHeap->isEmpty(); $i++) {
    echo "  #$i: " . $maxHeap->extract() . "\n";
}

echo "Elementos restantes: " . $maxHeap->count() . "\n";

// ============================================
// Ejemplo 3: Heap personalizado con compare()
// ============================================

echo "\n=== Ejemplo 3: Heap personalizado ===\n";

/**
 * Heap personalizado que ordena strings por longitud (más corto primero).
 * Se extiende SplHeap y se implementa el método compare().
 */
class HeapPorLongitud extends SplHeap
{
    /**
     * Comparar elementos: retornar positivo si $valor2 tiene prioridad,
     * negativo si $valor1 tiene prioridad, 0 si son iguales.
     * Para min-heap por longitud: el más corto tiene mayor prioridad.
     */
    protected function compare(mixed $valor1, mixed $valor2): int
    {
        // Menor longitud = mayor prioridad (se extrae primero)
        return strlen($valor2) - strlen($valor1);
    }
}

$heapTextos = new HeapPorLongitud();
$heapTextos->insert('Elefante');
$heapTextos->insert('Gato');
$heapTextos->insert('Rinoceronte');
$heapTextos->insert('Oso');
$heapTextos->insert('Hipopótamo');

echo "Extracción por longitud (más corto primero):\n";
while (!$heapTextos->isEmpty()) {
    $palabra = $heapTextos->extract();
    echo "  '$palabra' (longitud: " . strlen($palabra) . ")\n";
}

/**
 * Heap que ordena arrays por un campo específico (edad, de mayor a menor).
 */
class HeapPorEdad extends SplHeap
{
    protected function compare(mixed $valor1, mixed $valor2): int
    {
        // Mayor edad = mayor prioridad
        return $valor1['edad'] - $valor2['edad'];
    }
}

echo "\nOrdenar personas por edad (mayor primero):\n";

$heapPersonas = new HeapPorEdad();
$heapPersonas->insert(['nombre' => 'Ana', 'edad' => 28]);
$heapPersonas->insert(['nombre' => 'Luis', 'edad' => 45]);
$heapPersonas->insert(['nombre' => 'María', 'edad' => 33]);
$heapPersonas->insert(['nombre' => 'Pedro', 'edad' => 19]);
$heapPersonas->insert(['nombre' => 'Carmen', 'edad' => 52]);

while (!$heapPersonas->isEmpty()) {
    $persona = $heapPersonas->extract();
    echo "  {$persona['nombre']} - {$persona['edad']} años\n";
}

// ============================================
// Ejemplo 4: Caso práctico - Encontrar los N elementos más frecuentes
// ============================================

echo "\n=== Ejemplo 4: N elementos más frecuentes ===\n";

/**
 * Encuentra los N elementos más frecuentes de un arreglo
 * usando un MinHeap de tamaño limitado.
 */
function elementosMasFrecuentes(array $elementos, int $n): array
{
    // Contar frecuencias
    $frecuencias = array_count_values($elementos);

    // Usar un MaxHeap para ordenar por frecuencia
    $heap = new class extends SplHeap {
        protected function compare(mixed $a, mixed $b): int
        {
            return $a['frecuencia'] - $b['frecuencia'];
        }
    };

    foreach ($frecuencias as $elemento => $frecuencia) {
        $heap->insert(['elemento' => $elemento, 'frecuencia' => $frecuencia]);
    }

    // Extraer los N más frecuentes
    $resultado = [];
    for ($i = 0; $i < $n && !$heap->isEmpty(); $i++) {
        $resultado[] = $heap->extract();
    }

    return $resultado;
}

$datos = ['manzana', 'banana', 'manzana', 'cereza', 'banana', 'manzana',
          'durazno', 'banana', 'cereza', 'manzana', 'durazno', 'banana'];

echo "Datos: " . implode(', ', $datos) . "\n\n";

$topN = elementosMasFrecuentes($datos, 3);
echo "Top 3 más frecuentes:\n";
foreach ($topN as $i => $item) {
    $pos = $i + 1;
    echo "  #$pos: '{$item['elemento']}' ({$item['frecuencia']} veces)\n";
}

// ============================================
// Ejemplo 5: Caso práctico - Mediana dinámica con dos heaps
// ============================================

echo "\n=== Ejemplo 5: Mediana dinámica con dos heaps ===\n";

/**
 * Calcula la mediana de un flujo de números de forma eficiente
 * usando un MaxHeap para la mitad inferior y un MinHeap para la superior.
 */
class MedianaDinamica
{
    private SplMaxHeap $mitadInferior; // Almacena la mitad menor (max en la cima)
    private SplMinHeap $mitadSuperior; // Almacena la mitad mayor (min en la cima)

    public function __construct()
    {
        $this->mitadInferior = new SplMaxHeap();
        $this->mitadSuperior = new SplMinHeap();
    }

    /**
     * Agrega un número al flujo y mantiene el balance
     */
    public function agregar(int|float $numero): void
    {
        // Si la mitad inferior está vacía o el número es menor que su cima
        if ($this->mitadInferior->isEmpty() || $numero <= $this->mitadInferior->top()) {
            $this->mitadInferior->insert($numero);
        } else {
            $this->mitadSuperior->insert($numero);
        }

        // Balancear: la diferencia de tamaños no debe ser mayor a 1
        if ($this->mitadInferior->count() > $this->mitadSuperior->count() + 1) {
            $this->mitadSuperior->insert($this->mitadInferior->extract());
        } elseif ($this->mitadSuperior->count() > $this->mitadInferior->count()) {
            $this->mitadInferior->insert($this->mitadSuperior->extract());
        }
    }

    /**
     * Obtiene la mediana actual
     */
    public function obtenerMediana(): float
    {
        if ($this->mitadInferior->count() === $this->mitadSuperior->count()) {
            // Par de elementos: promedio de las dos cimas
            return ($this->mitadInferior->top() + $this->mitadSuperior->top()) / 2.0;
        }
        // Impar: la cima de la mitad inferior es la mediana
        return (float) $this->mitadInferior->top();
    }
}

$mediana = new MedianaDinamica();
$flujo = [5, 15, 1, 3, 8, 7, 9, 10, 6, 2];

echo "Mediana dinámica al agregar cada número:\n";
foreach ($flujo as $num) {
    $mediana->agregar($num);
    echo "  Agregar $num => Mediana actual: " . $mediana->obtenerMediana() . "\n";
}

?>
