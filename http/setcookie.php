<?php
// ============================================================
// setcookie() - Crear y gestionar cookies HTTP
// ============================================================
// Las cookies son pequeños datos almacenados en el navegador del
// usuario. Se envían con cada solicitud HTTP al servidor.
// setcookie() debe llamarse ANTES de cualquier salida.
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Crear una cookie básica
// ------------------------------------------------------------
// setcookie(nombre, valor) crea una cookie de sesión que se
// elimina cuando el usuario cierra el navegador.

// Cookie simple de sesión (expira al cerrar el navegador)
setcookie("idioma", "es");

// Cookie con valor que contiene caracteres especiales
setcookie("nombre_usuario", "María García");

// Leer cookies previamente establecidas (disponibles en la SIGUIENTE solicitud)
if (isset($_COOKIE["idioma"])) {
    echo "Idioma seleccionado: " . $_COOKIE["idioma"] . "\n";
} else {
    echo "La cookie 'idioma' se estableció. Estará disponible en la próxima solicitud.\n";
}

// Mostrar todas las cookies actuales
echo "Cookies actuales:\n";
foreach ($_COOKIE as $nombre => $valor) {
    echo "  $nombre = $valor\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Cookie con tiempo de expiración
// ------------------------------------------------------------
// El tercer parámetro es el tiempo de expiración como timestamp Unix.
// time() devuelve el tiempo actual en segundos.

// Cookie que expira en 1 hora (3600 segundos)
setcookie("sesion_temporal", "abc123", time() + 3600);

// Cookie que expira en 7 días
$sieteDias = 7 * 24 * 60 * 60; // 604800 segundos
setcookie("preferencias", "tema_oscuro", time() + $sieteDias);

// Cookie que expira en 30 días
setcookie("recordar_usuario", "usuario_42", time() + (30 * 24 * 60 * 60));

// Cookie que expira en 1 año (máximo recomendado)
setcookie("consentimiento_cookies", "aceptado", time() + (365 * 24 * 60 * 60));

echo "Cookies con expiración creadas:\n";
echo "  sesion_temporal: expira en 1 hora\n";
echo "  preferencias: expira en 7 días\n";
echo "  recordar_usuario: expira en 30 días\n";
echo "  consentimiento_cookies: expira en 1 año\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Eliminar (expirar) una cookie
// ------------------------------------------------------------
// Para eliminar una cookie, se establece con un tiempo de
// expiración en el pasado y un valor vacío.

// Eliminar una cookie estableciendo su expiración en el pasado
setcookie("sesion_temporal", "", time() - 3600);

// Eliminar con los mismos parámetros de ruta y dominio originales
// (deben coincidir con los usados al crear la cookie)
setcookie("preferencias", "", time() - 3600, "/");

// También eliminar del arreglo $_COOKIE en la ejecución actual
unset($_COOKIE["sesion_temporal"]);
unset($_COOKIE["preferencias"]);

echo "Cookies 'sesion_temporal' y 'preferencias' eliminadas.\n";

// Verificar que se eliminaron
if (!isset($_COOKIE["sesion_temporal"])) {
    echo "Confirmado: 'sesion_temporal' ya no existe en \$_COOKIE.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Cookie con ruta y dominio específicos
// ------------------------------------------------------------
// La ruta limita en qué rutas del sitio la cookie es accesible.
// El dominio controla en qué subdominios está disponible.

// Cookie disponible en todo el sitio (ruta "/")
setcookie("global", "disponible_en_todo_el_sitio", time() + 3600, "/");

// Cookie solo disponible en /admin y sus subrutas
setcookie("admin_token", "tok_xyz789", time() + 3600, "/admin");

// Cookie solo disponible en /api
setcookie("api_version", "v2", time() + 3600, "/api");

// Cookie con dominio específico (disponible en todos los subdominios)
// El punto inicial hace que esté disponible en *.ejemplo.com
setcookie(
    "compartida",          // nombre
    "valor_compartido",    // valor
    time() + 86400,        // expira en 24 horas
    "/",                   // ruta: todo el sitio
    ".ejemplo.com"         // dominio: incluye subdominios
);

echo "Cookies con rutas específicas creadas:\n";
echo "  'global' -> disponible en /\n";
echo "  'admin_token' -> solo en /admin/*\n";
echo "  'api_version' -> solo en /api/*\n";
echo "  'compartida' -> disponible en *.ejemplo.com\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Cookies seguras (secure y httponly)
// ------------------------------------------------------------
// secure: la cookie solo se envía por HTTPS
// httponly: la cookie no es accesible desde JavaScript (protege contra XSS)

// Cookie segura y httponly (recomendado para tokens de sesión)
setcookie(
    "token_sesion",        // nombre
    "jwt_abc123def456",    // valor
    time() + 7200,         // expira en 2 horas
    "/",                   // ruta: todo el sitio
    "",                    // dominio: actual
    true,                  // secure: solo HTTPS
    true                   // httponly: no accesible desde JavaScript
);

echo "Cookie 'token_sesion' creada con protecciones:\n";
echo "  - Secure: solo se envía por HTTPS\n";
echo "  - HttpOnly: JavaScript no puede acceder a ella\n\n";

// Cookie NO httponly (accesible desde JavaScript)
// Útil para preferencias de interfaz que JS necesita leer
setcookie(
    "tema_ui",
    "oscuro",
    time() + (30 * 24 * 60 * 60),
    "/",
    "",
    true,     // secure
    false     // httponly: false = accesible desde JS
);

echo "Cookie 'tema_ui' accesible desde JavaScript para el tema visual.\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Cookie con SameSite (sintaxis moderna con arreglo)
// ------------------------------------------------------------
// SameSite controla cuándo se envía la cookie en solicitudes entre sitios.
// PHP 7.3+ admite la sintaxis con arreglo de opciones.

// SameSite=Strict: solo se envía en solicitudes del mismo sitio
// Máxima protección contra CSRF
setcookie("csrf_token", bin2hex(random_bytes(32)), [
    "expires"  => time() + 3600,
    "path"     => "/",
    "domain"   => "",
    "secure"   => true,
    "httponly"  => true,
    "samesite" => "Strict"   // Strict, Lax o None
]);

// SameSite=Lax: se envía en navegación de nivel superior entre sitios
// Buen balance entre seguridad y usabilidad (valor por defecto en navegadores modernos)
setcookie("sesion_usuario", session_id(), [
    "expires"  => time() + 86400,
    "path"     => "/",
    "secure"   => true,
    "httponly"  => true,
    "samesite" => "Lax"
]);

// SameSite=None: se envía en todas las solicitudes entre sitios
// Requiere secure=true. Útil para widgets embebidos o iframes
setcookie("rastreo_analytics", "ua_12345", [
    "expires"  => time() + (365 * 24 * 60 * 60),
    "path"     => "/",
    "secure"   => true,       // Obligatorio con SameSite=None
    "httponly"  => false,
    "samesite" => "None"
]);

echo "Cookies con SameSite creadas:\n";
echo "  csrf_token     -> SameSite=Strict (máxima protección CSRF)\n";
echo "  sesion_usuario -> SameSite=Lax (balance seguridad/usabilidad)\n";
echo "  rastreo_analytics -> SameSite=None (acceso entre sitios)\n";

// --- Resumen de mejores prácticas ---
echo "\n=== Mejores Prácticas para Cookies ===\n";
echo "1. Siempre usar 'secure' en producción (solo HTTPS)\n";
echo "2. Usar 'httponly' para cookies que no necesita JavaScript\n";
echo "3. Usar 'SameSite=Lax' o 'Strict' para protección CSRF\n";
echo "4. Establecer tiempo de expiración razonable\n";
echo "5. No almacenar datos sensibles directamente en cookies\n";
echo "6. Usar la sintaxis de arreglo (PHP 7.3+) para claridad\n";
?>
