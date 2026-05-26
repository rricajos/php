<?php
// ============================================
// OPERADOR NULLSAFE EN PHP 8.0
// ?-> operador, encadenamiento, vs null coalescing ??
// ============================================

// --- Ejemplo 1: Problema que resuelve el operador nullsafe ---
// Sin ?->, necesitamos verificar null en cada paso de la cadena

class Direccion {
    public function __construct(
        public readonly string $calle,
        public readonly string $ciudad,
        public readonly string $pais,
        public readonly ?string $codigoPostal = null
    ) {}

    public function formatear(): string {
        $cp = $this->codigoPostal ? " ({$this->codigoPostal})" : "";
        return "{$this->calle}, {$this->ciudad}, {$this->pais}{$cp}";
    }
}

class Empresa {
    public function __construct(
        public readonly string $nombre,
        public readonly ?Direccion $direccion = null
    ) {}
}

class Empleado {
    public function __construct(
        public readonly string $nombre,
        public readonly ?Empresa $empresa = null
    ) {}
}

$empleado1 = new Empleado(
    "Carlos",
    new Empresa("TechCorp", new Direccion("Av. Principal 123", "Madrid", "España", "28001"))
);
$empleado2 = new Empleado("Ana", new Empresa("StartUp", null)); // Sin dirección
$empleado3 = new Empleado("Pedro", null); // Sin empresa

// SIN operador nullsafe (PHP < 8.0): múltiples verificaciones
function obtenerCiudadAntiguo(?Empleado $emp): ?string {
    if ($emp !== null) {
        if ($emp->empresa !== null) {
            if ($emp->empresa->direccion !== null) {
                return $emp->empresa->direccion->ciudad;
            }
        }
    }
    return null;
}

// CON operador nullsafe (PHP 8.0): una sola línea elegante
// Si cualquier parte de la cadena es null, toda la expresión retorna null
function obtenerCiudad(?Empleado $emp): ?string {
    return $emp?->empresa?->direccion?->ciudad;
}

echo "Ciudad Carlos: " . (obtenerCiudad($empleado1) ?? "No disponible") . "\n";
// Ciudad Carlos: Madrid

echo "Ciudad Ana: " . (obtenerCiudad($empleado2) ?? "No disponible") . "\n";
// Ciudad Ana: No disponible (empresa existe, pero dirección es null)

echo "Ciudad Pedro: " . (obtenerCiudad($empleado3) ?? "No disponible") . "\n";
// Ciudad Pedro: No disponible (empresa es null)


// --- Ejemplo 2: Encadenamiento de métodos con ?-> ---
// El operador nullsafe funciona tanto con propiedades como con métodos

class Repositorio {
    private array $usuarios = [];

    public function agregarUsuario(string $id, array $datos): void {
        $this->usuarios[$id] = $datos;
    }

    public function buscarPorId(string $id): ?array {
        return $this->usuarios[$id] ?? null;
    }
}

class ServicioPerfil {
    public function __construct(private ?Repositorio $repo = null) {}

    public function obtenerPerfil(string $id): ?array {
        return $this->repo?->buscarPorId($id);
    }
}

class Aplicacion {
    private ?ServicioPerfil $servicioPerfil = null;

    public function inicializar(): void {
        $repo = new Repositorio();
        $repo->agregarUsuario("u1", ['nombre' => 'Sandra', 'rol' => 'admin']);
        $repo->agregarUsuario("u2", ['nombre' => 'Luis', 'rol' => 'editor']);
        $this->servicioPerfil = new ServicioPerfil($repo);
    }

    public function obtenerServicioPerfil(): ?ServicioPerfil {
        return $this->servicioPerfil;
    }
}

$app = new Aplicacion();
$app->inicializar();

// Encadenamiento con ?-> a través de múltiples niveles
$perfil = $app->obtenerServicioPerfil()?->obtenerPerfil("u1");
echo "Perfil encontrado: " . ($perfil ? $perfil['nombre'] : "No") . "\n"; // Sandra

// Si el servicio no está inicializado
$appVacia = new Aplicacion(); // Sin inicializar
$perfil = $appVacia->obtenerServicioPerfil()?->obtenerPerfil("u1");
echo "Perfil en app vacía: " . ($perfil ? $perfil['nombre'] : "null") . "\n"; // null


// --- Ejemplo 3: ?-> vs ?? (nullsafe vs null coalescing) ---
// Son complementarios, no equivalentes

class ConfiguracionSistema {
    private array $ajustes = [];

    public function __construct(array $ajustes = []) {
        $this->ajustes = $ajustes;
    }

    public function obtener(string $clave): ?string {
        return $this->ajustes[$clave] ?? null;
    }

    public function obtenerEntero(string $clave): ?int {
        $valor = $this->ajustes[$clave] ?? null;
        return $valor !== null ? (int) $valor : null;
    }
}

class ServidorWeb {
    public ?ConfiguracionSistema $config = null;

    public function __construct(?ConfiguracionSistema $config = null) {
        $this->config = $config;
    }
}

$servidor = new ServidorWeb(new ConfiguracionSistema([
    'puerto' => '8080',
    'host' => 'localhost',
]));

$servidorVacio = new ServidorWeb(null);

// ?-> navega la cadena de forma segura cuando hay nulls intermedios
$puerto1 = $servidor->config?->obtener('puerto');
$puerto2 = $servidorVacio->config?->obtener('puerto');

echo "Puerto servidor 1: {$puerto1}\n";    // 8080
echo "Puerto servidor 2: {$puerto2}\n";    // (vacío/null)

// ?? proporciona un valor por defecto cuando el resultado es null
$puertoFinal = $servidor->config?->obtener('puerto') ?? '3000';
echo "Puerto con default: {$puertoFinal}\n"; // 8080

$puertoFinal = $servidorVacio->config?->obtener('puerto') ?? '3000';
echo "Puerto vacío con default: {$puertoFinal}\n"; // 3000

// Combinación de ?-> y ?? es muy común y poderosa
$host = $servidorVacio->config?->obtener('host') ?? 'localhost';
$maxConexiones = $servidorVacio->config?->obtenerEntero('max_conexiones') ?? 100;
echo "Host: {$host}, Max conexiones: {$maxConexiones}\n";
// Host: localhost, Max conexiones: 100


// --- Ejemplo 4: ?-> con llamadas a métodos y arrays ---
// Uso práctico en cadenas complejas

class ResultadoAPI {
    public function __construct(
        private int $codigo,
        private ?array $datos = null,
        private ?string $error = null
    ) {}

    public function esExitoso(): bool {
        return $this->codigo >= 200 && $this->codigo < 300;
    }

    public function obtenerDatos(): ?array {
        return $this->datos;
    }

    public function obtenerError(): ?string {
        return $this->error;
    }

    public function obtenerPrimerElemento(): ?array {
        return $this->datos[0] ?? null;
    }
}

class ClienteAPI {
    public function buscarUsuarios(string $termino): ?ResultadoAPI {
        // Simulamos diferentes resultados
        return match ($termino) {
            'admin' => new ResultadoAPI(200, [
                ['id' => 1, 'nombre' => 'Admin Principal'],
                ['id' => 2, 'nombre' => 'Admin Secundario'],
            ]),
            'error' => new ResultadoAPI(500, null, 'Error del servidor'),
            default => null, // Simulamos fallo de conexión
        };
    }
}

$cliente = new ClienteAPI();

// Cadena exitosa
$nombre = $cliente->buscarUsuarios('admin')?->obtenerPrimerElemento();
echo "Primer usuario: " . ($nombre ? $nombre['nombre'] : 'N/A') . "\n";
// Primer usuario: Admin Principal

// Cadena con error del servidor
$datos = $cliente->buscarUsuarios('error')?->obtenerDatos();
echo "Datos con error: " . ($datos !== null ? 'hay datos' : 'sin datos') . "\n";
// Datos con error: sin datos

$errorMsg = $cliente->buscarUsuarios('error')?->obtenerError() ?? 'Sin error';
echo "Mensaje: {$errorMsg}\n"; // Error del servidor

// Cadena donde el método retorna null (fallo de conexión)
$resultado = $cliente->buscarUsuarios('desconocido')?->esExitoso();
echo "¿Exitoso?: " . var_export($resultado, true) . "\n"; // NULL
// ?-> cortocircuita toda la cadena cuando encuentra null

?>
