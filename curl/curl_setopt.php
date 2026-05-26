<?php
// =============================================================================
// curl_setopt() - Opciones de configuración de cURL
// CURLOPT_URL, CURLOPT_RETURNTRANSFER, CURLOPT_POST, CURLOPT_POSTFIELDS,
// CURLOPT_HTTPHEADER, CURLOPT_TIMEOUT, CURLOPT_FOLLOWLOCATION, CURLOPT_SSL_VERIFYPEER
// =============================================================================

// =============================================================================
// Ejemplo 1: CURLOPT_URL y CURLOPT_RETURNTRANSFER
// Establecer la URL destino y capturar la respuesta como string
// =============================================================================

echo "=== Ejemplo 1: CURLOPT_URL y CURLOPT_RETURNTRANSFER ===\n";

$ch = curl_init();

// CURLOPT_URL: establece la URL a la que se hará la petición
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/get");

// CURLOPT_RETURNTRANSFER: si es true, curl_exec() devuelve el resultado como string
// Si es false (por defecto), lo imprime directamente en la salida
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$respuesta = curl_exec($ch); // Devuelve string en vez de imprimir
curl_close($ch);

echo "Tipo de dato de la respuesta: " . gettype($respuesta) . "\n";
echo "Longitud de la respuesta: " . strlen($respuesta) . " bytes\n\n";

// Sin CURLOPT_RETURNTRANSFER, la salida se imprime directamente:
echo "--- Sin RETURNTRANSFER (se imprime directo): ---\n";
$ch = curl_init("https://httpbin.org/ip");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // Comportamiento por defecto
curl_exec($ch); // Imprime directamente en la salida estándar
curl_close($ch);
echo "\n\n";

// =============================================================================
// Ejemplo 2: CURLOPT_POST y CURLOPT_POSTFIELDS
// Enviar datos mediante POST (formulario y JSON)
// =============================================================================

echo "=== Ejemplo 2: CURLOPT_POST y CURLOPT_POSTFIELDS ===\n";

// Envío de datos como formulario (application/x-www-form-urlencoded)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/post");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// CURLOPT_POST: indica que es una petición POST
curl_setopt($ch, CURLOPT_POST, true);

// CURLOPT_POSTFIELDS: datos a enviar en el cuerpo del POST
// Si se pasa un array, se envía como multipart/form-data
// Si se pasa un string, se envía como application/x-www-form-urlencoded
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'usuario' => 'carlos',
    'correo'  => 'carlos@ejemplo.com',
]));

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Datos del formulario recibidos por el servidor:\n";
print_r($datos['form'] ?? []);
echo "\n";

// Envío de datos como JSON
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/post");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// Pasar un string JSON como cuerpo del POST
$jsonData = json_encode(['producto' => 'Laptop', 'precio' => 1299.99]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Datos JSON recibidos: " . ($datos['data'] ?? 'ninguno') . "\n\n";

// =============================================================================
// Ejemplo 3: CURLOPT_HTTPHEADER
// Enviar cabeceras HTTP personalizadas
// =============================================================================

echo "=== Ejemplo 3: CURLOPT_HTTPHEADER ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/headers");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// CURLOPT_HTTPHEADER: array de cabeceras en formato "Nombre: Valor"
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer mi_token_secreto_123',
    'X-Custom-Header: valor-personalizado',
    'Accept-Language: es-ES',
]);

$respuesta = curl_exec($ch);
curl_close($ch);

$datos = json_decode($respuesta, true);
echo "Cabeceras que el servidor recibió:\n";
foreach ($datos['headers'] ?? [] as $nombre => $valor) {
    echo "  $nombre: $valor\n";
}
echo "\n";

// =============================================================================
// Ejemplo 4: CURLOPT_TIMEOUT y CURLOPT_CONNECTTIMEOUT
// Controlar los tiempos de espera de la conexión
// =============================================================================

echo "=== Ejemplo 4: CURLOPT_TIMEOUT ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/delay/2"); // Respuesta con 2 seg de retraso
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// CURLOPT_TIMEOUT: tiempo máximo total (en segundos) para toda la operación
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Máximo 5 segundos en total

// CURLOPT_CONNECTTIMEOUT: tiempo máximo para establecer la conexión
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Máximo 3 segundos para conectar

$inicio = microtime(true);
$respuesta = curl_exec($ch);
$duracion = round(microtime(true) - $inicio, 2);

if ($respuesta === false) {
    echo "Error: " . curl_error($ch) . "\n";
} else {
    echo "Petición completada en $duracion segundos (timeout configurado: 5s)\n";
}
curl_close($ch);

// Demostrar timeout excedido
echo "\n--- Provocando un timeout ---\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/delay/10"); // Retraso de 10 seg
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Solo esperamos 2 segundos

$respuesta = curl_exec($ch);
if ($respuesta === false) {
    echo "Timeout alcanzado: " . curl_error($ch) . "\n";
    echo "Código de error: " . curl_errno($ch) . " (CURLE_OPERATION_TIMEDOUT = 28)\n";
}
curl_close($ch);
echo "\n";

// =============================================================================
// Ejemplo 5: CURLOPT_FOLLOWLOCATION
// Seguir redirecciones HTTP automáticamente (301, 302, etc.)
// =============================================================================

echo "=== Ejemplo 5: CURLOPT_FOLLOWLOCATION ===\n";

// Sin seguir redirecciones
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/redirect/3"); // 3 redirecciones
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // NO seguir redirecciones

$respuesta = curl_exec($ch);
$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Sin FOLLOWLOCATION -> Código HTTP: $codigoHttp (redirección, no la siguió)\n";

// Con seguir redirecciones
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/redirect/3");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// CURLOPT_FOLLOWLOCATION: seguir cabeceras Location automáticamente
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// CURLOPT_MAXREDIRS: limitar el número máximo de redirecciones
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

$respuesta = curl_exec($ch);
$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirecciones = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
curl_close($ch);

echo "Con FOLLOWLOCATION -> Código HTTP: $codigoHttp (llegó al destino final)\n";
echo "Número de redirecciones seguidas: $redirecciones\n\n";

// =============================================================================
// Ejemplo 6: CURLOPT_SSL_VERIFYPEER y CURLOPT_SSL_VERIFYHOST
// Configurar la verificación de certificados SSL
// =============================================================================

echo "=== Ejemplo 6: CURLOPT_SSL_VERIFYPEER ===\n";

// Petición con verificación SSL habilitada (recomendado para producción)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/get");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// CURLOPT_SSL_VERIFYPEER: verificar el certificado SSL del servidor (por defecto true)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// CURLOPT_SSL_VERIFYHOST: verificar que el nombre del host coincide con el certificado
// 0 = no verificar, 2 = verificar (valor recomendado)
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

// CURLOPT_CAINFO: ruta al archivo de certificados CA (opcional, usa el del sistema por defecto)
// curl_setopt($ch, CURLOPT_CAINFO, '/ruta/al/cacert.pem');

$respuesta = curl_exec($ch);
if ($respuesta === false) {
    echo "Error SSL: " . curl_error($ch) . "\n";
} else {
    echo "Petición HTTPS exitosa con verificación SSL habilitada.\n";
}
curl_close($ch);

// ADVERTENCIA: Deshabilitar la verificación SSL es INSEGURO
// Solo usar en desarrollo o entornos de prueba
echo "\n--- ADVERTENCIA: Deshabilitando verificación SSL (NO usar en producción) ---\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://httpbin.org/get");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ¡INSEGURO!
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // ¡INSEGURO!

$respuesta = curl_exec($ch);
echo "Petición sin verificación SSL: " . (($respuesta !== false) ? "exitosa" : "fallida") . "\n";
echo "IMPORTANTE: Nunca deshabilitar SSL en producción.\n";
curl_close($ch);

?>
