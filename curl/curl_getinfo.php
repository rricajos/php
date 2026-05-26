<?php
// =============================================================================
// curl_getinfo() - Obtener información sobre una transferencia cURL
// Código HTTP, tiempo total, tipo de contenido, conteo de redirecciones
// =============================================================================

// =============================================================================
// Ejemplo 1: Obtener el código de respuesta HTTP
// Verificar si una petición fue exitosa (200) o tuvo errores (4xx, 5xx)
// =============================================================================

echo "=== Ejemplo 1: Código de respuesta HTTP ===\n";

$urls = [
    "https://httpbin.org/status/200" => "Éxito",
    "https://httpbin.org/status/301" => "Redirección permanente",
    "https://httpbin.org/status/404" => "No encontrado",
    "https://httpbin.org/status/500" => "Error del servidor",
];

foreach ($urls as $url => $descripcion) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // No seguir redirecciones

    curl_exec($ch);

    // CURLINFO_HTTP_CODE devuelve el código HTTP de la respuesta
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "  $codigoHttp - $descripcion ($url)\n";
}
echo "\n";

// =============================================================================
// Ejemplo 2: Medir el tiempo total de la petición
// Analizar el rendimiento de las conexiones HTTP
// =============================================================================

echo "=== Ejemplo 2: Tiempo total de la petición ===\n";

$ch = curl_init("https://httpbin.org/delay/1"); // Simula retraso de 1 segundo
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

curl_exec($ch);

// Tiempos detallados de cada fase de la conexión
$tiempoTotal      = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
$tiempoDns        = curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME);
$tiempoConexion   = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
$tiempoSsl        = curl_getinfo($ch, CURLINFO_APPCONNECT_TIME);
$tiempoInicio     = curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);

curl_close($ch);

echo "Desglose de tiempos:\n";
echo "  Resolución DNS:        " . round($tiempoDns * 1000, 2) . " ms\n";
echo "  Conexión TCP:          " . round($tiempoConexion * 1000, 2) . " ms\n";
echo "  Negociación SSL/TLS:   " . round($tiempoSsl * 1000, 2) . " ms\n";
echo "  Hasta primer byte:     " . round($tiempoInicio * 1000, 2) . " ms\n";
echo "  Tiempo TOTAL:          " . round($tiempoTotal * 1000, 2) . " ms\n\n";

// =============================================================================
// Ejemplo 3: Obtener el tipo de contenido (Content-Type)
// Identificar el formato de la respuesta recibida
// =============================================================================

echo "=== Ejemplo 3: Tipo de contenido ===\n";

$urlsPorTipo = [
    "https://httpbin.org/json"  => "Debería ser JSON",
    "https://httpbin.org/html"  => "Debería ser HTML",
    "https://httpbin.org/xml"   => "Debería ser XML",
    "https://httpbin.org/image/png" => "Debería ser imagen PNG",
];

foreach ($urlsPorTipo as $url => $descripcion) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);

    // CURLINFO_CONTENT_TYPE devuelve la cabecera Content-Type de la respuesta
    $tipoContenido = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    // CURLINFO_SIZE_DOWNLOAD devuelve el tamaño de los datos descargados
    $tamano = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

    curl_close($ch);

    echo "  $descripcion:\n";
    echo "    Content-Type: $tipoContenido\n";
    echo "    Tamaño: " . round($tamano / 1024, 2) . " KB\n";
}
echo "\n";

// =============================================================================
// Ejemplo 4: Conteo de redirecciones y URL efectiva
// Rastrear la cadena de redirecciones hasta el destino final
// =============================================================================

echo "=== Ejemplo 4: Conteo de redirecciones ===\n";

$ch = curl_init("https://httpbin.org/redirect/4"); // 4 redirecciones encadenadas
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

curl_exec($ch);

// CURLINFO_REDIRECT_COUNT: número de redirecciones seguidas
$redirecciones = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);

// CURLINFO_EFFECTIVE_URL: URL final después de todas las redirecciones
$urlFinal = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

// CURLINFO_REDIRECT_TIME: tiempo total gastado en redirecciones
$tiempoRedirecciones = curl_getinfo($ch, CURLINFO_REDIRECT_TIME);

$codigoFinal = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "URL original:              https://httpbin.org/redirect/4\n";
echo "URL final (efectiva):      $urlFinal\n";
echo "Número de redirecciones:   $redirecciones\n";
echo "Tiempo en redirecciones:   " . round($tiempoRedirecciones * 1000, 2) . " ms\n";
echo "Código HTTP final:         $codigoFinal\n\n";

// =============================================================================
// Ejemplo 5: Obtener toda la información de una vez (sin parámetro específico)
// curl_getinfo() sin segundo parámetro devuelve un array completo
// =============================================================================

echo "=== Ejemplo 5: Toda la información de la transferencia ===\n";

$ch = curl_init("https://httpbin.org/get");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);

// Sin segundo parámetro, devuelve un array asociativo con toda la información
$infoCompleta = curl_getinfo($ch);
curl_close($ch);

// Mostrar las claves más útiles del array
$clavesImportantes = [
    'url',
    'http_code',
    'content_type',
    'total_time',
    'namelookup_time',
    'connect_time',
    'size_download',
    'speed_download',
    'redirect_count',
    'primary_ip',
    'primary_port',
    'scheme',
];

echo "Información seleccionada de la transferencia:\n";
foreach ($clavesImportantes as $clave) {
    if (isset($infoCompleta[$clave])) {
        $valor = $infoCompleta[$clave];
        // Formatear valores numéricos flotantes
        if (is_float($valor)) {
            $valor = round($valor, 6);
        }
        echo "  $clave: $valor\n";
    }
}

echo "\nTotal de campos disponibles en curl_getinfo(): " . count($infoCompleta) . "\n\n";

// =============================================================================
// Ejemplo 6: Función para analizar el rendimiento de múltiples URLs
// Comparar tiempos de respuesta de distintos endpoints
// =============================================================================

echo "=== Ejemplo 6: Análisis de rendimiento de múltiples URLs ===\n";

/**
 * Mide el rendimiento de una petición HTTP y devuelve métricas clave.
 *
 * @param string $url URL a medir
 * @return array Métricas de rendimiento
 */
function medirRendimiento(string $url): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $cuerpo = curl_exec($ch);
    $exito = ($cuerpo !== false);

    $metricas = [
        'url'             => $url,
        'exito'           => $exito,
        'codigo_http'     => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'tiempo_total_ms' => round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000, 2),
        'tiempo_dns_ms'   => round(curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME) * 1000, 2),
        'tamano_bytes'    => curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
        'velocidad_kbs'   => round(curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD) / 1024, 2),
        'tipo_contenido'  => curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?? 'desconocido',
    ];

    curl_close($ch);
    return $metricas;
}

$urlsParaMedir = [
    "https://httpbin.org/get",
    "https://httpbin.org/json",
    "https://httpbin.org/delay/1",
];

echo str_pad("URL", 40) . str_pad("HTTP", 6) . str_pad("Tiempo", 12) . str_pad("Tamaño", 12) . "Velocidad\n";
echo str_repeat("-", 85) . "\n";

foreach ($urlsParaMedir as $url) {
    $m = medirRendimiento($url);

    echo str_pad($m['url'], 40);
    echo str_pad((string)$m['codigo_http'], 6);
    echo str_pad($m['tiempo_total_ms'] . " ms", 12);
    echo str_pad(round($m['tamano_bytes'] / 1024, 1) . " KB", 12);
    echo $m['velocidad_kbs'] . " KB/s\n";
}

?>
