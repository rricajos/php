<?php
// ============================================================
// $_POST - Acceder a datos enviados por formularios y cuerpo HTTP
// ============================================================
// $_POST contiene datos enviados en el cuerpo de una solicitud HTTP
// con método POST. Comúnmente usado para formularios HTML y APIs.
// A diferencia de $_GET, los datos no aparecen en la URL.
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Recibir datos de un formulario HTML
// ------------------------------------------------------------
// Formulario HTML que envía datos por POST:
// <form method="POST" action="superglobals_post.php">
//     <input type="text" name="nombre">
//     <input type="email" name="email">
//     <button type="submit">Enviar</button>
// </form>

// Verificar que la solicitud es POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Acceder a los campos del formulario
    $nombre = $_POST["nombre"] ?? "";
    $email  = $_POST["email"] ?? "";

    echo "Datos recibidos del formulario:\n";
    echo "  Nombre: $nombre\n";
    echo "  Email: $email\n";
} else {
    echo "Esta página espera datos enviados por POST.\n";
    echo "Método actual: " . ($_SERVER["REQUEST_METHOD"] ?? "desconocido") . "\n";
}

// Mostrar todos los datos POST recibidos
echo "\nTodos los datos POST:\n";
if (!empty($_POST)) {
    foreach ($_POST as $campo => $valor) {
        if (is_array($valor)) {
            echo "  $campo = [" . implode(", ", $valor) . "]\n";
        } else {
            echo "  $campo = $valor\n";
        }
    }
} else {
    echo "  (sin datos POST)\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Validar datos de un formulario de registro
// ------------------------------------------------------------
// Validación completa de un formulario con múltiples campos.

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recopilar errores de validación
    $errores = [];

    // Validar nombre (requerido, mínimo 2 caracteres)
    $nombre = trim($_POST["nombre"] ?? "");
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    } elseif (mb_strlen($nombre) < 2) {
        $errores[] = "El nombre debe tener al menos 2 caracteres.";
    } elseif (mb_strlen($nombre) > 100) {
        $errores[] = "El nombre no puede exceder 100 caracteres.";
    }

    // Validar email (requerido, formato válido)
    $email = trim($_POST["email"] ?? "");
    if (empty($email)) {
        $errores[] = "El email es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido.";
    }

    // Validar contraseña (mínimo 8 caracteres, al menos un número)
    $contrasena = $_POST["contrasena"] ?? "";
    if (empty($contrasena)) {
        $errores[] = "La contraseña es obligatoria.";
    } elseif (strlen($contrasena) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    } elseif (!preg_match("/[0-9]/", $contrasena)) {
        $errores[] = "La contraseña debe contener al menos un número.";
    } elseif (!preg_match("/[A-Z]/", $contrasena)) {
        $errores[] = "La contraseña debe contener al menos una mayúscula.";
    }

    // Confirmar contraseña
    $confirmar = $_POST["confirmar_contrasena"] ?? "";
    if ($contrasena !== $confirmar) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Validar edad (opcional, debe ser número entre 13 y 120)
    $edad = $_POST["edad"] ?? "";
    if (!empty($edad)) {
        if (!ctype_digit($edad) || (int)$edad < 13 || (int)$edad > 120) {
            $errores[] = "La edad debe ser un número entre 13 y 120.";
        }
    }

    // Mostrar resultado de la validación
    if (empty($errores)) {
        echo "Registro exitoso:\n";
        echo "  Nombre: " . htmlspecialchars($nombre) . "\n";
        echo "  Email: " . htmlspecialchars($email) . "\n";
        echo "  Edad: " . ($edad ?: "no proporcionada") . "\n";
    } else {
        echo "Errores de validación:\n";
        foreach ($errores as $error) {
            echo "  - $error\n";
        }
    }
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Recibir datos JSON del cuerpo de la solicitud
// ------------------------------------------------------------
// Las APIs modernas envían datos como JSON en el cuerpo de la
// solicitud, no como formularios. Estos NO aparecen en $_POST.

// Leer el cuerpo crudo de la solicitud
$cuerpoJson = file_get_contents("php://input");

echo "Cuerpo crudo recibido: $cuerpoJson\n\n";

if (!empty($cuerpoJson)) {
    // Decodificar el JSON
    $datos = json_decode($cuerpoJson, true);

    // Verificar errores de decodificación
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "error" => "JSON inválido: " . json_last_error_msg()
        ]);
        exit;
    }

    echo "Datos JSON decodificados:\n";
    foreach ($datos as $clave => $valor) {
        if (is_array($valor)) {
            echo "  $clave: " . json_encode($valor, JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "  $clave: $valor\n";
        }
    }

    // Validar campos requeridos del JSON
    $camposRequeridos = ["titulo", "contenido", "autor"];
    $camposFaltantes = [];

    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || $datos[$campo] === "") {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        http_response_code(422);
        echo "\nCampos faltantes: " . implode(", ", $camposFaltantes) . "\n";
    }
} else {
    echo "No se recibió cuerpo JSON.\n";
    echo "Para enviar JSON, usar: Content-Type: application/json\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Manejar checkboxes, selects y radio buttons
// ------------------------------------------------------------
// Diferentes tipos de campos de formulario envían datos en
// formatos distintos a través de $_POST.

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- Checkbox único (acepto_terminos) ---
    // Si está marcado, envía su value. Si no, NO envía nada.
    $aceptaTerminos = isset($_POST["acepto_terminos"]);
    echo "Acepta términos: " . ($aceptaTerminos ? "Sí" : "No") . "\n";

    // --- Checkboxes múltiples (intereses[]) ---
    // <input type="checkbox" name="intereses[]" value="musica">
    // <input type="checkbox" name="intereses[]" value="deportes">
    $intereses = $_POST["intereses"] ?? [];
    if (!is_array($intereses)) {
        $intereses = [$intereses]; // Asegurar que sea arreglo
    }
    echo "Intereses: " . (empty($intereses) ? "ninguno" : implode(", ", $intereses)) . "\n";

    // --- Select (pais) ---
    // <select name="pais"><option value="mx">México</option>...</select>
    $pais = $_POST["pais"] ?? "";
    $paisesValidos = ["mx" => "México", "co" => "Colombia", "ar" => "Argentina", "es" => "España"];
    if (array_key_exists($pais, $paisesValidos)) {
        echo "País: " . $paisesValidos[$pais] . "\n";
    } else {
        echo "País no válido.\n";
    }

    // --- Select múltiple (idiomas[]) ---
    // <select name="idiomas[]" multiple>
    $idiomas = $_POST["idiomas"] ?? [];
    echo "Idiomas: " . (empty($idiomas) ? "ninguno" : implode(", ", $idiomas)) . "\n";

    // --- Radio buttons (genero) ---
    // Solo uno puede estar seleccionado. Envía el value del seleccionado.
    $genero = $_POST["genero"] ?? "";
    $generosValidos = ["masculino", "femenino", "otro", "prefiero_no_decir"];
    if (in_array($genero, $generosValidos, true)) {
        echo "Género: $genero\n";
    } else {
        echo "Género no seleccionado o no válido.\n";
    }
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Protección contra CSRF con token
// ------------------------------------------------------------
// CSRF (Cross-Site Request Forgery) ocurre cuando un sitio
// malicioso envía solicitudes en nombre del usuario.

session_start();

// Función para generar un token CSRF
function generarTokenCsrf(): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION["csrf_token"] = $token;
    $_SESSION["csrf_expiracion"] = time() + 3600; // Válido por 1 hora
    return $token;
}

// Función para validar el token CSRF
function validarTokenCsrf(string $token): bool {
    if (!isset($_SESSION["csrf_token"]) || !isset($_SESSION["csrf_expiracion"])) {
        return false;
    }

    // Verificar que no ha expirado
    if (time() > $_SESSION["csrf_expiracion"]) {
        unset($_SESSION["csrf_token"], $_SESSION["csrf_expiracion"]);
        return false;
    }

    // Comparación segura contra ataques de tiempo
    return hash_equals($_SESSION["csrf_token"], $token);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar el token CSRF enviado con el formulario
    $tokenRecibido = $_POST["csrf_token"] ?? "";

    if (!validarTokenCsrf($tokenRecibido)) {
        http_response_code(403);
        echo "Error: Token CSRF inválido o expirado.\n";
        echo "Posible ataque CSRF detectado.\n";
        exit;
    }

    echo "Token CSRF válido. Procesando formulario...\n";
    // Procesar los datos del formulario de forma segura
    $mensaje = htmlspecialchars($_POST["mensaje"] ?? "", ENT_QUOTES, "UTF-8");
    echo "Mensaje recibido: $mensaje\n";

    // Regenerar token para la siguiente solicitud
    $nuevoToken = generarTokenCsrf();
} else {
    // Generar token para incluir en el formulario
    $token = generarTokenCsrf();
    echo "Formulario HTML con protección CSRF:\n";
    echo "<form method=\"POST\">\n";
    echo "  <input type=\"hidden\" name=\"csrf_token\" value=\"$token\">\n";
    echo "  <textarea name=\"mensaje\"></textarea>\n";
    echo "  <button type=\"submit\">Enviar</button>\n";
    echo "</form>\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Diferencias entre $_POST, $_GET y php://input
// ------------------------------------------------------------
// Comparación práctica de los tres métodos de recepción de datos.

echo "=== Comparación de métodos de recepción de datos ===\n\n";

// 1. $_GET - Datos de la URL (query string)
echo "1. \$_GET (parámetros de URL):\n";
echo "   Cantidad: " . count($_GET) . " parámetros\n";
echo "   Método HTTP: cualquiera (los parámetros van en la URL)\n";
echo "   Visible en URL: Sí\n";
echo "   Tamaño máximo: ~2048 caracteres (límite del navegador)\n";
echo "   Uso: búsquedas, filtros, paginación, enlaces compartibles\n\n";

// 2. $_POST - Datos del cuerpo (formularios)
echo "2. \$_POST (cuerpo de formularios):\n";
echo "   Cantidad: " . count($_POST) . " campos\n";
echo "   Método HTTP: POST (Content-Type: application/x-www-form-urlencoded o multipart/form-data)\n";
echo "   Visible en URL: No\n";
echo "   Tamaño máximo: configurado en php.ini (post_max_size)\n";
echo "   Uso: formularios de registro, login, envío de archivos\n\n";

// 3. php://input - Cuerpo crudo de la solicitud
$cuerpo = file_get_contents("php://input");
echo "3. php://input (cuerpo crudo):\n";
echo "   Tamaño del cuerpo: " . strlen($cuerpo) . " bytes\n";
echo "   Content-Type: " . ($_SERVER["CONTENT_TYPE"] ?? "no especificado") . "\n";
echo "   Uso: recibir JSON, XML, o cualquier formato personalizado\n\n";

// Resumen de cuándo usar cada uno
echo "=== ¿Cuándo usar cada uno? ===\n";
echo "  \$_GET:       Leer parámetros de URL (filtros, búsqueda, paginación)\n";
echo "  \$_POST:      Formularios HTML estándar\n";
echo "  php://input: APIs REST con JSON, webhooks, datos binarios\n";
echo "  \$_REQUEST:   Combina \$_GET, \$_POST y \$_COOKIE (NO recomendado)\n";
?>
