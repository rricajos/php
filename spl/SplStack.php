<?php
/**
 * SplStack - Pila LIFO (Last In, First Out)
 *
 * La clase SplStack implementa una pila donde el último elemento
 * agregado es el primero en ser removido.
 */

// ============================================
// Ejemplo 1: Operaciones básicas - push, pop, top
// ============================================

echo "=== Ejemplo 1: Operaciones básicas ===\n";

$pila = new SplStack();

// Agregar elementos a la pila con push()
$pila->push('Primero');
$pila->push('Segundo');
$pila->push('Tercero');

// top() devuelve el elemento en la cima sin removerlo
echo "Elemento en la cima: " . $pila->top() . "\n"; // Tercero

// pop() remueve y devuelve el elemento de la cima
$removido = $pila->pop();
echo "Elemento removido: " . $removido . "\n"; // Tercero

// Verificar el nuevo elemento en la cima
echo "Nueva cima: " . $pila->top() . "\n"; // Segundo

// ============================================
// Ejemplo 2: Contar elementos y verificar si está vacía
// ============================================

echo "\n=== Ejemplo 2: count() e isEmpty() ===\n";

$pilaNumeros = new SplStack();

echo "¿Pila vacía? " . ($pilaNumeros->isEmpty() ? 'Sí' : 'No') . "\n"; // Sí

$pilaNumeros->push(10);
$pilaNumeros->push(20);
$pilaNumeros->push(30);
$pilaNumeros->push(40);

echo "Cantidad de elementos: " . $pilaNumeros->count() . "\n"; // 4
echo "También con count(): " . count($pilaNumeros) . "\n"; // 4

// Remover un elemento y contar de nuevo
$pilaNumeros->pop();
echo "Después de pop(), cantidad: " . $pilaNumeros->count() . "\n"; // 3
echo "¿Pila vacía? " . ($pilaNumeros->isEmpty() ? 'Sí' : 'No') . "\n"; // No

// ============================================
// Ejemplo 3: Iterar sobre la pila
// ============================================

echo "\n=== Ejemplo 3: Iteración sobre la pila ===\n";

$pilaColores = new SplStack();
$pilaColores->push('Rojo');
$pilaColores->push('Verde');
$pilaColores->push('Azul');
$pilaColores->push('Amarillo');

// Iterar con foreach (orden LIFO por defecto)
echo "Iteración con foreach (LIFO):\n";
foreach ($pilaColores as $indice => $color) {
    echo "  [$indice] => $color\n";
}

// Iterar usando el iterador manualmente
echo "\nIteración manual con rewind/next:\n";
$pilaColores->rewind();
while ($pilaColores->valid()) {
    echo "  Posición " . $pilaColores->key() . ": " . $pilaColores->current() . "\n";
    $pilaColores->next();
}

// ============================================
// Ejemplo 4: Caso práctico - Verificar paréntesis balanceados
// ============================================

echo "\n=== Ejemplo 4: Verificar paréntesis balanceados ===\n";

/**
 * Verifica si una cadena tiene paréntesis, corchetes y llaves balanceados
 * usando una pila SplStack.
 */
function verificarBalanceo(string $expresion): bool
{
    $pila = new SplStack();
    $apertura = ['(', '[', '{'];
    $cierre = [')', ']', '}'];
    $pares = [')' => '(', ']' => '[', '}' => '{'];

    for ($i = 0; $i < strlen($expresion); $i++) {
        $caracter = $expresion[$i];

        if (in_array($caracter, $apertura)) {
            $pila->push($caracter);
        } elseif (in_array($caracter, $cierre)) {
            if ($pila->isEmpty()) {
                return false;
            }
            if ($pila->top() !== $pares[$caracter]) {
                return false;
            }
            $pila->pop();
        }
    }

    return $pila->isEmpty();
}

$expresiones = [
    '((2 + 3) * [4 - 1])',  // Balanceado
    '{a + (b * c)}',         // Balanceado
    '((a + b)',              // No balanceado
    '{[()]}',                // Balanceado
    '([)]',                  // No balanceado
];

foreach ($expresiones as $exp) {
    $resultado = verificarBalanceo($exp) ? 'Balanceado' : 'No balanceado';
    echo "  '$exp' => $resultado\n";
}

// ============================================
// Ejemplo 5: Caso práctico - Deshacer acciones (Undo)
// ============================================

echo "\n=== Ejemplo 5: Sistema de deshacer (Undo) ===\n";

/**
 * Clase que simula un editor de texto simple con funcionalidad
 * de deshacer usando SplStack.
 */
class EditorTexto
{
    private string $contenido = '';
    private SplStack $historial;

    public function __construct()
    {
        $this->historial = new SplStack();
    }

    public function escribir(string $texto): void
    {
        // Guardar estado actual en el historial antes de modificar
        $this->historial->push($this->contenido);
        $this->contenido .= $texto;
        echo "  Escribiendo: '$texto' | Contenido actual: '{$this->contenido}'\n";
    }

    public function deshacer(): void
    {
        if ($this->historial->isEmpty()) {
            echo "  No hay acciones para deshacer.\n";
            return;
        }
        $this->contenido = $this->historial->pop();
        echo "  Deshaciendo... | Contenido actual: '{$this->contenido}'\n";
    }

    public function obtenerContenido(): string
    {
        return $this->contenido;
    }

    public function accionesPendientes(): int
    {
        return $this->historial->count();
    }
}

$editor = new EditorTexto();
$editor->escribir('Hola ');
$editor->escribir('mundo ');
$editor->escribir('cruel');

echo "  Acciones en historial: " . $editor->accionesPendientes() . "\n";

$editor->deshacer();
$editor->deshacer();

echo "  Contenido final: '" . $editor->obtenerContenido() . "'\n";
echo "  Acciones restantes: " . $editor->accionesPendientes() . "\n";

?>
