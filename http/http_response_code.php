<?php
// ============================================================
// http_response_code() - Obtener o establecer códigos de estado HTTP
// ============================================================
// Esta función permite obtener el código de respuesta actual o
// establecer uno nuevo. Los códigos indican el resultado de la
// solicitud HTTP (éxito, error, redirección, etc.).
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Obtener el código de respuesta actual
// ------------------------------------------------------------
// Sin argumentos, http_response_code() devuelve el código actual.

// Obtener el código de estado actual (por defecto es 200)
$codigoActual = http_response_code();
echo "Código de respuesta actual: $codigoActual\n"; // 200

// Verificar si la respuesta es exitosa
if ($codigoActual === 200) {
    echo "La solicitud se está procesando con éxito.\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Respuesta exitosa 200 OK
// ------------------------------------------------------------
// El código 200 indica que la solicitud fue procesada con éxito.
// Es el código por defecto en PHP.

// Establecer explícitamente el código 200
http_response_code(200);

header("Content-Type: application/json; charset=UTF-8");

$respuesta = [
    "codigo"  => 200,
    "estado"  => "éxito",
    "mensaje" => "Recurso obtenido correctamente",
    "datos"   => [
        "id"     => 42,
        "nombre" => "Producto de ejemplo",
        "precio" => 29.99
    ]
];

echo json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Error 404 - Recurso no encontrado
// ------------------------------------------------------------
// El código 404 indica que el recurso solicitado no existe.
// Muy usado para páginas o recursos inexistentes.

$idProducto = $_GET["id"] ?? null;

// Simular búsqueda en base de datos
$productosDisponibles = [1, 2, 3, 5, 8, 13];

if ($idProducto === null || !in_array((int)$idProducto, $productosDisponibles)) {
    // Establecer código 404 y enviar respuesta de error
    http_response_code(404);

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "codigo"  => 404,
        "estado"  => "error",
        "mensaje" => "Producto no encontrado",
        "detalle" => "No existe un producto con el ID: " . ($idProducto ?? "no proporcionado")
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Si llegamos aquí, el producto existe
http_response_code(200);
echo json_encode(["mensaje" => "Producto encontrado: #$idProducto"]);
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Redirección 301 - Movido permanentemente
// ------------------------------------------------------------
// El código 301 indica que el recurso se movió de forma permanente.
// Los motores de búsqueda actualizan sus índices con la nueva URL.

$rutasAntiguas = [
    "/blog-viejo"      => "/blog",
    "/contactenos"     => "/contacto",
    "/productos-lista" => "/catalogo",
];

$rutaActual = $_SERVER["REQUEST_URI"] ?? "";

if (array_key_exists($rutaActual, $rutasAntiguas)) {
    $nuevaRuta = $rutasAntiguas[$rutaActual];

    // Establecer código 301 y redirigir
    http_response_code(301);
    header("Location: $nuevaRuta");
    exit;
}

// Código 302 - Redirección temporal (no permanente)
// Útil para mantenimiento o pruebas A/B
// http_response_code(302);
// header("Location: /pagina-temporal.php");

// Código 307 - Redirección temporal preservando el método HTTP
// Similar al 302 pero garantiza que POST siga siendo POST
// http_response_code(307);
// header("Location: /nuevo-endpoint");
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Error 500 - Error interno del servidor
// ------------------------------------------------------------
// El código 500 indica un error inesperado en el servidor.
// Se usa cuando algo falla durante el procesamiento.

function procesarPago(float $monto): array {
    // Simular un proceso que puede fallar
    if ($monto <= 0) {
        throw new InvalidArgumentException("El monto debe ser positivo");
    }

    if ($monto > 10000) {
        throw new RuntimeException("Error de conexión con la pasarela de pago");
    }

    return ["transaccion_id" => uniqid("pago_"), "monto" => $monto];
}

header("Content-Type: application/json; charset=UTF-8");

try {
    $monto = (float)($_POST["monto"] ?? 15000);
    $resultado = procesarPago($monto);

    http_response_code(200);
    echo json_encode([
        "codigo"  => 200,
        "estado"  => "éxito",
        "mensaje" => "Pago procesado correctamente",
        "datos"   => $resultado
    ], JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    // Error del cliente (datos inválidos) -> 400
    http_response_code(400);
    echo json_encode([
        "codigo"  => 400,
        "estado"  => "error",
        "mensaje" => "Solicitud inválida: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} catch (RuntimeException $e) {
    // Error del servidor (fallo interno) -> 500
    http_response_code(500);
    echo json_encode([
        "codigo"  => 500,
        "estado"  => "error",
        "mensaje" => "Error interno del servidor",
        "detalle" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

    // Registrar el error en el log del servidor
    error_log("Error de pago: " . $e->getMessage());
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Tabla de referencia de códigos HTTP comunes
// ------------------------------------------------------------
// Resumen de los códigos de estado más utilizados en desarrollo web.

$codigosHTTP = [
    // Éxito (2xx)
    200 => "OK - Solicitud exitosa",
    201 => "Created - Recurso creado exitosamente",
    204 => "No Content - Éxito sin contenido en la respuesta",

    // Redirección (3xx)
    301 => "Moved Permanently - Recurso movido de forma permanente",
    302 => "Found - Redirección temporal",
    304 => "Not Modified - El recurso no ha cambiado (usar caché)",

    // Errores del cliente (4xx)
    400 => "Bad Request - Solicitud mal formada",
    401 => "Unauthorized - Se requiere autenticación",
    403 => "Forbidden - Acceso prohibido",
    404 => "Not Found - Recurso no encontrado",
    405 => "Method Not Allowed - Método HTTP no permitido",
    409 => "Conflict - Conflicto con el estado actual del recurso",
    422 => "Unprocessable Entity - Datos válidos pero no procesables",
    429 => "Too Many Requests - Demasiadas solicitudes (rate limit)",

    // Errores del servidor (5xx)
    500 => "Internal Server Error - Error interno del servidor",
    502 => "Bad Gateway - Respuesta inválida del servidor upstream",
    503 => "Service Unavailable - Servicio no disponible",
];

echo "=== Códigos de Estado HTTP Comunes ===\n\n";

foreach ($codigosHTTP as $codigo => $descripcion) {
    echo "  $codigo: $descripcion\n";
}

// Ejemplo práctico: función auxiliar para enviar respuestas
function enviarRespuesta(int $codigo, array $datos): void {
    http_response_code($codigo);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Uso de la función auxiliar
// enviarRespuesta(201, ["mensaje" => "Usuario creado", "id" => 55]);
// enviarRespuesta(404, ["error" => "No se encontró el recurso"]);
?>
