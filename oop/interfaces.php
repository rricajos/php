<?php
// ============================================
// INTERFACES EN PHP - Declaración, implements, múltiples interfaces
// ============================================

// --- Ejemplo 1: Interface básica y su implementación ---
// Una interface define un contrato: qué métodos debe tener una clase

interface Almacenable {
    // Las interfaces solo declaran la firma del método, sin cuerpo
    public function guardar(): bool;
    public function eliminar(int $id): bool;
    public function buscarPorId(int $id): ?array;
    public function listarTodos(): array;
}

// Una clase que implementa la interface DEBE definir todos sus métodos
class UsuarioRepositorio implements Almacenable {
    private array $datos = [];

    public function guardar(): bool {
        echo "Usuario guardado en la base de datos\n";
        return true;
    }

    public function eliminar(int $id): bool {
        echo "Usuario #{$id} eliminado\n";
        unset($this->datos[$id]);
        return true;
    }

    public function buscarPorId(int $id): ?array {
        return $this->datos[$id] ?? null;
    }

    public function listarTodos(): array {
        return $this->datos;
    }

    // Puede tener métodos adicionales más allá de la interface
    public function buscarPorEmail(string $email): ?array {
        foreach ($this->datos as $usuario) {
            if ($usuario['email'] === $email) {
                return $usuario;
            }
        }
        return null;
    }
}

$repo = new UsuarioRepositorio();
$repo->guardar(); // Usuario guardado en la base de datos

// Podemos verificar si un objeto implementa una interface
echo ($repo instanceof Almacenable) ? "Implementa Almacenable\n" : "";


// --- Ejemplo 2: Múltiples interfaces en una sola clase ---
// A diferencia de la herencia, una clase puede implementar múltiples interfaces

interface Serializable2 {
    public function serializar(): string;
    public function deserializar(string $datos): void;
}

interface Validable {
    public function validar(): bool;
    public function obtenerErrores(): array;
}

interface Imprimible {
    public function aTexto(): string;
    public function aHTML(): string;
}

// Una clase puede implementar múltiples interfaces separadas por coma
class Pedido implements Serializable2, Validable, Imprimible {
    private array $errores = [];

    public function __construct(
        private int $id,
        private string $cliente,
        private array $productos,
        private float $total
    ) {}

    // Métodos de Serializable2
    public function serializar(): string {
        return json_encode([
            'id' => $this->id,
            'cliente' => $this->cliente,
            'productos' => $this->productos,
            'total' => $this->total,
        ]);
    }

    public function deserializar(string $datos): void {
        $obj = json_decode($datos, true);
        $this->id = $obj['id'];
        $this->cliente = $obj['cliente'];
        $this->productos = $obj['productos'];
        $this->total = $obj['total'];
    }

    // Métodos de Validable
    public function validar(): bool {
        $this->errores = [];

        if (empty($this->cliente)) {
            $this->errores[] = "El cliente es obligatorio";
        }
        if (empty($this->productos)) {
            $this->errores[] = "Debe haber al menos un producto";
        }
        if ($this->total <= 0) {
            $this->errores[] = "El total debe ser mayor a cero";
        }

        return empty($this->errores);
    }

    public function obtenerErrores(): array {
        return $this->errores;
    }

    // Métodos de Imprimible
    public function aTexto(): string {
        $prods = implode(", ", $this->productos);
        return "Pedido #{$this->id} | Cliente: {$this->cliente} | Productos: {$prods} | Total: \${$this->total}";
    }

    public function aHTML(): string {
        $items = "";
        foreach ($this->productos as $prod) {
            $items .= "  <li>{$prod}</li>\n";
        }
        return "<div class='pedido'>
  <h3>Pedido #{$this->id}</h3>
  <p>Cliente: {$this->cliente}</p>
  <ul>\n{$items}  </ul>
  <p><strong>Total: \${$this->total}</strong></p>
</div>";
    }
}

$pedido = new Pedido(1, "Laura Martínez", ["Laptop", "Mouse", "Teclado"], 1599.99);

echo $pedido->aTexto() . "\n";
// Pedido #1 | Cliente: Laura Martínez | Productos: Laptop, Mouse, Teclado | Total: $1599.99

echo "¿Válido? " . ($pedido->validar() ? "Sí" : "No") . "\n"; // ¿Válido? Sí
echo "JSON: " . $pedido->serializar() . "\n";


// --- Ejemplo 3: Interfaces como tipos para polimorfismo ---
// Las interfaces nos permiten usar type hints para aceptar cualquier implementación

interface CanalNotificacion {
    public function enviar(string $destinatario, string $mensaje): bool;
    public function obtenerNombre(): string;
}

class NotificacionEmail implements CanalNotificacion {
    public function enviar(string $destinatario, string $mensaje): bool {
        echo "[EMAIL] Enviando a {$destinatario}: {$mensaje}\n";
        return true;
    }

    public function obtenerNombre(): string {
        return "Email";
    }
}

class NotificacionSMS implements CanalNotificacion {
    public function enviar(string $destinatario, string $mensaje): bool {
        echo "[SMS] Enviando a {$destinatario}: {$mensaje}\n";
        return true;
    }

    public function obtenerNombre(): string {
        return "SMS";
    }
}

class NotificacionPush implements CanalNotificacion {
    public function enviar(string $destinatario, string $mensaje): bool {
        echo "[PUSH] Enviando a {$destinatario}: {$mensaje}\n";
        return true;
    }

    public function obtenerNombre(): string {
        return "Notificación Push";
    }
}

// El despachador acepta CUALQUIER objeto que implemente CanalNotificacion
class DespachadorNotificaciones {
    /** @var CanalNotificacion[] */
    private array $canales = [];

    public function agregarCanal(CanalNotificacion $canal): void {
        $this->canales[] = $canal;
    }

    // Envia por todos los canales registrados
    public function notificarATodos(string $destinatario, string $mensaje): void {
        foreach ($this->canales as $canal) {
            $canal->enviar($destinatario, $mensaje);
            echo "  → Enviado por: {$canal->obtenerNombre()}\n";
        }
    }
}

$despachador = new DespachadorNotificaciones();
$despachador->agregarCanal(new NotificacionEmail());
$despachador->agregarCanal(new NotificacionSMS());
$despachador->agregarCanal(new NotificacionPush());

$despachador->notificarATodos("usuario@ejemplo.com", "¡Tu pedido ha sido enviado!");
// [EMAIL] Enviando a usuario@ejemplo.com: ¡Tu pedido ha sido enviado!
//   → Enviado por: Email
// [SMS] Enviando a usuario@ejemplo.com: ¡Tu pedido ha sido enviado!
//   → Enviado por: SMS
// [PUSH] Enviando a usuario@ejemplo.com: ¡Tu pedido ha sido enviado!
//   → Enviado por: Notificación Push


// --- Ejemplo 4: Interface que extiende otras interfaces ---
// Las interfaces pueden heredar de otras interfaces

interface Leible {
    public function leer(int $id): ?array;
    public function leerTodos(): array;
}

interface Escribible {
    public function crear(array $datos): int;
    public function actualizar(int $id, array $datos): bool;
    public function borrar(int $id): bool;
}

// Una interface puede extender múltiples interfaces
interface RepositorioCompleto extends Leible, Escribible {
    // Puede añadir métodos propios adicionales
    public function contar(): int;
    public function existeId(int $id): bool;
}

class ProductoRepositorio implements RepositorioCompleto {
    private array $productos = [];
    private int $siguienteId = 1;

    public function leer(int $id): ?array {
        return $this->productos[$id] ?? null;
    }

    public function leerTodos(): array {
        return $this->productos;
    }

    public function crear(array $datos): int {
        $id = $this->siguienteId++;
        $this->productos[$id] = array_merge(['id' => $id], $datos);
        return $id;
    }

    public function actualizar(int $id, array $datos): bool {
        if (!isset($this->productos[$id])) return false;
        $this->productos[$id] = array_merge($this->productos[$id], $datos);
        return true;
    }

    public function borrar(int $id): bool {
        if (!isset($this->productos[$id])) return false;
        unset($this->productos[$id]);
        return true;
    }

    public function contar(): int {
        return count($this->productos);
    }

    public function existeId(int $id): bool {
        return isset($this->productos[$id]);
    }
}

$repo = new ProductoRepositorio();
$id1 = $repo->crear(['nombre' => 'Monitor', 'precio' => 350.00]);
$id2 = $repo->crear(['nombre' => 'Teclado', 'precio' => 75.00]);

echo "Total productos: " . $repo->contar() . "\n"; // Total productos: 2
echo "¿Existe #1? " . ($repo->existeId($id1) ? "Sí" : "No") . "\n"; // ¿Existe #1? Sí

$repo->actualizar($id1, ['precio' => 299.99]);
print_r($repo->leer($id1));
// Array ( [id] => 1 [nombre] => Monitor [precio] => 299.99 )

?>
