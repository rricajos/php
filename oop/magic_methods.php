<?php
// ============================================
// MÉTODOS MÁGICOS EN PHP
// __toString, __get, __set, __isset, __unset, __call, __invoke, __clone
// ============================================

// --- Ejemplo 1: __toString - Representación en cadena ---
// Se invoca cuando el objeto se usa como string

class Temperatura {
    public function __construct(
        private float $grados,
        private string $escala = 'C' // C = Celsius, F = Fahrenheit
    ) {}

    // Se llama automáticamente al usar echo, print, concatenación, etc.
    public function __toString(): string {
        $simbolo = $this->escala === 'C' ? '°C' : '°F';
        return "{$this->grados}{$simbolo}";
    }

    public function aCelsius(): self {
        if ($this->escala === 'F') {
            return new self(round(($this->grados - 32) * 5 / 9, 2), 'C');
        }
        return clone $this;
    }

    public function aFahrenheit(): self {
        if ($this->escala === 'C') {
            return new self(round($this->grados * 9 / 5 + 32, 2), 'F');
        }
        return clone $this;
    }
}

$temp = new Temperatura(100, 'C');
echo "Temperatura: {$temp}\n";         // Temperatura: 100°C
echo "En Fahrenheit: {$temp->aFahrenheit()}\n"; // En Fahrenheit: 212°F
echo "Concatenado: " . $temp . " es el punto de ebullición\n";


// --- Ejemplo 2: __get, __set, __isset, __unset - Propiedades dinámicas ---
// Interceptan el acceso a propiedades no definidas o inaccesibles

class EntidadFlexible {
    private array $atributos = [];
    private array $modificados = [];

    // Se llama al intentar leer una propiedad inexistente
    public function __get(string $nombre): mixed {
        if (array_key_exists($nombre, $this->atributos)) {
            return $this->atributos[$nombre];
        }
        throw new RuntimeException("La propiedad '{$nombre}' no existe");
    }

    // Se llama al intentar escribir en una propiedad inexistente
    public function __set(string $nombre, mixed $valor): void {
        // Registramos que el campo fue modificado
        if (array_key_exists($nombre, $this->atributos) && $this->atributos[$nombre] !== $valor) {
            $this->modificados[] = $nombre;
        }
        $this->atributos[$nombre] = $valor;
    }

    // Se llama al usar isset() o empty() en una propiedad inexistente
    public function __isset(string $nombre): bool {
        return isset($this->atributos[$nombre]);
    }

    // Se llama al usar unset() en una propiedad inexistente
    public function __unset(string $nombre): void {
        unset($this->atributos[$nombre]);
    }

    public function obtenerModificados(): array {
        return array_unique($this->modificados);
    }

    public function obtenerTodos(): array {
        return $this->atributos;
    }
}

$entidad = new EntidadFlexible();

// __set se invoca para cada asignación
$entidad->nombre = "Sandra";
$entidad->edad = 28;
$entidad->ciudad = "Madrid";

// __get se invoca para cada lectura
echo "Nombre: {$entidad->nombre}\n"; // Sandra
echo "Edad: {$entidad->edad}\n";     // 28

// __isset
echo "¿Tiene nombre? " . (isset($entidad->nombre) ? "Sí" : "No") . "\n"; // Sí
echo "¿Tiene teléfono? " . (isset($entidad->telefono) ? "Sí" : "No") . "\n"; // No

// Modificamos un valor existente
$entidad->edad = 29;
echo "Campos modificados: " . implode(', ', $entidad->obtenerModificados()) . "\n"; // edad

// __unset
unset($entidad->ciudad);
echo "¿Tiene ciudad? " . (isset($entidad->ciudad) ? "Sí" : "No") . "\n"; // No


// --- Ejemplo 3: __call - Interceptar llamadas a métodos inexistentes ---
// Permite crear APIs fluidas y proxies de forma dinámica

class ConsultaBuilder {
    private string $tabla = '';
    private array $condiciones = [];
    private array $ordenamiento = [];

    // __call se invoca cuando se llama un método que no existe
    public function __call(string $nombre, array $argumentos): mixed {
        // Patrón "dondeCampo($valor)" → WHERE campo = valor
        if (str_starts_with($nombre, 'donde')) {
            $campo = $this->camelASnake(substr($nombre, 5));
            $this->condiciones[$campo] = $argumentos[0];
            return $this; // Para encadenamiento
        }

        // Patrón "ordenarPorCampo($direccion)" → ORDER BY campo
        if (str_starts_with($nombre, 'ordenarPor')) {
            $campo = $this->camelASnake(substr($nombre, 10));
            $direccion = $argumentos[0] ?? 'ASC';
            $this->ordenamiento[] = "{$campo} {$direccion}";
            return $this;
        }

        throw new BadMethodCallException("Método '{$nombre}' no encontrado");
    }

    public function tabla(string $tabla): self {
        $this->tabla = $tabla;
        return $this;
    }

    public function construir(): string {
        $sql = "SELECT * FROM {$this->tabla}";

        if (!empty($this->condiciones)) {
            $partes = [];
            foreach ($this->condiciones as $campo => $valor) {
                $partes[] = "{$campo} = '{$valor}'";
            }
            $sql .= " WHERE " . implode(" AND ", $partes);
        }

        if (!empty($this->ordenamiento)) {
            $sql .= " ORDER BY " . implode(", ", $this->ordenamiento);
        }

        return $sql;
    }

    private function camelASnake(string $texto): string {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($texto)));
    }
}

// Los métodos "donde..." y "ordenarPor..." no existen pero __call los maneja
$consulta = (new ConsultaBuilder())
    ->tabla('empleados')
    ->dondeNombre('Carlos')           // __call → WHERE nombre = 'Carlos'
    ->dondeDepartamento('Ventas')      // __call → AND departamento = 'Ventas'
    ->ordenarPorFechaIngreso('DESC')   // __call → ORDER BY fecha_ingreso DESC
    ->construir();

echo $consulta . "\n";
// SELECT * FROM empleados WHERE nombre = 'Carlos' AND departamento = 'Ventas' ORDER BY fecha_ingreso DESC


// --- Ejemplo 4: __invoke - Objetos como funciones ---
// Permite usar un objeto como si fuera una función callable

class Validador {
    private string $patron;
    private string $mensajeError;

    public function __construct(string $patron, string $mensajeError) {
        $this->patron = $patron;
        $this->mensajeError = $mensajeError;
    }

    // __invoke se llama cuando usamos el objeto como función: $obj($arg)
    public function __invoke(string $valor): bool {
        return (bool) preg_match($this->patron, $valor);
    }

    public function obtenerMensajeError(): string {
        return $this->mensajeError;
    }
}

// Creamos validadores como objetos invocables
$validarEmail = new Validador(
    '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
    "Email no válido"
);

$validarTelefono = new Validador(
    '/^\+?[0-9]{10,15}$/',
    "Teléfono no válido"
);

// Usamos los objetos como funciones gracias a __invoke
echo "¿Email válido? " . ($validarEmail("usuario@ejemplo.com") ? "Sí" : "No") . "\n"; // Sí
echo "¿Email válido? " . ($validarEmail("no-es-email") ? "Sí" : "No") . "\n";         // No

echo "¿Teléfono válido? " . ($validarTelefono("+5215512345678") ? "Sí" : "No") . "\n"; // Sí

// Podemos pasar objetos invocables donde se espera un callable
$datos = ["hola@correo.com", "invalido", "otro@email.org", "123"];
$validos = array_filter($datos, $validarEmail); // __invoke usado por array_filter
echo "Emails válidos: " . implode(', ', $validos) . "\n"; // hola@correo.com, otro@email.org


// --- Ejemplo 5: __clone - Controlar la clonación de objetos ---
// Se invoca cuando se usa 'clone' sobre un objeto

class Documento {
    private string $id;
    private string $titulo;
    private array $versiones = [];
    private \DateTime $fechaCreacion;

    public function __construct(string $titulo) {
        $this->id = uniqid('doc_');
        $this->titulo = $titulo;
        $this->fechaCreacion = new DateTime();
        $this->versiones[] = ['version' => 1, 'fecha' => $this->fechaCreacion->format('Y-m-d H:i:s')];
    }

    // __clone se ejecuta DESPUÉS de que PHP copia superficialmente el objeto
    public function __clone(): void {
        // Generamos un nuevo ID para el clon
        $this->id = uniqid('doc_');

        // Clonamos objetos internos para evitar referencias compartidas
        $this->fechaCreacion = new DateTime();

        // Añadimos el título como copia
        $this->titulo = "Copia de {$this->titulo}";

        // Reiniciamos las versiones
        $this->versiones = [['version' => 1, 'fecha' => $this->fechaCreacion->format('Y-m-d H:i:s')]];
    }

    public function obtenerInfo(): string {
        return "ID: {$this->id} | Título: {$this->titulo} | Versiones: " . count($this->versiones);
    }

    public function agregarVersion(): void {
        $num = count($this->versiones) + 1;
        $this->versiones[] = ['version' => $num, 'fecha' => date('Y-m-d H:i:s')];
    }
}

$original = new Documento("Informe Anual");
$original->agregarVersion();
$original->agregarVersion();

echo "Original: " . $original->obtenerInfo() . "\n";
// Original: ID: doc_xxx | Título: Informe Anual | Versiones: 3

// clone invoca __clone después de copiar
$copia = clone $original;

echo "Copia: " . $copia->obtenerInfo() . "\n";
// Copia: ID: doc_yyy | Título: Copia de Informe Anual | Versiones: 1

// Son objetos independientes
$original->agregarVersion();
echo "Original después: " . $original->obtenerInfo() . "\n"; // Versiones: 4
echo "Copia sin cambios: " . $copia->obtenerInfo() . "\n";   // Versiones: 1

?>
