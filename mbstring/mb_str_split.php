<?php
// =============================================================================
// mb_str_split() - Dividir cadenas UTF-8 en caracteres individuales (PHP 7.4+)
// Alternativa multibyte a str_split para cadenas Unicode
// =============================================================================

// =============================================================================
// Ejemplo 1: Diferencia entre str_split y mb_str_split
// str_split divide por bytes, mb_str_split por caracteres
// =============================================================================

echo "=== Ejemplo 1: str_split vs mb_str_split ===\n";

$texto = "café";

// str_split divide por bytes (rompe caracteres multibyte)
$porBytes = str_split($texto);
echo "str_split('$texto'):\n  ";
foreach ($porBytes as $i => $byte) {
    echo "[$i]='$byte' ";
}
echo "\n  Total: " . count($porBytes) . " elementos (bytes)\n\n";

// mb_str_split divide por caracteres (respeta multibyte)
$porCaracteres = mb_str_split($texto, 1, 'UTF-8');
echo "mb_str_split('$texto'):\n  ";
foreach ($porCaracteres as $i => $char) {
    echo "[$i]='$char' ";
}
echo "\n  Total: " . count($porCaracteres) . " elementos (caracteres)\n\n";

// =============================================================================
// Ejemplo 2: Dividir texto con emojis y caracteres especiales
// Cada emoji se trata como un carácter independiente
// =============================================================================

echo "=== Ejemplo 2: Emojis y caracteres especiales ===\n";

$textos = [
    "Hola 🌍"          => "Texto con emoji",
    "🍎🍊🍋🍇"        => "Solo emojis",
    "café ☕ piña 🍍"  => "Mezcla de texto y emojis",
    "日本語テスト"      => "Caracteres japoneses",
    "Ñoño España"      => "Caracteres españoles",
];

foreach ($textos as $texto => $descripcion) {
    $caracteres = mb_str_split($texto, 1, 'UTF-8');
    echo "$descripcion: \"$texto\"\n";
    echo "  Caracteres (" . count($caracteres) . "): ";
    echo "['" . implode("', '", $caracteres) . "']\n\n";
}

// =============================================================================
// Ejemplo 3: Dividir en fragmentos de N caracteres
// mb_str_split con longitud mayor a 1 para crear fragmentos
// =============================================================================

echo "=== Ejemplo 3: Fragmentos de N caracteres ===\n";

$texto = "Programación en español con caracteres especiales como ñ y acentos";
echo "Texto: \"$texto\"\n";
echo "Longitud: " . mb_strlen($texto, 'UTF-8') . " caracteres\n\n";

// Dividir en fragmentos de 10 caracteres
$fragmentos = mb_str_split($texto, 10, 'UTF-8');
echo "Fragmentos de 10 caracteres:\n";
foreach ($fragmentos as $i => $fragmento) {
    $long = mb_strlen($fragmento, 'UTF-8');
    echo "  [$i] \"$fragmento\" ($long chars)\n";
}
echo "\n";

// Dividir emojis en pares
$emojis = "🍎🍊🍋🍇🍓🫐🍑🍒";
$pares = mb_str_split($emojis, 2, 'UTF-8');
echo "Emojis en pares:\n";
foreach ($pares as $par) {
    echo "  \"$par\"\n";
}
echo "\n";

// =============================================================================
// Ejemplo 4: Invertir una cadena multibyte
// No se puede usar strrev con UTF-8, necesitamos mb_str_split
// =============================================================================

echo "=== Ejemplo 4: Invertir cadenas multibyte ===\n";

/**
 * Invierte una cadena multibyte respetando los caracteres Unicode.
 *
 * @param string $texto Texto a invertir
 * @return string Texto invertido
 */
function mbStrRev(string $texto): string
{
    $caracteres = mb_str_split($texto, 1, 'UTF-8');
    return implode('', array_reverse($caracteres));
}

$pruebas = [
    "Hola",
    "café",
    "ñoño",
    "🍎🍊🍋",
    "日本語",
    "¡Hola!",
    "palindromo",
];

echo str_pad("Original", 20) . str_pad("strrev (bytes)", 20) . "mb_str_rev (chars)\n";
echo str_repeat("-", 60) . "\n";

foreach ($pruebas as $texto) {
    echo str_pad("\"$texto\"", 20);
    echo str_pad("\"" . strrev($texto) . "\"", 20);  // Puede romper UTF-8
    echo "\"" . mbStrRev($texto) . "\"\n";
}
echo "\n";

// Verificar palíndromos multibyte
function esPalindromo(string $texto): bool
{
    $limpio = mb_strtolower(preg_replace('/\s+/u', '', $texto), 'UTF-8');
    return $limpio === mbStrRev($limpio);
}

$candidatos = ["aba", "reconocer", "oso", "café", "ana", "あいあ"];
echo "Verificación de palíndromos:\n";
foreach ($candidatos as $c) {
    $resultado = esPalindromo($c) ? "Sí" : "No";
    echo "  \"$c\" -> $resultado\n";
}
echo "\n";

// =============================================================================
// Ejemplo 5: Análisis de caracteres en un texto
// Contar tipos de caracteres, frecuencias, etc.
// =============================================================================

echo "=== Ejemplo 5: Análisis de caracteres ===\n";

/**
 * Analiza la composición de caracteres de un texto UTF-8.
 *
 * @param string $texto Texto a analizar
 * @return array Estadísticas del texto
 */
function analizarTexto(string $texto): array
{
    $caracteres = mb_str_split($texto, 1, 'UTF-8');
    $estadisticas = [
        'total'       => count($caracteres),
        'ascii'       => 0,
        'acentuados'  => 0,
        'espacios'    => 0,
        'digitos'     => 0,
        'emojis'      => 0,
        'otros'       => 0,
        'frecuencias' => [],
    ];

    $acentuados = 'áéíóúàèìòùâêîôûäëïöüñçÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÄËÏÖÜÑÇ';
    $listaAcentuados = mb_str_split($acentuados, 1, 'UTF-8');

    foreach ($caracteres as $char) {
        // Contar frecuencia
        $charLower = mb_strtolower($char, 'UTF-8');
        if (!isset($estadisticas['frecuencias'][$charLower])) {
            $estadisticas['frecuencias'][$charLower] = 0;
        }
        $estadisticas['frecuencias'][$charLower]++;

        // Clasificar
        $ord = mb_ord($char, 'UTF-8');

        if ($char === ' ' || $char === "\t" || $char === "\n") {
            $estadisticas['espacios']++;
        } elseif ($ord >= 0x30 && $ord <= 0x39) {
            $estadisticas['digitos']++;
        } elseif (in_array($char, $listaAcentuados)) {
            $estadisticas['acentuados']++;
        } elseif ($ord >= 0x20 && $ord <= 0x7E) {
            $estadisticas['ascii']++;
        } elseif ($ord >= 0x1F600) {
            $estadisticas['emojis']++;
        } else {
            $estadisticas['otros']++;
        }
    }

    // Ordenar frecuencias de mayor a menor
    arsort($estadisticas['frecuencias']);

    return $estadisticas;
}

$texto = "¡Hola España! El café 🌍 es maravilloso. Año 2024.";
echo "Texto: \"$texto\"\n\n";

$stats = analizarTexto($texto);
echo "Composición:\n";
echo "  Total:      {$stats['total']} caracteres\n";
echo "  ASCII:      {$stats['ascii']}\n";
echo "  Acentuados: {$stats['acentuados']}\n";
echo "  Espacios:   {$stats['espacios']}\n";
echo "  Dígitos:    {$stats['digitos']}\n";
echo "  Emojis:     {$stats['emojis']}\n";
echo "  Otros:      {$stats['otros']}\n\n";

echo "Top 10 caracteres más frecuentes:\n";
$top10 = array_slice($stats['frecuencias'], 0, 10, true);
foreach ($top10 as $char => $count) {
    $display = ($char === ' ') ? '(espacio)' : "\"$char\"";
    echo "  $display: $count veces\n";
}
echo "\n";

// =============================================================================
// Ejemplo 6: Función de mezcla aleatoria de caracteres (shuffle multibyte)
// Y otras operaciones que requieren mb_str_split
// =============================================================================

echo "=== Ejemplo 6: Operaciones con mb_str_split ===\n";

/**
 * Mezcla aleatoriamente los caracteres de una cadena multibyte.
 */
function mbStrShuffle(string $texto): string
{
    $caracteres = mb_str_split($texto, 1, 'UTF-8');
    shuffle($caracteres);
    return implode('', $caracteres);
}

/**
 * Obtener caracteres únicos de un texto.
 */
function mbCaracteresUnicos(string $texto): string
{
    $caracteres = mb_str_split($texto, 1, 'UTF-8');
    return implode('', array_unique($caracteres));
}

/**
 * Intercalar dos cadenas multibyte carácter a carácter.
 */
function mbIntercalar(string $a, string $b): string
{
    $charsA = mb_str_split($a, 1, 'UTF-8');
    $charsB = mb_str_split($b, 1, 'UTF-8');
    $resultado = '';
    $max = max(count($charsA), count($charsB));

    for ($i = 0; $i < $max; $i++) {
        if (isset($charsA[$i])) $resultado .= $charsA[$i];
        if (isset($charsB[$i])) $resultado .= $charsB[$i];
    }

    return $resultado;
}

// Mezcla aleatoria
echo "Mezcla aleatoria:\n";
$textoOriginal = "España";
for ($i = 0; $i < 3; $i++) {
    echo "  \"$textoOriginal\" -> \"" . mbStrShuffle($textoOriginal) . "\"\n";
}
echo "\n";

// Caracteres únicos
echo "Caracteres únicos:\n";
$textoRepetido = "programación avanzada con ñ";
$unicos = mbCaracteresUnicos($textoRepetido);
echo "  \"$textoRepetido\"\n";
echo "  Únicos: \"$unicos\" (" . mb_strlen($unicos, 'UTF-8') . " caracteres)\n\n";

// Intercalar
echo "Intercalar cadenas:\n";
$resultado = mbIntercalar("café", "🍎🍊🍋🍇");
echo "  \"café\" + \"🍎🍊🍋🍇\" = \"$resultado\"\n\n";

// Ofuscar texto (reemplazar caracteres centrales con asteriscos)
function mbOfuscar(string $texto, int $visibles = 2): string
{
    $chars = mb_str_split($texto, 1, 'UTF-8');
    $total = count($chars);

    if ($total <= $visibles * 2) {
        return str_repeat('*', $total);
    }

    $resultado = '';
    for ($i = 0; $i < $total; $i++) {
        $resultado .= ($i < $visibles || $i >= $total - $visibles) ? $chars[$i] : '*';
    }

    return $resultado;
}

echo "Ofuscar texto:\n";
$datosPersonales = ["María García", "correo@ejemplo.com", "こんにちは", "🎉🎊🎈🎁🎆"];
foreach ($datosPersonales as $dato) {
    echo "  \"$dato\" -> \"" . mbOfuscar($dato) . "\"\n";
}

?>
