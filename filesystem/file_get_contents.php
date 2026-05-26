<?php
// Ejemplo 1: Leer un archivo completo como string
$contenido = file_get_contents(__FILE__);
echo "Ejemplo 1 (Leer archivo actual):\n";
echo "Primeros 50 chars: " . substr($contenido, 0, 50) . "...\n";
echo "Tamaño: " . strlen($contenido) . " bytes\n";

// Ejemplo 2: Leer con offset y longitud
echo "\nEjemplo 2 (Con offset y longitud):\n";
$parcial = file_get_contents(__FILE__, false, null, 0, 100);
echo "Primeros 100 bytes:\n$parcial\n";

// Ejemplo 3: Leer una URL (si allow_url_fopen está habilitado)
echo "\nEjemplo 3 (Leer URL):\n";
echo "file_get_contents('https://...') lee contenido de una URL\n";
echo "Requiere allow_url_fopen = On en php.ini\n";

// Ejemplo 4: Leer con contexto HTTP
$opciones = [
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
];
$contexto = stream_context_create($opciones);
echo "\nEjemplo 4 (Con contexto HTTP):\n";
echo "Se puede configurar método, headers, timeout, etc.\n";

// Ejemplo 5: Manejo de errores
echo "\nEjemplo 5 (Manejo de errores):\n";
$resultado = @file_get_contents('archivo_inexistente.txt');
if ($resultado === false) {
    echo "Error: no se pudo leer el archivo\n";
}

// Ejemplo 6: Leer archivo JSON
$json_data = '{"nombre":"Ana","edad":30}';
$temp = tempnam(sys_get_temp_dir(), 'php');
file_put_contents($temp, $json_data);
$datos = json_decode(file_get_contents($temp), true);
echo "\nEjemplo 6 (Leer JSON desde archivo):\n";
print_r($datos);
unlink($temp);
?>
