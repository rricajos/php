<?php
// =============================================================================
// mb_detect_encoding() y mb_check_encoding()
// Detectar y verificar la codificación de cadenas de texto
// =============================================================================

// =============================================================================
// Ejemplo 1: Uso básico de mb_detect_encoding
// Detectar automáticamente la codificación de una cadena
// =============================================================================

echo "=== Ejemplo 1: Detección básica de codificación ===\n";

$textoUtf8 = "Hola, ¿cómo estás? Café con piña.";

// mb_detect_encoding(cadena, lista_de_codificaciones, modo_estricto)
$codificacion = mb_detect_encoding($textoUtf8, 'UTF-8, ISO-8859-1, ASCII', true);
echo "Texto: \"$textoUtf8\"\n";
echo "Codificación detectada: $codificacion\n\n";

// Texto puramente ASCII (subconjunto de UTF-8)
$textoAscii = "Hello World 123";
$codAscii = mb_detect_encoding($textoAscii, 'ASCII, UTF-8, ISO-8859-1', true);
echo "Texto: \"$textoAscii\"\n";
echo "Codificación detectada: $codAscii\n\n";

// Detección con lista de codificaciones como array
$codArray = mb_detect_encoding($textoUtf8, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
echo "Detección con array: $codArray\n\n";

// =============================================================================
// Ejemplo 2: Modo estricto vs no estricto
// El modo estricto (true) es más preciso pero puede devolver false
// =============================================================================

echo "=== Ejemplo 2: Modo estricto vs no estricto ===\n";

// Crear una cadena con bytes que podrían ser ambiguos
$textoAmbiguo = "Café";

// Modo no estricto (false o sin parámetro): más permisivo
$deteccionLaxa = mb_detect_encoding($textoAmbiguo, 'UTF-8, ISO-8859-1', false);
echo "Modo no estricto: $deteccionLaxa\n";

// Modo estricto (true): verifica la validez de la codificación
$deteccionEstricta = mb_detect_encoding($textoAmbiguo, 'UTF-8, ISO-8859-1', true);
echo "Modo estricto: " . ($deteccionEstricta ?: "false (no detectada)") . "\n\n";

// Ejemplo con bytes inválidos para UTF-8
$bytesInvalidos = "\xC0\xAF"; // Secuencia UTF-8 inválida (overlong encoding)
$detLaxa = mb_detect_encoding($bytesInvalidos, 'UTF-8, ISO-8859-1', false);
$detEstricta = mb_detect_encoding($bytesInvalidos, 'UTF-8, ISO-8859-1', true);

echo "Bytes inválidos para UTF-8:\n";
echo "  Modo no estricto: " . ($detLaxa ?: "false") . "\n";
echo "  Modo estricto:    " . ($detEstricta ?: "false") . "\n";
echo "  Recomendación: siempre usar modo estricto (true) para resultados fiables.\n\n";

// =============================================================================
// Ejemplo 3: mb_check_encoding - Verificar si una cadena es válida en una codificación
// Devuelve true/false, más directo que mb_detect_encoding
// =============================================================================

echo "=== Ejemplo 3: mb_check_encoding ===\n";

$pruebas = [
    ["Hola mundo",                    "Texto ASCII simple"],
    ["Café con piña",                 "Texto español UTF-8"],
    ["こんにちは",                    "Japonés UTF-8"],
    ["🎉🚀",                         "Emojis UTF-8"],
    ["\xC3\xA9",                      "Byte válido UTF-8 (é)"],
    ["\xFF\xFE",                      "Bytes inválidos para UTF-8 (BOM UTF-16LE)"],
    ["\xC0\xAF",                      "Secuencia overlong (inválida UTF-8)"],
];

echo "Verificación de UTF-8 con mb_check_encoding:\n\n";
foreach ($pruebas as [$texto, $descripcion]) {
    $esValido = mb_check_encoding($texto, 'UTF-8');
    $estado = $esValido ? "VALIDO" : "INVALIDO";
    echo "  [$estado] $descripcion\n";
}
echo "\n";

// Verificar contra diferentes codificaciones
echo "Verificar 'Café' contra múltiples codificaciones:\n";
$textoVerificar = "Café";
$codificaciones = ['UTF-8', 'ASCII', 'ISO-8859-1', 'EUC-JP', 'SJIS'];

foreach ($codificaciones as $cod) {
    $valido = mb_check_encoding($textoVerificar, $cod);
    echo "  $cod: " . ($valido ? "Sí" : "No") . "\n";
}
echo "\n";

// =============================================================================
// Ejemplo 4: Detectar codificación de archivos o datos externos
// Escenario práctico al leer datos de fuentes desconocidas
// =============================================================================

echo "=== Ejemplo 4: Detectar codificación de datos externos ===\n";

/**
 * Detecta la codificación de un texto y lo convierte a UTF-8 si es necesario.
 *
 * @param string $texto Texto con codificación desconocida
 * @return array ['codificacion_original' => string, 'texto_utf8' => string, 'convertido' => bool]
 */
function asegurarUtf8(string $texto): array
{
    // Lista de codificaciones comunes, ordenadas por prioridad
    $codificaciones = [
        'UTF-8',
        'ISO-8859-1',   // Latin-1 (Europa Occidental)
        'ISO-8859-15',  // Latin-9 (con símbolo €)
        'Windows-1252', // CP1252 (Windows Europa Occidental)
        'ASCII',
    ];

    // Primero verificar si ya es UTF-8 válido
    if (mb_check_encoding($texto, 'UTF-8')) {
        return [
            'codificacion_original' => 'UTF-8',
            'texto_utf8' => $texto,
            'convertido' => false,
        ];
    }

    // Intentar detectar la codificación
    $codDetectada = mb_detect_encoding($texto, $codificaciones, true);

    if ($codDetectada === false) {
        // Si no se detecta, asumir ISO-8859-1 como fallback seguro
        $codDetectada = 'ISO-8859-1';
    }

    // Convertir a UTF-8
    $textoUtf8 = mb_convert_encoding($texto, 'UTF-8', $codDetectada);

    return [
        'codificacion_original' => $codDetectada,
        'texto_utf8' => $textoUtf8,
        'convertido' => true,
    ];
}

// Simular textos con diferentes codificaciones
$textoOriginal = "Café español con piña";

// Convertir a ISO-8859-1 para simular datos de una fuente antigua
$textoLatin1 = mb_convert_encoding($textoOriginal, 'ISO-8859-1', 'UTF-8');

echo "Texto original (UTF-8): \"$textoOriginal\"\n";
echo "  Bytes UTF-8: " . strlen($textoOriginal) . "\n";
echo "  Bytes ISO-8859-1: " . strlen($textoLatin1) . "\n\n";

$resultado = asegurarUtf8($textoOriginal);
echo "Procesando texto UTF-8:\n";
echo "  Codificación detectada: {$resultado['codificacion_original']}\n";
echo "  ¿Convertido?: " . ($resultado['convertido'] ? 'Sí' : 'No') . "\n\n";

$resultado = asegurarUtf8($textoLatin1);
echo "Procesando texto ISO-8859-1:\n";
echo "  Codificación detectada: {$resultado['codificacion_original']}\n";
echo "  ¿Convertido?: " . ($resultado['convertido'] ? 'Sí' : 'No') . "\n";
echo "  Texto UTF-8: \"{$resultado['texto_utf8']}\"\n\n";

// =============================================================================
// Ejemplo 5: mb_detect_order - Configurar el orden de detección predeterminado
// Establecer las codificaciones que se comprueban por defecto
// =============================================================================

echo "=== Ejemplo 5: mb_detect_order ===\n";

// Obtener el orden de detección actual
$ordenActual = mb_detect_order();
echo "Orden de detección actual:\n";
echo "  " . implode(', ', $ordenActual) . "\n\n";

// Establecer un nuevo orden de detección
mb_detect_order(['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII']);
$nuevoOrden = mb_detect_order();
echo "Nuevo orden de detección:\n";
echo "  " . implode(', ', $nuevoOrden) . "\n\n";

// Ahora mb_detect_encoding sin lista usará el orden configurado
$codDetectada = mb_detect_encoding("Café con piña");
echo "Detección con orden personalizado: $codDetectada\n\n";

// Restaurar el orden original
mb_detect_order($ordenActual);
echo "Orden restaurado: " . implode(', ', mb_detect_order()) . "\n\n";

// =============================================================================
// Ejemplo 6: Validar y sanitizar entrada de usuario
// Uso práctico para aplicaciones web que reciben texto de fuentes diversas
// =============================================================================

echo "=== Ejemplo 6: Validación y sanitización de entrada ===\n";

/**
 * Valida y sanitiza una cadena de texto para asegurar que sea UTF-8 válido.
 *
 * @param string $entrada Texto de entrada potencialmente inseguro
 * @return array ['valido' => bool, 'sanitizado' => string, 'problemas' => array]
 */
function sanitizarEntrada(string $entrada): array
{
    $problemas = [];

    // 1. Verificar si es UTF-8 válido
    if (!mb_check_encoding($entrada, 'UTF-8')) {
        $problemas[] = "La cadena no es UTF-8 válido";

        // Intentar convertir desde la codificación detectada
        $codDetectada = mb_detect_encoding($entrada, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($codDetectada) {
            $entrada = mb_convert_encoding($entrada, 'UTF-8', $codDetectada);
            $problemas[] = "Convertido desde $codDetectada a UTF-8";
        } else {
            // Último recurso: reemplazar bytes inválidos
            $entrada = mb_convert_encoding($entrada, 'UTF-8', 'UTF-8');
            $problemas[] = "Bytes inválidos eliminados";
        }
    }

    // 2. Eliminar caracteres de control (excepto saltos de línea y tabulaciones)
    $longitudAntes = mb_strlen($entrada, 'UTF-8');
    $entrada = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $entrada);
    $longitudDespues = mb_strlen($entrada, 'UTF-8');

    if ($longitudAntes !== $longitudDespues) {
        $eliminados = $longitudAntes - $longitudDespues;
        $problemas[] = "$eliminados caracteres de control eliminados";
    }

    // 3. Normalizar espacios en blanco
    $entrada = preg_replace('/\s+/u', ' ', trim($entrada));

    return [
        'valido'     => empty($problemas),
        'sanitizado' => $entrada,
        'problemas'  => $problemas,
    ];
}

// Probar con diferentes tipos de entrada
$entradas = [
    "Texto normal en español: café con piña",
    "Texto con\x00bytes\x01nulos\x02control",
    "   Espacios   múltiples   y   tabulaciones\t\tvarias   ",
    "Línea válida con acentos: á é í ó ú ñ ü",
];

foreach ($entradas as $entrada) {
    $resultado = sanitizarEntrada($entrada);
    echo "  Entrada: \"" . addcslashes($entrada, "\x00..\x1F") . "\"\n";
    echo "  Salida:  \"{$resultado['sanitizado']}\"\n";
    echo "  Válido:  " . ($resultado['valido'] ? "Sí" : "No") . "\n";
    if (!empty($resultado['problemas'])) {
        echo "  Problemas: " . implode('; ', $resultado['problemas']) . "\n";
    }
    echo "\n";
}

?>
