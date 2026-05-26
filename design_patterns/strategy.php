<?php
/**
 * =============================================================================
 * PATRON STRATEGY (ESTRATEGIA) EN PHP
 * =============================================================================
 *
 * El patron Strategy define una familia de algoritmos, encapsula cada uno
 * y los hace intercambiables. Permite que el algoritmo varie independientemente
 * de los clientes que lo usan.
 *
 * Componentes clave:
 * - Strategy (interfaz): define el contrato para todos los algoritmos
 * - ConcreteStrategy: implementaciones especificas del algoritmo
 * - Context: clase que usa una estrategia y puede cambiarla en tiempo de ejecucion
 *
 * Ventajas:
 * - Elimina cadenas de if/elseif o switch para seleccionar algoritmos
 * - Cumple con el principio Open/Closed (abierto a extension, cerrado a modificacion)
 * - Facilita pruebas unitarias de cada algoritmo por separado
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Interfaz Strategy con Algoritmos Intercambiables
// =============================================================================
// Problema: Una aplicacion necesita validar datos de entrada de multiples
// formas (email, telefono, codigo postal), y las reglas pueden cambiar.

echo "=== Ejemplo 1: Estrategias de Validacion ===\n\n";

// Interfaz Strategy para validadores
interface EstrategiaValidacion
{
    public function validar(string $valor): bool;
    public function obtenerMensajeError(): string;
    public function obtenerNombre(): string;
}

class ValidadorEmail implements EstrategiaValidacion
{
    public function validar(string $valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function obtenerMensajeError(): string
    {
        return 'El email proporcionado no tiene un formato valido.';
    }

    public function obtenerNombre(): string { return 'Email'; }
}

class ValidadorTelefono implements EstrategiaValidacion
{
    public function validar(string $valor): bool
    {
        // Acepta formatos: +52 55 1234 5678, 5512345678, (55) 1234-5678
        $limpio = preg_replace('/[\s\-\(\)\+]/', '', $valor);
        return preg_match('/^\d{10,13}$/', $limpio) === 1;
    }

    public function obtenerMensajeError(): string
    {
        return 'El telefono debe tener entre 10 y 13 digitos.';
    }

    public function obtenerNombre(): string { return 'Telefono'; }
}

class ValidadorCodigoPostal implements EstrategiaValidacion
{
    public function validar(string $valor): bool
    {
        // Codigo postal mexicano: 5 digitos
        return preg_match('/^\d{5}$/', $valor) === 1;
    }

    public function obtenerMensajeError(): string
    {
        return 'El codigo postal debe ser de 5 digitos.';
    }

    public function obtenerNombre(): string { return 'Codigo Postal'; }
}

class ValidadorContrasena implements EstrategiaValidacion
{
    public function validar(string $valor): bool
    {
        // Minimo 8 caracteres, al menos una mayuscula, una minuscula y un numero
        return strlen($valor) >= 8
            && preg_match('/[A-Z]/', $valor)
            && preg_match('/[a-z]/', $valor)
            && preg_match('/[0-9]/', $valor);
    }

    public function obtenerMensajeError(): string
    {
        return 'La contrasena debe tener al menos 8 caracteres, una mayuscula, una minuscula y un numero.';
    }

    public function obtenerNombre(): string { return 'Contrasena'; }
}

// Contexto: valida un campo usando la estrategia inyectada
class ValidadorCampo
{
    private EstrategiaValidacion $estrategia;

    public function __construct(EstrategiaValidacion $estrategia)
    {
        $this->estrategia = $estrategia;
    }

    // Permite cambiar la estrategia en tiempo de ejecucion
    public function establecerEstrategia(EstrategiaValidacion $estrategia): void
    {
        $this->estrategia = $estrategia;
    }

    public function validar(string $valor): array
    {
        $esValido = $this->estrategia->validar($valor);
        return [
            'campo'   => $this->estrategia->obtenerNombre(),
            'valor'   => $valor,
            'valido'  => $esValido,
            'mensaje' => $esValido ? 'Valor correcto.' : $this->estrategia->obtenerMensajeError(),
        ];
    }
}

// Probar distintas estrategias
$validador = new ValidadorCampo(new ValidadorEmail());

$pruebas = [
    ['estrategia' => new ValidadorEmail(), 'valor' => 'usuario@dominio.com'],
    ['estrategia' => new ValidadorEmail(), 'valor' => 'no-es-un-email'],
    ['estrategia' => new ValidadorTelefono(), 'valor' => '+52 55 1234 5678'],
    ['estrategia' => new ValidadorTelefono(), 'valor' => '123'],
    ['estrategia' => new ValidadorCodigoPostal(), 'valor' => '06600'],
    ['estrategia' => new ValidadorContrasena(), 'valor' => 'MiClave123'],
    ['estrategia' => new ValidadorContrasena(), 'valor' => 'debil'],
];

foreach ($pruebas as $prueba) {
    $validador->establecerEstrategia($prueba['estrategia']);
    $resultado = $validador->validar($prueba['valor']);
    $icono = $resultado['valido'] ? 'OK' : 'FALLO';
    echo "  [{$icono}] {$resultado['campo']}: '{$resultado['valor']}' -> {$resultado['mensaje']}\n";
}
echo "\n";


// =============================================================================
// Ejemplo 2: Clase Contexto que Delega a la Estrategia
// =============================================================================
// Problema: Una aplicacion de procesamiento de texto necesita diferentes
// formatos de salida (HTML, Markdown, texto plano) para el mismo contenido.

echo "=== Ejemplo 2: Formateador de Contenido con Contexto ===\n\n";

// Estrategia: formato de salida
interface EstrategiaFormato
{
    public function formatearTitulo(string $texto): string;
    public function formatearParrafo(string $texto): string;
    public function formatearLista(array $elementos): string;
    public function formatearEnlace(string $url, string $texto): string;
    public function formatearNegrita(string $texto): string;
}

class FormatoHTML implements EstrategiaFormato
{
    public function formatearTitulo(string $texto): string
    {
        return "<h1>{$texto}</h1>";
    }

    public function formatearParrafo(string $texto): string
    {
        return "<p>{$texto}</p>";
    }

    public function formatearLista(array $elementos): string
    {
        $items = implode('', array_map(fn($e) => "<li>{$e}</li>", $elementos));
        return "<ul>{$items}</ul>";
    }

    public function formatearEnlace(string $url, string $texto): string
    {
        return "<a href=\"{$url}\">{$texto}</a>";
    }

    public function formatearNegrita(string $texto): string
    {
        return "<strong>{$texto}</strong>";
    }
}

class FormatoMarkdown implements EstrategiaFormato
{
    public function formatearTitulo(string $texto): string
    {
        return "# {$texto}";
    }

    public function formatearParrafo(string $texto): string
    {
        return "{$texto}\n";
    }

    public function formatearLista(array $elementos): string
    {
        return implode("\n", array_map(fn($e) => "- {$e}", $elementos));
    }

    public function formatearEnlace(string $url, string $texto): string
    {
        return "[{$texto}]({$url})";
    }

    public function formatearNegrita(string $texto): string
    {
        return "**{$texto}**";
    }
}

class FormatoTextoPlano implements EstrategiaFormato
{
    public function formatearTitulo(string $texto): string
    {
        $linea = str_repeat('=', strlen($texto));
        return "{$texto}\n{$linea}";
    }

    public function formatearParrafo(string $texto): string
    {
        return $texto;
    }

    public function formatearLista(array $elementos): string
    {
        return implode("\n", array_map(
            fn($e, $i) => "  " . ($i + 1) . ") {$e}",
            $elementos,
            array_keys($elementos)
        ));
    }

    public function formatearEnlace(string $url, string $texto): string
    {
        return "{$texto} ({$url})";
    }

    public function formatearNegrita(string $texto): string
    {
        return strtoupper($texto);
    }
}

// Contexto: generador de documentos que delega el formato a la estrategia
class GeneradorDocumento
{
    private EstrategiaFormato $formato;
    private array $contenido = [];

    public function __construct(EstrategiaFormato $formato)
    {
        $this->formato = $formato;
    }

    public function cambiarFormato(EstrategiaFormato $formato): void
    {
        $this->formato = $formato;
    }

    public function agregarTitulo(string $texto): self
    {
        $this->contenido[] = $this->formato->formatearTitulo($texto);
        return $this;
    }

    public function agregarParrafo(string $texto): self
    {
        $this->contenido[] = $this->formato->formatearParrafo($texto);
        return $this;
    }

    public function agregarLista(array $elementos): self
    {
        $this->contenido[] = $this->formato->formatearLista($elementos);
        return $this;
    }

    public function renderizar(): string
    {
        return implode("\n", $this->contenido);
    }

    public function limpiar(): void
    {
        $this->contenido = [];
    }
}

$formatos = [
    'HTML'        => new FormatoHTML(),
    'Markdown'    => new FormatoMarkdown(),
    'Texto Plano' => new FormatoTextoPlano(),
];

foreach ($formatos as $nombre => $formato) {
    $doc = new GeneradorDocumento($formato);
    $doc->agregarTitulo('Boletin Semanal')
        ->agregarParrafo('Estas son las novedades de esta semana.')
        ->agregarLista(['Nuevo modulo de reportes', 'Correccion de errores', 'Mejoras de rendimiento']);

    echo "  --- Formato: {$nombre} ---\n";
    echo "  " . str_replace("\n", "\n  ", $doc->renderizar()) . "\n\n";
}


// =============================================================================
// Ejemplo 3: Calculadora de Envios (Caso Practico)
// =============================================================================
// Problema: Una tienda ofrece multiples opciones de envio. Cada una tiene
// su propia logica de calculo de costos y tiempos de entrega.

echo "=== Ejemplo 3: Calculadora de Envios ===\n\n";

// Resultado estandarizado del calculo de envio
class ResultadoEnvio
{
    public function __construct(
        public readonly string $metodo,
        public readonly float $costo,
        public readonly int $diasEstimados,
        public readonly string $descripcion,
        public readonly bool $disponible = true
    ) {}
}

// Estrategia de calculo de envio
interface EstrategiaEnvio
{
    public function calcular(float $pesoKg, float $distanciaKm, float $valorProducto): ResultadoEnvio;
    public function obtenerNombre(): string;
}

class EnvioExpress implements EstrategiaEnvio
{
    public function calcular(float $pesoKg, float $distanciaKm, float $valorProducto): ResultadoEnvio
    {
        // Tarifa base + por peso + por distancia
        $costo = 150.00 + ($pesoKg * 25.00) + ($distanciaKm * 0.15);

        // Envio express no disponible para distancias mayores a 1000 km
        $disponible = $distanciaKm <= 1000;

        return new ResultadoEnvio(
            metodo: 'Express',
            costo: round($costo, 2),
            diasEstimados: $distanciaKm <= 500 ? 1 : 2,
            descripcion: 'Entrega garantizada en 1-2 dias habiles',
            disponible: $disponible
        );
    }

    public function obtenerNombre(): string { return 'Envio Express'; }
}

class EnvioEstandar implements EstrategiaEnvio
{
    public function calcular(float $pesoKg, float $distanciaKm, float $valorProducto): ResultadoEnvio
    {
        // Tarifa mas economica
        $costo = 80.00 + ($pesoKg * 12.00) + ($distanciaKm * 0.08);

        // Envio gratis para compras mayores a $999
        if ($valorProducto >= 999.00) {
            $costo = 0.00;
        }

        return new ResultadoEnvio(
            metodo: 'Estandar',
            costo: round($costo, 2),
            diasEstimados: $distanciaKm <= 500 ? 5 : 8,
            descripcion: $costo === 0.0
                ? 'Envio GRATIS por compra mayor a $999'
                : 'Entrega en 5-8 dias habiles'
        );
    }

    public function obtenerNombre(): string { return 'Envio Estandar'; }
}

class RecogerEnTienda implements EstrategiaEnvio
{
    private array $tiendasDisponibles;

    public function __construct(array $tiendasDisponibles = ['CDMX Centro', 'CDMX Sur', 'Guadalajara'])
    {
        $this->tiendasDisponibles = $tiendasDisponibles;
    }

    public function calcular(float $pesoKg, float $distanciaKm, float $valorProducto): ResultadoEnvio
    {
        return new ResultadoEnvio(
            metodo: 'Recoger en Tienda',
            costo: 0.00,
            diasEstimados: 1,
            descripcion: 'Listo para recoger en: ' . implode(', ', $this->tiendasDisponibles)
        );
    }

    public function obtenerNombre(): string { return 'Recoger en Tienda'; }
}

class EnvioInternacional implements EstrategiaEnvio
{
    public function calcular(float $pesoKg, float $distanciaKm, float $valorProducto): ResultadoEnvio
    {
        $costo = 500.00 + ($pesoKg * 80.00) + ($distanciaKm * 0.05);

        // Aduanas: cargos adicionales para valores altos
        if ($valorProducto > 5000) {
            $costo += $valorProducto * 0.08; // 8% de aduanas estimado
        }

        return new ResultadoEnvio(
            metodo: 'Internacional',
            costo: round($costo, 2),
            diasEstimados: 15,
            descripcion: 'Envio internacional, 10-15 dias habiles. Aduanas incluidas estimadas.'
        );
    }

    public function obtenerNombre(): string { return 'Envio Internacional'; }
}

// Contexto: calculadora que usa la estrategia seleccionada
class CalculadoraEnvio
{
    private EstrategiaEnvio $estrategia;

    public function __construct(EstrategiaEnvio $estrategia)
    {
        $this->estrategia = $estrategia;
    }

    public function cambiarEstrategia(EstrategiaEnvio $estrategia): void
    {
        $this->estrategia = $estrategia;
    }

    public function calcular(float $pesoKg, float $distanciaKm, float $valorProducto): ResultadoEnvio
    {
        return $this->estrategia->calcular($pesoKg, $distanciaKm, $valorProducto);
    }

    // Calcular todas las opciones disponibles de una vez
    public static function calcularTodas(
        array $estrategias,
        float $pesoKg,
        float $distanciaKm,
        float $valorProducto
    ): array {
        $resultados = [];
        foreach ($estrategias as $estrategia) {
            $resultado = $estrategia->calcular($pesoKg, $distanciaKm, $valorProducto);
            if ($resultado->disponible) {
                $resultados[] = $resultado;
            }
        }
        // Ordenar por costo ascendente
        usort($resultados, fn($a, $b) => $a->costo <=> $b->costo);
        return $resultados;
    }
}

// Mostrar todas las opciones de envio para un pedido
echo "  Pedido: Laptop 3.2kg, distancia 350km, valor \$15,999\n";
echo "  " . str_repeat('-', 65) . "\n";

$opciones = CalculadoraEnvio::calcularTodas(
    [new EnvioExpress(), new EnvioEstandar(), new RecogerEnTienda(), new EnvioInternacional()],
    pesoKg: 3.2,
    distanciaKm: 350,
    valorProducto: 15999.00
);

foreach ($opciones as $opcion) {
    $costoFmt = $opcion->costo > 0 ? '$' . number_format($opcion->costo, 2) : 'GRATIS';
    echo "  {$opcion->metodo}: {$costoFmt} ({$opcion->diasEstimados} dias)\n";
    echo "    {$opcion->descripcion}\n";
}
echo "\n";


// =============================================================================
// Ejemplo 4: Estrategias de Ordenamiento
// =============================================================================
// Problema: Una lista de productos puede ordenarse de muchas maneras
// (por precio, nombre, popularidad, fecha). Sin Strategy, tendriamos
// un enorme switch/case o multiples if/else.

echo "=== Ejemplo 4: Estrategias de Ordenamiento de Productos ===\n\n";

class Producto
{
    public function __construct(
        public readonly string $nombre,
        public readonly float $precio,
        public readonly int $ventas,
        public readonly float $calificacion,
        public readonly string $fechaCreacion
    ) {}
}

// Estrategia de ordenamiento
interface EstrategiaOrdenamiento
{
    /**
     * Ordenar un array de productos y devolver el array ordenado.
     * @param Producto[] $productos
     * @return Producto[]
     */
    public function ordenar(array $productos): array;
    public function obtenerNombre(): string;
}

class OrdenarPorPrecioAscendente implements EstrategiaOrdenamiento
{
    public function ordenar(array $productos): array
    {
        usort($productos, fn(Producto $a, Producto $b) => $a->precio <=> $b->precio);
        return $productos;
    }

    public function obtenerNombre(): string { return 'Precio (menor a mayor)'; }
}

class OrdenarPorPrecioDescendente implements EstrategiaOrdenamiento
{
    public function ordenar(array $productos): array
    {
        usort($productos, fn(Producto $a, Producto $b) => $b->precio <=> $a->precio);
        return $productos;
    }

    public function obtenerNombre(): string { return 'Precio (mayor a menor)'; }
}

class OrdenarPorPopularidad implements EstrategiaOrdenamiento
{
    public function ordenar(array $productos): array
    {
        usort($productos, fn(Producto $a, Producto $b) => $b->ventas <=> $a->ventas);
        return $productos;
    }

    public function obtenerNombre(): string { return 'Mas vendidos'; }
}

class OrdenarPorCalificacion implements EstrategiaOrdenamiento
{
    public function ordenar(array $productos): array
    {
        usort($productos, fn(Producto $a, Producto $b) => $b->calificacion <=> $a->calificacion);
        return $productos;
    }

    public function obtenerNombre(): string { return 'Mejor calificados'; }
}

class OrdenarPorNombre implements EstrategiaOrdenamiento
{
    public function ordenar(array $productos): array
    {
        usort($productos, fn(Producto $a, Producto $b) => strcasecmp($a->nombre, $b->nombre));
        return $productos;
    }

    public function obtenerNombre(): string { return 'Nombre (A-Z)'; }
}

// Contexto: catalogo de productos con ordenamiento configurable
class CatalogoProductos
{
    private EstrategiaOrdenamiento $estrategia;

    /** @var Producto[] */
    private array $productos = [];

    public function __construct(EstrategiaOrdenamiento $estrategia)
    {
        $this->estrategia = $estrategia;
    }

    public function agregarProducto(Producto $producto): void
    {
        $this->productos[] = $producto;
    }

    public function cambiarOrdenamiento(EstrategiaOrdenamiento $estrategia): void
    {
        $this->estrategia = $estrategia;
    }

    public function listar(): array
    {
        return $this->estrategia->ordenar($this->productos);
    }
}

// Crear catalogo con productos de ejemplo
$catalogo = new CatalogoProductos(new OrdenarPorPrecioAscendente());
$catalogo->agregarProducto(new Producto('Audifonos BT', 899.00, 1520, 4.5, '2025-08-15'));
$catalogo->agregarProducto(new Producto('Teclado Mecanico', 2499.00, 890, 4.8, '2025-03-01'));
$catalogo->agregarProducto(new Producto('Mouse Gaming', 1299.00, 2100, 4.2, '2025-11-20'));
$catalogo->agregarProducto(new Producto('Monitor 27"', 6999.00, 450, 4.9, '2026-01-10'));
$catalogo->agregarProducto(new Producto('Webcam HD', 599.00, 3200, 3.8, '2025-06-05'));

// Probar distintos ordenamientos
$estrategias = [
    new OrdenarPorPrecioAscendente(),
    new OrdenarPorPopularidad(),
    new OrdenarPorCalificacion(),
];

foreach ($estrategias as $estrategia) {
    $catalogo->cambiarOrdenamiento($estrategia);
    echo "  Ordenado por: {$estrategia->obtenerNombre()}\n";

    foreach ($catalogo->listar() as $i => $prod) {
        echo "    " . ($i + 1) . ". {$prod->nombre} - \${$prod->precio} ";
        echo "(ventas: {$prod->ventas}, cal: {$prod->calificacion})\n";
    }
    echo "\n";
}


// =============================================================================
// Ejemplo 5: Seleccion de Estrategia en Tiempo de Ejecucion
// =============================================================================
// Problema: La estrategia de descuento debe seleccionarse automaticamente
// segun las condiciones del cliente (tipo de membresia, historial de compras,
// temporada, etc.).

echo "=== Ejemplo 5: Seleccion Automatica de Estrategia de Descuento ===\n\n";

// Datos del contexto para la seleccion
class ContextoCompra
{
    public function __construct(
        public readonly string $tipoCliente,     // 'nuevo', 'regular', 'vip', 'empleado'
        public readonly float $totalCompra,
        public readonly int $comprasAnteriores,
        public readonly string $temporada,         // 'normal', 'navidad', 'buen_fin', 'verano'
        public readonly bool $tieneCupon = false,
        public readonly float $valorCupon = 0.0
    ) {}
}

interface EstrategiaDescuento
{
    public function calcular(ContextoCompra $contexto): float;
    public function obtenerDescripcion(): string;
}

class SinDescuento implements EstrategiaDescuento
{
    public function calcular(ContextoCompra $contexto): float
    {
        return 0.0;
    }

    public function obtenerDescripcion(): string { return 'Sin descuento aplicable'; }
}

class DescuentoClienteNuevo implements EstrategiaDescuento
{
    public function calcular(ContextoCompra $contexto): float
    {
        return $contexto->totalCompra * 0.10; // 10% primera compra
    }

    public function obtenerDescripcion(): string { return '10% descuento de bienvenida'; }
}

class DescuentoVIP implements EstrategiaDescuento
{
    public function calcular(ContextoCompra $contexto): float
    {
        // 15% base + 5% adicional si supera $5,000
        $porcentaje = 0.15;
        if ($contexto->totalCompra > 5000) {
            $porcentaje += 0.05;
        }
        return $contexto->totalCompra * $porcentaje;
    }

    public function obtenerDescripcion(): string { return '15-20% descuento VIP'; }
}

class DescuentoTemporada implements EstrategiaDescuento
{
    private array $porcentajes = [
        'navidad'  => 0.25,
        'buen_fin' => 0.30,
        'verano'   => 0.15,
    ];

    public function calcular(ContextoCompra $contexto): float
    {
        $porcentaje = $this->porcentajes[$contexto->temporada] ?? 0.0;
        return $contexto->totalCompra * $porcentaje;
    }

    public function obtenerDescripcion(): string { return 'Descuento de temporada'; }
}

class DescuentoEmpleado implements EstrategiaDescuento
{
    public function calcular(ContextoCompra $contexto): float
    {
        return $contexto->totalCompra * 0.35; // 35% descuento empleado
    }

    public function obtenerDescripcion(): string { return '35% descuento empleado'; }
}

/**
 * Selector automatico de estrategia basado en condiciones.
 *
 * Este es el componente clave: evalua el contexto y selecciona
 * la MEJOR estrategia aplicable (la que genera mayor descuento).
 */
class SelectorDescuento
{
    /** @var EstrategiaDescuento[] */
    private array $estrategiasDisponibles = [];

    public function registrar(string $condicion, EstrategiaDescuento $estrategia): void
    {
        $this->estrategiasDisponibles[$condicion] = $estrategia;
    }

    /**
     * Seleccionar la mejor estrategia: evalua todas las aplicables
     * y devuelve la que ofrece mayor descuento al cliente.
     */
    public function seleccionar(ContextoCompra $contexto): EstrategiaDescuento
    {
        $candidatas = [];

        // Verificar que estrategias son aplicables segun el contexto
        if ($contexto->tipoCliente === 'nuevo' && $contexto->comprasAnteriores === 0) {
            $candidatas[] = $this->estrategiasDisponibles['cliente_nuevo'] ?? null;
        }

        if ($contexto->tipoCliente === 'vip') {
            $candidatas[] = $this->estrategiasDisponibles['vip'] ?? null;
        }

        if ($contexto->tipoCliente === 'empleado') {
            $candidatas[] = $this->estrategiasDisponibles['empleado'] ?? null;
        }

        if (in_array($contexto->temporada, ['navidad', 'buen_fin', 'verano'])) {
            $candidatas[] = $this->estrategiasDisponibles['temporada'] ?? null;
        }

        // Filtrar nulos
        $candidatas = array_filter($candidatas);

        if (empty($candidatas)) {
            return new SinDescuento();
        }

        // Seleccionar la estrategia que genera el mayor descuento
        $mejorEstrategia = null;
        $mayorDescuento = 0.0;

        foreach ($candidatas as $estrategia) {
            $descuento = $estrategia->calcular($contexto);
            if ($descuento > $mayorDescuento) {
                $mayorDescuento = $descuento;
                $mejorEstrategia = $estrategia;
            }
        }

        return $mejorEstrategia ?? new SinDescuento();
    }
}

// Configurar el selector con las estrategias disponibles
$selector = new SelectorDescuento();
$selector->registrar('cliente_nuevo', new DescuentoClienteNuevo());
$selector->registrar('vip', new DescuentoVIP());
$selector->registrar('temporada', new DescuentoTemporada());
$selector->registrar('empleado', new DescuentoEmpleado());

// Probar con distintos contextos de compra
$escenarios = [
    new ContextoCompra('nuevo', 1500.00, 0, 'normal'),
    new ContextoCompra('regular', 3000.00, 12, 'navidad'),
    new ContextoCompra('vip', 8000.00, 50, 'buen_fin'),
    new ContextoCompra('empleado', 2000.00, 5, 'normal'),
    new ContextoCompra('regular', 500.00, 3, 'normal'),
];

foreach ($escenarios as $i => $escenario) {
    $estrategia = $selector->seleccionar($escenario);
    $descuento = $estrategia->calcular($escenario);
    $total = $escenario->totalCompra - $descuento;

    echo "  Escenario " . ($i + 1) . ":\n";
    echo "    Cliente: {$escenario->tipoCliente} | Temporada: {$escenario->temporada}\n";
    echo "    Subtotal: \$" . number_format($escenario->totalCompra, 2) . "\n";
    echo "    Estrategia: {$estrategia->obtenerDescripcion()}\n";
    echo "    Descuento: -\$" . number_format($descuento, 2) . "\n";
    echo "    Total final: \$" . number_format($total, 2) . "\n\n";
}

echo "  NOTA: El selector evalua todas las estrategias aplicables y\n";
echo "  automaticamente elige la que mas beneficia al cliente.\n";
echo "  Agregar nuevas estrategias no requiere modificar el selector.\n";

?>
