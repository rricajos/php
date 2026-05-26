<?php
// =============================================================================
// Peticiones POST con cURL
// Envío de datos de formulario, cuerpo JSON y cabeceras personalizadas
// =============================================================================

// =============================================================================
// Ejemplo 1: POST con datos de formulario (application/x-www-form-urlencoded)
// El formato más común para formularios HTML
// =============================================================================

echo "=== Ejemplo 1: POST con datos de formulario ===\n";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/post");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// Datos del formulario como string codificado
$datosFormulario = [
    'nombre'   => 'Ana López',
    'email'    => 'ana.lopez@correo.com',
    'mensaje'  => 'Hola, este es un mensaje de prueba con tildes: áéíóú',
];

// http_build_query codifica los datos correctamente para el envío
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datosFormulario));

$respuesta = curl_exec($ch);
$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Código HTTP: $codigoHttp\n";
echo "Datos del formulario recibidos:\n";
foreach ($datos['form'] ?? [] as $campo => $valor) {
    echo "  $campo: $valor\n";
}
echo "\n";

// =============================================================================
// Ejemplo 2: POST con cuerpo JSON
// Enviar datos estructurados en formato JSON a una API
// =============================================================================

echo "=== Ejemplo 2: POST con cuerpo JSON ===\n";

$datosJson = [
    'producto' => [
        'nombre'      => 'Teclado mecánico',
        'precio'      => 89.99,
        'moneda'      => 'EUR',
        'disponible'  => true,
        'categorias'  => ['electrónica', 'periféricos', 'oficina'],
    ],
];

$jsonCodificado = json_encode($datosJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo "JSON a enviar:\n$jsonCodificado\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/post");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonCodificado);

// Es fundamental establecer el Content-Type para JSON
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "El servidor recibió este JSON:\n";
echo $datos['data'] ?? 'Sin datos';
echo "\n\n";

// =============================================================================
// Ejemplo 3: POST con subida de archivo (multipart/form-data)
// Enviar archivos usando CURLFile
// =============================================================================

echo "=== Ejemplo 3: POST con subida de archivo ===\n";

// Crear un archivo temporal de prueba
$archivoTemporal = tempnam(sys_get_temp_dir(), 'curl_upload_');
file_put_contents($archivoTemporal, "Contenido del archivo de prueba.\nLínea 2 con acentos: ñ, á, é.");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/post");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// Usar CURLFile para adjuntar archivos (recomendado desde PHP 5.5+)
// El constructor acepta: ruta, tipo MIME, nombre del archivo
$archivo = new CURLFile($archivoTemporal, 'text/plain', 'documento.txt');

// Cuando POSTFIELDS es un array con CURLFile, se envía como multipart/form-data
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'descripcion' => 'Archivo de prueba',
    'archivo'     => $archivo,
]);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Campos del formulario:\n";
print_r($datos['form'] ?? []);
echo "Archivos recibidos:\n";
foreach ($datos['files'] ?? [] as $nombre => $contenido) {
    echo "  $nombre: " . substr($contenido, 0, 60) . "...\n";
}

// Limpiar archivo temporal
unlink($archivoTemporal);
echo "\n";

// =============================================================================
// Ejemplo 4: POST con cabeceras personalizadas y autenticación Bearer
// Simular una petición a una API protegida
// =============================================================================

echo "=== Ejemplo 4: POST con cabeceras personalizadas ===\n";

$tokenAcceso = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.ejemplo";

$datosUsuario = json_encode([
    'accion'    => 'actualizar_perfil',
    'nombre'    => 'Roberto Martínez',
    'idioma'    => 'es',
    'zona_hora' => 'America/Mexico_City',
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/post");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $datosUsuario);

// Múltiples cabeceras personalizadas
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json; charset=utf-8',
    'Authorization: Bearer ' . $tokenAcceso,
    'X-Request-ID: ' . uniqid('req_'),
    'X-Client-Version: 2.1.0',
    'Accept-Language: es-MX',
]);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Cabeceras enviadas al servidor:\n";
foreach ($datos['headers'] ?? [] as $nombre => $valor) {
    echo "  $nombre: $valor\n";
}
echo "\n";

// =============================================================================
// Ejemplo 5: POST con PUT y DELETE usando CURLOPT_CUSTOMREQUEST
// Otros métodos HTTP comunes en APIs REST
// =============================================================================

echo "=== Ejemplo 5: Peticiones PUT y DELETE ===\n";

// Petición PUT (actualizar un recurso)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/put");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Cambiar el método HTTP
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => 42, 'estado' => 'actualizado']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$respuesta = curl_exec($ch);
$datos = json_decode($respuesta, true);
echo "PUT - Datos enviados: " . ($datos['data'] ?? 'ninguno') . "\n";
curl_close($ch);

// Petición DELETE (eliminar un recurso)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer token_de_ejemplo',
]);

$respuesta = curl_exec($ch);
$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "DELETE - Código HTTP: $codigoHttp\n";
curl_close($ch);
echo "\n";

// =============================================================================
// Ejemplo 6: Función reutilizable para peticiones POST
// Encapsular la lógica de POST en una función práctica
// =============================================================================

echo "=== Ejemplo 6: Función reutilizable para POST ===\n";

/**
 * Realiza una petición POST a una URL con datos JSON.
 *
 * @param string $url      URL destino
 * @param array  $datos    Datos a enviar como JSON
 * @param array  $cabeceras Cabeceras adicionales (opcional)
 * @param int    $timeout  Tiempo máximo de espera en segundos
 * @return array ['exito' => bool, 'codigo' => int, 'cuerpo' => string, 'error' => string]
 */
function hacerPostJson(string $url, array $datos, array $cabeceras = [], int $timeout = 10): array
{
    $ch = curl_init();

    $cabecerasPorDefecto = [
        'Content-Type: application/json; charset=utf-8',
        'Accept: application/json',
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($datos, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => array_merge($cabecerasPorDefecto, $cabeceras),
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);

    $cuerpo = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    return [
        'exito'  => ($cuerpo !== false && $codigo >= 200 && $codigo < 300),
        'codigo' => $codigo,
        'cuerpo' => $cuerpo ?: '',
        'error'  => $error,
    ];
}

// Usar la función
$resultado = hacerPostJson("https://httpbin.org/post", [
    'tarea'     => 'Revisar documentación',
    'prioridad' => 'alta',
    'asignado'  => 'equipo-backend',
]);

echo "¿Éxito?: " . ($resultado['exito'] ? 'Sí' : 'No') . "\n";
echo "Código HTTP: {$resultado['codigo']}\n";

if ($resultado['exito']) {
    $respuestaJson = json_decode($resultado['cuerpo'], true);
    echo "Datos JSON que el servidor recibió:\n";
    echo $respuestaJson['data'] ?? 'Sin datos';
    echo "\n";
} else {
    echo "Error: {$resultado['error']}\n";
}

?>
