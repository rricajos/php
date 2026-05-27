# SPL (Standard PHP Library)

Estructuras de datos e iteradores especializados incluidos en la biblioteca estandar de PHP.

## Orden de lectura

1. **`SplFixedArray.php`** - Arreglo de tamano fijo con menor uso de memoria y acceso mas rapido
2. **`SplStack.php`** - Pila LIFO con push, pop y casos practicos de undo y verificacion de parentesis
3. **`SplQueue.php`** - Cola FIFO con enqueue, dequeue y caso practico de sistema de turnos
4. **`SplDoublyLinkedList.php`** - Lista doblemente enlazada con operaciones en ambos extremos y modos de iteracion
5. **`SplPriorityQueue.php`** - Cola de prioridad con modos de extraccion y desempate por timestamp
6. **`SplHeap.php`** - Monticulos SplMinHeap, SplMaxHeap y heaps personalizados con compare()
7. **`SplObjectStorage.php`** - Almacenamiento de objetos como claves con datos asociados y operaciones de conjunto
8. **`iterators.php`** - Iteradores SPL: ArrayIterator, FilterIterator, LimitIterator, RegexIterator y pipelines
