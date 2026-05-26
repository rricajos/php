<?php
/**
 * =============================================================================
 * PATRON DECORATOR (DECORADOR) EN PHP
 * =============================================================================
 *
 * El patron Decorator permite agregar responsabilidades a un objeto de forma
 * dinamica, sin modificar su clase original. Los decoradores "envuelven" al
 * objeto original y extienden su comportamiento.
 *
 * Componentes:
 * - Component (interfaz): define la operacion que puede ser decorada
 * - ConcreteComponent: implementacion base del componente
 * - Decorator (abstracto): mantiene una referencia al componente y delega
 * - ConcreteDecorator: agrega responsabilidades especificas
 *
 * Ventajas sobre herencia:
 * - Se pueden combinar decoradores en cualquier orden
 * - Se pueden agregar/quitar en tiempo de ejecucion
 * - Evita la explosion combinatoria de subclases
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Interfaz Component, ConcreteComponent y Decorator Abstracto
// =============================================================================
// Problema: Un sistema de mensajeria necesita poder agregar cifrado,
// compresion y formato a los mensajes de forma flexible y combinable.

echo "=== Ejemplo 1: Decorador de Mensajes ===\n\n";

// Componente: interfaz base para procesamiento de mensajes
interface ProcesadorMensaje
{
    public function procesar(string $mensaje): string;
    public function obtenerDescripcion(): string;
}

// Componente concreto: procesamiento basico (sin transformacion)
class MensajeBasico implements ProcesadorMensaje
{
    public function procesar(string $mensaje): string
    {
        return $mensaje;
    }

    public function obtenerDescripcion(): string
    {
        return 'Mensaje basico';
    }
}

// Decorador abstracto: establece la estructura para todos los decoradores
abstract class DecoradorMensaje implements ProcesadorMensaje
{
    // Referencia al componente envuelto (puede ser otro decorador)
    public function __construct(
        protected ProcesadorMensaje $componente
    ) {}

    // Por defecto, delega al componente envuelto
    public function procesar(string $mensaje): string
    {
        return $this->componente->procesar($mensaje);
    }

    public function obtenerDescripcion(): string
    {
        return $this->componente->obtenerDescripcion();
    }
}

// Decoradores concretos

class DecoradorCifrado extends DecoradorMensaje
{
    public function procesar(string $mensaje): string
    {
        // Primero procesamos con el componente envuelto
        $procesado = parent::procesar($mensaje);
        // Luego aplicamos cifrado (simulado con base64)
        return base64_encode($procesado);
    }

    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Cifrado';
    }
}

class DecoradorCompresion extends DecoradorMensaje
{
    public function procesar(string $mensaje): string
    {
        $procesado = parent::procesar($mensaje);
        // Simulamos compresion mostrando que se redujo el tamano
        $comprimido = gzcompress($procesado, 9);
        return base64_encode($comprimido);
    }

    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Compresion';
    }
}

class DecoradorMarcaTiempo extends DecoradorMensaje
{
    public function procesar(string $mensaje): string
    {
        $procesado = parent::procesar($mensaje);
        return '[' . date('Y-m-d H:i:s') . '] ' . $procesado;
    }

    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Marca de Tiempo';
    }
}

class DecoradorMayusculas extends DecoradorMensaje
{
    public function procesar(string $mensaje): string
    {
        $procesado = parent::procesar($mensaje);
        return mb_strtoupper($procesado);
    }

    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Mayusculas';
    }
}

$mensajeOriginal = "Hola, este es un mensaje de prueba para el patron Decorator.";

// Sin decorador
$basico = new MensajeBasico();
echo "  Original:          {$basico->procesar($mensajeOriginal)}\n";
echo "  Pipeline:          {$basico->obtenerDescripcion()}\n\n";

// Con marca de tiempo
$conTiempo = new DecoradorMarcaTiempo(new MensajeBasico());
echo "  Con timestamp:     {$conTiempo->procesar($mensajeOriginal)}\n";
echo "  Pipeline:          {$conTiempo->obtenerDescripcion()}\n\n";

// Apilando multiples decoradores: tiempo + mayusculas
$combinado = new DecoradorMayusculas(
    new DecoradorMarcaTiempo(
        new MensajeBasico()
    )
);
echo "  Tiempo+Mayusculas: {$combinado->procesar($mensajeOriginal)}\n";
echo "  Pipeline:          {$combinado->obtenerDescripcion()}\n\n";


// =============================================================================
// Ejemplo 2: Apilando Multiples Decoradores
// =============================================================================
// Problema: Demostrar que los decoradores se pueden apilar en cualquier orden
// y que el orden importa (cada capa transforma la salida de la anterior).

echo "=== Ejemplo 2: Orden de Apilamiento de Decoradores ===\n\n";

// Componente base: formateador de texto
interface FormateadorTexto
{
    public function formatear(string $texto): string;
}

class TextoPlano implements FormateadorTexto
{
    public function formatear(string $texto): string
    {
        return $texto;
    }
}

abstract class DecoradorTexto implements FormateadorTexto
{
    public function __construct(protected FormateadorTexto $componente) {}

    public function formatear(string $texto): string
    {
        return $this->componente->formatear($texto);
    }
}

class DecoradorNegrita extends DecoradorTexto
{
    public function formatear(string $texto): string
    {
        return '<b>' . parent::formatear($texto) . '</b>';
    }
}

class DecoradorItalica extends DecoradorTexto
{
    public function formatear(string $texto): string
    {
        return '<i>' . parent::formatear($texto) . '</i>';
    }
}

class DecoradorSubrayado extends DecoradorTexto
{
    public function formatear(string $texto): string
    {
        return '<u>' . parent::formatear($texto) . '</u>';
    }
}

class DecoradorColor extends DecoradorTexto
{
    public function __construct(
        FormateadorTexto $componente,
        private string $color
    ) {
        parent::__construct($componente);
    }

    public function formatear(string $texto): string
    {
        return "<span style=\"color:{$this->color}\">" . parent::formatear($texto) . '</span>';
    }
}

class DecoradorEnlace extends DecoradorTexto
{
    public function __construct(
        FormateadorTexto $componente,
        private string $url
    ) {
        parent::__construct($componente);
    }

    public function formatear(string $texto): string
    {
        return "<a href=\"{$this->url}\">" . parent::formatear($texto) . '</a>';
    }
}

$texto = "Patron Decorator";

// Diferentes combinaciones de decoradores
$combinaciones = [
    'Negrita' => new DecoradorNegrita(new TextoPlano()),
    'Negrita + Italica' => new DecoradorItalica(new DecoradorNegrita(new TextoPlano())),
    'Color + Negrita + Subrayado' => new DecoradorSubrayado(
        new DecoradorNegrita(
            new DecoradorColor(new TextoPlano(), '#ff6600')
        )
    ),
    'Enlace + Negrita + Color' => new DecoradorColor(
        new DecoradorNegrita(
            new DecoradorEnlace(new TextoPlano(), 'https://ejemplo.com')
        ),
        '#0066cc'
    ),
];

foreach ($combinaciones as $nombre => $formateador) {
    echo "  {$nombre}:\n";
    echo "    {$formateador->formatear($texto)}\n\n";
}


// =============================================================================
// Ejemplo 3: Cafeteria - Pedido con Calculo de Precios (Caso Clasico)
// =============================================================================
// Problema: Una cafeteria ofrece bebidas base (cafe, te, chocolate) con
// multiples complementos (leche, azucar, crema, saborizantes). Cada
// combinacion tiene un precio distinto. Con herencia, necesitariamos
// decenas de subclases; con Decorator, se resuelve elegantemente.

echo "=== Ejemplo 3: Cafeteria con Calculo de Precios ===\n\n";

// Componente: bebida base
interface Bebida
{
    public function obtenerDescripcion(): string;
    public function obtenerPrecio(): float;
    public function obtenerCalorias(): int;
}

// Componentes concretos: bebidas base
class CafeAmericano implements Bebida
{
    public function obtenerDescripcion(): string { return 'Cafe Americano'; }
    public function obtenerPrecio(): float { return 35.00; }
    public function obtenerCalorias(): int { return 5; }
}

class CafeExpresso implements Bebida
{
    public function obtenerDescripcion(): string { return 'Cafe Expresso'; }
    public function obtenerPrecio(): float { return 40.00; }
    public function obtenerCalorias(): int { return 10; }
}

class TeVerde implements Bebida
{
    public function obtenerDescripcion(): string { return 'Te Verde'; }
    public function obtenerPrecio(): float { return 30.00; }
    public function obtenerCalorias(): int { return 0; }
}

class ChocolateCaliente implements Bebida
{
    public function obtenerDescripcion(): string { return 'Chocolate Caliente'; }
    public function obtenerPrecio(): float { return 45.00; }
    public function obtenerCalorias(): int { return 180; }
}

// Decorador abstracto para complementos
abstract class ComplementoBebida implements Bebida
{
    public function __construct(protected Bebida $bebida) {}

    public function obtenerDescripcion(): string
    {
        return $this->bebida->obtenerDescripcion();
    }

    public function obtenerPrecio(): float
    {
        return $this->bebida->obtenerPrecio();
    }

    public function obtenerCalorias(): int
    {
        return $this->bebida->obtenerCalorias();
    }
}

// Complementos concretos (decoradores)

class Leche extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Leche';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio() + 10.00;
    }

    public function obtenerCalorias(): int
    {
        return parent::obtenerCalorias() + 60;
    }
}

class LecheAlmendras extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Leche de Almendras';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio() + 18.00;
    }

    public function obtenerCalorias(): int
    {
        return parent::obtenerCalorias() + 30;
    }
}

class Azucar extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Azucar';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio(); // El azucar es gratis
    }

    public function obtenerCalorias(): int
    {
        return parent::obtenerCalorias() + 45;
    }
}

class CremaChantilly extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Crema Chantilly';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio() + 15.00;
    }

    public function obtenerCalorias(): int
    {
        return parent::obtenerCalorias() + 120;
    }
}

class SaborizanteVainilla extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Vainilla';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio() + 12.00;
    }

    public function obtenerCalorias(): int
    {
        return parent::obtenerCalorias() + 20;
    }
}

class SaborizanteCaramelo extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' + Caramelo';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio() + 12.00;
    }

    public function obtenerCalorias(): int
    {
        return parent::obtenerCalorias() + 35;
    }
}

class TamanoGrande extends ComplementoBebida
{
    public function obtenerDescripcion(): string
    {
        return parent::obtenerDescripcion() . ' (Grande)';
    }

    public function obtenerPrecio(): float
    {
        return parent::obtenerPrecio() + 20.00;
    }

    public function obtenerCalorias(): int
    {
        return (int) (parent::obtenerCalorias() * 1.5); // 50% mas calorias
    }
}

// Funcion auxiliar para imprimir un pedido
function imprimirPedido(Bebida $bebida, int $numero): void
{
    echo "  Pedido #{$numero}:\n";
    echo "    {$bebida->obtenerDescripcion()}\n";
    echo "    Precio: \$" . number_format($bebida->obtenerPrecio(), 2) . "\n";
    echo "    Calorias: {$bebida->obtenerCalorias()} kcal\n\n";
}

// Pedido 1: Cafe americano simple
$pedido1 = new CafeAmericano();
imprimirPedido($pedido1, 1);

// Pedido 2: Expresso con leche y azucar
$pedido2 = new Azucar(new Leche(new CafeExpresso()));
imprimirPedido($pedido2, 2);

// Pedido 3: Chocolate caliente grande con crema chantilly y caramelo
$pedido3 = new SaborizanteCaramelo(
    new CremaChantilly(
        new TamanoGrande(
            new ChocolateCaliente()
        )
    )
);
imprimirPedido($pedido3, 3);

// Pedido 4: Te verde con leche de almendras y vainilla
$pedido4 = new SaborizanteVainilla(
    new LecheAlmendras(
        new TeVerde()
    )
);
imprimirPedido($pedido4, 4);

// Pedido 5: Expresso doble con doble leche, crema y caramelo (Grande)
// Nota: se puede agregar el mismo decorador multiples veces
$pedido5 = new TamanoGrande(
    new SaborizanteCaramelo(
        new CremaChantilly(
            new Leche(
                new Leche( // Doble leche
                    new CafeExpresso()
                )
            )
        )
    )
);
imprimirPedido($pedido5, 5);

// Resumen del ticket
echo "  --- Resumen del Ticket ---\n";
$pedidos = [$pedido1, $pedido2, $pedido3, $pedido4, $pedido5];
$total = 0.0;
foreach ($pedidos as $i => $pedido) {
    $precio = $pedido->obtenerPrecio();
    $total += $precio;
    echo "  " . ($i + 1) . ". {$pedido->obtenerDescripcion()} - \$" . number_format($precio, 2) . "\n";
}
echo "  " . str_repeat('-', 50) . "\n";
echo "  TOTAL: \$" . number_format($total, 2) . "\n\n";


// =============================================================================
// Ejemplo 4: Decoradores en Middleware HTTP (Caso Practico Web)
// =============================================================================
// Problema: Las peticiones HTTP necesitan pasar por multiples capas de
// procesamiento (autenticacion, logging, CORS, rate limiting). Cada capa
// es un decorador que envuelve al siguiente manejador.

echo "=== Ejemplo 4: Middleware HTTP como Decoradores ===\n\n";

// Representaciones simples de Request/Response
class PeticionHTTP
{
    public array $cabeceras = [];
    public string $metodo;
    public string $ruta;
    public ?string $cuerpo;
    public array $atributos = [];  // Datos agregados por middleware

    public function __construct(string $metodo, string $ruta, array $cabeceras = [], ?string $cuerpo = null)
    {
        $this->metodo = $metodo;
        $this->ruta = $ruta;
        $this->cabeceras = $cabeceras;
        $this->cuerpo = $cuerpo;
    }
}

class RespuestaHTTP
{
    public function __construct(
        public int $codigo = 200,
        public string $cuerpo = '',
        public array $cabeceras = []
    ) {}
}

// Componente: manejador de peticiones
interface ManejadorHTTP
{
    public function manejar(PeticionHTTP $peticion): RespuestaHTTP;
}

// Manejador base: la aplicacion real
class ManejadorAplicacion implements ManejadorHTTP
{
    public function manejar(PeticionHTTP $peticion): RespuestaHTTP
    {
        $usuario = $peticion->atributos['usuario'] ?? 'anonimo';
        return new RespuestaHTTP(
            codigo: 200,
            cuerpo: json_encode([
                'mensaje' => "Hola {$usuario}, peticion procesada",
                'ruta'    => $peticion->ruta,
                'metodo'  => $peticion->metodo,
            ]),
            cabeceras: ['Content-Type' => 'application/json']
        );
    }
}

// Decorador abstracto para middleware
abstract class MiddlewareHTTP implements ManejadorHTTP
{
    public function __construct(protected ManejadorHTTP $siguiente) {}
}

// Middleware de autenticacion
class MiddlewareAutenticacion extends MiddlewareHTTP
{
    public function manejar(PeticionHTTP $peticion): RespuestaHTTP
    {
        $token = $peticion->cabeceras['Authorization'] ?? null;

        if ($token === null) {
            echo "    [Auth] Sin token - acceso denegado\n";
            return new RespuestaHTTP(401, '{"error":"No autorizado"}');
        }

        // Simulamos validar el token
        $peticion->atributos['usuario'] = 'Carlos (token validado)';
        echo "    [Auth] Token valido, usuario autenticado\n";

        return $this->siguiente->manejar($peticion);
    }
}

// Middleware de logging
class MiddlewareLogging extends MiddlewareHTTP
{
    public function manejar(PeticionHTTP $peticion): RespuestaHTTP
    {
        $inicio = microtime(true);
        echo "    [Log] >> {$peticion->metodo} {$peticion->ruta}\n";

        $respuesta = $this->siguiente->manejar($peticion);

        $duracion = round((microtime(true) - $inicio) * 1000, 2);
        echo "    [Log] << {$respuesta->codigo} ({$duracion}ms)\n";

        return $respuesta;
    }
}

// Middleware de CORS
class MiddlewareCORS extends MiddlewareHTTP
{
    public function manejar(PeticionHTTP $peticion): RespuestaHTTP
    {
        $respuesta = $this->siguiente->manejar($peticion);

        // Agregar cabeceras CORS a la respuesta
        $respuesta->cabeceras['Access-Control-Allow-Origin'] = '*';
        $respuesta->cabeceras['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE';
        echo "    [CORS] Cabeceras CORS agregadas\n";

        return $respuesta;
    }
}

// Construir el pipeline de middleware (de afuera hacia adentro)
// La peticion pasa por: CORS -> Logging -> Auth -> Aplicacion
$app = new MiddlewareCORS(
    new MiddlewareLogging(
        new MiddlewareAutenticacion(
            new ManejadorAplicacion()
        )
    )
);

// Peticion con autenticacion
echo "  --- Peticion autenticada ---\n";
$peticion = new PeticionHTTP('GET', '/api/perfil', ['Authorization' => 'Bearer abc123']);
$respuesta = $app->manejar($peticion);
echo "    Respuesta: {$respuesta->cuerpo}\n\n";

// Peticion sin autenticacion
echo "  --- Peticion sin autenticar ---\n";
$peticion = new PeticionHTTP('GET', '/api/perfil');
$respuesta = $app->manejar($peticion);
echo "    Respuesta [{$respuesta->codigo}]: {$respuesta->cuerpo}\n\n";


// =============================================================================
// Ejemplo 5: Decorator vs Herencia (Comparacion)
// =============================================================================
// Problema: Mostrar por que el patron Decorator es superior a la herencia
// cuando necesitamos combinaciones flexibles de funcionalidades.

echo "=== Ejemplo 5: Decorator vs Herencia ===\n\n";

/**
 * CON HERENCIA (problematico):
 *
 * Si tenemos 3 bebidas base y 5 complementos, necesitariamos clases como:
 *
 *   CafeConLeche
 *   CafeConLecheYAzucar
 *   CafeConLecheYCrema
 *   CafeConLecheYAzucarYCrema
 *   CafeConAzucar
 *   CafeConCrema
 *   CafeConAzucarYCrema
 *   TeConLeche
 *   TeConLecheYAzucar
 *   ... y asi sucesivamente
 *
 * Para 3 bebidas y 5 complementos: 3 * 2^5 = 96 clases posibles.
 * Esto se llama "explosion combinatoria de subclases".
 *
 * CON DECORATOR:
 *   Solo necesitamos: 3 bebidas + 5 decoradores = 8 clases
 *   Y se pueden combinar de las 96 maneras posibles (y mas).
 */

// Demostrar la flexibilidad: un "constructor de bebidas" fluido
class ConstructorBebida
{
    private Bebida $bebida;

    public function __construct(Bebida $bebidaBase)
    {
        $this->bebida = $bebidaBase;
    }

    public function conLeche(): self
    {
        $this->bebida = new Leche($this->bebida);
        return $this;
    }

    public function conLecheAlmendras(): self
    {
        $this->bebida = new LecheAlmendras($this->bebida);
        return $this;
    }

    public function conAzucar(): self
    {
        $this->bebida = new Azucar($this->bebida);
        return $this;
    }

    public function conCrema(): self
    {
        $this->bebida = new CremaChantilly($this->bebida);
        return $this;
    }

    public function conVainilla(): self
    {
        $this->bebida = new SaborizanteVainilla($this->bebida);
        return $this;
    }

    public function conCaramelo(): self
    {
        $this->bebida = new SaborizanteCaramelo($this->bebida);
        return $this;
    }

    public function tamanoGrande(): self
    {
        $this->bebida = new TamanoGrande($this->bebida);
        return $this;
    }

    public function construir(): Bebida
    {
        return $this->bebida;
    }
}

// API fluida para construir bebidas - muy legible y flexible
echo "  --- Usando Builder + Decorator (API fluida) ---\n\n";

$miCafe = (new ConstructorBebida(new CafeExpresso()))
    ->conLeche()
    ->conVainilla()
    ->conCrema()
    ->tamanoGrande()
    ->construir();

echo "  {$miCafe->obtenerDescripcion()}\n";
echo "  Precio: \$" . number_format($miCafe->obtenerPrecio(), 2) . "\n";
echo "  Calorias: {$miCafe->obtenerCalorias()} kcal\n\n";

// Otro pedido completamente diferente con los mismos componentes
$miTe = (new ConstructorBebida(new TeVerde()))
    ->conLecheAlmendras()
    ->conAzucar()
    ->construir();

echo "  {$miTe->obtenerDescripcion()}\n";
echo "  Precio: \$" . number_format($miTe->obtenerPrecio(), 2) . "\n";
echo "  Calorias: {$miTe->obtenerCalorias()} kcal\n\n";

echo "  RESUMEN COMPARATIVO:\n";
echo "  " . str_repeat('-', 55) . "\n";
echo "  | Aspecto              | Herencia        | Decorator       |\n";
echo "  " . str_repeat('-', 55) . "\n";
echo "  | Clases necesarias    | 96 (explosion)  | 8 (componibles) |\n";
echo "  | Combinar en runtime  | No              | Si              |\n";
echo "  | Agregar complemento  | Muchas clases   | 1 clase nueva   |\n";
echo "  | Doble complemento    | Imposible       | Trivial         |\n";
echo "  | Quitar complemento   | Imposible       | Reconstruir     |\n";
echo "  " . str_repeat('-', 55) . "\n";

?>
