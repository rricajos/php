<?php
// ============================================================
// $_SERVER - Información del servidor y la solicitud HTTP
// ============================================================
// $_SERVER es un arreglo superglobal que contiene información
// sobre el servidor web, la solicitud HTTP actual, encabezados,
// rutas de archivos y más. Creado por el servidor web.
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: REQUEST_METHOD - Método HTTP de la solicitud
// ------------------------------------------------------------
// Indica si la solicitud es GET, POST, PUT, DELETE, etc.
// Fundamental para construir APIs REST.

$metodo = $_SERVER["REQUEST_METHOD"] ?? "desconocido";
echo "Método HTTP actual: $metodo\n\n";

// Manejar diferentes métodos HTTP (patrón de enrutador simple)
switch ($metodo) {
    case "GET":
        echo "Solicitud GET: Obtener/leer un recurso.\n";
        // Lógica para listar o mostrar datos
        break;

    case "POST":
        echo "Solicitud POST: Crear un nuevo recurso.\n";
        // Lógica para crear datos
        break;

    case "PUT":
    case "PATCH":
        echo "Solicitud $metodo: Actualizar un recurso existente.\n";
        // Lógica para actualizar datos
        break;

    case "DELETE":
        echo "Solicitud DELETE: Eliminar un recurso.\n";
        // Lógica para eliminar datos
        break;

    case "OPTIONS":
        echo "Solicitud OPTIONS: Verificar métodos permitidos (preflight CORS).\n";
        header("Allow: GET, POST, PUT, DELETE, OPTIONS");
        break;

    default:
        http_response_code(405);
        echo "Método $metodo no permitido.\n";
}

// Verificación rápida del método (útil para formularios)
$esPost = ($_SERVER["REQUEST_METHOD"] === "POST");
echo "\n¿Es una solicitud POST? " . ($esPost ? "Sí" : "No") . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: HTTP_HOST y nombres del servidor
// ------------------------------------------------------------
// Información sobre el dominio y servidor que atiende la solicitud.

// HTTP_HOST: dominio de la solicitud (enviado por el cliente)
$host = $_SERVER["HTTP_HOST"] ?? "localhost";
echo "HTTP_HOST: $host\n";

// SERVER_NAME: nombre configurado en el servidor web
$servidorNombre = $_SERVER["SERVER_NAME"] ?? "localhost";
echo "SERVER_NAME: $servidorNombre\n";

// SERVER_PORT: puerto en el que escucha el servidor
$puerto = $_SERVER["SERVER_PORT"] ?? 80;
echo "SERVER_PORT: $puerto\n";

// Determinar si la conexión es HTTPS
$esHttps = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
    || ($_SERVER["SERVER_PORT"] ?? 80) == 443;
$protocolo = $esHttps ? "https" : "http";
echo "Protocolo: $protocolo\n";

// Construir la URL base completa del sitio
$urlBase = "$protocolo://$host";
if (($esHttps && $puerto != 443) || (!$esHttps && $puerto != 80)) {
    $urlBase .= ":$puerto";
}
echo "URL base: $urlBase\n";

// SERVER_SOFTWARE: información del servidor web
$software = $_SERVER["SERVER_SOFTWARE"] ?? "desconocido";
echo "Software del servidor: $software\n";

// SERVER_PROTOCOL: versión del protocolo HTTP
$protocoloHttp = $_SERVER["SERVER_PROTOCOL"] ?? "HTTP/1.1";
echo "Protocolo HTTP: $protocoloHttp\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: REMOTE_ADDR - Dirección IP del cliente
// ------------------------------------------------------------
// Información sobre la conexión del cliente que realiza la solicitud.

// IP directa del cliente (o del último proxy)
$ipCliente = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
echo "REMOTE_ADDR (IP directa): $ipCliente\n";

// Si hay un proxy o balanceador de carga, la IP real puede estar en
// encabezados adicionales (CUIDADO: estos encabezados son manipulables)
$ipReal = $_SERVER["HTTP_X_FORWARDED_FOR"]
    ?? $_SERVER["HTTP_X_REAL_IP"]
    ?? $_SERVER["HTTP_CLIENT_IP"]
    ?? $_SERVER["REMOTE_ADDR"]
    ?? "0.0.0.0";

// Si X-Forwarded-For tiene múltiples IPs, tomar la primera (cliente original)
if (str_contains($ipReal, ",")) {
    $ipReal = trim(explode(",", $ipReal)[0]);
}

echo "IP real estimada: $ipReal\n";

// Puerto del cliente
$puertoCliente = $_SERVER["REMOTE_PORT"] ?? "desconocido";
echo "Puerto del cliente: $puertoCliente\n";

// Validar que la IP tiene formato correcto
if (filter_var($ipCliente, FILTER_VALIDATE_IP)) {
    echo "La IP '$ipCliente' tiene un formato válido.\n";

    // Determinar si es IPv4 o IPv6
    if (filter_var($ipCliente, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        echo "Tipo: IPv4\n";
    } elseif (filter_var($ipCliente, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        echo "Tipo: IPv6\n";
    }

    // Verificar si es una IP privada (red local)
    if (!filter_var($ipCliente, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
        echo "Es una IP de red privada/local.\n";
    }
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: REQUEST_URI y rutas de la solicitud
// ------------------------------------------------------------
// Variables que describen qué URL solicitó el cliente.

// REQUEST_URI: URI completa solicitada (incluye query string)
$uriCompleta = $_SERVER["REQUEST_URI"] ?? "/";
echo "REQUEST_URI: $uriCompleta\n";

// Separar la ruta del query string
$partesUri = parse_url($uriCompleta);
$ruta = $partesUri["path"] ?? "/";
$queryString = $partesUri["query"] ?? "";

echo "Ruta: $ruta\n";
echo "Query string: $queryString\n";

// QUERY_STRING: la cadena de consulta (sin el ?)
$query = $_SERVER["QUERY_STRING"] ?? "";
echo "QUERY_STRING: $query\n";

// PHP_SELF: ruta del script actual (relativa a la raíz del sitio)
$phpSelf = $_SERVER["PHP_SELF"] ?? "";
echo "PHP_SELF: $phpSelf\n";

// SCRIPT_NAME: ruta del script actual
$scriptName = $_SERVER["SCRIPT_NAME"] ?? "";
echo "SCRIPT_NAME: $scriptName\n";

// PATH_INFO: información adicional de ruta después del script
// URL: /api.php/usuarios/42 -> PATH_INFO = /usuarios/42
$pathInfo = $_SERVER["PATH_INFO"] ?? "(no disponible)";
echo "PATH_INFO: $pathInfo\n";

// Ejemplo práctico: enrutador simple basado en la URI
echo "\n--- Enrutador simple ---\n";

$rutaLimpia = strtok($uriCompleta, "?"); // Quitar query string
$segmentos = array_filter(explode("/", $rutaLimpia)); // Dividir en segmentos
$segmentos = array_values($segmentos); // Reindexar

echo "Segmentos de la ruta:\n";
foreach ($segmentos as $i => $segmento) {
    echo "  [$i] $segmento\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: SCRIPT_FILENAME y rutas del sistema de archivos
// ------------------------------------------------------------
// Variables que indican la ubicación física de los archivos en el servidor.

// SCRIPT_FILENAME: ruta absoluta del script que se está ejecutando
$archivoScript = $_SERVER["SCRIPT_FILENAME"] ?? __FILE__;
echo "SCRIPT_FILENAME: $archivoScript\n";

// DOCUMENT_ROOT: directorio raíz del sitio web
$raizDocumentos = $_SERVER["DOCUMENT_ROOT"] ?? "/var/www/html";
echo "DOCUMENT_ROOT: $raizDocumentos\n";

// __FILE__: constante mágica con la ruta del archivo actual
echo "__FILE__: " . __FILE__ . "\n";

// __DIR__: constante mágica con el directorio del archivo actual
echo "__DIR__: " . __DIR__ . "\n";

// Ruta relativa del script respecto a la raíz del documento
$rutaRelativa = str_replace($raizDocumentos, "", $archivoScript);
echo "Ruta relativa: $rutaRelativa\n";

// Verificar si un archivo existe dentro del directorio del proyecto
$archivoConfig = $raizDocumentos . "/config/database.php";
echo "\n¿Existe archivo de configuración? "
    . (file_exists($archivoConfig) ? "Sí" : "No") . "\n";

// Información del servidor sobre PHP
echo "\nInformación de PHP:\n";
echo "  Versión: " . PHP_VERSION . "\n";
echo "  Sistema operativo: " . PHP_OS . "\n";
echo "  SAPI (interfaz): " . php_sapi_name() . "\n";
echo "  Gateway: " . ($_SERVER["GATEWAY_INTERFACE"] ?? "N/A") . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Encabezados HTTP y User-Agent
// ------------------------------------------------------------
// $_SERVER contiene los encabezados HTTP con prefijo "HTTP_".
// El guión (-) se convierte en guión bajo (_) y se ponen en mayúsculas.

echo "=== Encabezados HTTP del cliente ===\n\n";

// User-Agent: navegador y sistema operativo del cliente
$agente = $_SERVER["HTTP_USER_AGENT"] ?? "desconocido";
echo "User-Agent: $agente\n";

// Detectar tipo de dispositivo (básico)
$esMobile = preg_match("/(Mobile|Android|iPhone|iPad)/i", $agente);
echo "Dispositivo: " . ($esMobile ? "Móvil" : "Escritorio") . "\n\n";

// Accept: tipos de contenido que el cliente acepta
$accept = $_SERVER["HTTP_ACCEPT"] ?? "*/*";
echo "Accept: $accept\n";

// Accept-Language: idiomas preferidos del cliente
$idiomas = $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? "es";
echo "Accept-Language: $idiomas\n";

// Extraer el idioma principal
$idiomaPrincipal = substr($idiomas, 0, 2);
echo "Idioma principal: $idiomaPrincipal\n\n";

// Referer: página desde la que el usuario llegó
$referencia = $_SERVER["HTTP_REFERER"] ?? "(acceso directo)";
echo "Referer: $referencia\n";

// Authorization: encabezado de autenticación
$autorizacion = $_SERVER["HTTP_AUTHORIZATION"] ?? "(sin autenticación)";
echo "Authorization: $autorizacion\n\n";

// Content-Type: tipo de contenido del cuerpo de la solicitud
$tipoContenido = $_SERVER["CONTENT_TYPE"] ?? "(sin cuerpo)";
echo "Content-Type: $tipoContenido\n";

// Content-Length: tamaño del cuerpo en bytes
$tamano = $_SERVER["CONTENT_LENGTH"] ?? "0";
echo "Content-Length: $tamano bytes\n\n";

// Listar TODOS los encabezados HTTP disponibles
echo "--- Todos los encabezados HTTP_ ---\n";
foreach ($_SERVER as $clave => $valor) {
    if (str_starts_with($clave, "HTTP_")) {
        $nombreEncabezado = str_replace("HTTP_", "", $clave);
        $nombreEncabezado = str_replace("_", "-", $nombreEncabezado);
        echo "  $nombreEncabezado: $valor\n";
    }
}
?>
