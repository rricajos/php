<?php
// ============================================
// TIPOS UNION EN PHP 8.0
// int|string, ?Type (nullable), pseudo-tipo false
// ============================================

// --- Ejemplo 1: Tipos union básicos con int|string ---
// Un parámetro puede aceptar múltiples tipos

// Antes de PHP 8: usábamos @param en PHPDoc sin verificación real
// Ahora: declaración de tipos union directamente en la firma

function formatearId(int|string $id): string {
    if (is_int($id)) {
        return str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }
    return strtoupper($id);
}

echo formatearId(42) . "\n";          // 000042
echo formatearId("abc-123") . "\n";   // ABC-123

// Retorno con tipo union
function buscarValor(array $datos, string $clave): int|string|null {
    return $datos[$clave] ?? null;
}

$config = ['puerto' => 8080, 'host' => 'localhost', 'debug' => 'true'];
$puerto = buscarValor($config, 'puerto');   // int: 8080
$host = buscarValor($config, 'host');       // string: 'localhost'
$inexistente = buscarValor($config, 'nada'); // null

echo "Puerto: {$puerto} (" . gettype($puerto) . ")\n";
echo "Host: {$host} (" . gettype($host) . ")\n";
echo "Inexistente: " . var_export($inexistente, true) . "\n";


// --- Ejemplo 2: Tipo nullable (?Type) vs union con null ---
// ?Type es un atajo para Type|null

class Empleado {
    // ?string es equivalente a string|null
    private ?string $supervisor;

    // Podemos escribir la forma larga también
    private int|null $departamentoId;

    public function __construct(
        private string $nombre,
        ?string $supervisor = null,
        int|null $departamentoId = null
    ) {
        $this->supervisor = $supervisor;
        $this->departamentoId = $departamentoId;
    }

    public function obtenerSupervisor(): ?string {
        return $this->supervisor;
    }

    public function asignarSupervisor(?string $supervisor): void {
        $this->supervisor = $supervisor;
    }

    public function mostrar(): string {
        $sup = $this->supervisor ?? "Sin supervisor";
        $dept = $this->departamentoId !== null ? "Dept #{$this->departamentoId}" : "Sin departamento";
        return "{$this->nombre} | {$sup} | {$dept}";
    }
}

$emp1 = new Empleado("Carlos", "María", 5);
$emp2 = new Empleado("Ana"); // supervisor y departamento son null

echo $emp1->mostrar() . "\n"; // Carlos | María | Dept #5
echo $emp2->mostrar() . "\n"; // Ana | Sin supervisor | Sin departamento

$emp2->asignarSupervisor("Roberto");
echo $emp2->mostrar() . "\n"; // Ana | Roberto | Sin departamento


// --- Ejemplo 3: Pseudo-tipo false en retornos ---
// PHP 8.0 permite 'false' como tipo de retorno en uniones
// Muchas funciones nativas de PHP retornan false en caso de error

// Función que retorna int o false (patrón típico de PHP)
function buscarPosicion(string $texto, string $buscar): int|false {
    $pos = strpos($texto, $buscar);
    return $pos; // retorna int si encuentra, false si no
}

$resultado = buscarPosicion("Hola mundo PHP", "mundo");
if ($resultado !== false) {
    echo "Encontrado en posición: {$resultado}\n"; // Encontrado en posición: 5
}

$resultado = buscarPosicion("Hola mundo PHP", "Python");
if ($resultado === false) {
    echo "No encontrado\n"; // No encontrado
}

// Función personalizada que usa el patrón int|false
function dividirSeguro(float $dividendo, float $divisor): float|false {
    if ($divisor == 0) {
        return false; // Indicamos fallo retornando false
    }
    return $dividendo / $divisor;
}

$resultado = dividirSeguro(10, 3);
echo ($resultado !== false) ? "Resultado: {$resultado}\n" : "Error\n";
// Resultado: 3.3333333333333

$resultado = dividirSeguro(10, 0);
echo ($resultado !== false) ? "Resultado: {$resultado}\n" : "División por cero\n";
// División por cero


// --- Ejemplo 4: Union types en propiedades de clase ---
// Las propiedades también soportan tipos union

class Configuracion {
    // Propiedad que puede ser string, int, float, bool o array
    private array $valores = [];

    public function establecer(string $clave, string|int|float|bool|array $valor): void {
        $this->valores[$clave] = $valor;
    }

    public function obtener(string $clave): string|int|float|bool|array|null {
        return $this->valores[$clave] ?? null;
    }

    // Métodos con coerción explícita según tipo esperado
    public function obtenerString(string $clave): ?string {
        $valor = $this->obtener($clave);
        return $valor !== null ? (string) $valor : null;
    }

    public function obtenerInt(string $clave): ?int {
        $valor = $this->obtener($clave);
        return $valor !== null ? (int) $valor : null;
    }

    public function obtenerBool(string $clave): ?bool {
        $valor = $this->obtener($clave);
        if ($valor === null) return null;

        // Manejar strings como "true", "false", "1", "0"
        if (is_string($valor)) {
            return in_array(strtolower($valor), ['true', '1', 'yes', 'sí'], true);
        }

        return (bool) $valor;
    }
}

$config = new Configuracion();
$config->establecer('puerto', 8080);
$config->establecer('host', 'localhost');
$config->establecer('debug', true);
$config->establecer('ratio', 0.75);
$config->establecer('etiquetas', ['php', 'web', 'backend']);

echo "Puerto: " . $config->obtenerInt('puerto') . "\n";     // 8080
echo "Host: " . $config->obtenerString('host') . "\n";       // localhost
echo "Debug: " . ($config->obtenerBool('debug') ? 'Sí' : 'No') . "\n"; // Sí

$tags = $config->obtener('etiquetas');
echo "Tipo de etiquetas: " . gettype($tags) . "\n"; // array


// --- Ejemplo 5: Union types con clases e interfaces ---
// Podemos combinar tipos de clase en una union

interface Renderizable {
    public function renderizar(): string;
}

class ComponenteTexto implements Renderizable {
    public function __construct(private string $texto) {}

    public function renderizar(): string {
        return "<p>{$this->texto}</p>";
    }
}

class ComponenteImagen implements Renderizable {
    public function __construct(
        private string $src,
        private string $alt = ''
    ) {}

    public function renderizar(): string {
        return "<img src=\"{$this->src}\" alt=\"{$this->alt}\">";
    }
}

class Pagina {
    /** @var Renderizable[] */
    private array $componentes = [];

    // Aceptamos un objeto renderizable O un string plano
    public function agregar(Renderizable|string $contenido): void {
        $this->componentes[] = $contenido;
    }

    public function renderizar(): string {
        $html = "";
        foreach ($this->componentes as $comp) {
            if ($comp instanceof Renderizable) {
                $html .= $comp->renderizar() . "\n";
            } else {
                // Es un string plano
                $html .= "<div>{$comp}</div>\n";
            }
        }
        return $html;
    }
}

$pagina = new Pagina();
$pagina->agregar(new ComponenteTexto("Bienvenido a mi sitio"));
$pagina->agregar(new ComponenteImagen("/logo.png", "Logo"));
$pagina->agregar("Este es texto plano sin componente");

echo $pagina->renderizar();
// <p>Bienvenido a mi sitio</p>
// <img src="/logo.png" alt="Logo">
// <div>Este es texto plano sin componente</div>

?>
