<?php
// ============================================
// PROPIEDADES Y CLASES READONLY EN PHP 8.1 / 8.2
// readonly properties (8.1), readonly classes (8.2)
// ============================================

// --- Ejemplo 1: Propiedades readonly básicas (PHP 8.1) ---
// Una propiedad readonly solo puede asignarse una vez

class Coordenadas {
    // readonly: se puede asignar una sola vez, luego es inmutable
    public readonly float $latitud;
    public readonly float $longitud;

    public function __construct(float $latitud, float $longitud) {
        // Primera asignación: permitida
        $this->latitud = $latitud;
        $this->longitud = $longitud;
    }

    public function distanciaA(Coordenadas $otra): float {
        // Fórmula de Haversine simplificada
        $dLat = deg2rad($otra->latitud - $this->latitud);
        $dLon = deg2rad($otra->longitud - $this->longitud);
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($this->latitud)) * cos(deg2rad($otra->latitud)) *
             sin($dLon / 2) ** 2;
        return round(6371 * 2 * asin(sqrt($a)), 2); // km
    }

    public function __toString(): string {
        return "({$this->latitud}, {$this->longitud})";
    }
}

$madrid = new Coordenadas(40.4168, -3.7038);
$barcelona = new Coordenadas(41.3874, 2.1686);

echo "Madrid: {$madrid}\n";
echo "Barcelona: {$barcelona}\n";
echo "Distancia: {$madrid->distanciaA($barcelona)} km\n";

// Intentar modificar una propiedad readonly lanza un Error:
// $madrid->latitud = 0.0; // Error: Cannot modify readonly property


// --- Ejemplo 2: readonly con constructor promotion (PHP 8.1) ---
// Combinación muy común: promotion + readonly para objetos de valor inmutables

class Moneda {
    public function __construct(
        public readonly float $cantidad,
        public readonly string $codigo
    ) {
        if ($cantidad < 0) {
            throw new InvalidArgumentException("La cantidad no puede ser negativa");
        }
    }

    public function sumar(Moneda $otra): self {
        if ($this->codigo !== $otra->codigo) {
            throw new InvalidArgumentException("No se pueden sumar monedas diferentes: {$this->codigo} y {$otra->codigo}");
        }
        // Como es readonly, creamos un nuevo objeto en lugar de modificar
        return new self($this->cantidad + $otra->cantidad, $this->codigo);
    }

    public function multiplicar(float $factor): self {
        return new self(round($this->cantidad * $factor, 2), $this->codigo);
    }

    public function formatear(): string {
        $simbolos = ['USD' => '$', 'EUR' => "\u{20AC}", 'MXN' => 'MX$'];
        $simbolo = $simbolos[$this->codigo] ?? $this->codigo . ' ';
        return $simbolo . number_format($this->cantidad, 2);
    }

    public function __toString(): string {
        return $this->formatear();
    }
}

$precio = new Moneda(99.99, 'USD');
$impuesto = $precio->multiplicar(0.16);
$total = $precio->sumar($impuesto);

echo "Precio: {$precio}\n";     // $99.99
echo "Impuesto: {$impuesto}\n"; // $16.00
echo "Total: {$total}\n";       // $115.99

// Los objetos originales no se modificaron (inmutabilidad)
echo "Precio sigue siendo: {$precio}\n"; // $99.99


// --- Ejemplo 3: Readonly class completa (PHP 8.2) ---
// Todas las propiedades de la clase son automáticamente readonly

// En PHP 8.2, la palabra 'readonly' antes de 'class' hace que
// TODAS las propiedades sean readonly automáticamente
readonly class EventoDominio {
    public string $ocurridoEn;

    public function __construct(
        public string $nombre,
        public string $agregadoId,
        public array $datos,
        public string $version = '1.0'
    ) {
        // Las propiedades declaradas en el cuerpo también son readonly
        $this->ocurridoEn = date('Y-m-d\TH:i:s.v\Z');
    }

    public function serializar(): string {
        return json_encode([
            'nombre' => $this->nombre,
            'agregado_id' => $this->agregadoId,
            'datos' => $this->datos,
            'version' => $this->version,
            'ocurrido_en' => $this->ocurridoEn,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

$evento = new EventoDominio(
    nombre: 'PedidoCreado',
    agregadoId: 'pedido-001',
    datos: ['total' => 150.00, 'items' => 3]
);

echo $evento->serializar() . "\n";
// Todas las propiedades son inmutables
// $evento->nombre = "Otro"; // Error: Cannot modify readonly property


// --- Ejemplo 4: Patrones inmutables con readonly - Value Objects ---
// readonly es ideal para implementar Value Objects del DDD

readonly class RangoFechas {
    public function __construct(
        public \DateTimeImmutable $inicio,
        public \DateTimeImmutable $fin
    ) {
        if ($inicio > $fin) {
            throw new InvalidArgumentException(
                "La fecha de inicio no puede ser posterior a la fecha de fin"
            );
        }
    }

    public function contieneFecha(\DateTimeImmutable $fecha): bool {
        return $fecha >= $this->inicio && $fecha <= $this->fin;
    }

    public function diasDuracion(): int {
        return (int) $this->inicio->diff($this->fin)->days;
    }

    public function seSuperpone(RangoFechas $otro): bool {
        return $this->inicio <= $otro->fin && $this->fin >= $otro->inicio;
    }

    // Para "modificar", creamos una nueva instancia
    public function extenderHasta(\DateTimeImmutable $nuevaFechaFin): self {
        return new self($this->inicio, $nuevaFechaFin);
    }

    public function __toString(): string {
        return $this->inicio->format('d/m/Y') . ' - ' . $this->fin->format('d/m/Y');
    }
}

$vacaciones = new RangoFechas(
    new \DateTimeImmutable('2024-07-01'),
    new \DateTimeImmutable('2024-07-15')
);

$navidad = new RangoFechas(
    new \DateTimeImmutable('2024-12-20'),
    new \DateTimeImmutable('2024-12-31')
);

echo "Vacaciones: {$vacaciones} ({$vacaciones->diasDuracion()} días)\n";
// Vacaciones: 01/07/2024 - 15/07/2024 (14 días)

echo "¿Se superponen? " . ($vacaciones->seSuperpone($navidad) ? "Sí" : "No") . "\n";
// No

$hoy = new \DateTimeImmutable('2024-07-10');
echo "¿{$hoy->format('d/m')} está en vacaciones? "
   . ($vacaciones->contieneFecha($hoy) ? "Sí" : "No") . "\n";
// Sí

// "Modificar" crea un nuevo objeto, el original no cambia
$extendidas = $vacaciones->extenderHasta(new \DateTimeImmutable('2024-07-31'));
echo "Vacaciones originales: {$vacaciones}\n";   // 01/07/2024 - 15/07/2024
echo "Vacaciones extendidas: {$extendidas}\n";   // 01/07/2024 - 31/07/2024


// --- Ejemplo 5: readonly con herencia ---
// Las restricciones de readonly se heredan

readonly class EntidadBase {
    public function __construct(
        public string $id,
        public \DateTimeImmutable $creadoEn
    ) {}
}

// Las clases hijas de una readonly class también deben ser readonly
readonly class UsuarioEntidad extends EntidadBase {
    public function __construct(
        string $id,
        public string $nombre,
        public string $email,
        public string $rol = 'usuario'
    ) {
        parent::__construct($id, new \DateTimeImmutable());
    }

    public function conRol(string $nuevoRol): self {
        // Patrón "wither": crear nueva instancia con un cambio
        return new self(
            id: $this->id,
            nombre: $this->nombre,
            email: $this->email,
            rol: $nuevoRol
        );
    }

    public function __toString(): string {
        return "[{$this->id}] {$this->nombre} <{$this->email}> ({$this->rol})";
    }
}

$usuario = new UsuarioEntidad('usr-001', 'Sandra', 'sandra@correo.com');
echo "Original: {$usuario}\n";
// Original: [usr-001] Sandra <sandra@correo.com> (usuario)

$admin = $usuario->conRol('admin');
echo "Nuevo: {$admin}\n";
// Nuevo: [usr-001] Sandra <sandra@correo.com> (admin)

// El original permanece sin cambios
echo "Sin cambios: {$usuario}\n";
// Sin cambios: [usr-001] Sandra <sandra@correo.com> (usuario)

?>
