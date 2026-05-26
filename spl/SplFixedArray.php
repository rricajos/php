<?php
/**
 * SplFixedArray - Arreglo de tamaño fijo
 *
 * A diferencia de los arrays nativos de PHP (que son realmente hash maps),
 * SplFixedArray usa un arreglo C real con índices enteros consecutivos.
 * Esto resulta en menor uso de memoria y acceso más rápido.
 */

// ============================================
// Ejemplo 1: Crear y acceder elementos
// ============================================

echo "=== Ejemplo 1: Crear y acceder ===\n";

// Crear un arreglo fijo de tamaño 5
$arreglo = new SplFixedArray(5);

// Asignar valores por índice (solo índices enteros de 0 a n-1)
$arreglo[0] = 'Lunes';
$arreglo[1] = 'Martes';
$arreglo[2] = 'Miércoles';
$arreglo[3] = 'Jueves';
$arreglo[4] = 'Viernes';

echo "Tamaño: " . $arreglo->getSize() . "\n";       // 5
echo "count(): " . count($arreglo) . "\n";            // 5
echo "Elemento [0]: " . $arreglo[0] . "\n";           // Lunes
echo "Elemento [3]: " . $arreglo[3] . "\n";           // Jueves

// Verificar si existe un índice (isset no funciona con null, usar offsetExists)
echo "¿Existe [2]? " . (isset($arreglo[2]) ? 'Sí' : 'No') . "\n"; // Sí

// Intentar acceder fuera de rango genera una excepción
try {
    $valor = $arreglo[10];
} catch (RuntimeException $e) {
    echo "Error al acceder índice 10: " . $e->getMessage() . "\n";
}

// ============================================
// Ejemplo 2: Redimensionar el arreglo
// ============================================

echo "\n=== Ejemplo 2: Redimensionar ===\n";

$numeros = new SplFixedArray(3);
$numeros[0] = 100;
$numeros[1] = 200;
$numeros[2] = 300;

echo "Tamaño original: " . $numeros->getSize() . "\n"; // 3

// Agrandar el arreglo (los nuevos elementos son null)
$numeros->setSize(5);
echo "Nuevo tamaño: " . $numeros->getSize() . "\n"; // 5

$numeros[3] = 400;
$numeros[4] = 500;

echo "Todos los elementos:\n";
for ($i = 0; $i < $numeros->getSize(); $i++) {
    echo "  [$i] => " . ($numeros[$i] ?? 'null') . "\n";
}

// Reducir el arreglo (se pierden los elementos excedentes)
$numeros->setSize(2);
echo "\nDespués de reducir a 2:\n";
for ($i = 0; $i < $numeros->getSize(); $i++) {
    echo "  [$i] => {$numeros[$i]}\n";
}

// ============================================
// Ejemplo 3: Conversión desde y hacia array nativo
// ============================================

echo "\n=== Ejemplo 3: Conversión con array nativo ===\n";

// Crear desde un array nativo
$arrayNativo = ['PHP', 'Python', 'JavaScript', 'Go', 'Rust'];
$arregloFijo = SplFixedArray::fromArray($arrayNativo);

echo "Creado desde array nativo:\n";
foreach ($arregloFijo as $indice => $lenguaje) {
    echo "  [$indice] => $lenguaje\n";
}

// Convertir de vuelta a array nativo
$deVuelta = $arregloFijo->toArray();
echo "\nConvertido a array nativo: " . implode(', ', $deVuelta) . "\n";
echo "Tipo: " . gettype($deVuelta) . "\n"; // array

// fromArray con preservación de claves
$arrayConClaves = [2 => 'A', 5 => 'B', 8 => 'C'];
// preserveKeys = true crea arreglo de tamaño max_key + 1
$fijo = SplFixedArray::fromArray($arrayConClaves, true);
echo "\nDesde array con claves (tamaño " . $fijo->getSize() . "):\n";
for ($i = 0; $i < $fijo->getSize(); $i++) {
    echo "  [$i] => " . ($fijo[$i] ?? 'null') . "\n";
}

// ============================================
// Ejemplo 4: Comparación de rendimiento vs array nativo
// ============================================

echo "\n=== Ejemplo 4: Rendimiento vs array nativo ===\n";

$tamano = 500000;

// Prueba con SplFixedArray
$inicio = microtime(true);
$memoriaInicio = memory_get_usage();

$fijo = new SplFixedArray($tamano);
for ($i = 0; $i < $tamano; $i++) {
    $fijo[$i] = $i * 2;
}

$memoriaFijo = memory_get_usage() - $memoriaInicio;
$tiempoFijo = microtime(true) - $inicio;

// Liberar memoria
unset($fijo);

// Prueba con array nativo
$inicio = microtime(true);
$memoriaInicio = memory_get_usage();

$nativo = [];
for ($i = 0; $i < $tamano; $i++) {
    $nativo[$i] = $i * 2;
}

$memoriaNativo = memory_get_usage() - $memoriaInicio;
$tiempoNativo = microtime(true) - $inicio;

unset($nativo);

echo "Elementos: " . number_format($tamano) . "\n\n";
echo "SplFixedArray:\n";
echo "  Tiempo: " . number_format($tiempoFijo * 1000, 2) . " ms\n";
echo "  Memoria: " . number_format($memoriaFijo / 1024, 2) . " KB\n\n";
echo "Array nativo:\n";
echo "  Tiempo: " . number_format($tiempoNativo * 1000, 2) . " ms\n";
echo "  Memoria: " . number_format($memoriaNativo / 1024, 2) . " KB\n\n";

$ahorroMemoria = (1 - $memoriaFijo / $memoriaNativo) * 100;
echo "Ahorro de memoria con SplFixedArray: " . number_format($ahorroMemoria, 1) . "%\n";

// ============================================
// Ejemplo 5: Caso práctico - Matriz bidimensional con SplFixedArray
// ============================================

echo "\n=== Ejemplo 5: Matriz bidimensional ===\n";

/**
 * Implementación de una matriz bidimensional eficiente
 * usando SplFixedArray anidados.
 */
class MatrizFija
{
    private SplFixedArray $datos;
    private int $filas;
    private int $columnas;

    public function __construct(int $filas, int $columnas)
    {
        $this->filas = $filas;
        $this->columnas = $columnas;

        // Crear arreglo de filas, cada una es un SplFixedArray
        $this->datos = new SplFixedArray($filas);
        for ($i = 0; $i < $filas; $i++) {
            $this->datos[$i] = new SplFixedArray($columnas);
        }
    }

    public function establecer(int $fila, int $col, mixed $valor): void
    {
        $this->validarIndices($fila, $col);
        $this->datos[$fila][$col] = $valor;
    }

    public function obtener(int $fila, int $col): mixed
    {
        $this->validarIndices($fila, $col);
        return $this->datos[$fila][$col];
    }

    private function validarIndices(int $fila, int $col): void
    {
        if ($fila < 0 || $fila >= $this->filas || $col < 0 || $col >= $this->columnas) {
            throw new OutOfRangeException(
                "Índice fuera de rango: [$fila][$col] en matriz {$this->filas}x{$this->columnas}"
            );
        }
    }

    public function mostrar(): void
    {
        for ($i = 0; $i < $this->filas; $i++) {
            $fila = [];
            for ($j = 0; $j < $this->columnas; $j++) {
                $fila[] = str_pad($this->datos[$i][$j] ?? '.', 4, ' ', STR_PAD_LEFT);
            }
            echo "  " . implode(' ', $fila) . "\n";
        }
    }
}

// Crear y llenar una matriz 4x4 (tabla de multiplicar)
$matriz = new MatrizFija(4, 4);

for ($i = 0; $i < 4; $i++) {
    for ($j = 0; $j < 4; $j++) {
        $matriz->establecer($i, $j, ($i + 1) * ($j + 1));
    }
}

echo "Tabla de multiplicar (4x4):\n";
$matriz->mostrar();

echo "\nValor en [2][3]: " . $matriz->obtener(2, 3) . "\n"; // 3 * 4 = 12

// Intentar acceder fuera de rango
try {
    $matriz->obtener(10, 0);
} catch (OutOfRangeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
