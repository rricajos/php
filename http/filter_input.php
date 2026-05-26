<?php
// ============================================================
// filter_input() - Filtrar y validar datos de entrada externos
// ============================================================
// filter_input() obtiene una variable externa específica y la
// filtra/valida en un solo paso. Es más seguro que acceder
// directamente a $_GET, $_POST, etc.
// Sintaxis: filter_input(tipo, nombre, filtro, opciones)
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: INPUT_GET - Filtrar parámetros de URL
// ------------------------------------------------------------
// filter_input(INPUT_GET, ...) obtiene y filtra datos de $_GET.
// URL ejemplo: pagina.php?id=42&nombre=María&activo=true

// Obtener y validar un entero desde GET
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id === false) {
    echo "Error: 'id' no es un número entero válido.\n";
} elseif ($id === null) {
    echo "El parámetro 'id' no fue proporcionado.\n";
} else {
    echo "ID validado: $id (tipo: " . gettype($id) . ")\n";
}

// Validar entero con rango (opciones min/max)
$pagina = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT, [
    "options" => [
        "min_range" => 1,
        "max_range" => 1000,
        "default"   => 1  // Valor por defecto si la validación falla
    ]
]);
echo "Página: $pagina\n";

// Obtener una cadena sanitizada desde GET
$nombre = filter_input(INPUT_GET, "nombre", FILTER_SANITIZE_SPECIAL_CHARS);
echo "Nombre sanitizado: " . ($nombre ?? "(no proporcionado)") . "\n";

// Validar un booleano desde GET (acepta "true", "1", "on", "yes")
$activo = filter_input(INPUT_GET, "activo", FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
echo "Activo: " . ($activo === null ? "no proporcionado" : ($activo ? "sí" : "no")) . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: INPUT_POST - Filtrar datos de formularios
// ------------------------------------------------------------
// filter_input(INPUT_POST, ...) obtiene y filtra datos de $_POST.
// Ideal para procesar formularios de forma segura.

// Validar email desde un formulario POST
$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);

if ($email === false) {
    echo "Error: El email proporcionado no es válido.\n";
} elseif ($email === null) {
    echo "El campo 'email' no fue enviado.\n";
} else {
    echo "Email válido: $email\n";
}

// Sanitizar texto de un campo de mensaje (eliminar etiquetas HTML)
$mensaje = filter_input(INPUT_POST, "mensaje", FILTER_SANITIZE_SPECIAL_CHARS);
echo "Mensaje sanitizado: " . ($mensaje ?? "(vacío)") . "\n";

// Validar un número de teléfono (solo dígitos, +, -, espacios)
$telefono = filter_input(INPUT_POST, "telefono", FILTER_SANITIZE_NUMBER_INT);
echo "Teléfono (solo números): " . ($telefono ?? "(no proporcionado)") . "\n";

// Validar un monto monetario como float
$monto = filter_input(INPUT_POST, "monto", FILTER_VALIDATE_FLOAT, [
    "options" => ["decimal" => "."],
    "flags"   => FILTER_FLAG_ALLOW_THOUSAND
]);

if ($monto !== false && $monto !== null) {
    echo "Monto válido: $" . number_format($monto, 2) . "\n";
} else {
    echo "Monto: no válido o no proporcionado.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Validar emails y URLs
// ------------------------------------------------------------
// FILTER_VALIDATE_EMAIL y FILTER_VALIDATE_URL verifican que los
// datos tengan el formato correcto.

// --- Validar email ---
$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);

if ($email !== false && $email !== null) {
    // Sanitizar adicionalmente (convertir a minúsculas)
    $email = strtolower($email);
    echo "Email válido y normalizado: $email\n";
} else {
    echo "Email inválido o no proporcionado.\n";
}

// --- Validar URL ---
$sitioWeb = filter_input(INPUT_POST, "sitio_web", FILTER_VALIDATE_URL, [
    "flags" => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED
]);

if ($sitioWeb !== false && $sitioWeb !== null) {
    echo "URL válida: $sitioWeb\n";
} else {
    echo "URL inválida o no proporcionada.\n";
    echo "La URL debe incluir el esquema (http:// o https://).\n";
}

// --- Validar dirección IP ---
$ip = filter_input(INPUT_GET, "ip", FILTER_VALIDATE_IP);
echo "IP: " . ($ip !== false && $ip !== null ? "$ip (válida)" : "no válida") . "\n";

// Validar solo IPv4
$ipv4 = filter_input(INPUT_GET, "ip", FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
echo "IPv4: " . ($ipv4 !== false && $ipv4 !== null ? "sí" : "no") . "\n";

// Validar que NO sea IP privada (útil para verificar IPs públicas)
$ipPublica = filter_input(INPUT_GET, "ip", FILTER_VALIDATE_IP,
    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
);
echo "IP pública: " . ($ipPublica !== false && $ipPublica !== null ? "sí" : "no") . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: FILTER_SANITIZE_* - Limpieza de datos
// ------------------------------------------------------------
// Los filtros SANITIZE limpian los datos eliminando caracteres
// no deseados, en lugar de solo validar.

echo "=== Filtros de sanitización ===\n\n";

// FILTER_SANITIZE_SPECIAL_CHARS - Escapa caracteres HTML especiales
// Convierte <, >, &, " y ' a entidades HTML
$textoHtml = filter_input(INPUT_POST, "comentario", FILTER_SANITIZE_SPECIAL_CHARS);
echo "SPECIAL_CHARS: " . ($textoHtml ?? "(vacío)") . "\n";

// FILTER_SANITIZE_EMAIL - Elimina caracteres no válidos para email
$emailLimpio = filter_input(INPUT_POST, "email_sucio", FILTER_SANITIZE_EMAIL);
echo "SANITIZE_EMAIL: " . ($emailLimpio ?? "(vacío)") . "\n";
// Ejemplo: "user @exam ple.com!" -> "user@example.com"

// FILTER_SANITIZE_URL - Elimina caracteres no válidos para URLs
$urlLimpia = filter_input(INPUT_POST, "url", FILTER_SANITIZE_URL);
echo "SANITIZE_URL: " . ($urlLimpia ?? "(vacío)") . "\n";

// FILTER_SANITIZE_NUMBER_INT - Elimina todo excepto dígitos, + y -
$numeroLimpio = filter_input(INPUT_POST, "cantidad", FILTER_SANITIZE_NUMBER_INT);
echo "SANITIZE_NUMBER_INT: " . ($numeroLimpio ?? "(vacío)") . "\n";
// Ejemplo: "abc123def456" -> "123456"

// FILTER_SANITIZE_NUMBER_FLOAT - Elimina todo excepto dígitos, +, - y opcionalmente . y ,
$precioLimpio = filter_input(INPUT_POST, "precio", FILTER_SANITIZE_NUMBER_FLOAT, [
    "flags" => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
]);
echo "SANITIZE_NUMBER_FLOAT: " . ($precioLimpio ?? "(vacío)") . "\n";
// Ejemplo: "$1,234.56 USD" -> "1,234.56"

// FILTER_SANITIZE_ADD_SLASHES - Añade barras invertidas (como addslashes)
$textoEscapado = filter_input(INPUT_POST, "texto", FILTER_SANITIZE_ADD_SLASHES);
echo "SANITIZE_ADD_SLASHES: " . ($textoEscapado ?? "(vacío)") . "\n";

// FILTER_SANITIZE_ENCODED - Codifica URL (como urlencode)
$urlCodificada = filter_input(INPUT_GET, "busqueda", FILTER_SANITIZE_ENCODED);
echo "SANITIZE_ENCODED: " . ($urlCodificada ?? "(vacío)") . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: FILTER_VALIDATE_INT con opciones avanzadas
// ------------------------------------------------------------
// filter_input permite opciones detalladas para validación de enteros.

echo "=== Validación avanzada de enteros ===\n\n";

// Validar edad con rango
$edad = filter_input(INPUT_POST, "edad", FILTER_VALIDATE_INT, [
    "options" => [
        "min_range" => 0,
        "max_range" => 150,
    ]
]);

echo "Edad: ";
if ($edad === false) {
    echo "valor inválido (no es entero o fuera de rango 0-150)\n";
} elseif ($edad === null) {
    echo "no proporcionada\n";
} else {
    echo "$edad años (válido)\n";
}

// Validar con valor por defecto
$cantidad = filter_input(INPUT_POST, "cantidad", FILTER_VALIDATE_INT, [
    "options" => [
        "default"   => 1,    // Si falla, devolver 1
        "min_range" => 1,
        "max_range" => 100,
    ]
]);
echo "Cantidad: $cantidad\n";

// Validar número octal (base 8)
$permisos = filter_input(INPUT_POST, "permisos", FILTER_VALIDATE_INT, [
    "flags" => FILTER_FLAG_ALLOW_OCTAL
]);
echo "Permisos: " . ($permisos !== false && $permisos !== null ? decoct($permisos) : "inválido") . "\n";

// Validar número hexadecimal (base 16)
$color = filter_input(INPUT_POST, "color_hex", FILTER_VALIDATE_INT, [
    "flags" => FILTER_FLAG_ALLOW_HEX
]);
echo "Color hex: " . ($color !== false && $color !== null ? "#" . dechex($color) : "inválido") . "\n";

// Validar múltiples valores de una sola vez con filter_input_array
$filtros = [
    "id"     => FILTER_VALIDATE_INT,
    "email"  => FILTER_VALIDATE_EMAIL,
    "activo" => FILTER_VALIDATE_BOOLEAN,
    "nombre" => FILTER_SANITIZE_SPECIAL_CHARS,
];

$datosLimpios = filter_input_array(INPUT_GET, $filtros);
echo "\nResultado de filter_input_array:\n";
if ($datosLimpios) {
    foreach ($datosLimpios as $campo => $valor) {
        echo "  $campo: " . var_export($valor, true) . "\n";
    }
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Función completa de validación de formulario
// ------------------------------------------------------------
// Combinando filter_input con validación personalizada para
// procesar un formulario de contacto de forma segura.

function validarFormularioContacto(): array {
    $errores = [];
    $datos = [];

    // Validar nombre (sanitizar y verificar longitud)
    $nombre = filter_input(INPUT_POST, "nombre", FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($nombre)) {
        $errores["nombre"] = "El nombre es obligatorio.";
    } elseif (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100) {
        $errores["nombre"] = "El nombre debe tener entre 2 y 100 caracteres.";
    } else {
        $datos["nombre"] = $nombre;
    }

    // Validar email
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    if ($email === false || $email === null) {
        $errores["email"] = "Proporcione un email válido.";
    } else {
        $datos["email"] = strtolower($email);
    }

    // Validar teléfono (opcional)
    $telefono = filter_input(INPUT_POST, "telefono", FILTER_SANITIZE_NUMBER_INT);
    if (!empty($telefono)) {
        // Verificar que tiene entre 7 y 15 dígitos
        $soloDigitos = preg_replace("/[^0-9]/", "", $telefono);
        if (strlen($soloDigitos) < 7 || strlen($soloDigitos) > 15) {
            $errores["telefono"] = "El teléfono debe tener entre 7 y 15 dígitos.";
        } else {
            $datos["telefono"] = $telefono;
        }
    }

    // Validar asunto (lista predefinida)
    $asuntoValido = filter_input(INPUT_POST, "asunto", FILTER_SANITIZE_SPECIAL_CHARS);
    $asuntosPermitidos = ["consulta", "soporte", "ventas", "otro"];
    if (!in_array($asuntoValido, $asuntosPermitidos, true)) {
        $errores["asunto"] = "Seleccione un asunto válido.";
    } else {
        $datos["asunto"] = $asuntoValido;
    }

    // Validar mensaje (sanitizar y verificar longitud)
    $mensaje = filter_input(INPUT_POST, "mensaje", FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($mensaje)) {
        $errores["mensaje"] = "El mensaje es obligatorio.";
    } elseif (mb_strlen($mensaje) < 10) {
        $errores["mensaje"] = "El mensaje debe tener al menos 10 caracteres.";
    } elseif (mb_strlen($mensaje) > 5000) {
        $errores["mensaje"] = "El mensaje no puede exceder 5000 caracteres.";
    } else {
        $datos["mensaje"] = $mensaje;
    }

    return [
        "valido"  => empty($errores),
        "datos"   => $datos,
        "errores" => $errores
    ];
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $resultado = validarFormularioContacto();

    header("Content-Type: application/json; charset=UTF-8");

    if ($resultado["valido"]) {
        http_response_code(200);
        echo json_encode([
            "estado"  => "éxito",
            "mensaje" => "Formulario enviado correctamente",
            "datos"   => $resultado["datos"]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(422);
        echo json_encode([
            "estado"  => "error",
            "mensaje" => "Errores de validación",
            "errores" => $resultado["errores"]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} else {
    echo "Envíe un formulario POST para validar.\n";
    echo "Campos: nombre, email, telefono (opcional), asunto, mensaje\n";
}
?>
