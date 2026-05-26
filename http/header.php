<?php
// ============================================================
// header() - Enviar encabezados HTTP sin procesar
// ============================================================
// La función header() envía un encabezado HTTP crudo al cliente.
// Debe llamarse ANTES de cualquier salida (echo, HTML, espacios).
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Redirección a otra página
// ------------------------------------------------------------
// header() con "Location" redirige al navegador a otra URL.
// Se recomienda usar exit después para detener la ejecución.

$necesitaLogin = true;

if ($necesitaLogin) {
    // Redirigir al usuario a la página de inicio de sesión
    header("Location: /login.php");
    exit; // Importante: detener ejecución después de redirigir
}

// Redirección con código de estado 301 (movido permanentemente)
// Útil para SEO cuando una página cambió de dirección
header("Location: https://nuevo-sitio.com/pagina", true, 301);
exit;

// Redirección con código de estado 302 (temporal, por defecto)
header("Location: /pagina-temporal.php");
exit;

echo "Este texto nunca se mostrará debido al exit anterior.";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Establecer tipo de contenido JSON
// ------------------------------------------------------------
// Content-Type indica al navegador qué tipo de datos se envían.
// Esencial para APIs que devuelven JSON.

// Indicar que la respuesta es JSON con codificación UTF-8
header("Content-Type: application/json; charset=UTF-8");

// Preparar datos de respuesta
$respuesta = [
    "estado"  => "éxito",
    "mensaje" => "Datos obtenidos correctamente",
    "datos"   => [
        ["id" => 1, "nombre" => "María García"],
        ["id" => 2, "nombre" => "Carlos López"],
    ]
];

// Convertir el arreglo a JSON y enviarlo
echo json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Establecer tipo de contenido HTML con charset
// ------------------------------------------------------------
// Para páginas HTML normales, se puede especificar la codificación

header("Content-Type: text/html; charset=UTF-8");

echo "<h1>Página con codificación UTF-8</h1>";
echo "<p>Los caracteres especiales como ñ, á, é se muestran correctamente.</p>";

// Otros tipos de contenido comunes:
// header("Content-Type: text/plain");          // Texto plano
// header("Content-Type: text/xml");            // XML
// header("Content-Type: text/css");            // Hoja de estilos
// header("Content-Type: application/pdf");     // Archivo PDF
// header("Content-Type: image/png");           // Imagen PNG
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Control de caché del navegador
// ------------------------------------------------------------
// Los encabezados de caché controlan cómo el navegador almacena
// las respuestas. Importante para rendimiento y datos sensibles.

// --- Deshabilitar completamente el caché ---
// Útil para páginas con datos sensibles o que cambian constantemente
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false); // false = no reemplazar
header("Pragma: no-cache");    // Compatibilidad con HTTP/1.0
header("Expires: 0");          // La página ya expiró

echo "Esta página nunca se almacena en caché del navegador.<br>";

// --- Permitir caché por tiempo limitado ---
// max-age en segundos: 3600 = 1 hora
// header("Cache-Control: public, max-age=3600");
// header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");

// --- Caché privado (solo navegador, no proxies) ---
// header("Cache-Control: private, max-age=600"); // 10 minutos
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Encabezados CORS (Cross-Origin Resource Sharing)
// ------------------------------------------------------------
// CORS permite que recursos de un dominio sean solicitados
// desde otro dominio diferente. Esencial para APIs.

// Permitir solicitudes desde cualquier origen (desarrollo)
header("Access-Control-Allow-Origin: *");

// En producción, especificar el dominio permitido
// header("Access-Control-Allow-Origin: https://mi-frontend.com");

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Encabezados personalizados permitidos en la solicitud
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Permitir envío de cookies/credenciales en solicitudes CORS
// Nota: no funciona con Allow-Origin: *, se necesita dominio específico
// header("Access-Control-Allow-Credentials: true");

// Tiempo en segundos que el navegador puede cachear la respuesta preflight
header("Access-Control-Max-Age: 86400"); // 24 horas

// Manejar solicitud preflight OPTIONS
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    // Responder con 200 OK y sin cuerpo
    http_response_code(200);
    exit;
}

// Respuesta normal de la API
header("Content-Type: application/json; charset=UTF-8");
echo json_encode(["mensaje" => "CORS configurado correctamente"]);
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Forzar descarga de archivo
// ------------------------------------------------------------
// Se pueden usar encabezados para forzar la descarga de un archivo
// en lugar de mostrarlo en el navegador.

$archivo = "/ruta/al/archivo/reporte.pdf";

if (file_exists($archivo)) {
    // Indicar que es contenido descargable
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");

    // El nombre que verá el usuario al descargar
    header("Content-Disposition: attachment; filename=\"reporte_mensual.pdf\"");

    // Evitar caché para descargas
    header("Cache-Control: must-revalidate");
    header("Pragma: public");

    // Tamaño del archivo para la barra de progreso
    header("Content-Length: " . filesize($archivo));

    // Leer y enviar el archivo
    readfile($archivo);
    exit;
} else {
    // Archivo no encontrado
    http_response_code(404);
    echo "El archivo solicitado no existe.";
}
?>
