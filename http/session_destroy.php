<?php
// ============================================================
// session_destroy() y session_unset() - Destruir sesiones
// ============================================================
// session_unset() - Elimina todas las variables de sesión.
// session_destroy() - Destruye los datos de sesión en el servidor.
// Ambas se usan juntas para un cierre de sesión completo.
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: session_unset() - Eliminar variables de sesión
// ------------------------------------------------------------
// session_unset() elimina todas las variables almacenadas en
// $_SESSION pero mantiene la sesión activa.

session_start();

// Establecer algunos datos de ejemplo
$_SESSION["usuario"] = "Pedro López";
$_SESSION["rol"] = "editor";
$_SESSION["tema"] = "oscuro";

echo "Antes de session_unset():\n";
echo "  Número de variables: " . count($_SESSION) . "\n";
echo "  Usuario: " . $_SESSION["usuario"] . "\n";

// Eliminar todas las variables de sesión
session_unset();

echo "\nDespués de session_unset():\n";
echo "  Número de variables: " . count($_SESSION) . "\n";
echo "  La sesión sigue activa con ID: " . session_id() . "\n";

// También se pueden eliminar variables individuales con unset()
$_SESSION["nueva_variable"] = "valor temporal";
unset($_SESSION["nueva_variable"]); // Eliminar solo esta variable
echo "  Variable individual eliminada con unset().\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: session_destroy() - Destruir la sesión
// ------------------------------------------------------------
// session_destroy() elimina los datos de sesión del servidor.
// Sin embargo, la cookie PHPSESSID permanece en el navegador.

session_start();

$_SESSION["datos_ejemplo"] = "Este valor se perderá";

echo "Antes de session_destroy():\n";
echo "  ID de sesión: " . session_id() . "\n";
echo "  Datos: " . $_SESSION["datos_ejemplo"] . "\n";

// Destruir la sesión en el servidor
$resultado = session_destroy();

if ($resultado) {
    echo "\nSesión destruida exitosamente en el servidor.\n";
} else {
    echo "\nError al destruir la sesión.\n";
}

// Nota importante: $_SESSION todavía contiene los datos en esta ejecución
echo "Nota: \$_SESSION aún tiene datos en esta ejecución: "
    . ($_SESSION["datos_ejemplo"] ?? "vacío") . "\n";
echo "Pero en la siguiente solicitud, los datos ya no estarán.\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Procedimiento COMPLETO de cierre de sesión
// ------------------------------------------------------------
// Un logout seguro requiere varios pasos: limpiar variables,
// destruir la sesión y eliminar la cookie del navegador.

session_start();

// Simular datos de sesión activa
$_SESSION["usuario_id"] = 42;
$_SESSION["nombre"] = "Laura Hernández";
$_SESSION["token_csrf"] = bin2hex(random_bytes(16));

echo "=== Procedimiento de cierre de sesión seguro ===\n\n";
echo "Sesión activa: " . session_id() . "\n";
echo "Usuario: " . $_SESSION["nombre"] . "\n\n";

// Paso 1: Limpiar todas las variables de sesión
$_SESSION = []; // Alternativa a session_unset()
echo "Paso 1: Variables de sesión limpiadas.\n";

// Paso 2: Eliminar la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),        // nombre de la cookie (PHPSESSID)
        "",                    // valor vacío
        time() - 42000,        // expirar en el pasado
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
    echo "Paso 2: Cookie de sesión eliminada del navegador.\n";
}

// Paso 3: Destruir la sesión en el servidor
session_destroy();
echo "Paso 3: Sesión destruida en el servidor.\n";

echo "\nCierre de sesión completado. Redirigiendo al inicio...\n";
// header("Location: /login.php?mensaje=sesion_cerrada");
// exit;
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Expiración de sesión por inactividad
// ------------------------------------------------------------
// Implementar un timeout que destruya la sesión si el usuario
// permanece inactivo por un período determinado.

session_start();

// Tiempo máximo de inactividad en segundos (30 minutos)
$tiempoMaximoInactividad = 30 * 60; // 1800 segundos

// Verificar si existe un timestamp de última actividad
if (isset($_SESSION["ultima_actividad"])) {
    $tiempoInactivo = time() - $_SESSION["ultima_actividad"];

    if ($tiempoInactivo > $tiempoMaximoInactividad) {
        // La sesión ha expirado por inactividad
        echo "Tu sesión expiró después de 30 minutos de inactividad.\n";
        echo "Tiempo inactivo: " . floor($tiempoInactivo / 60) . " minutos.\n";

        // Procedimiento completo de limpieza
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        echo "Sesión destruida. Por favor, inicia sesión nuevamente.\n";
        // header("Location: /login.php?razon=timeout");
        // exit;
    } else {
        // Sesión válida, actualizar timestamp
        $_SESSION["ultima_actividad"] = time();
        $minutosRestantes = floor(($tiempoMaximoInactividad - $tiempoInactivo) / 60);
        echo "Sesión activa. Tiempo restante: $minutosRestantes minutos.\n";
    }
} else {
    // Primera solicitud, establecer timestamp
    $_SESSION["ultima_actividad"] = time();
    echo "Timestamp de actividad inicializado.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Regenerar ID de sesión por seguridad
// ------------------------------------------------------------
// Regenerar el ID previene ataques de fijación de sesión.
// Se recomienda hacerlo después del login y periódicamente.

session_start();

// Simular un proceso de inicio de sesión
function iniciarSesionSegura(string $usuario, string $contrasena): bool {
    // Simular verificación de credenciales
    $credencialesValidas = ($usuario === "admin" && $contrasena === "segura123");

    if ($credencialesValidas) {
        // IMPORTANTE: Regenerar el ID de sesión después del login
        // Esto previene ataques de fijación de sesión
        $idAnterior = session_id();
        session_regenerate_id(true); // true = eliminar sesión anterior
        $idNuevo = session_id();

        echo "ID de sesión regenerado por seguridad:\n";
        echo "  Anterior: $idAnterior\n";
        echo "  Nuevo: $idNuevo\n";

        // Almacenar datos del usuario autenticado
        $_SESSION["autenticado"] = true;
        $_SESSION["usuario"] = $usuario;
        $_SESSION["ip_login"] = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
        $_SESSION["momento_login"] = time();
        $_SESSION["agente_usuario"] = $_SERVER["HTTP_USER_AGENT"] ?? "desconocido";

        return true;
    }

    return false;
}

// Ejecutar el login
if (iniciarSesionSegura("admin", "segura123")) {
    echo "\nInicio de sesión exitoso.\n";
    echo "Usuario: " . $_SESSION["usuario"] . "\n";
} else {
    echo "Credenciales incorrectas.\n";
}

// Regeneración periódica (cada 15 minutos durante la sesión)
$intervaloRegeneracion = 15 * 60; // 15 minutos

if (!isset($_SESSION["ultima_regeneracion"])) {
    $_SESSION["ultima_regeneracion"] = time();
} elseif (time() - $_SESSION["ultima_regeneracion"] > $intervaloRegeneracion) {
    session_regenerate_id(true);
    $_SESSION["ultima_regeneracion"] = time();
    echo "ID de sesión regenerado periódicamente.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Función auxiliar completa para gestión de sesiones
// ------------------------------------------------------------
// Clase práctica que encapsula las operaciones comunes de sesión.

class GestorSesion {
    private int $tiempoExpiracion;

    public function __construct(int $tiempoExpiracionMinutos = 30) {
        $this->tiempoExpiracion = $tiempoExpiracionMinutos * 60;
    }

    // Iniciar sesión con opciones seguras
    public function iniciar(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                "cookie_lifetime" => 0,
                "cookie_secure"   => true,
                "cookie_httponly"  => true,
                "cookie_samesite" => "Lax",
                "use_strict_mode" => true,
            ]);
        }
    }

    // Verificar si la sesión está activa y no ha expirado
    public function estaActiva(): bool {
        if (!isset($_SESSION["ultima_actividad"])) {
            return false;
        }

        if (time() - $_SESSION["ultima_actividad"] > $this->tiempoExpiracion) {
            $this->destruir();
            return false;
        }

        $_SESSION["ultima_actividad"] = time();
        return true;
    }

    // Destruir sesión completamente
    public function destruir(): void {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000,
                $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }

        session_destroy();
    }

    // Regenerar ID de sesión
    public function regenerarId(): void {
        session_regenerate_id(true);
    }

    // Obtener el estado actual de la sesión
    public function obtenerEstado(): string {
        return match (session_status()) {
            PHP_SESSION_DISABLED => "Sesiones deshabilitadas",
            PHP_SESSION_NONE     => "Sesión no iniciada",
            PHP_SESSION_ACTIVE   => "Sesión activa (ID: " . session_id() . ")",
        };
    }
}

// Uso del gestor de sesiones
$gestor = new GestorSesion(30); // 30 minutos de expiración
$gestor->iniciar();

echo "Estado: " . $gestor->obtenerEstado() . "\n";

// Simular flujo de autenticación
$_SESSION["usuario"] = "Ana";
$_SESSION["ultima_actividad"] = time();

if ($gestor->estaActiva()) {
    echo "Sesión válida para: " . $_SESSION["usuario"] . "\n";
}

// Cerrar sesión
// $gestor->destruir();
// echo "Estado tras destruir: " . $gestor->obtenerEstado() . "\n";
?>
