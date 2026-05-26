<?php
/**
 * SplObjectStorage - Almacenamiento de objetos
 *
 * Permite almacenar objetos como claves (algo que no se puede hacer
 * con arrays nativos). Opcionalmente, cada objeto puede tener datos asociados.
 * Usa la identidad del objeto (no su valor) para determinar unicidad.
 */

// ============================================
// Ejemplo 1: Operaciones básicas - attach, detach, contains
// ============================================

echo "=== Ejemplo 1: Operaciones básicas ===\n";

$almacen = new SplObjectStorage();

// Crear algunos objetos
$obj1 = new stdClass();
$obj1->nombre = 'Objeto A';

$obj2 = new stdClass();
$obj2->nombre = 'Objeto B';

$obj3 = new stdClass();
$obj3->nombre = 'Objeto C';

// attach() agrega un objeto al almacén
$almacen->attach($obj1);
$almacen->attach($obj2);
$almacen->attach($obj3);

echo "Cantidad de objetos: " . $almacen->count() . "\n"; // 3

// contains() verifica si un objeto está en el almacén
echo "¿Contiene obj1? " . ($almacen->contains($obj1) ? 'Sí' : 'No') . "\n"; // Sí
echo "¿Contiene obj2? " . ($almacen->contains($obj2) ? 'Sí' : 'No') . "\n"; // Sí

// detach() remueve un objeto
$almacen->detach($obj2);
echo "\nDespués de detach(obj2):\n";
echo "¿Contiene obj2? " . ($almacen->contains($obj2) ? 'Sí' : 'No') . "\n"; // No
echo "Cantidad: " . $almacen->count() . "\n"; // 2

// Agregar el mismo objeto dos veces no lo duplica
$almacen->attach($obj1);
echo "Después de attach(obj1) duplicado, cantidad: " . $almacen->count() . "\n"; // 2

// ============================================
// Ejemplo 2: Asociar datos a objetos
// ============================================

echo "\n=== Ejemplo 2: Datos asociados a objetos ===\n";

$almacenConDatos = new SplObjectStorage();

class Producto
{
    public function __construct(
        public string $nombre,
        public float $precio
    ) {}
}

$producto1 = new Producto('Laptop', 999.99);
$producto2 = new Producto('Mouse', 29.99);
$producto3 = new Producto('Teclado', 79.99);

// attach() con segundo parámetro: datos asociados
$almacenConDatos->attach($producto1, ['cantidad' => 5, 'ubicacion' => 'Estante A']);
$almacenConDatos->attach($producto2, ['cantidad' => 50, 'ubicacion' => 'Estante B']);
$almacenConDatos->attach($producto3, ['cantidad' => 20, 'ubicacion' => 'Estante C']);

// Obtener datos asociados a un objeto
$datos = $almacenConDatos[$producto1]; // Acceso con notación de array
echo "Datos de '{$producto1->nombre}':\n";
echo "  Cantidad: {$datos['cantidad']}\n";
echo "  Ubicación: {$datos['ubicacion']}\n";

// Modificar datos asociados
$almacenConDatos[$producto2] = ['cantidad' => 48, 'ubicacion' => 'Estante B'];
$datosActualizados = $almacenConDatos[$producto2];
echo "\nDatos actualizados de '{$producto2->nombre}':\n";
echo "  Cantidad: {$datosActualizados['cantidad']}\n";

// ============================================
// Ejemplo 3: Iterar sobre el almacén
// ============================================

echo "\n=== Ejemplo 3: Iteración ===\n";

$almacenIter = new SplObjectStorage();

$claseA = new stdClass();
$claseA->tipo = 'Clase A';
$claseB = new stdClass();
$claseB->tipo = 'Clase B';
$claseC = new stdClass();
$claseC->tipo = 'Clase C';

$almacenIter->attach($claseA, 'Datos de A');
$almacenIter->attach($claseB, 'Datos de B');
$almacenIter->attach($claseC, 'Datos de C');

// Iterar con foreach
echo "Iteración con foreach:\n";
foreach ($almacenIter as $objeto) {
    $datos = $almacenIter->getInfo(); // Obtener datos asociados al objeto actual
    echo "  {$objeto->tipo} => $datos\n";
}

// Iterar manualmente con rewind/next
echo "\nIteración manual:\n";
$almacenIter->rewind();
while ($almacenIter->valid()) {
    $objeto = $almacenIter->current();
    $info = $almacenIter->getInfo();
    $hash = $almacenIter->getHash($objeto);
    echo "  Hash: " . substr($hash, 0, 16) . "... | {$objeto->tipo} | $info\n";
    $almacenIter->next();
}

// ============================================
// Ejemplo 4: Caso práctico - Sistema de observadores (Observer Pattern)
// ============================================

echo "\n=== Ejemplo 4: Patrón Observer ===\n";

/**
 * Interfaz para observadores que reciben notificaciones.
 */
interface Observador
{
    public function actualizar(string $evento, mixed $datos): void;
}

/**
 * Sujeto observable que mantiene una lista de observadores
 * usando SplObjectStorage.
 */
class EventoSistema
{
    private SplObjectStorage $observadores;
    private string $nombre;

    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
        $this->observadores = new SplObjectStorage();
    }

    public function suscribir(Observador $obs, string $filtro = '*'): void
    {
        $this->observadores->attach($obs, $filtro);
        echo "  [+] Observador suscrito con filtro: $filtro\n";
    }

    public function desuscribir(Observador $obs): void
    {
        $this->observadores->detach($obs);
        echo "  [-] Observador desuscrito\n";
    }

    public function notificar(string $evento, mixed $datos = null): void
    {
        echo "\n  Notificando evento: '$evento'\n";
        foreach ($this->observadores as $observador) {
            $filtro = $this->observadores->getInfo();
            if ($filtro === '*' || $filtro === $evento) {
                $observador->actualizar($evento, $datos);
            }
        }
    }

    public function contarSuscriptores(): int
    {
        return $this->observadores->count();
    }
}

// Implementaciones concretas de observadores
class LogObservador implements Observador
{
    public function actualizar(string $evento, mixed $datos): void
    {
        echo "    [LOG] Evento '$evento': " . json_encode($datos) . "\n";
    }
}

class EmailObservador implements Observador
{
    public function actualizar(string $evento, mixed $datos): void
    {
        echo "    [EMAIL] Enviando notificación por correo: $evento\n";
    }
}

class MetricasObservador implements Observador
{
    public function actualizar(string $evento, mixed $datos): void
    {
        echo "    [METRICAS] Registrando métrica para: $evento\n";
    }
}

$sistema = new EventoSistema('Mi Aplicación');

$logger = new LogObservador();
$email = new EmailObservador();
$metricas = new MetricasObservador();

$sistema->suscribir($logger, '*');           // Recibe todos los eventos
$sistema->suscribir($email, 'usuario.nuevo'); // Solo usuarios nuevos
$sistema->suscribir($metricas, '*');          // Recibe todos los eventos

echo "Suscriptores: " . $sistema->contarSuscriptores() . "\n";

$sistema->notificar('usuario.nuevo', ['nombre' => 'Carlos', 'email' => 'carlos@mail.com']);
$sistema->notificar('pedido.creado', ['id' => 1234, 'total' => 99.99]);

// Desuscribir email
echo "\n";
$sistema->desuscribir($email);
$sistema->notificar('usuario.nuevo', ['nombre' => 'Ana']);

// ============================================
// Ejemplo 5: Operaciones de conjunto (unión, diferencia)
// ============================================

echo "\n=== Ejemplo 5: Operaciones de conjunto ===\n";

$conjuntoA = new SplObjectStorage();
$conjuntoB = new SplObjectStorage();

$rojo = new stdClass(); $rojo->color = 'Rojo';
$azul = new stdClass(); $azul->color = 'Azul';
$verde = new stdClass(); $verde->color = 'Verde';
$amarillo = new stdClass(); $amarillo->color = 'Amarillo';

$conjuntoA->attach($rojo);
$conjuntoA->attach($azul);
$conjuntoA->attach($verde);

$conjuntoB->attach($azul);
$conjuntoB->attach($verde);
$conjuntoB->attach($amarillo);

echo "Conjunto A: ";
foreach ($conjuntoA as $obj) echo $obj->color . " ";
echo "\n";

echo "Conjunto B: ";
foreach ($conjuntoB as $obj) echo $obj->color . " ";
echo "\n";

// Unión: addAll() agrega todos los objetos de otro almacén
$union = new SplObjectStorage();
$union->addAll($conjuntoA);
$union->addAll($conjuntoB);
echo "\nUnión (A ∪ B): ";
foreach ($union as $obj) echo $obj->color . " ";
echo " [" . $union->count() . " elementos]\n";

// Diferencia: removeAll() remueve los objetos que están en otro almacén
$diferencia = new SplObjectStorage();
$diferencia->addAll($conjuntoA);
$diferencia->removeAll($conjuntoB);
echo "Diferencia (A - B): ";
foreach ($diferencia as $obj) echo $obj->color . " ";
echo " [" . $diferencia->count() . " elementos]\n";

// Intersección: removeAllExcept() mantiene solo los que están en ambos
$interseccion = new SplObjectStorage();
$interseccion->addAll($conjuntoA);
$interseccion->removeAllExcept($conjuntoB);
echo "Intersección (A ∩ B): ";
foreach ($interseccion as $obj) echo $obj->color . " ";
echo " [" . $interseccion->count() . " elementos]\n";

?>
