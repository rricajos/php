<?php
/**
 * SplDoublyLinkedList - Lista doblemente enlazada
 *
 * Permite agregar y remover elementos tanto del inicio como del final.
 * Cada nodo tiene referencias al nodo anterior y al siguiente.
 * Es la clase base de SplStack y SplQueue.
 */

// ============================================
// Ejemplo 1: push/pop (operaciones al final)
// ============================================

echo "=== Ejemplo 1: push/pop (final de la lista) ===\n";

$lista = new SplDoublyLinkedList();

// push() agrega al final de la lista
$lista->push('A');
$lista->push('B');
$lista->push('C');
$lista->push('D');

echo "Lista después de push A, B, C, D:\n";
foreach ($lista as $indice => $valor) {
    echo "  [$indice] => $valor\n";
}

// pop() remueve y devuelve el último elemento
$ultimo = $lista->pop();
echo "\npop() removió: $ultimo\n"; // D

// top() devuelve el último sin removerlo
echo "Último ahora (top): " . $lista->top() . "\n"; // C

// bottom() devuelve el primero sin removerlo
echo "Primero (bottom): " . $lista->bottom() . "\n"; // A

// ============================================
// Ejemplo 2: shift/unshift (operaciones al inicio)
// ============================================

echo "\n=== Ejemplo 2: shift/unshift (inicio de la lista) ===\n";

$lista2 = new SplDoublyLinkedList();

// unshift() agrega al inicio de la lista
$lista2->unshift('Tercero');
$lista2->unshift('Segundo');
$lista2->unshift('Primero');

echo "Lista después de unshift:\n";
foreach ($lista2 as $i => $val) {
    echo "  [$i] => $val\n";
}

// shift() remueve y devuelve el primer elemento
$primero = $lista2->shift();
echo "\nshift() removió: $primero\n"; // Primero

echo "Nuevo primero (bottom): " . $lista2->bottom() . "\n"; // Segundo
echo "Cantidad: " . $lista2->count() . "\n"; // 2

// Combinar push y unshift
$lista2->push('Final');       // Agregar al final
$lista2->unshift('Inicio');   // Agregar al inicio

echo "\nLista combinada:\n";
foreach ($lista2 as $i => $val) {
    echo "  [$i] => $val\n";
}

// ============================================
// Ejemplo 3: Acceso por índice y modificación
// ============================================

echo "\n=== Ejemplo 3: Acceso y modificación por índice ===\n";

$lista3 = new SplDoublyLinkedList();
$lista3->push('Rojo');
$lista3->push('Verde');
$lista3->push('Azul');
$lista3->push('Negro');

// Acceso por índice con offsetGet / notación de array
echo "Elemento [0]: " . $lista3[0] . "\n";            // Rojo
echo "Elemento [2]: " . $lista3->offsetGet(2) . "\n"; // Azul

// Verificar si existe un índice
echo "¿Existe [1]? " . ($lista3->offsetExists(1) ? 'Sí' : 'No') . "\n"; // Sí
echo "¿Existe [10]? " . ($lista3->offsetExists(10) ? 'Sí' : 'No') . "\n"; // No

// Modificar un elemento
$lista3[1] = 'Amarillo';
echo "\nDespués de cambiar [1] a 'Amarillo':\n";
foreach ($lista3 as $i => $val) {
    echo "  [$i] => $val\n";
}

// Eliminar por índice
$lista3->offsetUnset(2); // Eliminar 'Azul'
echo "\nDespués de eliminar [2]:\n";
foreach ($lista3 as $i => $val) {
    echo "  [$i] => $val\n";
}

// Agregar en posición específica
$lista3->add(1, 'Insertado');
echo "\nDespués de insertar en posición 1:\n";
foreach ($lista3 as $i => $val) {
    echo "  [$i] => $val\n";
}

// ============================================
// Ejemplo 4: Modos de iteración (IT_MODE)
// ============================================

echo "\n=== Ejemplo 4: Modos de iteración ===\n";

$lista4 = new SplDoublyLinkedList();
$lista4->push('Uno');
$lista4->push('Dos');
$lista4->push('Tres');
$lista4->push('Cuatro');

// Modo FIFO (inicio a fin) - por defecto
$lista4->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
echo "IT_MODE_FIFO (inicio a fin):\n";
foreach ($lista4 as $i => $val) {
    echo "  [$i] => $val\n";
}

// Modo LIFO (fin a inicio)
$lista4->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
echo "\nIT_MODE_LIFO (fin a inicio):\n";
foreach ($lista4 as $i => $val) {
    echo "  [$i] => $val\n";
}

// Modo DELETE: elimina elementos al iterar
echo "\nIT_MODE_FIFO | IT_MODE_DELETE (itera y elimina):\n";
$listaDelete = new SplDoublyLinkedList();
$listaDelete->push('X');
$listaDelete->push('Y');
$listaDelete->push('Z');

$listaDelete->setIteratorMode(
    SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_DELETE
);

echo "Antes de iterar, cantidad: " . $listaDelete->count() . "\n";
foreach ($listaDelete as $i => $val) {
    echo "  Procesando: $val\n";
}
echo "Después de iterar, cantidad: " . $listaDelete->count() . "\n"; // 0

// ============================================
// Ejemplo 5: Caso práctico - Historial de navegación
// ============================================

echo "\n=== Ejemplo 5: Historial de navegación ===\n";

/**
 * Simula el historial de navegación de un navegador web
 * usando una lista doblemente enlazada.
 * Permite ir hacia adelante y hacia atrás.
 */
class HistorialNavegacion
{
    private SplDoublyLinkedList $paginas;
    private int $posicionActual;

    public function __construct()
    {
        $this->paginas = new SplDoublyLinkedList();
        $this->posicionActual = -1;
    }

    /**
     * Navegar a una nueva página.
     * Si estamos en medio del historial, elimina las páginas adelante.
     */
    public function navegar(string $url): void
    {
        // Eliminar páginas "adelante" si estamos en medio del historial
        while ($this->paginas->count() - 1 > $this->posicionActual && !$this->paginas->isEmpty()) {
            $this->paginas->pop();
        }
        $this->paginas->push($url);
        $this->posicionActual = $this->paginas->count() - 1;
        echo "  Navegando a: $url\n";
    }

    /**
     * Ir a la página anterior
     */
    public function atras(): ?string
    {
        if ($this->posicionActual <= 0) {
            echo "  No hay páginas anteriores.\n";
            return null;
        }
        $this->posicionActual--;
        $url = $this->paginas[$this->posicionActual];
        echo "  Atrás => $url\n";
        return $url;
    }

    /**
     * Ir a la página siguiente
     */
    public function adelante(): ?string
    {
        if ($this->posicionActual >= $this->paginas->count() - 1) {
            echo "  No hay páginas siguientes.\n";
            return null;
        }
        $this->posicionActual++;
        $url = $this->paginas[$this->posicionActual];
        echo "  Adelante => $url\n";
        return $url;
    }

    public function paginaActual(): string
    {
        return $this->paginas[$this->posicionActual] ?? '(ninguna)';
    }

    public function mostrarHistorial(): void
    {
        echo "  Historial completo:\n";
        foreach ($this->paginas as $i => $url) {
            $marca = ($i === $this->posicionActual) ? ' <-- actual' : '';
            echo "    [$i] $url$marca\n";
        }
    }
}

$historial = new HistorialNavegacion();

$historial->navegar('https://inicio.com');
$historial->navegar('https://productos.com');
$historial->navegar('https://contacto.com');
$historial->navegar('https://blog.com');

echo "\n";
$historial->mostrarHistorial();

echo "\n";
$historial->atras();
$historial->atras();

echo "\nPágina actual: " . $historial->paginaActual() . "\n\n";

// Navegar a nueva página desde el medio del historial
$historial->navegar('https://ofertas.com');
$historial->mostrarHistorial();

?>
