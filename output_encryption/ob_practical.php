<?php
/**
 * Output Buffering - Usos prácticos
 *
 * Casos reales donde el buffer de salida es indispensable:
 * capturar HTML, modificar salida, prevenir errores de headers, etc.
 */

// ============================================
// Ejemplo 1: Capturar HTML de funciones que imprimen directamente
// ============================================

echo "=== Ejemplo 1: Capturar HTML de funciones ===\n";

/**
 * Algunas funciones de PHP (como var_dump, print_r, phpinfo)
 * imprimen directamente. Con ob_ podemos capturar su salida.
 */

// Capturar var_dump en una variable
function varDumpToString(mixed ...$vars): string
{
    ob_start();
    var_dump(...$vars);
    return ob_get_clean();
}

$datos = ['nombre' => 'Carlos', 'edad' => 30, 'activo' => true];
$dump = varDumpToString($datos);
echo "var_dump capturado en variable:\n$dump\n";

// Capturar print_r en variable (print_r ya tiene el parámetro true, pero como ejemplo)
function capturarSalida(callable $funcion): string
{
    ob_start();
    $funcion();
    return ob_get_clean();
}

$salida = capturarSalida(function () {
    echo "<table>\n";
    echo "  <tr><th>Producto</th><th>Precio</th></tr>\n";
    echo "  <tr><td>Laptop</td><td>\$999</td></tr>\n";
    echo "  <tr><td>Mouse</td><td>\$29</td></tr>\n";
    echo "</table>\n";
});

echo "HTML capturado (" . strlen($salida) . " bytes):\n$salida\n";

// ============================================
// Ejemplo 2: Sistema de plantillas simple
// ============================================

echo "=== Ejemplo 2: Sistema de plantillas ===\n";

/**
 * Usar output buffering para crear un sistema de plantillas
 * que renderiza archivos PHP y captura su salida.
 */
class TemplateEngine
{
    private string $templateDir;
    private array $variables = [];

    public function __construct(string $templateDir = '')
    {
        $this->templateDir = $templateDir;
    }

    /**
     * Asignar variable para la plantilla
     */
    public function asignar(string $nombre, mixed $valor): void
    {
        $this->variables[$nombre] = $valor;
    }

    /**
     * Renderizar una plantilla (string PHP) y capturar la salida
     */
    public function renderizar(string $templateCode): string
    {
        // Extraer variables al scope local
        extract($this->variables);

        ob_start();
        // Evaluar el código PHP de la plantilla
        eval('?>' . $templateCode);
        return ob_get_clean();
    }

    /**
     * Renderizar un bloque con layout
     */
    public function renderizarConLayout(string $contenido, string $layoutCode): string
    {
        $this->asignar('contenido', $contenido);
        return $this->renderizar($layoutCode);
    }
}

$engine = new TemplateEngine();

// Definir variables de la plantilla
$engine->asignar('titulo', 'Mi Página');
$engine->asignar('items', ['PHP', 'JavaScript', 'Python']);
$engine->asignar('usuario', 'Sandra');

// Plantilla como string (en producción serían archivos separados)
$plantilla = <<<'TPL'
<h1><?= htmlspecialchars($titulo) ?></h1>
<p>Bienvenida, <?= htmlspecialchars($usuario) ?></p>
<ul>
<?php foreach ($items as $item): ?>
  <li><?= htmlspecialchars($item) ?></li>
<?php endforeach; ?>
</ul>
TPL;

$html = $engine->renderizar($plantilla);
echo "HTML renderizado:\n$html\n";

// Renderizar con layout
$layout = <<<'LAYOUT'
<!DOCTYPE html>
<html>
<head><title><?= htmlspecialchars($titulo) ?></title></head>
<body>
<?= $contenido ?>
<footer>Generado por TemplateEngine</footer>
</body>
</html>
LAYOUT;

$paginaCompleta = $engine->renderizarConLayout($html, $layout);
echo "Página completa:\n$paginaCompleta\n";

// ============================================
// Ejemplo 3: Prevenir errores de headers
// ============================================

echo "=== Ejemplo 3: Prevenir errores de headers ===\n";

/**
 * En PHP, las funciones header(), setcookie() y session_start()
 * deben llamarse ANTES de enviar cualquier salida al navegador.
 *
 * Si se envía salida antes, PHP lanza:
 * "Cannot modify header information - headers already sent"
 *
 * Output buffering soluciona esto al retener la salida.
 */

// Sin buffer: este código fallaría en un contexto web real
echo "Simulación del problema sin buffer:\n";
echo "  1. echo 'algo' -> salida enviada al navegador\n";
echo "  2. header('Location: /') -> ERROR: headers already sent\n\n";

// Con buffer: la salida se retiene hasta ob_end_flush()
echo "Solución con buffer:\n";
echo "  1. ob_start() -> activa el buffer\n";
echo "  2. echo 'algo' -> se almacena en buffer (NO se envía)\n";
echo "  3. header('Location: /') -> funciona correctamente\n";
echo "  4. ob_end_flush() -> ahora se envía todo\n\n";

// Ejemplo práctico: función de redirección segura
function redireccionSegura(string $url, int $codigo = 302): void
{
    // En un entorno web real, estos headers funcionarían
    // gracias al output buffering
    echo "  [Simulación] header('Location: $url', true, $codigo)\n";
    echo "  [Simulación] La página redirigiría a: $url\n";
}

// Simular flujo típico de aplicación web
ob_start();

// Lógica de aplicación que puede generar salida
echo "Verificando sesión del usuario...\n";

$usuarioLogueado = false;
if (!$usuarioLogueado) {
    // Esto funciona gracias al buffer activo
    redireccionSegura('/login', 302);
    $buffer = ob_get_clean(); // Descartar salida anterior
    echo "Redirección simulada (buffer descartado).\n";
} else {
    ob_end_flush(); // Enviar salida normalmente
}

// ============================================
// Ejemplo 4: Modificar la salida (compresión, caché, reemplazo)
// ============================================

echo "\n=== Ejemplo 4: Modificar salida ===\n";

// Reemplazar contenido en la salida
echo "Reemplazo de contenido:\n";
ob_start(function (string $buffer): string {
    // Reemplazar palabras específicas
    $reemplazos = [
        'malo'   => '****',
        'feo'    => '***',
        'tonto'  => '*****',
    ];
    return str_ireplace(array_keys($reemplazos), array_values($reemplazos), $buffer);
});

echo "  Este texto es malo y feo pero no tonto.\n";
echo "  Otro texto MALO con diferentes mayúsculas.\n";

ob_end_flush();

// Agregar metadatos a la salida HTML
echo "\nAgregar información al HTML:\n";
ob_start(function (string $buffer): string {
    $tiempoGeneracion = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true), 4);
    $memoria = number_format(memory_get_peak_usage() / 1024, 2);
    $comentario = "<!-- Generado en {$tiempoGeneracion}s | Memoria: {$memoria}KB -->\n";
    return $buffer . $comentario;
});

echo "<html><body><p>Contenido de la página</p></body></html>\n";

$htmlConMeta = ob_get_clean();
echo $htmlConMeta;

// Sistema de caché con output buffering
echo "Sistema de caché con buffer:\n";

function renderizarConCache(string $cacheKey, int $ttl, callable $generador): string
{
    // Simular caché en memoria (en producción se usaría archivos o Redis)
    static $cache = [];

    if (isset($cache[$cacheKey]) && $cache[$cacheKey]['expira'] > time()) {
        echo "  [CACHE HIT] $cacheKey\n";
        return $cache[$cacheKey]['contenido'];
    }

    echo "  [CACHE MISS] $cacheKey - Generando contenido...\n";

    ob_start();
    $generador();
    $contenido = ob_get_clean();

    $cache[$cacheKey] = [
        'contenido' => $contenido,
        'expira'    => time() + $ttl,
    ];

    return $contenido;
}

// Primera llamada: genera el contenido
$html = renderizarConCache('pagina_inicio', 3600, function () {
    echo "<h1>Página de inicio</h1>\n";
    echo "<p>Generada a las " . date('H:i:s') . "</p>\n";
});
echo $html;

// Segunda llamada: usa la caché
$html = renderizarConCache('pagina_inicio', 3600, function () {
    echo "<h1>Contenido nuevo</h1>\n";
});
echo $html;

// ============================================
// Ejemplo 5: Caso práctico - Capturar errores fatales
// ============================================

echo "\n=== Ejemplo 5: Página de error personalizada ===\n";

/**
 * Usar output buffering junto con register_shutdown_function
 * para mostrar páginas de error personalizadas incluso en errores fatales.
 */
class ErrorPageHandler
{
    private bool $bufferActivo = false;

    public function iniciar(): void
    {
        $this->bufferActivo = true;
        ob_start();

        register_shutdown_function([$this, 'manejarShutdown']);
    }

    public function manejarShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Limpiar cualquier salida que se haya generado
            if ($this->bufferActivo) {
                ob_end_clean();
            }

            // Mostrar página de error personalizada
            echo $this->generarPaginaError($error);
        } elseif ($this->bufferActivo) {
            // Sin error: enviar la salida normalmente
            ob_end_flush();
        }
    }

    private function generarPaginaError(array $error): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><title>Error del servidor</title></head>
        <body>
          <h1>Oops! Algo salió mal</h1>
          <p>Estamos trabajando para resolver el problema.</p>
          <!-- Error: {$error['message']} en {$error['file']}:{$error['line']} -->
        </body>
        </html>
        HTML;
    }
}

// Demostración del concepto (sin provocar error fatal real)
echo "Concepto de manejo de errores fatales:\n";
echo "  1. ob_start() captura toda la salida\n";
echo "  2. register_shutdown_function() se ejecuta al final\n";
echo "  3. Si hubo error fatal: descartar buffer, mostrar página de error\n";
echo "  4. Si no hubo error: enviar buffer normalmente\n\n";

$handler = new ErrorPageHandler();
// $handler->iniciar(); // Descomentar en una aplicación real

echo "Ejemplo de página de error generada:\n";
$errorSimulado = [
    'type'    => E_ERROR,
    'message' => 'Call to undefined function foo()',
    'file'    => '/var/www/app/index.php',
    'line'    => 42,
];
echo "  Error simulado: {$errorSimulado['message']}\n";
echo "  En producción se mostraría una página HTML personalizada.\n";

?>
