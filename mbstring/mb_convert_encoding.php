<?php
// =============================================================================
// mb_convert_encoding() - Convertir entre codificaciones de caracteres
// UTF-8 a ISO-8859-1, Windows-1252, UTF-16, y más
// =============================================================================

// =============================================================================
// Ejemplo 1: Conversión básica de UTF-8 a ISO-8859-1 (Latin-1)
// Necesario al interactuar con sistemas antiguos o bases de datos legacy
// =============================================================================

echo "=== Ejemplo 1: UTF-8 a ISO-8859-1 ===\n";

$textoUtf8 = "Café español con piña";

// Convertir de UTF-8 a ISO-8859-1
$textoLatin1 = mb_convert_encoding($textoUtf8, 'ISO-8859-1', 'UTF-8');

echo "Texto original (UTF-8):\n";
echo "  Contenido: \"$textoUtf8\"\n";
echo "  Bytes: " . strlen($textoUtf8) . "\n";
echo "  Caracteres: " . mb_strlen($textoUtf8, 'UTF-8') . "\n\n";

echo "Texto convertido (ISO-8859-1):\n";
echo "  Bytes: " . strlen($textoLatin1) . "\n";
echo "  En ISO-8859-1 cada carácter ocupa exactamente 1 byte.\n\n";

// Convertir de vuelta a UTF-8
$deVuelta = mb_convert_encoding($textoLatin1, 'UTF-8', 'ISO-8859-1');
echo "De vuelta a UTF-8: \"$deVuelta\"\n";
echo "¿Coincide con el original?: " . ($deVuelta === $textoUtf8 ? 'Sí' : 'No') . "\n\n";

// =============================================================================
// Ejemplo 2: Conversión entre múltiples codificaciones europeas
// Windows-1252, ISO-8859-15, UTF-8
// =============================================================================

echo "=== Ejemplo 2: Codificaciones europeas ===\n";

$textoOriginal = "Precio: 15€ — incluye café y más";

// UTF-8 a Windows-1252 (la codificación más común en Windows para Europa Occidental)
$windows1252 = mb_convert_encoding($textoOriginal, 'Windows-1252', 'UTF-8');

// UTF-8 a ISO-8859-15 (Latin-9: incluye el símbolo €, a diferencia de ISO-8859-1)
$latin9 = mb_convert_encoding($textoOriginal, 'ISO-8859-15', 'UTF-8');

echo "Texto: \"$textoOriginal\"\n\n";

echo "Comparación de tamaños:\n";
echo "  UTF-8:          " . strlen($textoOriginal) . " bytes\n";
echo "  Windows-1252:   " . strlen($windows1252) . " bytes\n";
echo "  ISO-8859-15:    " . strlen($latin9) . " bytes\n\n";

// Nota: ISO-8859-1 NO incluye el símbolo €
echo "Nota sobre el símbolo €:\n";
echo "  ISO-8859-1 (Latin-1):  NO incluye €\n";
echo "  ISO-8859-15 (Latin-9): SÍ incluye €\n";
echo "  Windows-1252:          SÍ incluye €\n\n";

// =============================================================================
// Ejemplo 3: Trabajar con codificaciones asiáticas
// Convertir entre UTF-8, Shift_JIS, EUC-JP, GB2312, EUC-KR
// =============================================================================

echo "=== Ejemplo 3: Codificaciones asiáticas ===\n";

// Texto japonés en UTF-8
$japones = "東京タワー (Torre de Tokio)";
echo "Japonés (UTF-8): \"$japones\"\n";
echo "  Bytes UTF-8: " . strlen($japones) . "\n";

// Convertir a Shift_JIS (codificación japonesa tradicional)
$shiftJis = mb_convert_encoding($japones, 'SJIS', 'UTF-8');
echo "  Bytes Shift_JIS: " . strlen($shiftJis) . "\n";

// Convertir a EUC-JP (otra codificación japonesa)
$eucJp = mb_convert_encoding($japones, 'EUC-JP', 'UTF-8');
echo "  Bytes EUC-JP: " . strlen($eucJp) . "\n";

// Convertir de vuelta a UTF-8 desde Shift_JIS
$deVuelta = mb_convert_encoding($shiftJis, 'UTF-8', 'SJIS');
echo "  De vuelta SJIS->UTF-8: \"$deVuelta\"\n\n";

// Texto chino
$chino = "你好世界";
echo "Chino (UTF-8): \"$chino\"\n";
echo "  Bytes UTF-8: " . strlen($chino) . "\n";

$gb2312 = mb_convert_encoding($chino, 'GB2312', 'UTF-8');
echo "  Bytes GB2312: " . strlen($gb2312) . "\n\n";

// =============================================================================
// Ejemplo 4: Manejo de caracteres que no existen en la codificación destino
// Qué sucede cuando un carácter no tiene equivalente
// =============================================================================

echo "=== Ejemplo 4: Caracteres sin equivalente ===\n";

// Emojis y caracteres CJK no existen en ISO-8859-1
$textoConEmojis = "Hola 🌍 mundo";
echo "Texto original: \"$textoConEmojis\"\n";

// Intentar convertir emojis a ISO-8859-1 (los emojis se perderán)
$sinEmojis = mb_convert_encoding($textoConEmojis, 'ISO-8859-1', 'UTF-8');
$recuperado = mb_convert_encoding($sinEmojis, 'UTF-8', 'ISO-8859-1');
echo "Después de UTF-8 -> ISO-8859-1 -> UTF-8: \"$recuperado\"\n";
echo "Los emojis se pierden porque ISO-8859-1 solo soporta 256 caracteres.\n\n";

// Caracteres japoneses tampoco existen en ISO-8859-1
$mixto = "Café 日本語 España";
echo "Texto mixto: \"$mixto\"\n";
$convertido = mb_convert_encoding($mixto, 'ISO-8859-1', 'UTF-8');
$recuperado = mb_convert_encoding($convertido, 'UTF-8', 'ISO-8859-1');
echo "Después de conversión: \"$recuperado\"\n";
echo "Los caracteres japoneses se pierden.\n\n";

/**
 * Verifica si un texto puede convertirse a una codificación sin pérdida.
 */
function esConvertibleSinPerdida(string $texto, string $codificacionDestino): bool
{
    $convertido = mb_convert_encoding($texto, $codificacionDestino, 'UTF-8');
    $recuperado = mb_convert_encoding($convertido, 'UTF-8', $codificacionDestino);
    return $texto === $recuperado;
}

echo "¿Conversión sin pérdida?\n";
$pruebaTextos = [
    "Café español"    => ['ISO-8859-1', 'Windows-1252', 'UTF-16'],
    "Hola 🌍"        => ['ISO-8859-1', 'Windows-1252', 'UTF-16'],
    "東京"            => ['ISO-8859-1', 'SJIS', 'UTF-16'],
];

foreach ($pruebaTextos as $texto => $codificaciones) {
    echo "  \"$texto\":\n";
    foreach ($codificaciones as $cod) {
        $sinPerdida = esConvertibleSinPerdida($texto, $cod);
        echo "    -> $cod: " . ($sinPerdida ? "Sí" : "No (pérdida de datos)") . "\n";
    }
}
echo "\n";

// =============================================================================
// Ejemplo 5: Convertir codificación de archivos completos
// Leer archivo en una codificación y guardarlo en otra
// =============================================================================

echo "=== Ejemplo 5: Convertir codificación de archivos ===\n";

/**
 * Convierte la codificación de un archivo.
 *
 * @param string $archivoOrigen  Ruta del archivo origen
 * @param string $archivoDestino Ruta del archivo destino
 * @param string $codOrigen      Codificación del archivo origen
 * @param string $codDestino     Codificación del archivo destino
 * @param bool   $agregarBom     Agregar BOM (Byte Order Mark) si es UTF-8
 * @return array Información sobre la conversión
 */
function convertirArchivo(
    string $archivoOrigen,
    string $archivoDestino,
    string $codOrigen,
    string $codDestino,
    bool $agregarBom = false
): array {
    $contenido = file_get_contents($archivoOrigen);

    if ($contenido === false) {
        return ['exito' => false, 'error' => 'No se pudo leer el archivo'];
    }

    $bytesOriginal = strlen($contenido);
    $convertido = mb_convert_encoding($contenido, $codDestino, $codOrigen);

    // Agregar BOM si se solicita y el destino es UTF-8
    if ($agregarBom && $codDestino === 'UTF-8') {
        $convertido = "\xEF\xBB\xBF" . $convertido;
    }

    $bytesConvertido = strlen($convertido);
    $exito = file_put_contents($archivoDestino, $convertido);

    return [
        'exito'            => ($exito !== false),
        'bytes_original'   => $bytesOriginal,
        'bytes_convertido' => $bytesConvertido,
        'codificacion_origen'  => $codOrigen,
        'codificacion_destino' => $codDestino,
    ];
}

// Crear un archivo de prueba en UTF-8
$archivoUtf8 = tempnam(sys_get_temp_dir(), 'utf8_');
$archivoLatin1 = tempnam(sys_get_temp_dir(), 'lat1_');

$contenidoPrueba = "Línea 1: Café con piña\nLínea 2: España ñoño\nLínea 3: Información útil\n";
file_put_contents($archivoUtf8, $contenidoPrueba);

// Convertir de UTF-8 a ISO-8859-1
$resultado = convertirArchivo($archivoUtf8, $archivoLatin1, 'UTF-8', 'ISO-8859-1');

echo "Conversión de archivo:\n";
echo "  Origen: UTF-8 ({$resultado['bytes_original']} bytes)\n";
echo "  Destino: ISO-8859-1 ({$resultado['bytes_convertido']} bytes)\n";
echo "  Éxito: " . ($resultado['exito'] ? 'Sí' : 'No') . "\n";
echo "  Diferencia: " . ($resultado['bytes_original'] - $resultado['bytes_convertido']) . " bytes menos\n\n";

// Limpiar archivos temporales
unlink($archivoUtf8);
unlink($archivoLatin1);

// =============================================================================
// Ejemplo 6: Detección automática y conversión a UTF-8
// Función robusta para normalizar cualquier texto a UTF-8
// =============================================================================

echo "=== Ejemplo 6: Normalización automática a UTF-8 ===\n";

/**
 * Convierte cualquier texto a UTF-8, detectando la codificación automáticamente.
 *
 * @param string $texto               Texto con codificación desconocida
 * @param array  $codificacionesPosibles Codificaciones a intentar
 * @return array ['texto' => string, 'codificacion_original' => string, 'convertido' => bool]
 */
function normalizarAUtf8(string $texto, array $codificacionesPosibles = []): array
{
    if (empty($codificacionesPosibles)) {
        $codificacionesPosibles = [
            'UTF-8', 'ISO-8859-1', 'Windows-1252', 'ISO-8859-15',
            'SJIS', 'EUC-JP', 'GB2312', 'EUC-KR', 'ASCII',
        ];
    }

    // Verificar si ya es UTF-8 válido
    if (mb_check_encoding($texto, 'UTF-8')) {
        // Verificar que no sea ASCII puro detectado erróneamente
        $codDetectada = mb_detect_encoding($texto, $codificacionesPosibles, true);
        return [
            'texto' => $texto,
            'codificacion_original' => $codDetectada ?: 'UTF-8',
            'convertido' => false,
        ];
    }

    // Detectar la codificación
    $codDetectada = mb_detect_encoding($texto, $codificacionesPosibles, true);

    if ($codDetectada === false) {
        // Asumir ISO-8859-1 como fallback universal
        $codDetectada = 'ISO-8859-1';
    }

    $textoUtf8 = mb_convert_encoding($texto, 'UTF-8', $codDetectada);

    return [
        'texto' => $textoUtf8,
        'codificacion_original' => $codDetectada,
        'convertido' => true,
    ];
}

// Probar con textos en diferentes codificaciones
$textoBase = "Señor López pidió más café";

$codificaciones = ['UTF-8', 'ISO-8859-1', 'Windows-1252'];
foreach ($codificaciones as $cod) {
    if ($cod === 'UTF-8') {
        $textoEnCod = $textoBase;
    } else {
        $textoEnCod = mb_convert_encoding($textoBase, $cod, 'UTF-8');
    }

    $resultado = normalizarAUtf8($textoEnCod);
    echo "  Entrada ($cod, " . strlen($textoEnCod) . " bytes):\n";
    echo "    Detectada: {$resultado['codificacion_original']}\n";
    echo "    Convertido: " . ($resultado['convertido'] ? 'Sí' : 'No') . "\n";
    echo "    Resultado: \"{$resultado['texto']}\"\n\n";
}

// Lista de codificaciones soportadas
echo "Algunas codificaciones soportadas por mb_convert_encoding():\n";
$todasLasCod = mb_list_encodings();
$seleccion = array_slice($todasLasCod, 0, 15);
echo "  " . implode(', ', $seleccion) . "...\n";
echo "  Total disponibles: " . count($todasLasCod) . "\n";

?>
