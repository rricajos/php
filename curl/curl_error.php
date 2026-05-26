<?php
// =============================================================================
// curl_error() y curl_errno() - Manejo de errores en cURL
// Detectar y manejar errores de conexión, timeout, DNS y más
// =============================================================================

// =============================================================================
// Ejemplo 1: Uso básico de curl_error() y curl_errno()
// Detectar cuando una petición falla y obtener información del error
// =============================================================================

echo "=== Ejemplo 1: Uso básico de curl_error() y curl_errno() ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://este-dominio-no-existe-xyz-123.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$respuesta = curl_exec($ch);

if ($respuesta === false) {
    // curl_errno() devuelve el código numérico del error
    $codigoError = curl_errno($ch);

    // curl_error() devuelve un mensaje descriptivo del error
    $mensajeError = curl_error($ch);

    echo "La petición falló.\n";
    echo "  Código de error: $codigoError\n";
    echo "  Mensaje: $mensajeError\n";
} else {
    echo "Petición exitosa.\n";
}

curl_close($ch);
echo "\n";

// =============================================================================
// Ejemplo 2: Error de timeout (CURLE_OPERATION_TIMEDOUT = 28)
// Cuando el servidor tarda más de lo permitido en responder
// =============================================================================

echo "=== Ejemplo 2: Error de timeout ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/delay/10"); // 10 seg de retraso
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Solo esperamos 2 segundos

$inicio = microtime(true);
$respuesta = curl_exec($ch);
$duracion = round(microtime(true) - $inicio, 2);

if ($respuesta === false) {
    $errno = curl_errno($ch);
    $error = curl_error($ch);

    echo "Timeout detectado después de {$duracion}s\n";
    echo "  Código: $errno\n";
    echo "  Mensaje: $error\n";

    // Verificar si es específicamente un timeout
    if ($errno === CURLE_OPERATION_TIMEDOUT) {
        echo "  Es un error de tipo CURLE_OPERATION_TIMEDOUT (28)\n";
    }
}

curl_close($ch);
echo "\n";

// =============================================================================
// Ejemplo 3: Error de resolución DNS (CURLE_COULDNT_RESOLVE_HOST = 6)
// Cuando el nombre de dominio no puede ser resuelto
// =============================================================================

echo "=== Ejemplo 3: Error de resolución DNS ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://servidor-inventado-abc-xyz.ejemplo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$respuesta = curl_exec($ch);

if ($respuesta === false) {
    $errno = curl_errno($ch);
    $error = curl_error($ch);

    echo "Error de DNS:\n";
    echo "  Código: $errno\n";
    echo "  Mensaje: $error\n";

    if ($errno === CURLE_COULDNT_RESOLVE_HOST) {
        echo "  Es un error de tipo CURLE_COULDNT_RESOLVE_HOST (6)\n";
        echo "  El dominio no pudo ser resuelto por el servidor DNS.\n";
    }
}

curl_close($ch);
echo "\n";

// =============================================================================
// Ejemplo 4: Conexión rechazada (CURLE_COULDNT_CONNECT = 7)
// Cuando el servidor rechaza la conexión o el puerto está cerrado
// =============================================================================

echo "=== Ejemplo 4: Conexión rechazada ===\n";

$ch = curl_init();
// Puerto 9999 probablemente no tiene un servidor escuchando
curl_setopt($ch, CURLOPT_URL, "http://localhost:9999/api");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

$respuesta = curl_exec($ch);

if ($respuesta === false) {
    $errno = curl_errno($ch);
    $error = curl_error($ch);

    echo "Error de conexión:\n";
    echo "  Código: $errno\n";
    echo "  Mensaje: $error\n";

    if ($errno === CURLE_COULDNT_CONNECT) {
        echo "  Es un error de tipo CURLE_COULDNT_CONNECT (7)\n";
        echo "  No se pudo establecer conexión con el servidor.\n";
    }
}

curl_close($ch);
echo "\n";

// =============================================================================
// Ejemplo 5: Manejo robusto de errores con función auxiliar
// Clasificar errores por tipo y sugerir acciones correctivas
// =============================================================================

echo "=== Ejemplo 5: Manejo robusto de errores ===\n";

/**
 * Realiza una petición HTTP con manejo completo de errores.
 *
 * @param string $url     URL a consultar
 * @param int    $timeout Tiempo máximo de espera
 * @return array Resultado con estado, datos y detalles del error si ocurrió
 */
function peticionSegura(string $url, int $timeout = 5): array
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min($timeout, 3),
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
    ]);

    $cuerpo = curl_exec($ch);
    $errno  = curl_errno($ch);
    $error  = curl_error($ch);
    $info   = curl_getinfo($ch);

    curl_close($ch);

    // Si curl_exec falló (error de red, timeout, etc.)
    if ($cuerpo === false) {
        $tipoError = match ($errno) {
            CURLE_COULDNT_RESOLVE_HOST => 'dns',
            CURLE_COULDNT_CONNECT     => 'conexion',
            CURLE_OPERATION_TIMEDOUT   => 'timeout',
            CURLE_SSL_CONNECT_ERROR,
            CURLE_SSL_CERTPROBLEM,
            CURLE_SSL_CIPHER,
            CURLE_SSL_CACERT          => 'ssl',
            CURLE_TOO_MANY_REDIRECTS  => 'redirecciones',
            default                   => 'desconocido',
        };

        return [
            'exito'      => false,
            'cuerpo'     => null,
            'codigo_http'=> 0,
            'tipo_error' => $tipoError,
            'errno'      => $errno,
            'error'      => $error,
            'sugerencia' => obtenerSugerencia($tipoError),
        ];
    }

    // Petición exitosa a nivel de red, pero puede tener error HTTP
    $codigoHttp = $info['http_code'];
    $esExito = ($codigoHttp >= 200 && $codigoHttp < 300);

    return [
        'exito'       => $esExito,
        'cuerpo'      => $cuerpo,
        'codigo_http' => $codigoHttp,
        'tipo_error'  => $esExito ? null : 'http',
        'errno'       => 0,
        'error'       => $esExito ? '' : "Código HTTP: $codigoHttp",
        'sugerencia'  => $esExito ? '' : obtenerSugerencia('http'),
    ];
}

/**
 * Devuelve una sugerencia basada en el tipo de error.
 */
function obtenerSugerencia(string $tipoError): string
{
    return match ($tipoError) {
        'dns'            => 'Verificar el nombre de dominio y la conexión a internet.',
        'conexion'       => 'Verificar que el servidor esté en ejecución y el puerto sea correcto.',
        'timeout'        => 'Aumentar el timeout o verificar la velocidad de la conexión.',
        'ssl'            => 'Verificar los certificados SSL o actualizar el paquete de CA.',
        'redirecciones'  => 'Verificar la URL, puede haber un bucle de redirecciones.',
        'http'           => 'Verificar la URL, los parámetros y la autenticación.',
        default          => 'Revisar la configuración de red y la URL.',
    };
}

// Probar con varias URLs que generan diferentes errores
$pruebasUrls = [
    "https://httpbin.org/get"                     => "URL válida",
    "https://httpbin.org/status/404"              => "URL con error 404",
    "https://dominio-inexistente-xyz.com"         => "Dominio inexistente",
    "http://localhost:9999"                       => "Puerto cerrado",
];

foreach ($pruebasUrls as $url => $descripcion) {
    echo "--- $descripcion ---\n";
    $resultado = peticionSegura($url, 3);

    if ($resultado['exito']) {
        echo "  Estado: OK (HTTP {$resultado['codigo_http']})\n";
    } else {
        echo "  Estado: ERROR\n";
        echo "  Tipo: {$resultado['tipo_error']}\n";
        echo "  Detalle: {$resultado['error']}\n";
        echo "  Sugerencia: {$resultado['sugerencia']}\n";
    }
    echo "\n";
}

// =============================================================================
// Ejemplo 6: Reintentos automáticos con retroceso exponencial
// Estrategia para manejar errores transitorios
// =============================================================================

echo "=== Ejemplo 6: Reintentos con retroceso exponencial ===\n";

/**
 * Realiza una petición con reintentos automáticos.
 *
 * @param string $url            URL a consultar
 * @param int    $maxReintentos  Número máximo de reintentos
 * @param int    $timeout        Timeout por intento
 * @return array Resultado de la petición
 */
function peticionConReintentos(string $url, int $maxReintentos = 3, int $timeout = 5): array
{
    $intentos = 0;
    $ultimoError = '';

    // Códigos de error de cURL que vale la pena reintentar
    $erroresReintentables = [
        CURLE_COULDNT_CONNECT,
        CURLE_OPERATION_TIMEDOUT,
        CURLE_GOT_NOTHING,
        CURLE_RECV_ERROR,
        CURLE_SEND_ERROR,
    ];

    while ($intentos <= $maxReintentos) {
        $intentos++;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);

        $cuerpo = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Si fue exitoso, devolver resultado
        if ($cuerpo !== false && $codigo >= 200 && $codigo < 300) {
            echo "  Intento $intentos: ÉXITO (HTTP $codigo)\n";
            return ['exito' => true, 'cuerpo' => $cuerpo, 'intentos' => $intentos];
        }

        $ultimoError = ($errno > 0) ? "cURL $errno: $error" : "HTTP $codigo";
        echo "  Intento $intentos: FALLO ($ultimoError)\n";

        // Solo reintentar si el error es reintentable
        if ($errno > 0 && !in_array($errno, $erroresReintentables)) {
            echo "  Error no reintentable, abortando.\n";
            break;
        }

        // Retroceso exponencial: 1s, 2s, 4s...
        if ($intentos <= $maxReintentos) {
            $espera = pow(2, $intentos - 1);
            echo "  Esperando {$espera}s antes de reintentar...\n";
            sleep($espera);
        }
    }

    return [
        'exito'    => false,
        'cuerpo'   => null,
        'intentos' => $intentos,
        'error'    => $ultimoError,
    ];
}

// Probar con una URL válida (debería funcionar al primer intento)
echo "Probando con URL válida:\n";
$resultado = peticionConReintentos("https://httpbin.org/get", 3, 5);
echo "Resultado: " . ($resultado['exito'] ? 'Éxito' : 'Fallo') . " en {$resultado['intentos']} intento(s)\n\n";

// Probar con una URL que fallará (dominio inexistente)
echo "Probando con dominio inexistente:\n";
$resultado = peticionConReintentos("https://dominio-inexistente-xyz.com", 2, 3);
echo "Resultado: " . ($resultado['exito'] ? 'Éxito' : 'Fallo') . " después de {$resultado['intentos']} intento(s)\n";

?>
