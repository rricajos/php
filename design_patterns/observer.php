<?php
/**
 * =============================================================================
 * PATRON OBSERVER (OBSERVADOR) EN PHP
 * =============================================================================
 *
 * El patron Observer define una relacion uno-a-muchos entre objetos, de modo
 * que cuando un objeto (sujeto) cambia de estado, todos sus dependientes
 * (observadores) son notificados y actualizados automaticamente.
 *
 * Casos de uso comunes:
 * - Sistemas de eventos (event-driven architecture)
 * - Notificaciones en tiempo real
 * - Propagacion de cambios en modelos de datos
 * - Desacoplamiento entre modulos de una aplicacion
 *
 * PHP proporciona las interfaces SplSubject y SplObserver en la SPL
 * (Standard PHP Library) como base para implementar este patron.
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Implementacion con SplSubject / SplObserver
// =============================================================================
// Problema: Una tienda online necesita que multiples sistemas reaccionen
// cuando un usuario se registra (enviar bienvenida, crear perfil, analytics).

echo "=== Ejemplo 1: SplSubject / SplObserver para Registro de Usuarios ===\n\n";

// El sujeto: gestiona el registro y notifica a los observadores
class GestorRegistroUsuarios implements \SplSubject
{
    private \SplObjectStorage $observadores;
    private array $datosEvento = [];

    public function __construct()
    {
        $this->observadores = new \SplObjectStorage();
    }

    public function attach(\SplObserver $observador): void
    {
        $this->observadores->attach($observador);
        echo "  [GestorRegistro] Observador agregado: " . get_class($observador) . "\n";
    }

    public function detach(\SplObserver $observador): void
    {
        $this->observadores->detach($observador);
        echo "  [GestorRegistro] Observador removido: " . get_class($observador) . "\n";
    }

    public function notify(): void
    {
        foreach ($this->observadores as $observador) {
            $observador->update($this);
        }
    }

    // Metodo para acceder a los datos del evento desde los observadores
    public function obtenerDatosEvento(): array
    {
        return $this->datosEvento;
    }

    // Accion que dispara la notificacion
    public function registrarUsuario(string $nombre, string $email): void
    {
        echo "\n  [GestorRegistro] Registrando usuario: {$nombre} ({$email})\n";

        $this->datosEvento = [
            'nombre'      => $nombre,
            'email'       => $email,
            'fecha'       => date('Y-m-d H:i:s'),
            'ip'          => '192.168.1.' . rand(1, 254),
        ];

        // Notificar a todos los observadores sobre el nuevo registro
        $this->notify();
    }
}

// Observadores concretos: cada uno reacciona de forma diferente al mismo evento

class ObservadorBienvenida implements \SplObserver
{
    public function update(\SplSubject $sujeto): void
    {
        $datos = $sujeto->obtenerDatosEvento();
        echo "    [Bienvenida] Enviando email de bienvenida a {$datos['email']}\n";
    }
}

class ObservadorPerfilInicial implements \SplObserver
{
    public function update(\SplSubject $sujeto): void
    {
        $datos = $sujeto->obtenerDatosEvento();
        echo "    [Perfil] Creando perfil inicial para {$datos['nombre']}\n";
    }
}

class ObservadorAnalytics implements \SplObserver
{
    public function update(\SplSubject $sujeto): void
    {
        $datos = $sujeto->obtenerDatosEvento();
        echo "    [Analytics] Registrando conversion desde IP {$datos['ip']}\n";
    }
}

// Configurar el sistema
$gestor = new GestorRegistroUsuarios();
$gestor->attach(new ObservadorBienvenida());
$gestor->attach(new ObservadorPerfilInicial());
$gestor->attach(new ObservadorAnalytics());

// Registrar usuarios: cada registro notifica a todos los observadores
$gestor->registrarUsuario('Maria Lopez', 'maria@email.com');
$gestor->registrarUsuario('Carlos Ramirez', 'carlos@email.com');
echo "\n";


// =============================================================================
// Ejemplo 2: Despachador de Eventos Personalizado (Event Dispatcher)
// =============================================================================
// Problema: SplObserver es limitado (un solo metodo update, sin tipos de evento).
// Necesitamos un sistema mas flexible con eventos tipados y listeners especificos.

echo "=== Ejemplo 2: Despachador de Eventos Personalizado ===\n\n";

// Clase base para eventos
class Evento
{
    private bool $propagacionDetenida = false;
    private readonly float $marcaTiempo;

    public function __construct(
        private readonly string $nombre,
        private array $datos = []
    ) {
        $this->marcaTiempo = microtime(true);
    }

    public function obtenerNombre(): string { return $this->nombre; }
    public function obtenerDatos(): array { return $this->datos; }
    public function obtenerMarcaTiempo(): float { return $this->marcaTiempo; }

    public function detenerPropagacion(): void
    {
        $this->propagacionDetenida = true;
    }

    public function estaPropagacionDetenida(): bool
    {
        return $this->propagacionDetenida;
    }
}

// El despachador de eventos: version mejorada del patron Observer
class DespachadorEventos
{
    // Los listeners se organizan por nombre de evento y prioridad
    private array $listeners = [];

    /**
     * Registrar un listener para un evento especifico.
     * Mayor prioridad = se ejecuta primero.
     */
    public function escuchar(string $nombreEvento, callable $listener, int $prioridad = 0): void
    {
        $this->listeners[$nombreEvento][] = [
            'callback'  => $listener,
            'prioridad' => $prioridad,
        ];

        // Ordenar por prioridad descendente
        usort($this->listeners[$nombreEvento], function ($a, $b) {
            return $b['prioridad'] <=> $a['prioridad'];
        });
    }

    // Despachar un evento a todos sus listeners
    public function despachar(Evento $evento): Evento
    {
        $nombre = $evento->obtenerNombre();

        if (!isset($this->listeners[$nombre])) {
            return $evento; // Sin listeners para este evento
        }

        foreach ($this->listeners[$nombre] as $listener) {
            if ($evento->estaPropagacionDetenida()) {
                break; // Un listener detuvo la propagacion
            }
            call_user_func($listener['callback'], $evento);
        }

        return $evento;
    }

    // Eliminar todos los listeners de un evento
    public function removerListeners(string $nombreEvento): void
    {
        unset($this->listeners[$nombreEvento]);
    }

    // Verificar si un evento tiene listeners
    public function tieneListeners(string $nombreEvento): bool
    {
        return !empty($this->listeners[$nombreEvento]);
    }
}

$despachador = new DespachadorEventos();

// Registrar listeners con diferentes prioridades
$despachador->escuchar('usuario.login', function (Evento $e) {
    $datos = $e->obtenerDatos();
    echo "  [Prioridad BAJA] Actualizar ultima fecha de login para {$datos['email']}\n";
}, prioridad: 1);

$despachador->escuchar('usuario.login', function (Evento $e) {
    $datos = $e->obtenerDatos();
    echo "  [Prioridad ALTA] Verificar seguridad para {$datos['email']} desde {$datos['ip']}\n";
}, prioridad: 100);

$despachador->escuchar('usuario.login', function (Evento $e) {
    $datos = $e->obtenerDatos();
    echo "  [Prioridad MEDIA] Registrar sesion activa para {$datos['email']}\n";
}, prioridad: 50);

// Despachar el evento: los listeners se ejecutan por prioridad
echo "  Despachando evento 'usuario.login':\n";
$despachador->despachar(new Evento('usuario.login', [
    'email' => 'admin@empresa.com',
    'ip'    => '10.0.0.1',
]));
echo "\n";


// =============================================================================
// Ejemplo 3: Multiples Observadores Reaccionando al Mismo Evento
// =============================================================================
// Problema: Cuando se realiza una venta, muchos sistemas deben enterarse:
// inventario, contabilidad, envio, CRM, etc.

echo "=== Ejemplo 3: Evento de Venta con Multiples Observadores ===\n\n";

// Eventos especificos del dominio de ventas
class EventoVenta extends Evento
{
    public function __construct(
        private readonly string $pedidoId,
        private readonly array $productos,
        private readonly float $total,
        private readonly string $clienteEmail
    ) {
        parent::__construct('venta.completada', [
            'pedido_id' => $pedidoId,
            'productos' => $productos,
            'total'     => $total,
            'cliente'   => $clienteEmail,
        ]);
    }

    public function obtenerPedidoId(): string { return $this->pedidoId; }
    public function obtenerProductos(): array { return $this->productos; }
    public function obtenerTotal(): float { return $this->total; }
    public function obtenerClienteEmail(): string { return $this->clienteEmail; }
}

// Cada observador es una clase independiente con responsabilidad unica

class GestorInventario
{
    public function alVenderProducto(Evento $evento): void
    {
        $datos = $evento->obtenerDatos();
        echo "  [Inventario] Actualizando stock para pedido {$datos['pedido_id']}:\n";
        foreach ($datos['productos'] as $producto) {
            echo "    - {$producto['nombre']}: reducir {$producto['cantidad']} unidades\n";
        }
    }
}

class GestorContabilidad
{
    public function registrarVenta(Evento $evento): void
    {
        $datos = $evento->obtenerDatos();
        $totalFormateado = number_format($datos['total'], 2);
        echo "  [Contabilidad] Registrando ingreso de \${$totalFormateado} ";
        echo "del pedido {$datos['pedido_id']}\n";
    }
}

class GestorEnvio
{
    public function prepararEnvio(Evento $evento): void
    {
        $datos = $evento->obtenerDatos();
        $totalArticulos = array_sum(array_column($datos['productos'], 'cantidad'));
        echo "  [Envio] Preparando paquete con {$totalArticulos} articulos ";
        echo "para {$datos['cliente']}\n";
    }
}

class GestorNotificaciones
{
    public function enviarConfirmacion(Evento $evento): void
    {
        $datos = $evento->obtenerDatos();
        echo "  [Notificaciones] Email de confirmacion enviado a {$datos['cliente']}\n";
    }
}

class GestorPuntosLealtad
{
    public function acreditarPuntos(Evento $evento): void
    {
        $datos = $evento->obtenerDatos();
        $puntos = (int) floor($datos['total']); // 1 punto por cada peso gastado
        echo "  [Lealtad] Acreditando {$puntos} puntos al cliente {$datos['cliente']}\n";
    }
}

// Conectar todo mediante el despachador
$despachadorVentas = new DespachadorEventos();

$inventario = new GestorInventario();
$contabilidad = new GestorContabilidad();
$envio = new GestorEnvio();
$notificaciones = new GestorNotificaciones();
$lealtad = new GestorPuntosLealtad();

$despachadorVentas->escuchar('venta.completada', [$inventario, 'alVenderProducto'], 100);
$despachadorVentas->escuchar('venta.completada', [$contabilidad, 'registrarVenta'], 90);
$despachadorVentas->escuchar('venta.completada', [$envio, 'prepararEnvio'], 80);
$despachadorVentas->escuchar('venta.completada', [$notificaciones, 'enviarConfirmacion'], 70);
$despachadorVentas->escuchar('venta.completada', [$lealtad, 'acreditarPuntos'], 60);

// Simular una venta completada
$eventoVenta = new EventoVenta(
    pedidoId: 'PED-2026-0042',
    productos: [
        ['nombre' => 'Laptop Gamer', 'cantidad' => 1, 'precio' => 25999.00],
        ['nombre' => 'Mouse Inalambrico', 'cantidad' => 2, 'precio' => 499.00],
        ['nombre' => 'Teclado Mecanico', 'cantidad' => 1, 'precio' => 1899.00],
    ],
    total: 28896.00,
    clienteEmail: 'comprador@email.com'
);

echo "  --- Venta completada: Pedido PED-2026-0042 ---\n";
$despachadorVentas->despachar($eventoVenta);
echo "\n";


// =============================================================================
// Ejemplo 4: Eventos de E-Commerce (Email, Inventario, Analytics)
// =============================================================================
// Problema: El ciclo de vida de un pedido tiene multiples estados, y cada
// cambio de estado debe disparar acciones diferentes en distintos sistemas.

echo "=== Ejemplo 4: Ciclo de Vida de Pedido con Eventos ===\n\n";

// Sistema de eventos orientado al ciclo de vida de un pedido
class SistemaPedidos
{
    private DespachadorEventos $despachador;
    private array $pedidos = [];

    public function __construct(DespachadorEventos $despachador)
    {
        $this->despachador = $despachador;
    }

    public function crearPedido(string $clienteEmail, array $articulos): string
    {
        $pedidoId = 'ORD-' . str_pad(count($this->pedidos) + 1, 5, '0', STR_PAD_LEFT);

        $this->pedidos[$pedidoId] = [
            'id'       => $pedidoId,
            'cliente'  => $clienteEmail,
            'articulos' => $articulos,
            'estado'   => 'creado',
            'total'    => array_sum(array_column($articulos, 'precio')),
            'creado'   => date('Y-m-d H:i:s'),
        ];

        $this->despachador->despachar(new Evento('pedido.creado', $this->pedidos[$pedidoId]));
        return $pedidoId;
    }

    public function confirmarPago(string $pedidoId): void
    {
        $this->pedidos[$pedidoId]['estado'] = 'pagado';
        $this->despachador->despachar(new Evento('pedido.pagado', $this->pedidos[$pedidoId]));
    }

    public function enviarPedido(string $pedidoId, string $guia): void
    {
        $this->pedidos[$pedidoId]['estado'] = 'enviado';
        $this->pedidos[$pedidoId]['guia'] = $guia;
        $this->despachador->despachar(new Evento('pedido.enviado', $this->pedidos[$pedidoId]));
    }

    public function entregarPedido(string $pedidoId): void
    {
        $this->pedidos[$pedidoId]['estado'] = 'entregado';
        $this->despachador->despachar(new Evento('pedido.entregado', $this->pedidos[$pedidoId]));
    }
}

$desp = new DespachadorEventos();

// Servicio de email: reacciona a distintos eventos del pedido
$desp->escuchar('pedido.creado', function (Evento $e) {
    $d = $e->obtenerDatos();
    echo "    [Email] Confirmacion de pedido {$d['id']} enviada a {$d['cliente']}\n";
});

$desp->escuchar('pedido.pagado', function (Evento $e) {
    $d = $e->obtenerDatos();
    echo "    [Email] Recibo de pago enviado a {$d['cliente']} (Total: \${$d['total']})\n";
});

$desp->escuchar('pedido.enviado', function (Evento $e) {
    $d = $e->obtenerDatos();
    echo "    [Email] Notificacion de envio a {$d['cliente']} (Guia: {$d['guia']})\n";
});

$desp->escuchar('pedido.entregado', function (Evento $e) {
    $d = $e->obtenerDatos();
    echo "    [Email] Solicitud de resena enviada a {$d['cliente']}\n";
});

// Servicio de inventario: solo le interesa cuando se paga
$desp->escuchar('pedido.pagado', function (Evento $e) {
    $d = $e->obtenerDatos();
    $total = count($d['articulos']);
    echo "    [Inventario] Reservando {$total} articulos para pedido {$d['id']}\n";
});

// Analytics: rastrea todos los eventos
foreach (['pedido.creado', 'pedido.pagado', 'pedido.enviado', 'pedido.entregado'] as $evento) {
    $desp->escuchar($evento, function (Evento $e) {
        $d = $e->obtenerDatos();
        echo "    [Analytics] Evento '{$e->obtenerNombre()}' para pedido {$d['id']}\n";
    });
}

// Simular el ciclo de vida completo de un pedido
$sistema = new SistemaPedidos($desp);

echo "  --- 1. Crear pedido ---\n";
$id = $sistema->crearPedido('ana@tienda.com', [
    ['nombre' => 'Libro PHP', 'precio' => 450],
    ['nombre' => 'Curso Video', 'precio' => 1200],
]);

echo "\n  --- 2. Confirmar pago ---\n";
$sistema->confirmarPago($id);

echo "\n  --- 3. Enviar pedido ---\n";
$sistema->enviarPedido($id, 'FDX-987654321');

echo "\n  --- 4. Marcar como entregado ---\n";
$sistema->entregarPedido($id);
echo "\n";


// =============================================================================
// Ejemplo 5: Remover y Priorizar Observadores
// =============================================================================
// Problema: A veces necesitamos desactivar temporalmente un observador
// o cambiar el orden de ejecucion de los listeners.

echo "=== Ejemplo 5: Gestion Avanzada de Observadores ===\n\n";

class DespachadorAvanzado
{
    // Almacena listeners con ID unico para poder removerlos individualmente
    private array $listeners = [];
    private int $contadorId = 0;

    /**
     * Registrar un listener y devolver su ID para poder removerlo despues.
     */
    public function escuchar(string $evento, callable $callback, int $prioridad = 0): int
    {
        $id = ++$this->contadorId;
        $this->listeners[$evento][$id] = [
            'callback'  => $callback,
            'prioridad' => $prioridad,
            'activo'    => true,
        ];
        return $id;
    }

    // Remover un listener especifico por su ID
    public function removerListener(string $evento, int $id): bool
    {
        if (isset($this->listeners[$evento][$id])) {
            unset($this->listeners[$evento][$id]);
            return true;
        }
        return false;
    }

    // Desactivar temporalmente un listener (sin eliminarlo)
    public function desactivarListener(string $evento, int $id): void
    {
        if (isset($this->listeners[$evento][$id])) {
            $this->listeners[$evento][$id]['activo'] = false;
        }
    }

    // Reactivar un listener previamente desactivado
    public function activarListener(string $evento, int $id): void
    {
        if (isset($this->listeners[$evento][$id])) {
            $this->listeners[$evento][$id]['activo'] = true;
        }
    }

    public function despachar(string $evento, array $datos = []): void
    {
        if (!isset($this->listeners[$evento])) {
            return;
        }

        // Obtener solo listeners activos y ordenar por prioridad
        $activos = array_filter($this->listeners[$evento], fn($l) => $l['activo']);
        uasort($activos, fn($a, $b) => $b['prioridad'] <=> $a['prioridad']);

        foreach ($activos as $listener) {
            call_user_func($listener['callback'], $datos);
        }
    }

    // Obtener informacion sobre los listeners registrados
    public function obtenerEstadisticas(string $evento): array
    {
        if (!isset($this->listeners[$evento])) {
            return ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        }

        $total = count($this->listeners[$evento]);
        $activos = count(array_filter($this->listeners[$evento], fn($l) => $l['activo']));

        return [
            'total'     => $total,
            'activos'   => $activos,
            'inactivos' => $total - $activos,
        ];
    }
}

$desp = new DespachadorAvanzado();

// Registrar listeners y guardar sus IDs
$idValidacion = $desp->escuchar('formulario.enviado', function ($datos) {
    echo "    [Validacion] Validando datos del formulario...\n";
}, prioridad: 100);

$idGuardado = $desp->escuchar('formulario.enviado', function ($datos) {
    echo "    [Guardado] Almacenando en base de datos...\n";
}, prioridad: 50);

$idAuditoria = $desp->escuchar('formulario.enviado', function ($datos) {
    echo "    [Auditoria] Registrando accion en log de auditoria...\n";
}, prioridad: 25);

$idDebug = $desp->escuchar('formulario.enviado', function ($datos) {
    echo "    [Debug] Volcado de datos: " . json_encode($datos) . "\n";
}, prioridad: 10);

// Primera ejecucion: todos los listeners activos
echo "  --- Ejecucion 1: Todos los listeners activos ---\n";
$desp->despachar('formulario.enviado', ['campo' => 'nombre', 'valor' => 'Juan']);

$stats = $desp->obtenerEstadisticas('formulario.enviado');
echo "  Estadisticas: " . json_encode($stats) . "\n\n";

// Desactivar el listener de debug (no lo necesitamos en produccion)
echo "  --- Ejecucion 2: Debug desactivado ---\n";
$desp->desactivarListener('formulario.enviado', $idDebug);
$desp->despachar('formulario.enviado', ['campo' => 'email', 'valor' => 'juan@mail.com']);

$stats = $desp->obtenerEstadisticas('formulario.enviado');
echo "  Estadisticas: " . json_encode($stats) . "\n\n";

// Remover completamente el listener de auditoria
echo "  --- Ejecucion 3: Auditoria removida, Debug reactivado ---\n";
$desp->removerListener('formulario.enviado', $idAuditoria);
$desp->activarListener('formulario.enviado', $idDebug);
$desp->despachar('formulario.enviado', ['campo' => 'telefono', 'valor' => '555-0123']);

$stats = $desp->obtenerEstadisticas('formulario.enviado');
echo "  Estadisticas: " . json_encode($stats) . "\n";

echo "\n  NOTA: Este patron permite control granular sobre que observadores\n";
echo "  estan activos, facilitando la depuracion y la configuracion\n";
echo "  dinamica de la aplicacion.\n";

?>
