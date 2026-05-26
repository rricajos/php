<?php
// =============================================================================
// curl_init, curl_setopt, curl_exec, curl_close
// Funciones fundamentales para realizar peticiones HTTP con cURL en PHP
// =============================================================================

// =============================================================================
// Ejemplo 1: Petición GET básica
// Inicializar cURL, establecer URL, ejecutar y cerrar
// =============================================================================

echo "=== Ejemplo 1: Petición GET básica ===\n";

// curl_init() crea un nuevo recurso cURL
$ch = curl_init();

// curl_setopt() establece opciones para la sesión cURL
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/get");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devolver respuesta como string

// curl_exec() ejecuta la sesión cURL
$respuesta = curl_exec($ch);

// curl_close() cierra la sesión cURL y libera recursos
curl_close($ch);

echo "Respuesta recibida:\n";
echo substr($respuesta, 0, 200) . "...\n\n";

// =============================================================================
// Ejemplo 2: Inicializar cURL directamente con una URL
// curl_init() acepta la URL como parámetro opcional
// =============================================================================

echo "=== Ejemplo 2: Inicializar con URL directa ===\n";

// Se puede pasar la URL directamente a curl_init()
$ch = curl_init("https://httpbin.org/ip");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Mi dirección IP pública: " . ($datos['origin'] ?? 'No disponible') . "\n\n";

// =============================================================================
// Ejemplo 3: Verificar que curl_init() fue exitoso
// Buenas prácticas para manejo de errores en la inicialización
// =============================================================================

echo "=== Ejemplo 3: Verificación de inicialización ===\n";

$ch = curl_init();

// Verificar que el recurso se creó correctamente
if ($ch === false) {
    echo "ERROR: No se pudo inicializar cURL. ¿Está instalada la extensión?\n";
} else {
    echo "cURL inicializado correctamente.\n";

    curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/user-agent");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $respuesta = curl_exec($ch);

    // Verificar que la ejecución fue exitosa
    if ($respuesta === false) {
        echo "ERROR en la ejecución: " . curl_error($ch) . "\n";
    } else {
        echo "Respuesta: $respuesta\n";
    }

    curl_close($ch);
}
echo "\n";

// =============================================================================
// Ejemplo 4: Petición GET con parámetros en la URL (query string)
// Construir URL con parámetros usando http_build_query
// =============================================================================

echo "=== Ejemplo 4: GET con parámetros en la URL ===\n";

$urlBase = "https://httpbin.org/get";
$parametros = [
    'nombre'  => 'María García',
    'ciudad'  => 'Bogotá',
    'idioma'  => 'español',
];

// Construir la URL completa con parámetros codificados
$urlCompleta = $urlBase . '?' . http_build_query($parametros);
echo "URL construida: $urlCompleta\n";

$ch = curl_init($urlCompleta);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Parámetros recibidos por el servidor:\n";
foreach ($datos['args'] ?? [] as $clave => $valor) {
    echo "  $clave => $valor\n";
}
echo "\n";

// =============================================================================
// Ejemplo 5: Descargar contenido y guardarlo en un archivo
// Usar CURLOPT_FILE para escribir directamente a disco
// =============================================================================

echo "=== Ejemplo 5: Descargar contenido a un archivo ===\n";

$archivoDestino = tempnam(sys_get_temp_dir(), 'curl_');
$fp = fopen($archivoDestino, 'w');

$ch = curl_init("https://httpbin.org/json");
curl_setopt($ch, CURLOPT_FILE, $fp); // Escribir la respuesta directamente al archivo

curl_exec($ch);
$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
fclose($fp);

echo "Código HTTP: $codigoHttp\n";
echo "Archivo guardado en: $archivoDestino\n";
echo "Tamaño del archivo: " . filesize($archivoDestino) . " bytes\n";

// Limpiar archivo temporal
unlink($archivoDestino);
echo "Archivo temporal eliminado.\n\n";

// =============================================================================
// Ejemplo 6: Establecer User-Agent personalizado en una petición GET
// Identificar nuestra aplicación ante el servidor
// =============================================================================

echo "=== Ejemplo 6: GET con User-Agent personalizado ===\n";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/user-agent");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "MiAplicacionPHP/1.0 (ejemplo educativo)");

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "El servidor vio nuestro User-Agent como: " . ($datos['user-agent'] ?? 'No disponible') . "\n";

?>
