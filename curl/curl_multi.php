<?php
// =============================================================================
// curl_multi - Peticiones HTTP en paralelo
// curl_multi_init, curl_multi_add_handle, curl_multi_exec, curl_multi_remove_handle
// =============================================================================

// =============================================================================
// Ejemplo 1: Peticiones paralelas básicas con curl_multi
// Ejecutar varias peticiones simultáneamente en lugar de secuencialmente
// =============================================================================

echo "=== Ejemplo 1: Peticiones paralelas básicas ===\n";

// Crear el manejador multi
$multiCurl = curl_multi_init();

// Definir las URLs a consultar en paralelo
$urls = [
    'ip'     => "https://httpbin.org/ip",
    'agente' => "https://httpbin.org/user-agent",
    'cabeceras' => "https://httpbin.org/headers",
];

// Crear e inicializar cada manejador individual
$manejadores = [];
foreach ($urls as $nombre => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Agregar el manejador al grupo multi
    curl_multi_add_handle($multiCurl, $ch);
    $manejadores[$nombre] = $ch;
}

// Ejecutar todas las peticiones en paralelo
$activos = null;
$inicio = microtime(true);

do {
    // curl_multi_exec ejecuta las transferencias activas
    $estado = curl_multi_exec($multiCurl, $activos);

    // curl_multi_select espera actividad en las conexiones (evita CPU al 100%)
    if ($activos > 0) {
        curl_multi_select($multiCurl);
    }
} while ($activos > 0 && $estado === CURLM_OK);

$duracion = round(microtime(true) - $inicio, 3);

// Recoger los resultados
echo "Resultados (completados en {$duracion}s):\n";
foreach ($manejadores as $nombre => $ch) {
    $respuesta = curl_multi_getcontent($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $datos = json_decode($respuesta, true);

    echo "  [$nombre] HTTP $codigo: ";
    echo json_encode($datos, JSON_UNESCAPED_UNICODE) . "\n";

    // Remover el manejador del grupo y cerrarlo
    curl_multi_remove_handle($multiCurl, $ch);
    curl_close($ch);
}

// Cerrar el manejador multi
curl_multi_close($multiCurl);
echo "\n";

// =============================================================================
// Ejemplo 2: Comparar tiempo secuencial vs paralelo
// Demostrar la ventaja de rendimiento de curl_multi
// =============================================================================

echo "=== Ejemplo 2: Secuencial vs Paralelo ===\n";

$urlsConRetraso = [
    "https://httpbin.org/delay/1",
    "https://httpbin.org/delay/1",
    "https://httpbin.org/delay/1",
];

// Peticiones secuenciales (una tras otra)
$inicioSec = microtime(true);
foreach ($urlsConRetraso as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    curl_close($ch);
}
$tiempoSecuencial = round(microtime(true) - $inicioSec, 2);

// Peticiones paralelas (todas al mismo tiempo)
$inicioParalelo = microtime(true);
$mh = curl_multi_init();
$handles = [];

foreach ($urlsConRetraso as $i => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

$activos = null;
do {
    curl_multi_exec($mh, $activos);
    if ($activos > 0) {
        curl_multi_select($mh);
    }
} while ($activos > 0);

foreach ($handles as $ch) {
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);
$tiempoParalelo = round(microtime(true) - $inicioParalelo, 2);

echo "3 peticiones con 1s de retraso cada una:\n";
echo "  Secuencial: {$tiempoSecuencial}s (aprox. 3s esperados)\n";
echo "  Paralelo:   {$tiempoParalelo}s (aprox. 1s esperado)\n";
echo "  Aceleración: " . round($tiempoSecuencial / max($tiempoParalelo, 0.01), 1) . "x más rápido\n\n";

// =============================================================================
// Ejemplo 3: Manejar errores en peticiones paralelas
// Verificar el estado de cada petición individual
// =============================================================================

echo "=== Ejemplo 3: Manejo de errores en paralelo ===\n";

$urlsMixtas = [
    'valida'     => "https://httpbin.org/get",
    'no_existe'  => "https://dominio-falso-xyz-456.com",
    'error_404'  => "https://httpbin.org/status/404",
    'valida_2'   => "https://httpbin.org/json",
];

$mh = curl_multi_init();
$handles = [];

foreach ($urlsMixtas as $nombre => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_multi_add_handle($mh, $ch);
    $handles[$nombre] = $ch;
}

$activos = null;
do {
    curl_multi_exec($mh, $activos);
    if ($activos > 0) {
        curl_multi_select($mh);
    }
} while ($activos > 0);

echo "Resultados de peticiones mixtas:\n";
foreach ($handles as $nombre => $ch) {
    $contenido = curl_multi_getcontent($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "  [$nombre]: ";

    if ($errno > 0) {
        // Error a nivel de cURL (red, DNS, timeout, etc.)
        echo "ERROR cURL #$errno - $error\n";
    } elseif ($codigo >= 400) {
        // Error HTTP (4xx, 5xx)
        echo "ERROR HTTP $codigo\n";
    } else {
        // Éxito
        $tamano = strlen($contenido);
        echo "OK (HTTP $codigo, $tamano bytes)\n";
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);
echo "\n";

// =============================================================================
// Ejemplo 4: Usar curl_multi_info_read para procesar resultados
// Obtener información detallada del estado de cada transferencia
// =============================================================================

echo "=== Ejemplo 4: curl_multi_info_read ===\n";

$urlsInfo = [
    "https://httpbin.org/get",
    "https://httpbin.org/json",
    "https://httpbin.org/ip",
];

$mh = curl_multi_init();
$handles = [];

foreach ($urlsInfo as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_multi_add_handle($mh, $ch);
    $handles[(int)$ch] = ['url' => $url, 'handle' => $ch];
}

// Ejecutar peticiones
$activos = null;
do {
    curl_multi_exec($mh, $activos);
    if ($activos > 0) {
        curl_multi_select($mh);
    }
} while ($activos > 0);

// Leer información sobre cada transferencia completada
echo "Información de transferencias completadas:\n";
while ($info = curl_multi_info_read($mh)) {
    $ch = $info['handle'];
    $id = (int)$ch;
    $url = $handles[$id]['url'];

    // $info['result'] contiene el código de resultado de cURL (CURLE_OK = 0)
    $resultado = $info['result'];
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $tiempo = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000, 2);

    echo "  URL: $url\n";
    echo "    Resultado cURL: $resultado " . ($resultado === CURLE_OK ? '(OK)' : '(ERROR)') . "\n";
    echo "    HTTP: $codigo | Tiempo: {$tiempo}ms\n";
}

foreach ($handles as $datos) {
    curl_multi_remove_handle($mh, $datos['handle']);
    curl_close($datos['handle']);
}
curl_multi_close($mh);
echo "\n";

// =============================================================================
// Ejemplo 5: Función reutilizable para peticiones multi-cURL
// Encapsular la lógica de peticiones paralelas en una función
// =============================================================================

echo "=== Ejemplo 5: Función reutilizable para multi-cURL ===\n";

/**
 * Ejecuta múltiples peticiones GET en paralelo.
 *
 * @param array $peticiones Array asociativo ['nombre' => 'url']
 * @param int   $timeout    Tiempo máximo por petición
 * @return array Resultados indexados por nombre
 */
function peticionesParalelas(array $peticiones, int $timeout = 10): array
{
    $mh = curl_multi_init();
    $handles = [];
    $resultados = [];

    // Crear manejadores
    foreach ($peticiones as $nombre => $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$nombre] = $ch;
    }

    // Ejecutar en paralelo
    $activos = null;
    do {
        curl_multi_exec($mh, $activos);
        if ($activos > 0) {
            curl_multi_select($mh);
        }
    } while ($activos > 0);

    // Recopilar resultados
    foreach ($handles as $nombre => $ch) {
        $contenido = curl_multi_getcontent($ch);
        $resultados[$nombre] = [
            'exito'       => ($contenido !== false && curl_errno($ch) === 0),
            'codigo_http' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'cuerpo'      => $contenido ?: '',
            'tiempo_ms'   => round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000, 2),
            'error'       => curl_error($ch),
        ];

        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }

    curl_multi_close($mh);
    return $resultados;
}

// Usar la función
$peticiones = [
    'datos_json' => "https://httpbin.org/json",
    'mi_ip'      => "https://httpbin.org/ip",
    'cabeceras'  => "https://httpbin.org/headers",
    'retraso'    => "https://httpbin.org/delay/1",
];

$inicio = microtime(true);
$resultados = peticionesParalelas($peticiones, 10);
$total = round(microtime(true) - $inicio, 3);

echo "Resultados de " . count($peticiones) . " peticiones paralelas ({$total}s total):\n";
foreach ($resultados as $nombre => $r) {
    $estado = $r['exito'] ? "OK" : "ERROR";
    echo "  [$nombre] $estado - HTTP {$r['codigo_http']} ({$r['tiempo_ms']}ms)\n";
}
echo "\n";

// =============================================================================
// Ejemplo 6: Pool de peticiones con límite de concurrencia
// Controlar cuántas peticiones se ejecutan simultáneamente
// =============================================================================

echo "=== Ejemplo 6: Pool con límite de concurrencia ===\n";

/**
 * Ejecuta peticiones en paralelo con límite de concurrencia.
 *
 * @param array $urls         Lista de URLs a consultar
 * @param int   $concurrencia Número máximo de peticiones simultáneas
 * @param int   $timeout      Timeout por petición
 * @return array Resultados
 */
function poolConLimite(array $urls, int $concurrencia = 3, int $timeout = 10): array
{
    $mh = curl_multi_init();

    // Limitar la cantidad de conexiones simultáneas
    curl_multi_setopt($mh, CURLMOPT_MAXCONNECTS, $concurrencia);

    $resultados = [];
    $handleMap = [];   // Mapea ID de manejador -> índice de URL
    $pendientes = $urls;
    $indice = 0;
    $activos = 0;

    // Función para agregar un manejador al pool
    $agregarSiguiente = function () use (&$pendientes, &$indice, &$handleMap, $mh, $timeout) {
        if (empty($pendientes)) {
            return false;
        }

        $url = array_shift($pendientes);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handleMap[(int)$ch] = ['indice' => $indice, 'url' => $url, 'handle' => $ch];
        $indice++;
        return true;
    };

    // Llenar el pool hasta el límite de concurrencia
    for ($i = 0; $i < $concurrencia; $i++) {
        if (!$agregarSiguiente()) {
            break;
        }
    }

    // Ejecutar y procesar resultados
    do {
        curl_multi_exec($mh, $activos);

        // Verificar transferencias completadas
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle'];
            $id = (int)$ch;
            $datos = $handleMap[$id];

            $resultados[$datos['indice']] = [
                'url'         => $datos['url'],
                'exito'       => ($info['result'] === CURLE_OK),
                'codigo_http' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'tiempo_ms'   => round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000, 2),
            ];

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            unset($handleMap[$id]);

            // Agregar la siguiente petición pendiente
            $agregarSiguiente();
        }

        if ($activos > 0 || !empty($handleMap)) {
            curl_multi_select($mh, 0.1);
        }
    } while ($activos > 0 || !empty($handleMap));

    curl_multi_close($mh);
    ksort($resultados); // Ordenar por índice original
    return $resultados;
}

// Ejecutar 6 peticiones con concurrencia máxima de 2
$urls = [
    "https://httpbin.org/get?n=1",
    "https://httpbin.org/get?n=2",
    "https://httpbin.org/get?n=3",
    "https://httpbin.org/get?n=4",
    "https://httpbin.org/get?n=5",
    "https://httpbin.org/get?n=6",
];

$inicio = microtime(true);
$resultados = poolConLimite($urls, 2, 10);
$total = round(microtime(true) - $inicio, 3);

echo "6 peticiones con concurrencia máxima de 2 ({$total}s total):\n";
foreach ($resultados as $r) {
    $estado = $r['exito'] ? "OK" : "ERROR";
    echo "  {$r['url']} -> $estado (HTTP {$r['codigo_http']}, {$r['tiempo_ms']}ms)\n";
}

?>
