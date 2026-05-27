<?php
// ============================================================================
// NAMESPACES (ESPACIOS DE NOMBRES) EN PHP
// ============================================================================
// Los namespaces resuelven dos problemas fundamentales:
// 1. Colision de nombres: dos clases/funciones con el mismo nombre de
//    diferentes librerias ya no generan conflicto.
// 2. Organizacion logica: agrupan codigo relacionado en jerarquias claras,
//    similar a los directorios del sistema de archivos.
//
// NOTA: En un proyecto real, cada namespace estaria en su propio archivo.
// Aqui usamos la sintaxis de llaves para demostrar multiples namespaces
// en un solo archivo con fines didacticos.
// ============================================================================

// ============================================================================
// Ejemplo 1: Declarar namespaces y sub-namespaces
// ============================================================================
// Un namespace se declara con la palabra clave 'namespace'. Los sub-namespaces
// se separan con backslash (\). El namespace DEBE ser la primera declaracion
// en el archivo (excepto por 'declare').

// NOTA IMPORTANTE: En un archivo real con namespace, la declaracion iria al
// inicio del archivo. Aqui simulamos con la sintaxis de llaves para poder
// tener multiples namespaces en un solo archivo demostrativo.

namespace App\Models {

    // Esta clase vive en el namespace App\Models
    // Su nombre completo (fully qualified) es: App\Models\Usuario
    class Usuario {
        private string $nombre;
        private string $email;
        private string $rol;

        public function __construct(string $nombre, string $email, string $rol = 'usuario') {
            $this->nombre = $nombre;
            $this->email = $email;
            $this->rol = $rol;
        }

        public function getNombre(): string {
            return $this->nombre;
        }

        public function getEmail(): string {
            return $this->email;
        }

        public function getRol(): string {
            return $this->rol;
        }

        public function __toString(): string {
            return "[Modelo] {$this->nombre} <{$this->email}> ({$this->rol})";
        }
    }

    // Otra clase en el mismo namespace
    class Producto {
        public function __construct(
            private string $nombre,
            private float $precio,
            private int $stock = 0
        ) {}

        public function getNombre(): string {
            return $this->nombre;
        }

        public function getPrecio(): float {
            return $this->precio;
        }

        public function getStock(): int {
            return $this->stock;
        }

        public function __toString(): string {
            return "[Modelo] {$this->nombre} - \${$this->precio} (Stock: {$this->stock})";
        }
    }

    // Funcion dentro del namespace
    function crearUsuarioAdmin(string $nombre, string $email): Usuario {
        return new Usuario($nombre, $email, 'admin');
    }

    // Constante dentro del namespace
    const TABLA_USUARIOS = 'tbl_usuarios';
    const TABLA_PRODUCTOS = 'tbl_productos';
}

// ============================================================================
// Ejemplo 2: Sub-namespaces y la palabra clave 'use' para importar
// ============================================================================
// 'use' importa clases, funciones y constantes de otros namespaces.
// Se pueden crear alias con 'as' para evitar nombres largos o conflictos.

namespace App\Services {

    // Importar clases de otro namespace
    use App\Models\Usuario;
    use App\Models\Producto;

    // Importar funcion de otro namespace
    use function App\Models\crearUsuarioAdmin;

    // Importar constante de otro namespace
    use const App\Models\TABLA_USUARIOS;

    class ServicioUsuario {
        private array $usuarios = [];

        // Podemos usar Usuario sin prefijo gracias al 'use' de arriba
        public function registrar(string $nombre, string $email): Usuario {
            $usuario = new Usuario($nombre, $email);
            $this->usuarios[] = $usuario;
            return $usuario;
        }

        public function registrarAdmin(string $nombre, string $email): Usuario {
            // Usando la funcion importada
            $admin = crearUsuarioAdmin($nombre, $email);
            $this->usuarios[] = $admin;
            return $admin;
        }

        public function listar(): array {
            return $this->usuarios;
        }

        public function getTabla(): string {
            // Usando la constante importada
            return TABLA_USUARIOS;
        }
    }

    class ServicioProducto {
        private array $productos = [];

        public function agregar(string $nombre, float $precio, int $stock): Producto {
            $producto = new Producto($nombre, $precio, $stock);
            $this->productos[] = $producto;
            return $producto;
        }

        public function buscarPorNombre(string $busqueda): array {
            return array_filter(
                $this->productos,
                fn(Producto $p) => stripos($p->getNombre(), $busqueda) !== false
            );
        }

        public function listar(): array {
            return $this->productos;
        }
    }
}

// ============================================================================
// Ejemplo 3: Aliasing con 'as' y resolucion de conflictos
// ============================================================================
// Cuando dos namespaces definen clases con el mismo nombre, usamos alias
// para diferenciarlas.

namespace App\Controllers {

    // Importar con alias para evitar conflictos o nombres largos
    use App\Services\ServicioUsuario as UsuarioService;
    use App\Services\ServicioProducto as ProductoService;
    use App\Models\Usuario as UsuarioModel;
    use App\Models\Producto as ProductoModel;

    class ControladorUsuario {
        private UsuarioService $servicio;

        public function __construct() {
            $this->servicio = new UsuarioService();
        }

        public function crear(string $nombre, string $email): string {
            $usuario = $this->servicio->registrar($nombre, $email);
            return "Usuario creado: {$usuario}";
        }

        public function crearAdmin(string $nombre, string $email): string {
            $admin = $this->servicio->registrarAdmin($nombre, $email);
            return "Admin creado: {$admin}";
        }

        public function listar(): array {
            return $this->servicio->listar();
        }
    }

    class ControladorProducto {
        private ProductoService $servicio;

        public function __construct() {
            $this->servicio = new ProductoService();
        }

        public function crear(string $nombre, float $precio, int $stock): string {
            $producto = $this->servicio->agregar($nombre, $precio, $stock);
            return "Producto creado: {$producto}";
        }

        public function buscar(string $termino): array {
            return $this->servicio->buscarPorNombre($termino);
        }

        public function listar(): array {
            return $this->servicio->listar();
        }
    }
}

// ============================================================================
// Ejemplo 4: Namespace global (\) y la palabra 'namespace' como operador
// ============================================================================
// El backslash \ al inicio de un nombre hace referencia al namespace global
// (raiz). Esto es necesario cuando se quiere usar clases/funciones nativas
// de PHP desde dentro de un namespace personalizado.

namespace App\Utils {

    // Sin el \ al inicio, PHP buscaria estas clases en App\Utils\DateTime, etc.
    // Con el \, indicamos que son del namespace global (built-in de PHP)

    class Formateador {
        // Usar \DateTime para referirse a la clase global, no App\Utils\DateTime
        public static function formatearFecha(\DateTime $fecha, string $formato = 'd/m/Y'): string {
            return $fecha->format($formato);
        }

        // \NumberFormatter es otra clase global de PHP
        public static function formatearMoneda(float $monto, string $locale = 'es_MX'): string {
            // Si la extension intl no esta disponible, formateamos manualmente
            return '$' . number_format($monto, 2, '.', ',');
        }

        // strlen() existe tanto en namespace global como podria existir en App\Utils
        // Usamos \ para ser explicitos sobre cual queremos
        public static function longitudTexto(string $texto): int {
            return \strlen($texto); // Funcion global strlen
        }

        // Demostrar el uso del operador 'namespace'
        // La palabra clave 'namespace' se puede usar como operador para referirse
        // al namespace actual
        public static function getNamespaceActual(): string {
            return __NAMESPACE__; // Constante magica que retorna el namespace actual
        }
    }

    // Constante y funcion en este namespace
    const VERSION_UTILS = '1.0.0';

    function formatearTelefono(string $telefono): string {
        // Limpiar todo excepto digitos
        $digitos = preg_replace('/\D/', '', $telefono);

        if (\strlen($digitos) === 10) {
            return \sprintf(
                '(%s) %s-%s',
                \substr($digitos, 0, 3),
                \substr($digitos, 3, 3),
                \substr($digitos, 6, 4)
            );
        }

        return $telefono; // Retornar original si no tiene 10 digitos
    }
}

// ============================================================================
// Ejemplo 5: Importar multiples elementos y agrupacion (PHP 7+)
// ============================================================================
// PHP 7+ permite agrupar importaciones del mismo namespace usando llaves.
// Esto reduce la repeticion y mejora la legibilidad.

namespace App\Reportes {

    // Importaciones agrupadas (PHP 7+) - todas del mismo namespace base
    use App\Models\{Usuario, Producto};
    use App\Services\{ServicioUsuario, ServicioProducto};
    use App\Controllers\{ControladorUsuario, ControladorProducto};

    // Importar funcion y constante con agrupacion
    use function App\Utils\formatearTelefono;
    use const App\Utils\VERSION_UTILS;

    class GeneradorReporte {
        private string $titulo;
        private array $lineas = [];

        public function __construct(string $titulo) {
            $this->titulo = $titulo;
            $this->lineas[] = str_repeat('=', 60);
            $this->lineas[] = "  {$titulo}";
            $this->lineas[] = "  Generado con Utils v" . VERSION_UTILS;
            $this->lineas[] = str_repeat('=', 60);
        }

        public function agregarSeccion(string $nombre): self {
            $this->lineas[] = "\n--- {$nombre} ---";
            return $this;
        }

        public function agregarLinea(string $texto): self {
            $this->lineas[] = "  {$texto}";
            return $this;
        }

        public function generar(): string {
            return implode("\n", $this->lineas);
        }
    }

    class ReporteInventario extends GeneradorReporte {
        public function __construct() {
            parent::__construct('REPORTE DE INVENTARIO');
        }

        public function agregarProducto(Producto $producto): self {
            $estado = $producto->getStock() > 0 ? 'En stock' : 'AGOTADO';
            $this->agregarLinea(
                sprintf(
                    "%-25s $%8.2f  Stock: %4d  [%s]",
                    $producto->getNombre(),
                    $producto->getPrecio(),
                    $producto->getStock(),
                    $estado
                )
            );
            return $this;
        }
    }
}

// ============================================================================
// Ejemplo 6: Aplicacion completa organizando con namespaces
// ============================================================================
// Demostramos el uso de todos los namespaces definidos anteriormente
// trabajando juntos en el namespace global.

namespace {
    // Estamos en el namespace global (raiz)
    // Necesitamos importar todo lo que vayamos a usar

    use App\Models\Usuario;
    use App\Models\Producto;
    use App\Services\ServicioUsuario;
    use App\Services\ServicioProducto;
    use App\Controllers\ControladorUsuario;
    use App\Controllers\ControladorProducto;
    use App\Utils\Formateador;
    use App\Reportes\ReporteInventario;
    use function App\Models\crearUsuarioAdmin;
    use function App\Utils\formatearTelefono;

    echo "=== Ejemplo 1-5: Namespaces, sub-namespaces, use, alias, global ===\n";
    echo "(Los ejemplos 1-5 definen la estructura. El ejemplo 6 los usa todos.)\n\n";

    echo "=== Ejemplo 6: Aplicacion completa con namespaces ===\n\n";

    // --- Trabajar con Controladores (que usan Servicios que usan Modelos) ---
    echo "--- Gestion de Usuarios ---\n";
    $ctrlUsuario = new ControladorUsuario();

    echo $ctrlUsuario->crear('Maria Garcia', 'maria@example.com') . "\n";
    echo $ctrlUsuario->crear('Pedro Sanchez', 'pedro@example.com') . "\n";
    echo $ctrlUsuario->crearAdmin('Laura Diaz', 'laura@example.com') . "\n";

    echo "\nUsuarios registrados:\n";
    foreach ($ctrlUsuario->listar() as $usuario) {
        echo "  - {$usuario}\n";
    }

    // --- Trabajar con Productos ---
    echo "\n--- Gestion de Productos ---\n";
    $ctrlProducto = new ControladorProducto();

    echo $ctrlProducto->crear('Laptop Dell XPS', 25000.00, 15) . "\n";
    echo $ctrlProducto->crear('Monitor LG 27"', 7500.00, 30) . "\n";
    echo $ctrlProducto->crear('Teclado Mecanico', 1800.00, 0) . "\n";
    echo $ctrlProducto->crear('Mouse Logitech', 650.00, 100) . "\n";
    echo $ctrlProducto->crear('Laptop HP Pavilion', 18000.00, 8) . "\n";

    // Buscar productos
    echo "\nBusqueda de 'Laptop':\n";
    foreach ($ctrlProducto->buscar('Laptop') as $producto) {
        echo "  - {$producto}\n";
    }

    // --- Usar utilidades ---
    echo "\n--- Utilidades ---\n";
    $ahora = new DateTime();
    echo "Fecha formateada: " . Formateador::formatearFecha($ahora) . "\n";
    echo "Fecha completa: " . Formateador::formatearFecha($ahora, 'd \d\e F \d\e Y, H:i') . "\n";
    echo "Monto: " . Formateador::formatearMoneda(25000.50) . "\n";
    echo "Longitud de 'Hola Mundo': " . Formateador::longitudTexto('Hola Mundo') . "\n";
    echo "Namespace de Formateador: " . Formateador::getNamespaceActual() . "\n";

    // Funcion importada del namespace App\Utils
    echo "Telefono: " . formatearTelefono('5512345678') . "\n";
    echo "Telefono: " . formatearTelefono('(55) 1234-5678') . "\n";

    // --- Crear modelos directamente ---
    echo "\n--- Usando funciones de namespace ---\n";
    $superAdmin = crearUsuarioAdmin('Roberto Torres', 'roberto@example.com');
    echo "Creado con funcion importada: {$superAdmin}\n";

    // --- Generar reporte ---
    echo "\n--- Reporte de Inventario ---\n";
    $reporte = new ReporteInventario();
    $reporte->agregarSeccion('Productos registrados');

    foreach ($ctrlProducto->listar() as $producto) {
        $reporte->agregarProducto($producto);
    }

    $reporte->agregarSeccion('Resumen');
    $total = count($ctrlProducto->listar());
    $reporte->agregarLinea("Total de productos: {$total}");
    $reporte->agregarLinea("Fecha: " . Formateador::formatearFecha(new DateTime()));

    echo $reporte->generar() . "\n\n";

    // --- Mostrar nombres completamente cualificados ---
    echo "--- Nombres completamente cualificados (Fully Qualified Names) ---\n";
    echo "Clase Usuario: " . Usuario::class . "\n";
    echo "Clase Producto: " . Producto::class . "\n";
    echo "Clase ServicioUsuario: " . ServicioUsuario::class . "\n";
    echo "Clase Formateador: " . Formateador::class . "\n";
    echo "Clase ReporteInventario: " . ReporteInventario::class . "\n";
}
?>
