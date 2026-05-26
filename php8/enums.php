<?php
// ============================================
// ENUMERACIONES (ENUMS) EN PHP 8.1
// Enums básicos, backed enums (string/int), métodos, implements interface
// ============================================

// --- Ejemplo 1: Enum básico (Unit Enum) ---
// Los enums definen un conjunto cerrado de valores posibles

enum EstadoPedido {
    case Pendiente;
    case Confirmado;
    case EnPreparacion;
    case Enviado;
    case Entregado;
    case Cancelado;
}

// Usar un enum como tipo garantiza que solo se acepten valores válidos
function procesarPedido(int $pedidoId, EstadoPedido $estado): string {
    return match ($estado) {
        EstadoPedido::Pendiente => "Pedido #{$pedidoId}: esperando confirmación",
        EstadoPedido::Confirmado => "Pedido #{$pedidoId}: confirmado, preparando...",
        EstadoPedido::EnPreparacion => "Pedido #{$pedidoId}: en preparación",
        EstadoPedido::Enviado => "Pedido #{$pedidoId}: en camino",
        EstadoPedido::Entregado => "Pedido #{$pedidoId}: entregado exitosamente",
        EstadoPedido::Cancelado => "Pedido #{$pedidoId}: CANCELADO",
    };
}

$estado = EstadoPedido::Enviado;
echo procesarPedido(1001, $estado) . "\n";
// Pedido #1001: en camino

// El nombre del caso como string
echo "Estado actual: {$estado->name}\n"; // Enviado

// Comparación de enums
echo ($estado === EstadoPedido::Enviado) ? "Es enviado\n" : "No es enviado\n"; // Es enviado

// Listar todos los casos
echo "Todos los estados:\n";
foreach (EstadoPedido::cases() as $caso) {
    echo "  - {$caso->name}\n";
}


// --- Ejemplo 2: Backed Enum con valores string ---
// Cada caso tiene un valor escalar asociado (string o int)

enum Color: string {
    case Rojo = '#FF0000';
    case Verde = '#00FF00';
    case Azul = '#0000FF';
    case Amarillo = '#FFFF00';
    case Naranja = '#FFA500';
    case Morado = '#800080';
    case Negro = '#000000';
    case Blanco = '#FFFFFF';
}

// Acceder al valor escalar con ->value
$color = Color::Azul;
echo "Color: {$color->name} → {$color->value}\n"; // Azul → #0000FF

// Crear un enum desde su valor con from() o tryFrom()
$desdeValor = Color::from('#FF0000');
echo "Desde valor: {$desdeValor->name}\n"; // Rojo

// tryFrom() retorna null si no encuentra el valor (sin excepción)
$invalido = Color::tryFrom('#123456');
echo "Inválido: " . ($invalido?->name ?? 'No encontrado') . "\n"; // No encontrado

// Uso práctico: aplicar estilo CSS
function generarCSS(Color $fondo, Color $texto): string {
    return "background-color: {$fondo->value}; color: {$texto->value};";
}

echo generarCSS(Color::Negro, Color::Blanco) . "\n";
// background-color: #000000; color: #FFFFFF;


// --- Ejemplo 3: Backed Enum con valores int ---
// Enums respaldados por enteros, ideal para códigos numéricos

enum NivelAcceso: int {
    case Invitado = 0;
    case Usuario = 10;
    case Moderador = 50;
    case Editor = 70;
    case Admin = 90;
    case SuperAdmin = 100;
}

function verificarPermiso(NivelAcceso $nivelUsuario, NivelAcceso $nivelRequerido): bool {
    return $nivelUsuario->value >= $nivelRequerido->value;
}

$usuario = NivelAcceso::Editor;
echo "Nivel: {$usuario->name} ({$usuario->value})\n"; // Editor (70)

$acciones = [
    'ver_perfil' => NivelAcceso::Invitado,
    'publicar_articulo' => NivelAcceso::Editor,
    'moderar_comentarios' => NivelAcceso::Moderador,
    'gestionar_usuarios' => NivelAcceso::Admin,
    'configurar_sistema' => NivelAcceso::SuperAdmin,
];

echo "Permisos de {$usuario->name}:\n";
foreach ($acciones as $accion => $nivelRequerido) {
    $permitido = verificarPermiso($usuario, $nivelRequerido);
    echo "  {$accion}: " . ($permitido ? "Sí" : "No") . "\n";
}
// ver_perfil: Sí, publicar_articulo: Sí, moderar_comentarios: Sí
// gestionar_usuarios: No, configurar_sistema: No


// --- Ejemplo 4: Enums con métodos ---
// Los enums pueden tener métodos, constantes y usar traits

enum Moneda: string {
    case USD = 'USD';
    case EUR = 'EUR';
    case MXN = 'MXN';
    case GBP = 'GBP';
    case JPY = 'JPY';

    // Los enums pueden tener métodos
    public function simbolo(): string {
        return match ($this) {
            self::USD => '$',
            self::EUR => "\u{20AC}",
            self::MXN => 'MX$',
            self::GBP => "\u{00A3}",
            self::JPY => "\u{00A5}",
        };
    }

    public function nombre(): string {
        return match ($this) {
            self::USD => 'Dólar estadounidense',
            self::EUR => 'Euro',
            self::MXN => 'Peso mexicano',
            self::GBP => 'Libra esterlina',
            self::JPY => 'Yen japonés',
        };
    }

    public function decimales(): int {
        return match ($this) {
            self::JPY => 0,          // El yen no usa decimales
            default => 2,
        };
    }

    public function formatear(float $cantidad): string {
        return $this->simbolo() . number_format($cantidad, $this->decimales());
    }

    // Constante dentro del enum
    const PRINCIPALES = [self::USD, self::EUR, self::GBP];
}

$precio = 1299.99;
foreach (Moneda::cases() as $moneda) {
    echo "{$moneda->nombre()}: {$moneda->formatear($precio)}\n";
}
// Dólar estadounidense: $1,299.99
// Euro: €1,299.99
// Peso mexicano: MX$1,299.99
// Libra esterlina: £1,299.99
// Yen japonés: ¥1,300

echo "\nMonedas principales:\n";
foreach (Moneda::PRINCIPALES as $m) {
    echo "  {$m->value}: {$m->simbolo()}\n";
}


// --- Ejemplo 5: Enum que implementa una interface ---
// Los enums pueden implementar interfaces para mayor polimorfismo

interface Etiquetable {
    public function etiqueta(): string;
    public function color(): string;
    public function icono(): string;
}

enum PrioridadTarea: int implements Etiquetable {
    case Baja = 1;
    case Normal = 2;
    case Alta = 3;
    case Urgente = 4;
    case Critica = 5;

    public function etiqueta(): string {
        return match ($this) {
            self::Baja => 'Baja prioridad',
            self::Normal => 'Normal',
            self::Alta => 'Alta prioridad',
            self::Urgente => '¡Urgente!',
            self::Critica => '¡¡CRÍTICA!!',
        };
    }

    public function color(): string {
        return match ($this) {
            self::Baja => '#6c757d',      // gris
            self::Normal => '#0d6efd',     // azul
            self::Alta => '#ffc107',       // amarillo
            self::Urgente => '#fd7e14',    // naranja
            self::Critica => '#dc3545',    // rojo
        };
    }

    public function icono(): string {
        return match ($this) {
            self::Baja => '▽',
            self::Normal => '◇',
            self::Alta => '△',
            self::Urgente => '⚠',
            self::Critica => '🔴',
        };
    }

    // Método adicional específico del enum
    public function esCritica(): bool {
        return $this->value >= self::Urgente->value;
    }
}

// Función que acepta cualquier Etiquetable
function renderizarBadge(Etiquetable $item): string {
    return "<span style=\"color: {$item->color()}\">{$item->icono()} {$item->etiqueta()}</span>";
}

$tareas = [
    ['nombre' => 'Actualizar docs', 'prioridad' => PrioridadTarea::Baja],
    ['nombre' => 'Corregir bug login', 'prioridad' => PrioridadTarea::Critica],
    ['nombre' => 'Revisar PR', 'prioridad' => PrioridadTarea::Normal],
    ['nombre' => 'Parche de seguridad', 'prioridad' => PrioridadTarea::Urgente],
];

echo "\nLista de tareas:\n";
foreach ($tareas as $tarea) {
    $badge = renderizarBadge($tarea['prioridad']);
    $alerta = $tarea['prioridad']->esCritica() ? " ← ¡ATENCIÓN!" : "";
    echo "  {$badge} - {$tarea['nombre']}{$alerta}\n";
}

?>
