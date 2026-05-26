<?php
// ============================================
// TIPOS DE INTERSECCIÓN EN PHP 8.1
// Type1&Type2, uso práctico con interfaces
// ============================================

// --- Ejemplo 1: Concepto básico de intersección ---
// Un tipo de intersección exige que el valor implemente TODAS las interfaces

interface Contable {
    public function contar(): int;
}

interface Iterable2 {
    public function elementos(): array;
}

interface Serializable2 {
    public function serializar(): string;
}

// Esta clase implementa las tres interfaces
class Coleccion implements Contable, Iterable2, Serializable2 {
    private array $items;

    public function __construct(array $items = []) {
        $this->items = $items;
    }

    public function contar(): int {
        return count($this->items);
    }

    public function elementos(): array {
        return $this->items;
    }

    public function serializar(): string {
        return json_encode($this->items);
    }

    public function agregar(mixed $item): void {
        $this->items[] = $item;
    }
}

// La función exige que el parámetro implemente AMBAS interfaces
// Tipo de intersección: Contable&Iterable2
function procesarColeccion(Contable&Iterable2 $datos): void {
    echo "Total de elementos: {$datos->contar()}\n";
    echo "Elementos: " . implode(', ', $datos->elementos()) . "\n";
}

// Exigimos que sea contable Y serializable
function guardarColeccion(Contable&Serializable2 $datos): void {
    echo "Guardando {$datos->contar()} elementos...\n";
    echo "Datos: {$datos->serializar()}\n";
}

$miColeccion = new Coleccion(["PHP", "Python", "JavaScript"]);

// $miColeccion cumple con ambas intersecciones
procesarColeccion($miColeccion);
// Total de elementos: 3
// Elementos: PHP, Python, JavaScript

guardarColeccion($miColeccion);
// Guardando 3 elementos...
// Datos: ["PHP","Python","JavaScript"]


// --- Ejemplo 2: Uso práctico con interfaces de dominio ---
// Los tipos de intersección son ideales para garantizar múltiples capacidades

interface Identificable {
    public function obtenerId(): string;
}

interface Auditable {
    public function obtenerCreadoPor(): string;
    public function obtenerFechaCreacion(): string;
}

interface Cacheable {
    public function obtenerClaveCache(): string;
    public function obtenerTTL(): int;
}

class Producto implements Identificable, Auditable, Cacheable {
    private string $id;
    private string $fechaCreacion;

    public function __construct(
        private string $nombre,
        private float $precio,
        private string $creadoPor
    ) {
        $this->id = uniqid('prod_');
        $this->fechaCreacion = date('Y-m-d H:i:s');
    }

    public function obtenerId(): string {
        return $this->id;
    }

    public function obtenerCreadoPor(): string {
        return $this->creadoPor;
    }

    public function obtenerFechaCreacion(): string {
        return $this->fechaCreacion;
    }

    public function obtenerClaveCache(): string {
        return "producto:{$this->id}";
    }

    public function obtenerTTL(): int {
        return 3600; // 1 hora
    }

    public function __toString(): string {
        return "{$this->nombre} (\${$this->precio})";
    }
}

// Solo acepta objetos que sean identificables Y auditables
function registrarEnBitacora(Identificable&Auditable $entidad): void {
    echo "Bitácora: Entidad {$entidad->obtenerId()} "
       . "creada por {$entidad->obtenerCreadoPor()} "
       . "el {$entidad->obtenerFechaCreacion()}\n";
}

// Solo acepta objetos que sean identificables Y cacheables
function almacenarEnCache(Identificable&Cacheable $entidad): void {
    echo "Cache: Almacenando {$entidad->obtenerId()} "
       . "con clave '{$entidad->obtenerClaveCache()}' "
       . "por {$entidad->obtenerTTL()}s\n";
}

$producto = new Producto("Laptop Gamer", 1299.99, "admin");
registrarEnBitacora($producto);
// Bitácora: Entidad prod_xxx creada por admin el 2024-01-15 10:30:00

almacenarEnCache($producto);
// Cache: Almacenando prod_xxx con clave 'producto:prod_xxx' por 3600s


// --- Ejemplo 3: Intersección en tipo de retorno ---
// Las funciones también pueden declarar intersecciones en el retorno

interface Stringable2 {
    public function __toString(): string;
}

interface JsonSerializable2 {
    public function jsonSerialize(): mixed;
}

class Respuesta implements Stringable2, JsonSerializable2 {
    public function __construct(
        private int $codigo,
        private string $mensaje,
        private mixed $datos = null
    ) {}

    public function __toString(): string {
        return "[{$this->codigo}] {$this->mensaje}";
    }

    public function jsonSerialize(): mixed {
        return [
            'codigo' => $this->codigo,
            'mensaje' => $this->mensaje,
            'datos' => $this->datos,
        ];
    }

    public function obtenerCodigo(): int {
        return $this->codigo;
    }
}

// El retorno debe cumplir con ambas interfaces
function crearRespuestaExitosa(mixed $datos): Stringable2&JsonSerializable2 {
    return new Respuesta(200, "Éxito", $datos);
}

function crearRespuestaError(string $mensaje): Stringable2&JsonSerializable2 {
    return new Respuesta(500, $mensaje);
}

$exito = crearRespuestaExitosa(['usuarios' => ['Ana', 'Carlos']]);
echo "Como texto: {$exito}\n";
// Como texto: [200] Éxito

echo "Como JSON: " . json_encode($exito) . "\n";
// Como JSON: {"codigo":200,"mensaje":"Éxito","datos":{"usuarios":["Ana","Carlos"]}}


// --- Ejemplo 4: Patrón Repository con intersección ---
// Ejemplo realista de cómo las intersecciones mejoran la seguridad de tipos

interface Paginable {
    public function paginar(int $pagina, int $porPagina): array;
    public function totalPaginas(int $porPagina): int;
}

interface Filtrable {
    public function filtrarPor(string $campo, mixed $valor): static;
    public function obtenerFiltros(): array;
}

interface Ordenable {
    public function ordenarPor(string $campo, string $direccion = 'ASC'): static;
}

class ConsultaUsuarios implements Paginable, Filtrable, Ordenable {
    private array $datos;
    private array $filtros = [];
    private array $orden = [];

    public function __construct() {
        // Datos simulados
        $this->datos = [
            ['id' => 1, 'nombre' => 'Ana', 'edad' => 28, 'ciudad' => 'Madrid'],
            ['id' => 2, 'nombre' => 'Carlos', 'edad' => 35, 'ciudad' => 'Barcelona'],
            ['id' => 3, 'nombre' => 'Elena', 'edad' => 22, 'ciudad' => 'Madrid'],
            ['id' => 4, 'nombre' => 'David', 'edad' => 41, 'ciudad' => 'Sevilla'],
            ['id' => 5, 'nombre' => 'Beatriz', 'edad' => 30, 'ciudad' => 'Madrid'],
        ];
    }

    public function paginar(int $pagina, int $porPagina): array {
        $inicio = ($pagina - 1) * $porPagina;
        return array_slice($this->obtenerDatosFiltrados(), $inicio, $porPagina);
    }

    public function totalPaginas(int $porPagina): int {
        return (int) ceil(count($this->obtenerDatosFiltrados()) / $porPagina);
    }

    public function filtrarPor(string $campo, mixed $valor): static {
        $clon = clone $this;
        $clon->filtros[$campo] = $valor;
        return $clon;
    }

    public function obtenerFiltros(): array {
        return $this->filtros;
    }

    public function ordenarPor(string $campo, string $direccion = 'ASC'): static {
        $clon = clone $this;
        $clon->orden = ['campo' => $campo, 'direccion' => $direccion];
        return $clon;
    }

    private function obtenerDatosFiltrados(): array {
        $resultado = $this->datos;
        foreach ($this->filtros as $campo => $valor) {
            $resultado = array_filter($resultado, fn($item) => ($item[$campo] ?? null) === $valor);
        }
        return array_values($resultado);
    }
}

// Función que requiere una consulta que sea paginable Y filtrable
function mostrarResultadosPaginados(Paginable&Filtrable $consulta, int $pagina): void {
    $resultados = $consulta->paginar($pagina, 2);
    $total = $consulta->totalPaginas(2);
    $filtros = $consulta->obtenerFiltros();

    echo "Página {$pagina}/{$total}";
    if (!empty($filtros)) {
        echo " (Filtros: " . implode(', ', array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($filtros),
            array_values($filtros)
        )) . ")";
    }
    echo "\n";

    foreach ($resultados as $item) {
        echo "  - {$item['nombre']} ({$item['edad']} años, {$item['ciudad']})\n";
    }
}

$consulta = new ConsultaUsuarios();
mostrarResultadosPaginados($consulta, 1);
// Página 1/3
//   - Ana (28 años, Madrid)
//   - Carlos (35 años, Barcelona)

$filtrada = $consulta->filtrarPor('ciudad', 'Madrid');
mostrarResultadosPaginados($filtrada, 1);
// Página 1/2 (Filtros: ciudad=Madrid)
//   - Ana (28 años, Madrid)
//   - Elena (22 años, Madrid)

?>
