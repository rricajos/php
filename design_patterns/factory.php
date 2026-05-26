<?php
/**
 * =============================================================================
 * PATRON FACTORY (FABRICA) EN PHP
 * =============================================================================
 *
 * Los patrones Factory encapsulan la logica de creacion de objetos, permitiendo
 * que el codigo cliente trabaje con interfaces en lugar de clases concretas.
 *
 * Variantes principales:
 * - Simple Factory: una clase con un metodo que decide que objeto crear
 * - Factory Method: subclases deciden que clase concreta instanciar
 * - Abstract Factory: crea familias de objetos relacionados
 *
 * Ventajas:
 * - Desacopla la creacion del uso de objetos
 * - Facilita agregar nuevos tipos sin modificar codigo existente (Open/Closed)
 * - Centraliza la logica de construccion compleja
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Simple Factory (Fabrica Simple)
// =============================================================================
// Problema: Tenemos multiples tipos de notificacion (email, SMS, push) y
// no queremos que el codigo cliente sepa como construir cada una.

echo "=== Ejemplo 1: Simple Factory de Notificaciones ===\n\n";

// Interfaz comun para todas las notificaciones
interface Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void;
    public function obtenerTipo(): string;
}

class NotificacionEmail implements Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        echo "  [EMAIL] Enviando a {$destinatario}: {$mensaje}\n";
    }

    public function obtenerTipo(): string
    {
        return 'email';
    }
}

class NotificacionSMS implements Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        // Los SMS tienen limite de caracteres
        $mensajeCorto = mb_substr($mensaje, 0, 160);
        echo "  [SMS] Enviando a {$destinatario}: {$mensajeCorto}\n";
    }

    public function obtenerTipo(): string
    {
        return 'sms';
    }
}

class NotificacionPush implements Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        echo "  [PUSH] Notificacion push a dispositivo {$destinatario}: {$mensaje}\n";
    }

    public function obtenerTipo(): string
    {
        return 'push';
    }
}

// Fabrica Simple: centraliza la decision de que clase crear
class FabricaNotificaciones
{
    public static function crear(string $tipo): Notificacion
    {
        return match ($tipo) {
            'email' => new NotificacionEmail(),
            'sms'   => new NotificacionSMS(),
            'push'  => new NotificacionPush(),
            default => throw new \InvalidArgumentException(
                "Tipo de notificacion desconocido: {$tipo}"
            ),
        };
    }
}

// El cliente no necesita conocer las clases concretas
$tiposRequeridos = ['email', 'sms', 'push'];

foreach ($tiposRequeridos as $tipo) {
    $notificacion = FabricaNotificaciones::crear($tipo);
    $notificacion->enviar('usuario_123', "Su pedido #4567 ha sido enviado.");
}

echo "\n";


// =============================================================================
// Ejemplo 2: Factory Method (Metodo Fabrica) con Creador Abstracto
// =============================================================================
// Problema: Diferentes tipos de documentos necesitan ser exportados en
// distintos formatos, y cada formato tiene su propia logica de generacion.

echo "=== Ejemplo 2: Factory Method para Exportacion de Documentos ===\n\n";

// Producto: el documento exportado
interface DocumentoExportado
{
    public function generar(array $datos): string;
    public function obtenerExtension(): string;
    public function obtenerTipoMime(): string;
}

class DocumentoPDF implements DocumentoExportado
{
    public function generar(array $datos): string
    {
        // Simulacion de generacion de PDF
        $contenido = "%%PDF-1.4\n";
        foreach ($datos as $clave => $valor) {
            $contenido .= "{$clave}: {$valor}\n";
        }
        return $contenido;
    }

    public function obtenerExtension(): string { return 'pdf'; }
    public function obtenerTipoMime(): string { return 'application/pdf'; }
}

class DocumentoCSV implements DocumentoExportado
{
    public function generar(array $datos): string
    {
        $lineas = [implode(',', array_keys($datos))];
        $lineas[] = implode(',', array_values($datos));
        return implode("\n", $lineas);
    }

    public function obtenerExtension(): string { return 'csv'; }
    public function obtenerTipoMime(): string { return 'text/csv'; }
}

class DocumentoJSON implements DocumentoExportado
{
    public function generar(array $datos): string
    {
        return json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function obtenerExtension(): string { return 'json'; }
    public function obtenerTipoMime(): string { return 'application/json'; }
}

// Creador abstracto: define el Factory Method
abstract class ExportadorInforme
{
    // Factory Method: las subclases deciden que documento crear
    abstract protected function crearDocumento(): DocumentoExportado;

    // Metodo plantilla que usa el Factory Method
    public function exportar(array $datos): string
    {
        $documento = $this->crearDocumento();

        echo "  Generando archivo .{$documento->obtenerExtension()} ";
        echo "(MIME: {$documento->obtenerTipoMime()})\n";

        $contenido = $documento->generar($datos);

        echo "  Contenido generado ({$this->contarBytes($contenido)} bytes)\n";

        return $contenido;
    }

    private function contarBytes(string $contenido): int
    {
        return strlen($contenido);
    }
}

// Creadores concretos: cada uno produce un tipo diferente de documento
class ExportadorPDF extends ExportadorInforme
{
    protected function crearDocumento(): DocumentoExportado
    {
        return new DocumentoPDF();
    }
}

class ExportadorCSV extends ExportadorInforme
{
    protected function crearDocumento(): DocumentoExportado
    {
        return new DocumentoCSV();
    }
}

class ExportadorJSON extends ExportadorInforme
{
    protected function crearDocumento(): DocumentoExportado
    {
        return new DocumentoJSON();
    }
}

$datosInforme = [
    'titulo'  => 'Ventas Mensuales',
    'periodo' => 'Enero 2026',
    'total'   => '$45,230.00',
    'unidades' => '1,247',
];

$exportadores = [
    new ExportadorPDF(),
    new ExportadorCSV(),
    new ExportadorJSON(),
];

foreach ($exportadores as $exportador) {
    echo "  --- " . get_class($exportador) . " ---\n";
    $exportador->exportar($datosInforme);
    echo "\n";
}


// =============================================================================
// Ejemplo 3: Abstract Factory para Familias de Objetos Relacionados
// =============================================================================
// Problema: Una aplicacion debe soportar multiples temas visuales (claro/oscuro),
// y cada tema requiere un conjunto coherente de componentes UI.

echo "=== Ejemplo 3: Abstract Factory - Temas de Interfaz de Usuario ===\n\n";

// Productos abstractos: componentes de la interfaz
interface Boton
{
    public function renderizar(): string;
}

interface CampoTexto
{
    public function renderizar(): string;
}

interface Tarjeta
{
    public function renderizar(string $contenido): string;
}

// Familia 1: Tema Claro
class BotonClaro implements Boton
{
    public function renderizar(): string
    {
        return '<button class="btn-light" style="bg:#fff; color:#333">Accion</button>';
    }
}

class CampoTextoClaro implements CampoTexto
{
    public function renderizar(): string
    {
        return '<input class="input-light" style="bg:#fafafa; border:#ccc" />';
    }
}

class TarjetaClara implements Tarjeta
{
    public function renderizar(string $contenido): string
    {
        return "<div class=\"card-light\" style=\"bg:#fff; shadow:soft\">{$contenido}</div>";
    }
}

// Familia 2: Tema Oscuro
class BotonOscuro implements Boton
{
    public function renderizar(): string
    {
        return '<button class="btn-dark" style="bg:#333; color:#fff">Accion</button>';
    }
}

class CampoTextoOscuro implements CampoTexto
{
    public function renderizar(): string
    {
        return '<input class="input-dark" style="bg:#2a2a2a; border:#555" />';
    }
}

class TarjetaOscura implements Tarjeta
{
    public function renderizar(string $contenido): string
    {
        return "<div class=\"card-dark\" style=\"bg:#1a1a1a; shadow:deep\">{$contenido}</div>";
    }
}

// Abstract Factory: declara la creacion de la familia completa
interface FabricaTemaUI
{
    public function crearBoton(): Boton;
    public function crearCampoTexto(): CampoTexto;
    public function crearTarjeta(): Tarjeta;
}

class FabricaTemaClaro implements FabricaTemaUI
{
    public function crearBoton(): Boton { return new BotonClaro(); }
    public function crearCampoTexto(): CampoTexto { return new CampoTextoClaro(); }
    public function crearTarjeta(): Tarjeta { return new TarjetaClara(); }
}

class FabricaTemaOscuro implements FabricaTemaUI
{
    public function crearBoton(): Boton { return new BotonOscuro(); }
    public function crearCampoTexto(): CampoTexto { return new CampoTextoOscuro(); }
    public function crearTarjeta(): Tarjeta { return new TarjetaOscura(); }
}

// Funcion que construye una interfaz usando la fabrica
// No le importa que tema es: trabaja con la interfaz abstracta
function construirFormularioRegistro(FabricaTemaUI $fabrica): void
{
    $boton = $fabrica->crearBoton();
    $campo = $fabrica->crearCampoTexto();
    $tarjeta = $fabrica->crearTarjeta();

    echo "  Formulario de Registro:\n";
    echo "    Tarjeta: " . $tarjeta->renderizar("Crear Cuenta") . "\n";
    echo "    Campo:   " . $campo->renderizar() . "\n";
    echo "    Boton:   " . $boton->renderizar() . "\n";
}

echo "  --- Tema Claro ---\n";
construirFormularioRegistro(new FabricaTemaClaro());
echo "\n";

echo "  --- Tema Oscuro ---\n";
construirFormularioRegistro(new FabricaTemaOscuro());
echo "\n";


// =============================================================================
// Ejemplo 4: Fabrica de Pasarelas de Pago (Caso Practico Real)
// =============================================================================
// Problema: Una tienda online debe soportar multiples pasarelas de pago
// (PayPal, Stripe, Transferencia Bancaria) con una interfaz unificada.

echo "=== Ejemplo 4: Fabrica de Pasarelas de Pago ===\n\n";

// Resultado estandarizado de un pago
class ResultadoPago
{
    public function __construct(
        public readonly bool $exitoso,
        public readonly string $transaccionId,
        public readonly string $pasarela,
        public readonly float $monto,
        public readonly string $moneda,
        public readonly string $mensaje,
    ) {}
}

// Interfaz comun para todas las pasarelas
interface PasarelaPago
{
    public function procesar(float $monto, string $moneda, array $datosCliente): ResultadoPago;
    public function reembolsar(string $transaccionId, float $monto): ResultadoPago;
    public function obtenerNombre(): string;
    public function soportaMoneda(string $moneda): bool;
}

class PasarelaPayPal implements PasarelaPago
{
    public function __construct(
        private string $clienteId,
        private string $secreto
    ) {}

    public function procesar(float $monto, string $moneda, array $datosCliente): ResultadoPago
    {
        // Simulacion de llamada a la API de PayPal
        echo "  [PayPal] Procesando \${$monto} {$moneda} via API REST...\n";
        $txId = 'PP-' . strtoupper(substr(md5(uniqid()), 0, 12));

        return new ResultadoPago(
            exitoso: true,
            transaccionId: $txId,
            pasarela: 'paypal',
            monto: $monto,
            moneda: $moneda,
            mensaje: 'Pago aprobado por PayPal'
        );
    }

    public function reembolsar(string $transaccionId, float $monto): ResultadoPago
    {
        echo "  [PayPal] Reembolsando \${$monto} para transaccion {$transaccionId}...\n";
        return new ResultadoPago(true, $transaccionId, 'paypal', $monto, 'USD', 'Reembolso procesado');
    }

    public function obtenerNombre(): string { return 'PayPal'; }

    public function soportaMoneda(string $moneda): bool
    {
        return in_array($moneda, ['USD', 'EUR', 'MXN', 'GBP']);
    }
}

class PasarelaStripe implements PasarelaPago
{
    public function __construct(
        private string $claveSecreta
    ) {}

    public function procesar(float $monto, string $moneda, array $datosCliente): ResultadoPago
    {
        echo "  [Stripe] Procesando \${$monto} {$moneda} via Stripe Charges API...\n";
        $txId = 'ch_' . strtoupper(substr(md5(uniqid()), 0, 16));

        return new ResultadoPago(
            exitoso: true,
            transaccionId: $txId,
            pasarela: 'stripe',
            monto: $monto,
            moneda: $moneda,
            mensaje: 'Cargo completado exitosamente'
        );
    }

    public function reembolsar(string $transaccionId, float $monto): ResultadoPago
    {
        echo "  [Stripe] Reembolsando \${$monto} para {$transaccionId}...\n";
        return new ResultadoPago(true, $transaccionId, 'stripe', $monto, 'USD', 'Reembolso emitido');
    }

    public function obtenerNombre(): string { return 'Stripe'; }

    public function soportaMoneda(string $moneda): bool
    {
        return in_array($moneda, ['USD', 'EUR', 'MXN', 'GBP', 'JPY', 'CAD']);
    }
}

class PasarelaBancaria implements PasarelaPago
{
    public function __construct(
        private string $codigoBanco,
        private string $cuentaDestino
    ) {}

    public function procesar(float $monto, string $moneda, array $datosCliente): ResultadoPago
    {
        echo "  [Banco] Procesando transferencia de \${$monto} {$moneda}...\n";
        $txId = 'TRF-' . date('Ymd') . '-' . rand(10000, 99999);

        return new ResultadoPago(
            exitoso: true,
            transaccionId: $txId,
            pasarela: 'banco',
            monto: $monto,
            moneda: $moneda,
            mensaje: 'Transferencia en proceso (24-48 horas)'
        );
    }

    public function reembolsar(string $transaccionId, float $monto): ResultadoPago
    {
        echo "  [Banco] Iniciando devolucion de \${$monto}...\n";
        return new ResultadoPago(true, $transaccionId, 'banco', $monto, 'MXN', 'Devolucion en proceso');
    }

    public function obtenerNombre(): string { return 'Transferencia Bancaria'; }

    public function soportaMoneda(string $moneda): bool
    {
        return in_array($moneda, ['MXN', 'USD']);
    }
}

// Fabrica que crea la pasarela segun la configuracion
class FabricaPasarelasPago
{
    // Configuracion centralizada de credenciales por pasarela
    private array $configuraciones;

    public function __construct(array $configuraciones)
    {
        $this->configuraciones = $configuraciones;
    }

    public function crear(string $tipo): PasarelaPago
    {
        $config = $this->configuraciones[$tipo]
            ?? throw new \InvalidArgumentException("Pasarela no configurada: {$tipo}");

        return match ($tipo) {
            'paypal' => new PasarelaPayPal(
                $config['cliente_id'],
                $config['secreto']
            ),
            'stripe' => new PasarelaStripe(
                $config['clave_secreta']
            ),
            'banco' => new PasarelaBancaria(
                $config['codigo_banco'],
                $config['cuenta_destino']
            ),
            default => throw new \InvalidArgumentException("Tipo de pasarela desconocido: {$tipo}"),
        };
    }

    // Obtener la mejor pasarela disponible para una moneda
    public function obtenerPasarelaPorMoneda(string $moneda): PasarelaPago
    {
        $prioridad = ['stripe', 'paypal', 'banco'];

        foreach ($prioridad as $tipo) {
            if (isset($this->configuraciones[$tipo])) {
                $pasarela = $this->crear($tipo);
                if ($pasarela->soportaMoneda($moneda)) {
                    return $pasarela;
                }
            }
        }

        throw new \RuntimeException("No hay pasarela disponible para la moneda: {$moneda}");
    }
}

// Configuracion de la aplicacion (normalmente vendria de un archivo .env)
$fabrica = new FabricaPasarelasPago([
    'paypal' => ['cliente_id' => 'PP_CLIENT_123', 'secreto' => 'PP_SECRET_456'],
    'stripe' => ['clave_secreta' => 'sk_test_ABC123'],
    'banco'  => ['codigo_banco' => 'BMEX', 'cuenta_destino' => '0123456789'],
]);

// Procesar pagos con distintas pasarelas
$pasarela = $fabrica->crear('stripe');
$resultado = $pasarela->procesar(149.99, 'USD', ['email' => 'cliente@email.com']);
echo "  Resultado: {$resultado->mensaje} (TX: {$resultado->transaccionId})\n\n";

// Seleccion automatica segun moneda
$pasarela = $fabrica->obtenerPasarelaPorMoneda('JPY');
echo "  Pasarela seleccionada para JPY: {$pasarela->obtenerNombre()}\n";
$resultado = $pasarela->procesar(15000, 'JPY', ['email' => 'cliente@email.jp']);
echo "  Resultado: {$resultado->mensaje}\n\n";


// =============================================================================
// Ejemplo 5: Metodos de Fabrica Estaticos vs Constructores
// =============================================================================
// Problema: Los constructores tienen limitaciones (un solo por clase, nombre
// fijo, no pueden devolver null o subtipos). Los metodos estaticos de fabrica
// ofrecen mayor flexibilidad y expresividad.

echo "=== Ejemplo 5: Metodos Estaticos de Fabrica vs Constructores ===\n\n";

class Dinero
{
    // Constructor privado: fuerza el uso de metodos de fabrica
    private function __construct(
        private readonly int $centavos,  // Almacenamos en centavos para evitar errores de punto flotante
        private readonly string $moneda
    ) {}

    // --- Metodos de fabrica estaticos: mas expresivos que 'new Dinero(...)' ---

    // Crear desde cantidad en unidades principales (pesos, dolares, etc.)
    public static function desdePesos(float $cantidad): self
    {
        return new self((int) round($cantidad * 100), 'MXN');
    }

    public static function desdeDolares(float $cantidad): self
    {
        return new self((int) round($cantidad * 100), 'USD');
    }

    public static function desdeEuros(float $cantidad): self
    {
        return new self((int) round($cantidad * 100), 'EUR');
    }

    // Crear desde centavos directamente (preciso, sin redondeo)
    public static function desdeCentavos(int $centavos, string $moneda): self
    {
        return new self($centavos, $moneda);
    }

    // Crear un valor de cero
    public static function cero(string $moneda = 'MXN'): self
    {
        return new self(0, $moneda);
    }

    // Parsear desde un string formateado
    public static function desdeTexto(string $texto): self
    {
        // Soporta formatos como "$100.50 USD", "150.00 MXN"
        if (preg_match('/^\$?([\d,]+\.?\d*)\s*([A-Z]{3})$/', trim($texto), $coincidencias)) {
            $cantidad = (float) str_replace(',', '', $coincidencias[1]);
            $moneda = $coincidencias[2];
            return new self((int) round($cantidad * 100), $moneda);
        }
        throw new \InvalidArgumentException("Formato de dinero invalido: {$texto}");
    }

    // Operaciones que devuelven nuevos objetos (inmutabilidad)
    public function sumar(Dinero $otro): self
    {
        $this->verificarMismaMoneda($otro);
        return new self($this->centavos + $otro->centavos, $this->moneda);
    }

    public function restar(Dinero $otro): self
    {
        $this->verificarMismaMoneda($otro);
        return new self($this->centavos - $otro->centavos, $this->moneda);
    }

    public function multiplicar(float $factor): self
    {
        return new self((int) round($this->centavos * $factor), $this->moneda);
    }

    public function esMayorQue(Dinero $otro): bool
    {
        $this->verificarMismaMoneda($otro);
        return $this->centavos > $otro->centavos;
    }

    private function verificarMismaMoneda(Dinero $otro): void
    {
        if ($this->moneda !== $otro->moneda) {
            throw new \InvalidArgumentException(
                "No se pueden operar monedas diferentes: {$this->moneda} y {$otro->moneda}"
            );
        }
    }

    public function formatear(): string
    {
        $simbolos = ['MXN' => '$', 'USD' => 'US$', 'EUR' => 'EUR '];
        $simbolo = $simbolos[$this->moneda] ?? $this->moneda . ' ';
        return $simbolo . number_format($this->centavos / 100, 2);
    }

    public function __toString(): string
    {
        return $this->formatear();
    }
}

/**
 * Ventajas de los metodos de fabrica estaticos sobre constructores:
 *
 * 1. NOMBRES DESCRIPTIVOS: desdePesos() es mas claro que new Dinero(10000, 'MXN')
 * 2. MULTIPLES FORMAS DE CREAR: desdePesos(), desdeCentavos(), desdeTexto()
 * 3. CONTROL DE INSTANCIAS: pueden devolver cache o null si es necesario
 * 4. PUEDEN DEVOLVER SUBTIPOS: polimorfismo en la creacion
 * 5. VALIDACION CENTRALIZADA: cada metodo valida su formato de entrada
 */

// Los metodos de fabrica hacen el codigo mucho mas legible
$precio = Dinero::desdePesos(299.99);
$descuento = Dinero::desdePesos(50.00);
$impuesto = $precio->multiplicar(0.16); // IVA 16%

$subtotal = $precio->restar($descuento);
$total = $subtotal->sumar($impuesto);

echo "  Precio:     {$precio}\n";
echo "  Descuento: -{$descuento}\n";
echo "  IVA (16%):  {$impuesto}\n";
echo "  Total:      {$total}\n\n";

// Desde texto (util para importar datos)
$importado = Dinero::desdeTexto('1,500.00 MXN');
echo "  Importado desde texto: {$importado}\n";

// Comparacion
$presupuesto = Dinero::desdePesos(1000.00);
echo "  El importe importado excede el presupuesto: "
    . ($importado->esMayorQue($presupuesto) ? 'SI' : 'NO') . "\n";

?>
