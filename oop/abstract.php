<?php
// ============================================
// CLASES Y MÉTODOS ABSTRACTOS EN PHP
// abstract class, abstract methods, concreto vs abstracto
// ============================================

// --- Ejemplo 1: Clase abstracta básica ---
// Una clase abstracta no puede instanciarse directamente,
// sirve como plantilla para otras clases

abstract class Figura {
    protected string $color;

    public function __construct(string $color = "negro") {
        $this->color = $color;
    }

    // Métodos abstractos: DEBEN ser implementados por las clases hijas
    abstract public function area(): float;
    abstract public function perimetro(): float;

    // Métodos concretos: ya tienen implementación y se heredan tal cual
    public function describir(): string {
        $clase = static::class;
        return "{$clase} de color {$this->color} | Área: {$this->area()} | Perímetro: {$this->perimetro()}";
    }

    public function obtenerColor(): string {
        return $this->color;
    }
}

class Circulo extends Figura {
    public function __construct(
        private float $radio,
        string $color = "negro"
    ) {
        parent::__construct($color);
    }

    // Implementación obligatoria de los métodos abstractos
    public function area(): float {
        return round(M_PI * $this->radio ** 2, 2);
    }

    public function perimetro(): float {
        return round(2 * M_PI * $this->radio, 2);
    }
}

class Cuadrado extends Figura {
    public function __construct(
        private float $lado,
        string $color = "negro"
    ) {
        parent::__construct($color);
    }

    public function area(): float {
        return $this->lado ** 2;
    }

    public function perimetro(): float {
        return 4 * $this->lado;
    }
}

// No se puede hacer: $figura = new Figura("rojo"); // Error: Cannot instantiate abstract class

$circulo = new Circulo(5, "rojo");
$cuadrado = new Cuadrado(4, "azul");

echo $circulo->describir() . "\n";
// Circulo de color rojo | Área: 78.54 | Perímetro: 31.42

echo $cuadrado->describir() . "\n";
// Cuadrado de color azul | Área: 16 | Perímetro: 16

// Polimorfismo: podemos tratar ambos como Figura
$figuras = [$circulo, $cuadrado];
foreach ($figuras as $fig) {
    echo "  → Área: {$fig->area()}\n";
}


// --- Ejemplo 2: Clase abstracta como patrón Template Method ---
// El método plantilla define los pasos, las subclases implementan los detalles

abstract class GeneradorReporte {
    // Método plantilla: define el algoritmo paso a paso
    final public function generar(array $datos): string {
        $salida = $this->crearEncabezado();
        $salida .= $this->crearCuerpo($datos);
        $salida .= $this->crearPie();
        return $salida;
    }

    // Pasos abstractos que cada subclase debe implementar
    abstract protected function crearEncabezado(): string;
    abstract protected function crearCuerpo(array $datos): string;
    abstract protected function crearPie(): string;
}

class ReporteTexto extends GeneradorReporte {
    protected function crearEncabezado(): string {
        return "========== REPORTE ==========\n";
    }

    protected function crearCuerpo(array $datos): string {
        $cuerpo = "";
        foreach ($datos as $clave => $valor) {
            $cuerpo .= "  {$clave}: {$valor}\n";
        }
        return $cuerpo;
    }

    protected function crearPie(): string {
        return "==============================\n";
    }
}

class ReporteHTML extends GeneradorReporte {
    protected function crearEncabezado(): string {
        return "<html><body><h1>Reporte</h1><table border='1'>\n";
    }

    protected function crearCuerpo(array $datos): string {
        $cuerpo = "";
        foreach ($datos as $clave => $valor) {
            $cuerpo .= "<tr><td><strong>{$clave}</strong></td><td>{$valor}</td></tr>\n";
        }
        return $cuerpo;
    }

    protected function crearPie(): string {
        return "</table><p><em>Generado: " . date('Y-m-d') . "</em></p></body></html>\n";
    }
}

$datos = ['Ventas' => '$15,000', 'Gastos' => '$8,500', 'Ganancia' => '$6,500'];

$textoReporte = new ReporteTexto();
echo $textoReporte->generar($datos);
// ========== REPORTE ==========
//   Ventas: $15,000
//   Gastos: $8,500
//   Ganancia: $6,500
// ==============================

$htmlReporte = new ReporteHTML();
echo $htmlReporte->generar($datos);


// --- Ejemplo 3: Diferencia entre abstracto y concreto ---
// Una clase abstracta puede mezclar métodos abstractos y concretos

abstract class BaseDeDatos {
    protected string $host;
    protected string $nombre;
    protected bool $conectado = false;

    public function __construct(string $host, string $nombre) {
        $this->host = $host;
        $this->nombre = $nombre;
    }

    // Métodos abstractos: cada motor de BD los implementa diferente
    abstract protected function conectar(): bool;
    abstract protected function desconectar(): void;
    abstract public function ejecutarConsulta(string $sql): array;

    // Métodos concretos: lógica común para todos los motores
    public function estaConectado(): bool {
        return $this->conectado;
    }

    public function iniciar(): void {
        if (!$this->conectado) {
            $this->conectado = $this->conectar();
            echo "Conexión establecida a {$this->nombre}\n";
        }
    }

    public function cerrar(): void {
        if ($this->conectado) {
            $this->desconectar();
            $this->conectado = false;
            echo "Conexión cerrada a {$this->nombre}\n";
        }
    }

    // Método concreto que usa un método abstracto internamente
    public function seleccionar(string $tabla, array $condiciones = []): array {
        $sql = "SELECT * FROM {$tabla}";
        if (!empty($condiciones)) {
            $partes = [];
            foreach ($condiciones as $campo => $valor) {
                $partes[] = "{$campo} = '{$valor}'";
            }
            $sql .= " WHERE " . implode(" AND ", $partes);
        }
        return $this->ejecutarConsulta($sql);
    }
}

class MySQL extends BaseDeDatos {
    protected function conectar(): bool {
        echo "Conectando a MySQL en {$this->host}...\n";
        return true;
    }

    protected function desconectar(): void {
        echo "Desconectando de MySQL...\n";
    }

    public function ejecutarConsulta(string $sql): array {
        echo "MySQL ejecuta: {$sql}\n";
        return ['resultado' => 'datos de MySQL'];
    }
}

class PostgreSQL extends BaseDeDatos {
    protected function conectar(): bool {
        echo "Conectando a PostgreSQL en {$this->host}...\n";
        return true;
    }

    protected function desconectar(): void {
        echo "Desconectando de PostgreSQL...\n";
    }

    public function ejecutarConsulta(string $sql): array {
        echo "PostgreSQL ejecuta: {$sql}\n";
        return ['resultado' => 'datos de PostgreSQL'];
    }
}

$mysql = new MySQL("localhost", "tienda_db");
$mysql->iniciar();
$mysql->seleccionar("productos", ['categoria' => 'electrónica']);
$mysql->cerrar();
// Conectando a MySQL en localhost...
// Conexión establecida a tienda_db
// MySQL ejecuta: SELECT * FROM productos WHERE categoria = 'electrónica'
// Desconectando de MySQL...
// Conexión cerrada a tienda_db


// --- Ejemplo 4: Clase abstracta con clase abstracta hija ---
// Una clase abstracta puede extender otra clase abstracta

abstract class Notificacion {
    abstract public function enviar(string $destinatario, string $mensaje): bool;

    public function formatearMensaje(string $mensaje): string {
        return "[" . date('H:i') . "] " . $mensaje;
    }
}

// Clase abstracta que extiende otra abstracta - añade más abstracción
abstract class NotificacionConPrioridad extends Notificacion {
    abstract public function obtenerPrioridad(): string;

    // Sobreescribimos para incluir la prioridad
    public function formatearMensaje(string $mensaje): string {
        $prioridad = $this->obtenerPrioridad();
        return "[{$prioridad}] " . parent::formatearMensaje($mensaje);
    }
}

// Solo la clase final (concreta) necesita implementar TODO
class AlertaCritica extends NotificacionConPrioridad {
    public function enviar(string $destinatario, string $mensaje): bool {
        $mensajeFormateado = $this->formatearMensaje($mensaje);
        echo "¡ALERTA CRÍTICA! → {$destinatario}: {$mensajeFormateado}\n";
        return true;
    }

    public function obtenerPrioridad(): string {
        return "CRÍTICA";
    }
}

class AvisoInformativo extends NotificacionConPrioridad {
    public function enviar(string $destinatario, string $mensaje): bool {
        $mensajeFormateado = $this->formatearMensaje($mensaje);
        echo "Info → {$destinatario}: {$mensajeFormateado}\n";
        return true;
    }

    public function obtenerPrioridad(): string {
        return "INFO";
    }
}

$alerta = new AlertaCritica();
$alerta->enviar("admin@empresa.com", "Servidor caído");
// ¡ALERTA CRÍTICA! → admin@empresa.com: [CRÍTICA] [10:30] Servidor caído

$info = new AvisoInformativo();
$info->enviar("equipo@empresa.com", "Despliegue completado");
// Info → equipo@empresa.com: [INFO] [10:30] Despliegue completado

?>
