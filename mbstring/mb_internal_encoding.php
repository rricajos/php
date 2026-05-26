<?php
// =============================================================================
// mb_internal_encoding() y mb_regex_encoding()
// Configurar la codificación interna predeterminada para funciones mbstring
// =============================================================================

// =============================================================================
// Ejemplo 1: Obtener y establecer la codificación interna
// mb_internal_encoding() controla la codificación usada por defecto
// =============================================================================

echo "=== Ejemplo 1: Obtener y establecer codificación interna ===\n";

// Sin parámetros, devuelve la codificación interna actual
$codActual = mb_internal_encoding();
echo "Codificación interna actual: $codActual\n\n";

// Establecer una nueva codificación interna
$exito = mb_internal_encoding('UTF-8');
echo "Establecer UTF-8: " . ($exito ? 'Éxito' : 'Fallo') . "\n";
echo "Codificación actual: " . mb_internal_encoding() . "\n\n";

// Con la codificación interna establecida, no es necesario pasar
// el parámetro de codificación en cada llamada mb_*
$texto = "Café con piña";
echo "Texto: \"$texto\"\n";

// Sin especificar codificación (usa la interna configurada)
echo "  mb_strlen(): " . mb_strlen($texto) . " caracteres\n";
echo "  mb_substr(0, 4): \"" . mb_substr($texto, 0, 4) . "\"\n";
echo "  mb_strtoupper(): \"" . mb_strtoupper($texto) . "\"\n\n";

// =============================================================================
// Ejemplo 2: Efecto de cambiar la codificación interna
// Demostrar cómo afecta a las funciones mb_* cuando no se especifica
// =============================================================================

echo "=== Ejemplo 2: Efecto de cambiar la codificación interna ===\n";

$texto = "Información"; // La 'ó' es multibyte en UTF-8

// Con UTF-8 como codificación interna
mb_internal_encoding('UTF-8');
echo "Con UTF-8 interno:\n";
echo "  mb_strlen('$texto'): " . mb_strlen($texto) . " caracteres\n";
echo "  mb_strpos('$texto', 'ción'): " . mb_strpos($texto, 'ción') . "\n\n";

// Cambiar a ISO-8859-1 (los bytes UTF-8 se interpretarán diferente)
mb_internal_encoding('ISO-8859-1');
echo "Con ISO-8859-1 interno (¡interpretación incorrecta de UTF-8!):\n";
echo "  mb_strlen('$texto'): " . mb_strlen($texto) . " (cuenta bytes, no caracteres)\n";
echo "  ADVERTENCIA: Resultado incorrecto si el texto es realmente UTF-8.\n\n";

// Restaurar a UTF-8
mb_internal_encoding('UTF-8');
echo "Restaurado a UTF-8: " . mb_internal_encoding() . "\n\n";

// =============================================================================
// Ejemplo 3: Buena práctica - Configurar al inicio de la aplicación
// Establecer la codificación interna una sola vez al inicio
// =============================================================================

echo "=== Ejemplo 3: Configuración recomendada al inicio ===\n";

/**
 * Configurar las opciones de mbstring al inicio de la aplicación.
 * Llamar esta función al comienzo del script o en el bootstrap.
 */
function configurarMbstring(): void
{
    // Establecer UTF-8 como codificación interna predeterminada
    mb_internal_encoding('UTF-8');

    // Establecer la codificación para funciones de expresiones regulares mb
    mb_regex_encoding('UTF-8');

    // Establecer el idioma para funciones específicas de idioma
    // Afecta a mb_send_mail() y mb_encode_mimeheader()
    mb_language('uni'); // 'uni' = Unicode/UTF-8

    // Establecer el carácter de sustitución para caracteres no convertibles
    // '?' = signo de interrogación, 'none' = omitir, o un valor Unicode
    mb_substitute_character('none');
}

configurarMbstring();

echo "Configuración de mbstring aplicada:\n";
echo "  Codificación interna:  " . mb_internal_encoding() . "\n";
echo "  Codificación regex:    " . mb_regex_encoding() . "\n";
echo "  Idioma:                " . mb_language() . "\n";
echo "  Carácter sustitución:  " . mb_substitute_character() . "\n\n";

// Ahora todas las funciones mb_* usan UTF-8 sin necesidad de especificarlo
$prueba = "España: ñoño café 🎉";
echo "Funciones mb_* sin parámetro de codificación:\n";
echo "  mb_strlen():      " . mb_strlen($prueba) . "\n";
echo "  mb_strtoupper():  " . mb_strtoupper($prueba) . "\n";
echo "  mb_substr(0, 7):  " . mb_substr($prueba, 0, 7) . "\n\n";

// =============================================================================
// Ejemplo 4: mb_regex_encoding y funciones de regex multibyte
// Expresiones regulares con soporte para caracteres multibyte
// =============================================================================

echo "=== Ejemplo 4: mb_regex_encoding y regex multibyte ===\n";

// Obtener la codificación de regex actual
$codRegex = mb_regex_encoding();
echo "Codificación regex actual: $codRegex\n\n";

// Asegurar UTF-8 para regex
mb_regex_encoding('UTF-8');

// mb_ereg: versión multibyte de ereg
$texto = "El niño comió una piña en España";
echo "Texto: \"$texto\"\n\n";

// Buscar palabras con ñ usando mb_ereg
if (mb_ereg('(\w*ñ\w*)', $texto, $coincidencias)) {
    echo "Primera palabra con ñ: \"" . $coincidencias[1] . "\"\n";
}

// mb_ereg_search: búsqueda iterativa
mb_ereg_search_init($texto, '\w*[ñáéíóú]\w*');
echo "Palabras con caracteres acentuados o ñ:\n";

while ($resultado = mb_ereg_search_regs()) {
    echo "  \"" . $resultado[0] . "\"\n";
}
echo "\n";

// mb_ereg_replace: reemplazo con regex multibyte
$textoOriginal = "El café de la mañana es el mejor café";
$reemplazado = mb_ereg_replace('café', 'TÉ', $textoOriginal);
echo "Original:    \"$textoOriginal\"\n";
echo "Reemplazado: \"$reemplazado\"\n\n";

// =============================================================================
// Ejemplo 5: mb_http_output y mb_http_input
// Configurar la codificación para entrada/salida HTTP
// =============================================================================

echo "=== Ejemplo 5: Codificación HTTP de entrada/salida ===\n";

// mb_http_output: codificación usada para la salida HTTP
// Útil en aplicaciones web para convertir la salida automáticamente
echo "Codificación de salida HTTP actual: " . mb_http_output() . "\n";

// Establecer la codificación de salida HTTP
mb_http_output('UTF-8');
echo "Codificación de salida HTTP configurada: " . mb_http_output() . "\n\n";

// mb_http_input: detecta la codificación de la entrada HTTP
// Solo funciona en contexto web con datos de formularios
$codEntrada = mb_http_input('G'); // 'G' = GET, 'P' = POST, 'C' = COOKIE
echo "Codificación de entrada HTTP (GET): " . ($codEntrada ?: 'No disponible (no hay contexto web)') . "\n\n";

// Ejemplo de cómo configurar un callback de conversión de salida
echo "Ejemplo de configuración para aplicación web:\n";
echo "  // Al inicio de la aplicación:\n";
echo "  // mb_internal_encoding('UTF-8');\n";
echo "  // mb_http_output('UTF-8');\n";
echo "  // ob_start('mb_output_handler'); // Convierte salida automáticamente\n\n";

// =============================================================================
// Ejemplo 6: mb_list_encodings y mb_encoding_aliases
// Listar todas las codificaciones disponibles y sus alias
// =============================================================================

echo "=== Ejemplo 6: Codificaciones disponibles ===\n";

// Obtener la lista completa de codificaciones soportadas
$codificaciones = mb_list_encodings();
echo "Total de codificaciones soportadas: " . count($codificaciones) . "\n\n";

// Agrupar por categoría
$categorias = [
    'Unicode'  => [],
    'ISO-8859' => [],
    'Windows'  => [],
    'Asiáticas' => [],
    'Otras'    => [],
];

foreach ($codificaciones as $cod) {
    if (stripos($cod, 'UTF') !== false || stripos($cod, 'UCS') !== false || $cod === 'Unicode') {
        $categorias['Unicode'][] = $cod;
    } elseif (stripos($cod, 'ISO-8859') !== false) {
        $categorias['ISO-8859'][] = $cod;
    } elseif (stripos($cod, 'Windows') !== false || stripos($cod, 'CP') === 0) {
        $categorias['Windows'][] = $cod;
    } elseif (in_array($cod, ['SJIS', 'EUC-JP', 'JIS', 'GB2312', 'BIG-5', 'EUC-KR', 'HZ', 'GB18030',
                              'SJIS-win', 'EUC-JP-2004', 'SJIS-2004', 'SJIS-Mobile#DOCOMO',
                              'SJIS-Mobile#KDDI', 'SJIS-Mobile#SOFTBANK',
                              'UTF-8-Mobile#DOCOMO', 'UTF-8-Mobile#KDDI-A',
                              'UTF-8-Mobile#KDDI-B', 'UTF-8-Mobile#SOFTBANK',
                              'ISO-2022-JP', 'ISO-2022-JP-MS', 'ISO-2022-KR',
                              'EUC-CN', 'EUC-TW', 'CP51932', 'CP50220', 'CP50221', 'CP50222'])) {
        $categorias['Asiáticas'][] = $cod;
    } else {
        $categorias['Otras'][] = $cod;
    }
}

foreach ($categorias as $categoria => $codList) {
    if (!empty($codList)) {
        echo "$categoria (" . count($codList) . "):\n";
        echo "  " . implode(', ', array_slice($codList, 0, 8));
        if (count($codList) > 8) {
            echo "... (+" . (count($codList) - 8) . " más)";
        }
        echo "\n";
    }
}
echo "\n";

// Obtener alias de una codificación
$codificacionesConAlias = ['UTF-8', 'ISO-8859-1', 'SJIS', 'ASCII'];
echo "Alias de codificaciones comunes:\n";
foreach ($codificacionesConAlias as $cod) {
    $aliases = mb_encoding_aliases($cod);
    if ($aliases !== false && !empty($aliases)) {
        echo "  $cod: " . implode(', ', array_slice($aliases, 0, 5)) . "\n";
    } else {
        echo "  $cod: (sin alias)\n";
    }
}

?>
