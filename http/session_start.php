<?php
// ============================================================
// session_start() - Iniciar y gestionar sesiones en PHP
// ============================================================
// Las sesiones almacenan datos del usuario en el servidor entre
// solicitudes HTTP. Se identifican mediante un ID de sesión
// almacenado en una cookie del navegador (PHPSESSID).
// session_start() debe llamarse antes de acceder a $_SESSION.
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Iniciar una sesión básica
// ------------------------------------------------------------
// session_start() crea una nueva sesión o reanuda la existente.
// Debe ser lo primero antes de cualquier salida al navegador.

// Iniciar la sesión
session_start();

echo "Sesión iniciada correctamente.\n";
echo "ID de sesión: " . session_id() . "\n";
echo "Nombre de la cookie de sesión: " . session_name() . "\n"; // PHPSESSID

// Verificar si es una sesión nueva o existente
if (empty($_SESSION)) {
    echo "Esta es una sesión nueva (sin datos previos).\n";
} else {
    echo "Sesión existente con " . count($_SESSION) . " variables almacenadas.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Almacenar datos en $_SESSION
// ------------------------------------------------------------
// $_SESSION es un arreglo superglobal que persiste entre solicitudes.
// Los datos se guardan en el servidor, no en el navegador.

session_start();

// Almacenar datos simples
$_SESSION["usuario_id"] = 42;
$_SESSION["nombre"] = "Ana Martínez";
$_SESSION["email"] = "ana@ejemplo.com";
$_SESSION["rol"] = "administrador";

// Almacenar datos complejos (arreglos, objetos)
$_SESSION["permisos"] = ["leer", "escribir", "eliminar", "administrar"];
$_SESSION["preferencias"] = [
    "idioma" => "es",
    "tema"   => "oscuro",
    "zona_horaria" => "America/Mexico_City"
];

// Almacenar la hora de inicio de sesión
$_SESSION["inicio_sesion"] = time();
$_SESSION["ultima_actividad"] = time();

echo "Datos almacenados en la sesión:\n";
echo "  Usuario: " . $_SESSION["nombre"] . " (ID: " . $_SESSION["usuario_id"] . ")\n";
echo "  Email: " . $_SESSION["email"] . "\n";
echo "  Rol: " . $_SESSION["rol"] . "\n";
echo "  Permisos: " . implode(", ", $_SESSION["permisos"]) . "\n";
echo "  Idioma: " . $_SESSION["preferencias"]["idioma"] . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Leer y verificar datos de sesión
// ------------------------------------------------------------
// Siempre verificar que los datos existen antes de usarlos
// para evitar errores de índice indefinido.

session_start();

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION["usuario_id"])) {
    echo "Bienvenido/a, " . $_SESSION["nombre"] . "\n";
    echo "Tu rol es: " . $_SESSION["rol"] . "\n";

    // Calcular tiempo transcurrido desde el inicio de sesión
    if (isset($_SESSION["inicio_sesion"])) {
        $tiempoTranscurrido = time() - $_SESSION["inicio_sesion"];
        $minutos = floor($tiempoTranscurrido / 60);
        echo "Llevas $minutos minutos en sesión.\n";
    }
} else {
    echo "No has iniciado sesión. Redirigiendo al login...\n";
    // header("Location: /login.php");
    // exit;
}

// Usar el operador de fusión null para valores por defecto
$idioma = $_SESSION["preferencias"]["idioma"] ?? "es";
$tema = $_SESSION["preferencias"]["tema"] ?? "claro";
echo "Configuración: idioma=$idioma, tema=$tema\n";

// Verificar existencia con array_key_exists (detecta valores null)
if (array_key_exists("carrito", $_SESSION)) {
    echo "Tienes artículos en el carrito.\n";
} else {
    echo "Tu carrito está vacío.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: session_id() - Obtener y establecer el ID de sesión
// ------------------------------------------------------------
// session_id() sin argumentos devuelve el ID actual.
// Con argumento, establece un ID personalizado (antes de session_start).

// Obtener el ID de sesión actual
$idActual = session_id();

// Si no hay sesión activa, session_id() devuelve cadena vacía
if ($idActual === "") {
    echo "No hay sesión activa.\n";

    // Establecer un ID personalizado ANTES de iniciar la sesión
    // Útil para migración de sesiones entre servidores
    // session_id("mi_id_personalizado_12345");

    session_start();
    echo "Sesión iniciada con ID: " . session_id() . "\n";
} else {
    echo "Sesión ya activa con ID: $idActual\n";
}

// Regenerar el ID de sesión (buena práctica de seguridad)
// Preserva los datos de la sesión pero cambia el identificador
$idAnterior = session_id();
session_regenerate_id(true); // true = eliminar archivo de sesión anterior
$idNuevo = session_id();

echo "ID anterior: $idAnterior\n";
echo "ID nuevo: $idNuevo\n";
echo "Los datos de sesión se preservaron durante la regeneración.\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Configurar opciones de sesión
// ------------------------------------------------------------
// session_start() acepta un arreglo de opciones (PHP 7.0+)
// para personalizar el comportamiento de la sesión.

// Iniciar sesión con opciones personalizadas
session_start([
    // Tiempo de vida de la cookie en segundos (0 = hasta cerrar navegador)
    "cookie_lifetime" => 3600,       // 1 hora

    // Ruta donde la cookie es válida
    "cookie_path" => "/",

    // Solo enviar cookie por HTTPS
    "cookie_secure" => true,

    // No accesible desde JavaScript
    "cookie_httponly" => true,

    // Política SameSite
    "cookie_samesite" => "Lax",

    // Tiempo máximo de inactividad en segundos (servidor)
    "gc_maxlifetime" => 1800,        // 30 minutos

    // Usar solo cookies para propagar el ID de sesión (no URL)
    "use_strict_mode" => true,
    "use_only_cookies" => true,
]);

echo "Sesión configurada con opciones de seguridad:\n";
echo "  Vida de cookie: 1 hora\n";
echo "  Cookie segura (HTTPS): sí\n";
echo "  HttpOnly: sí\n";
echo "  SameSite: Lax\n";
echo "  Modo estricto: activado\n";

// Mostrar la configuración actual de la sesión
echo "\nConfiguración de sesión del servidor:\n";
echo "  save_path: " . session_save_path() . "\n";
echo "  name: " . session_name() . "\n";
echo "  gc_maxlifetime: " . ini_get("session.gc_maxlifetime") . " segundos\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Contador de visitas con sesiones
// ------------------------------------------------------------
// Ejemplo práctico que demuestra la persistencia de datos
// entre múltiples solicitudes HTTP.

session_start();

// Inicializar el contador si no existe
if (!isset($_SESSION["visitas"])) {
    $_SESSION["visitas"] = 0;
    $_SESSION["primera_visita"] = date("Y-m-d H:i:s");
}

// Incrementar el contador
$_SESSION["visitas"]++;
$_SESSION["ultima_visita"] = date("Y-m-d H:i:s");

// Registrar las páginas visitadas
if (!isset($_SESSION["paginas_visitadas"])) {
    $_SESSION["paginas_visitadas"] = [];
}

$paginaActual = $_SERVER["REQUEST_URI"] ?? "/session_start.php";
$_SESSION["paginas_visitadas"][] = [
    "url"   => $paginaActual,
    "fecha" => date("Y-m-d H:i:s")
];

// Mostrar estadísticas
echo "=== Estadísticas de tu sesión ===\n";
echo "ID de sesión: " . session_id() . "\n";
echo "Total de visitas: " . $_SESSION["visitas"] . "\n";
echo "Primera visita: " . $_SESSION["primera_visita"] . "\n";
echo "Última visita: " . $_SESSION["ultima_visita"] . "\n";
echo "Páginas visitadas: " . count($_SESSION["paginas_visitadas"]) . "\n";

// Mostrar las últimas 5 páginas visitadas
echo "\nÚltimas páginas visitadas:\n";
$ultimasPaginas = array_slice($_SESSION["paginas_visitadas"], -5);
foreach ($ultimasPaginas as $pagina) {
    echo "  [{$pagina["fecha"]}] {$pagina["url"]}\n";
}
?>
