<?php
// =============================================================================
// mb_strlen() vs strlen() con cadenas UTF-8
// Diferencias cruciales al contar caracteres en cadenas multibyte
// =============================================================================

// =============================================================================
// Ejemplo 1: Diferencia básica entre strlen y mb_strlen
// strlen cuenta bytes, mb_strlen cuenta caracteres
// =============================================================================

echo "=== Ejemplo 1: strlen vs mb_strlen ===\n";

$texto = "café";

// strlen() cuenta BYTES, no caracteres
// La 'é' en UTF-8 ocupa 2 bytes
$bytesStrlen = strlen($texto);

// mb_strlen() cuenta CARACTERES según la codificación especificada
$caracteresMbStrlen = mb_strlen($texto, 'UTF-8');

echo "Texto: \"$texto\"\n";
echo "  strlen():    $bytesStrlen (bytes)\n";
echo "  mb_strlen(): $caracteresMbStrlen (caracteres)\n";
echo "  La 'é' ocupa 2 bytes en UTF-8, por eso strlen da " . ($bytesStrlen - $caracteresMbStrlen) . " más.\n\n";

// =============================================================================
// Ejemplo 2: Caracteres con acentos y caracteres especiales del español
// El español tiene muchos caracteres que ocupan más de 1 byte en UTF-8
// =============================================================================

echo "=== Ejemplo 2: Caracteres especiales del español ===\n";

$palabras = [
    "hola"       => "sin acentos",
    "canción"    => "con ó",
    "niño"       => "con ñ",
    "pingüino"   => "con ü",
    "¡Hola!"     => "con signos de exclamación invertidos",
    "¿Cómo estás?" => "con signos de interrogación invertidos y acentos",
];

echo str_pad("Palabra", 20) . str_pad("strlen", 10) . str_pad("mb_strlen", 10) . "Nota\n";
echo str_repeat("-", 60) . "\n";

foreach ($palabras as $palabra => $nota) {
    echo str_pad("\"$palabra\"", 20);
    echo str_pad((string)strlen($palabra), 10);
    echo str_pad((string)mb_strlen($palabra, 'UTF-8'), 10);
    echo $nota . "\n";
}
echo "\n";

// =============================================================================
// Ejemplo 3: Emojis y caracteres Unicode de varios bytes
// Los emojis pueden ocupar 4 bytes cada uno en UTF-8
// =============================================================================

echo "=== Ejemplo 3: Emojis (caracteres de 4 bytes) ===\n";

$textos = [
    "Hello"         => "Solo ASCII",
    "Hola 🌍"      => "Con emoji de globo",
    "👨‍👩‍👧‍👦"         => "Familia (emoji compuesto con ZWJ)",
    "🇪🇸"          => "Bandera de España (2 indicadores regionales)",
    "café ☕"       => "Mezcla de texto y emoji",
    "🎉🎊🎈🎁"    => "Solo emojis",
];

foreach ($textos as $texto => $descripcion) {
    $bytes = strlen($texto);
    $caracteres = mb_strlen($texto, 'UTF-8');

    echo "\"$texto\" ($descripcion):\n";
    echo "  Bytes: $bytes | Caracteres MB: $caracteres\n";
}
echo "\n";

// =============================================================================
// Ejemplo 4: Caracteres japoneses (hiragana, katakana, kanji)
// Cada carácter japonés ocupa 3 bytes en UTF-8
// =============================================================================

echo "=== Ejemplo 4: Caracteres japoneses ===\n";

$japones = [
    "こんにちは"       => "Hiragana (konnichiwa)",
    "カタカナ"         => "Katakana",
    "東京タワー"       => "Kanji + Katakana (Torre de Tokio)",
    "日本語"           => "Kanji (nihongo = japonés)",
    "Hello世界"        => "Mezcla ASCII + Kanji",
];

foreach ($japones as $texto => $descripcion) {
    $bytes = strlen($texto);
    $caracteres = mb_strlen($texto, 'UTF-8');
    $bytesPromedio = round($bytes / max($caracteres, 1), 1);

    echo "\"$texto\" ($descripcion):\n";
    echo "  Bytes: $bytes | Caracteres: $caracteres | Bytes/carácter: $bytesPromedio\n";
}
echo "\n";

// =============================================================================
// Ejemplo 5: Validación de longitud de campos con mb_strlen
// Uso práctico para validar formularios con texto Unicode
// =============================================================================

echo "=== Ejemplo 5: Validación de longitud de campos ===\n";

/**
 * Valida la longitud de un campo de texto multibyte.
 *
 * @param string $valor    Valor del campo
 * @param int    $minimo   Longitud mínima en caracteres
 * @param int    $maximo   Longitud máxima en caracteres
 * @param string $campo    Nombre del campo (para mensajes de error)
 * @return array ['valido' => bool, 'mensaje' => string, 'longitud' => int]
 */
function validarLongitud(string $valor, int $minimo, int $maximo, string $campo): array
{
    // IMPORTANTE: usar mb_strlen para contar caracteres reales, no bytes
    $longitud = mb_strlen($valor, 'UTF-8');

    if ($longitud < $minimo) {
        return [
            'valido'   => false,
            'mensaje'  => "$campo debe tener al menos $minimo caracteres (tiene $longitud).",
            'longitud' => $longitud,
        ];
    }

    if ($longitud > $maximo) {
        return [
            'valido'   => false,
            'mensaje'  => "$campo no puede exceder $maximo caracteres (tiene $longitud).",
            'longitud' => $longitud,
        ];
    }

    return [
        'valido'   => true,
        'mensaje'  => "$campo es válido ($longitud caracteres).",
        'longitud' => $longitud,
    ];
}

// Probar validaciones con distintos textos
$pruebas = [
    ['valor' => 'Ana',                  'min' => 2,  'max' => 50,  'campo' => 'Nombre'],
    ['valor' => 'María José García',    'min' => 2,  'max' => 50,  'campo' => 'Nombre'],
    ['valor' => 'A',                    'min' => 2,  'max' => 50,  'campo' => 'Nombre'],
    ['valor' => '🎉🎊🎈',             'min' => 1,  'max' => 5,   'campo' => 'Reacción'],
    ['valor' => 'こんにちは世界',       'min' => 1,  'max' => 10,  'campo' => 'Mensaje'],
    ['valor' => str_repeat('あ', 100),  'min' => 1,  'max' => 50,  'campo' => 'Comentario'],
];

foreach ($pruebas as $prueba) {
    $resultado = validarLongitud($prueba['valor'], $prueba['min'], $prueba['max'], $prueba['campo']);
    $icono = $resultado['valido'] ? '[OK]' : '[FAIL]';
    $textoCorto = mb_substr($prueba['valor'], 0, 20, 'UTF-8');
    echo "  $icono \"$textoCorto\" -> {$resultado['mensaje']}\n";
}
echo "\n";

// =============================================================================
// Ejemplo 6: Tabla comparativa de bytes por carácter en diferentes scripts
// Visualizar cuánto espacio ocupa cada sistema de escritura en UTF-8
// =============================================================================

echo "=== Ejemplo 6: Bytes por carácter en diferentes scripts ===\n";

$scripts = [
    'ASCII (inglés)'   => 'Hello World',
    'Latín (español)'  => 'Ñoño café',
    'Griego'           => 'Ελληνικά',
    'Cirílico (ruso)'  => 'Привет мир',
    'Árabe'            => 'مرحبا بالعالم',
    'Hindi (devanagari)' => 'नमस्ते दुनिया',
    'Chino simplificado' => '你好世界',
    'Japonés (kanji)'  => '東京タワー',
    'Coreano (hangul)' => '안녕하세요',
    'Emojis'           => '😀🎉🌍❤️🚀',
];

echo str_pad("Script", 22) . str_pad("Texto", 18) . str_pad("Bytes", 8) . str_pad("Chars", 8) . "B/C\n";
echo str_repeat("-", 65) . "\n";

foreach ($scripts as $nombre => $texto) {
    $bytes = strlen($texto);
    $chars = mb_strlen($texto, 'UTF-8');
    $ratio = round($bytes / max($chars, 1), 1);

    echo str_pad($nombre, 22);
    echo str_pad($texto, 18);
    echo str_pad((string)$bytes, 8);
    echo str_pad((string)$chars, 8);
    echo "$ratio\n";
}

?>
