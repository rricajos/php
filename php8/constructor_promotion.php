<?php
// ============================================
// PROMOCIÓN DE PARÁMETROS EN CONSTRUCTOR (PHP 8.0)
// Parámetros promovidos, con readonly, visibilidad
// ============================================

// --- Ejemplo 1: Sin promoción vs con promoción ---
// Comparación directa para entender la simplificación

// ANTES de PHP 8.0: mucha repetición
class ProductoAntiguo {
    private string $nombre;
    private float $precio;
    private int $stock;
    private string $categoria;

    public function __construct(
        string $nombre,
        float $precio,
        int $stock,
        string $categoria
    ) {
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->categoria = $categoria;
    }

    public function obtenerNombre(): string { return $this->nombre; }
    public function obtenerPrecio(): float { return $this->precio; }
}

// CON promoción en PHP 8.0: declaración + asignación en una sola línea
// Al poner la visibilidad (public/protected/private) antes del parámetro,
// PHP automáticamente crea la propiedad y la asigna
class Producto {
    public function __construct(
        private string $nombre,
        private float $precio,
        private int $stock,
        private string $categoria
    ) {
        // No necesitamos $this->nombre = $nombre; etc.
        // PHP lo hace automáticamente
    }

    public function obtenerNombre(): string { return $this->nombre; }
    public function obtenerPrecio(): float { return $this->precio; }

    public function __toString(): string {
        return "{$this->nombre} - \${$this->precio} ({$this->stock} en stock) [{$this->categoria}]";
    }
}

$producto = new Producto("Teclado Mecánico", 89.99, 50, "Periféricos");
echo $producto . "\n";
// Teclado Mecánico - $89.99 (50 en stock) [Periféricos]


// --- Ejemplo 2: Mezcla de parámetros promovidos y normales ---
// Podemos combinar parámetros promovidos con parámetros regulares

class Pedido {
    private string $fechaCreacion;

    public function __construct(
        // Parámetros promovidos (se convierten en propiedades)
        public readonly int $id,
        private string $cliente,
        private array $items,
        private float $total,
        // Parámetro NO promovido (sin visibilidad = parámetro normal)
        string $zona = 'UTC'
    ) {
        // Los parámetros no promovidos se usan como variables locales
        date_default_timezone_set($zona);
        $this->fechaCreacion = date('Y-m-d H:i:s');
    }

    public function resumen(): string {
        $itemsStr = implode(', ', $this->items);
        return "Pedido #{$this->id} | {$this->cliente} | Items: {$itemsStr} | "
             . "Total: \${$this->total} | Fecha: {$this->fechaCreacion}";
    }
}

$pedido = new Pedido(
    id: 1001,
    cliente: "María López",
    items: ["Laptop", "Mouse", "Funda"],
    total: 1499.97,
    zona: 'America/Mexico_City'
);

echo $pedido->resumen() . "\n";
echo "ID público: {$pedido->id}\n"; // readonly + public = accesible directamente


// --- Ejemplo 3: Constructor promotion con diferentes visibilidades ---
// Cada parámetro puede tener su propia visibilidad

class CuentaUsuario {
    public function __construct(
        // public: accesible desde fuera
        public readonly string $nombreUsuario,
        public readonly string $email,

        // protected: accesible en subclases
        protected string $rol = 'usuario',

        // private: solo dentro de esta clase
        private string $contrasenaHash = '',
        private bool $activo = true,
        private array $permisos = []
    ) {
        // Podemos ejecutar lógica adicional en el constructor
        if (empty($this->contrasenaHash)) {
            $this->contrasenaHash = password_hash('temporal123', PASSWORD_DEFAULT);
        }
    }

    public function tienePermiso(string $permiso): bool {
        return in_array($permiso, $this->permisos, true);
    }

    public function estaActivo(): bool {
        return $this->activo;
    }

    public function obtenerRol(): string {
        return $this->rol;
    }

    public function mostrar(): string {
        return "@{$this->nombreUsuario} ({$this->email}) - Rol: {$this->rol} - "
             . "Activo: " . ($this->activo ? 'Sí' : 'No');
    }
}

$admin = new CuentaUsuario(
    nombreUsuario: "sandra_admin",
    email: "sandra@ejemplo.com",
    rol: "admin",
    permisos: ['leer', 'escribir', 'eliminar', 'gestionar_usuarios']
);

echo $admin->mostrar() . "\n";
echo "¿Puede eliminar? " . ($admin->tienePermiso('eliminar') ? 'Sí' : 'No') . "\n";

// Propiedades públicas accesibles directamente
echo "Usuario: {$admin->nombreUsuario}\n";
echo "Email: {$admin->email}\n";
// echo $admin->rol; // Error: protected
// echo $admin->contrasenaHash; // Error: private


// --- Ejemplo 4: Constructor promotion con readonly (PHP 8.1) ---
// La combinación de promotion + readonly crea Value Objects muy concisos

class Coordenada {
    public function __construct(
        public readonly float $latitud,
        public readonly float $longitud,
        public readonly ?float $altitud = null
    ) {
        // Validación en el constructor
        if ($latitud < -90 || $latitud > 90) {
            throw new InvalidArgumentException("Latitud debe estar entre -90 y 90");
        }
        if ($longitud < -180 || $longitud > 180) {
            throw new InvalidArgumentException("Longitud debe estar entre -180 y 180");
        }
    }

    public function __toString(): string {
        $alt = $this->altitud !== null ? ", alt: {$this->altitud}m" : "";
        return "({$this->latitud}, {$this->longitud}{$alt})";
    }
}

class DireccionEnvio {
    public function __construct(
        public readonly string $calle,
        public readonly string $ciudad,
        public readonly string $estado,
        public readonly string $codigoPostal,
        public readonly string $pais,
        public readonly ?Coordenada $coordenada = null
    ) {}

    public function formatear(): string {
        $lineas = [
            $this->calle,
            "{$this->ciudad}, {$this->estado} {$this->codigoPostal}",
            $this->pais,
        ];
        if ($this->coordenada) {
            $lineas[] = "GPS: {$this->coordenada}";
        }
        return implode("\n", $lineas);
    }
}

$direccion = new DireccionEnvio(
    calle: "Av. Reforma 505, Piso 3",
    ciudad: "Ciudad de México",
    estado: "CDMX",
    codigoPostal: "06500",
    pais: "México",
    coordenada: new Coordenada(19.4326, -99.1332, 2240)
);

echo $direccion->formatear() . "\n";
// Av. Reforma 505, Piso 3
// Ciudad de México, CDMX 06500
// México
// GPS: (19.4326, -99.1332, alt: 2240m)


// --- Ejemplo 5: Pattern práctico - DTO (Data Transfer Object) ---
// Los DTOs con promotion son extremadamente concisos

// DTO para crear un usuario (datos de entrada)
readonly class CrearUsuarioDTO {
    public function __construct(
        public string $nombre,
        public string $email,
        public string $contrasena,
        public ?string $telefono = null,
        public string $idioma = 'es',
        public array $preferencias = []
    ) {}

    // Método factory desde array (útil para datos de formulario o API)
    public static function desdeArray(array $datos): self {
        return new self(
            nombre: $datos['nombre'] ?? throw new \InvalidArgumentException('Nombre requerido'),
            email: $datos['email'] ?? throw new \InvalidArgumentException('Email requerido'),
            contrasena: $datos['contrasena'] ?? throw new \InvalidArgumentException('Contraseña requerida'),
            telefono: $datos['telefono'] ?? null,
            idioma: $datos['idioma'] ?? 'es',
            preferencias: $datos['preferencias'] ?? []
        );
    }
}

// DTO de respuesta
readonly class UsuarioRespuestaDTO {
    public function __construct(
        public int $id,
        public string $nombre,
        public string $email,
        public string $rol,
        public string $creadoEn
    ) {}

    public function aArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
            'creado_en' => $this->creadoEn,
        ];
    }
}

// Uso
$datosFormulario = [
    'nombre' => 'Roberto García',
    'email' => 'roberto@ejemplo.com',
    'contrasena' => 'clave_segura_123',
    'telefono' => '+52 55 1234 5678',
];

$dto = CrearUsuarioDTO::desdeArray($datosFormulario);
echo "Crear usuario: {$dto->nombre} ({$dto->email})\n";
echo "Teléfono: {$dto->telefono}\n";
echo "Idioma: {$dto->idioma}\n";

$respuesta = new UsuarioRespuestaDTO(
    id: 42,
    nombre: $dto->nombre,
    email: $dto->email,
    rol: 'usuario',
    creadoEn: date('Y-m-d H:i:s')
);

echo "Respuesta JSON: " . json_encode($respuesta->aArray(), JSON_PRETTY_PRINT) . "\n";

?>
