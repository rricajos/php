<?php
// =============================================================================
// mb_substr() - Extraer subcadenas de texto multibyte (UTF-8)
// Manejo seguro de subcadenas con caracteres Unicode y emojis
// =============================================================================

// =============================================================================
// Ejemplo 1: Diferencia entre substr y mb_substr
// substr puede cortar caracteres multibyte por la mitad
// =============================================================================

echo "=== Ejemplo 1: substr vs mb_substr ===\n";

$texto = "Información técnica";

// substr() trabaja con bytes, puede cortar caracteres a la mitad
$substrResultado = substr($texto, 0, 10);

// mb_substr() trabaja con caracteres, respeta los límites multibyte
$mbSubstrResultado = mb_substr($texto, 0, 10, 'UTF-8');

echo "Texto original: \"$texto\"\n";
echo "  substr(0, 10):    \"$substrResultado\" (puede mostrar caracteres rotos)\n";
echo "  mb_substr(0, 10): \"$mbSubstrResultado\" (caracteres intactos)\n\n";

// Otro ejemplo más claro con caracteres de 2+ bytes
$texto2 = "café latte";
echo "Texto: \"$texto2\"\n";
echo "  substr(0, 4):    \"" . substr($texto2, 0, 4) . "\" (corta la 'é' por la mitad)\n";
echo "  mb_substr(0, 4): \"" . mb_substr($texto2, 0, 4, 'UTF-8') . "\" (respeta la 'é')\n\n";

// =============================================================================
// Ejemplo 2: Extraer subcadenas con diferentes posiciones y longitudes
// mb_substr(string, inicio, longitud, codificación)
// =============================================================================

echo "=== Ejemplo 2: Posiciones y longitudes ===\n";

$frase = "¡Hola, mundo maravilloso!";
echo "Frase: \"$frase\" (" . mb_strlen($frase, 'UTF-8') . " caracteres)\n\n";

// Desde el inicio
echo "  mb_substr(frase, 0, 5):  \"" . mb_substr($frase, 0, 5, 'UTF-8') . "\"  (primeros 5)\n";

// Desde una posición intermedia
echo "  mb_substr(frase, 7, 5):  \"" . mb_substr($frase, 7, 5, 'UTF-8') . "\"  (5 desde posición 7)\n";

// Sin longitud: desde la posición hasta el final
echo "  mb_substr(frase, 7):     \"" . mb_substr($frase, 7, null, 'UTF-8') . "\"  (desde posición 7 al final)\n";

// Posición negativa: contar desde el final
echo "  mb_substr(frase, -12):   \"" . mb_substr($frase, -12, null, 'UTF-8') . "\"  (últimos 12)\n";

// Posición negativa con longitud
echo "  mb_substr(frase, -12, 6): \"" . mb_substr($frase, -12, 6, 'UTF-8') . "\"  (6 desde -12)\n";

// Longitud negativa: excluir los últimos N caracteres
echo "  mb_substr(frase, 1, -1): \"" . mb_substr($frase, 1, -1, 'UTF-8') . "\"  (sin primero ni último)\n";
echo "\n";

// =============================================================================
// Ejemplo 3: Manejo de emojis con mb_substr
// Los emojis son caracteres de 4 bytes que se deben tratar como unidades
// =============================================================================

echo "=== Ejemplo 3: Manejo de emojis ===\n";

$mensajeEmoji = "Hola 🌍🎉🚀 mundo";
echo "Mensaje: \"$mensajeEmoji\"\n";
echo "Longitud: " . mb_strlen($mensajeEmoji, 'UTF-8') . " caracteres\n\n";

// Extraer solo los emojis
$emojis = mb_substr($mensajeEmoji, 5, 3, 'UTF-8');
echo "Solo emojis (posición 5, longitud 3): \"$emojis\"\n";

// Texto antes de los emojis
$antes = mb_substr($mensajeEmoji, 0, 5, 'UTF-8');
echo "Antes de emojis: \"$antes\"\n";

// Texto después de los emojis
$despues = mb_substr($mensajeEmoji, 9, null, 'UTF-8');
echo "Después de emojis: \"$despues\"\n\n";

// Serie de emojis
$emojis = "🍎🍊🍋🍇🍓🫐🍑🍒";
echo "Frutas: \"$emojis\" (" . mb_strlen($emojis, 'UTF-8') . " caracteres)\n";
echo "  Primeras 3: \"" . mb_substr($emojis, 0, 3, 'UTF-8') . "\"\n";
echo "  Últimas 3:  \"" . mb_substr($emojis, -3, null, 'UTF-8') . "\"\n";
echo "  Del medio:  \"" . mb_substr($emojis, 2, 4, 'UTF-8') . "\"\n\n";

// =============================================================================
// Ejemplo 4: Truncar texto con puntos suspensivos (función práctica)
// Útil para mostrar resúmenes, vistas previas, etc.
// =============================================================================

echo "=== Ejemplo 4: Truncar texto con puntos suspensivos ===\n";

/**
 * Trunca un texto a una longitud máxima, añadiendo un sufijo si se recorta.
 *
 * @param string $texto    Texto a truncar
 * @param int    $maximo   Longitud máxima en caracteres (incluyendo el sufijo)
 * @param string $sufijo   Texto a añadir si se trunca (por defecto "...")
 * @return string Texto truncado
 */
function truncarTexto(string $texto, int $maximo = 50, string $sufijo = '...'): string
{
    $longitud = mb_strlen($texto, 'UTF-8');

    // Si el texto ya es menor o igual al máximo, devolverlo tal cual
    if ($longitud <= $maximo) {
        return $texto;
    }

    $longitudSufijo = mb_strlen($sufijo, 'UTF-8');
    $longitudCorte = $maximo - $longitudSufijo;

    // Asegurar que la longitud de corte sea al menos 1
    if ($longitudCorte < 1) {
        return mb_substr($sufijo, 0, $maximo, 'UTF-8');
    }

    return mb_substr($texto, 0, $longitudCorte, 'UTF-8') . $sufijo;
}

$textos = [
    "Hola",
    "Este es un texto más largo que necesita ser truncado para caber en un espacio limitado",
    "Programación en español con ñ, acentos á é í ó ú y más caracteres especiales",
    "🎉🎊🎈🎁🎆🎇✨🎏🎐🎑 fiesta de emojis",
    "日本語のテキストも正しく切り詰められます",
];

foreach ($textos as $texto) {
    $truncado = truncarTexto($texto, 30);
    $longOriginal = mb_strlen($texto, 'UTF-8');
    $longTruncado = mb_strlen($truncado, 'UTF-8');

    echo "  Original ($longOriginal): \"$texto\"\n";
    echo "  Truncado ($longTruncado): \"$truncado\"\n\n";
}

// =============================================================================
// Ejemplo 5: Extraer palabras y frases con mb_substr
// Operaciones comunes de manipulación de texto
// =============================================================================

echo "=== Ejemplo 5: Manipulación práctica de texto ===\n";

// Obtener las iniciales de un nombre
function obtenerIniciales(string $nombre): string
{
    $partes = explode(' ', trim($nombre));
    $iniciales = '';

    foreach ($partes as $parte) {
        if (mb_strlen($parte, 'UTF-8') > 0) {
            $iniciales .= mb_substr($parte, 0, 1, 'UTF-8');
        }
    }

    return mb_strtoupper($iniciales, 'UTF-8');
}

$nombres = [
    'María José García López',
    'José Ángel Ñoño',
    'Óscar Álvarez',
    'Ça Çelik',  // Nombre turco con cedilla
];

echo "Iniciales:\n";
foreach ($nombres as $nombre) {
    echo "  \"$nombre\" -> " . obtenerIniciales($nombre) . "\n";
}
echo "\n";

// Capitalizar la primera letra de cada palabra (respetando multibyte)
function capitalizarPalabras(string $texto): string
{
    $palabras = explode(' ', $texto);
    $resultado = [];

    foreach ($palabras as $palabra) {
        if (mb_strlen($palabra, 'UTF-8') > 0) {
            $primera = mb_strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'), 'UTF-8');
            $resto = mb_substr($palabra, 1, null, 'UTF-8');
            $resultado[] = $primera . $resto;
        }
    }

    return implode(' ', $resultado);
}

$frases = [
    "café con leche",
    "ñoño y compañía",
    "über cool técnica",
];

echo "Capitalizar palabras:\n";
foreach ($frases as $frase) {
    echo "  \"$frase\" -> \"" . capitalizarPalabras($frase) . "\"\n";
}
echo "\n";

// =============================================================================
// Ejemplo 6: Dividir texto en líneas de longitud fija (word wrap multibyte)
// Útil para formatear texto en consola o interfaces de ancho fijo
// =============================================================================

echo "=== Ejemplo 6: Word wrap multibyte ===\n";

/**
 * Divide un texto en líneas de longitud máxima, respetando caracteres multibyte.
 *
 * @param string $texto     Texto a dividir
 * @param int    $ancho     Ancho máximo por línea en caracteres
 * @param string $separador Separador de líneas
 * @return string Texto dividido en líneas
 */
function mbWordWrap(string $texto, int $ancho = 40, string $separador = "\n"): string
{
    $longitud = mb_strlen($texto, 'UTF-8');

    if ($longitud <= $ancho) {
        return $texto;
    }

    $lineas = [];
    $posicion = 0;

    while ($posicion < $longitud) {
        $linea = mb_substr($texto, $posicion, $ancho, 'UTF-8');
        $lineas[] = $linea;
        $posicion += $ancho;
    }

    return implode($separador, $lineas);
}

$textoLargo = "La programación en español requiere soporte para caracteres como ñ, á, é, í, ó, ú y los signos ¿¡ que son únicos del idioma.";

echo "Texto original:\n\"$textoLargo\"\n\n";
echo "Dividido en líneas de 40 caracteres:\n";
$dividido = mbWordWrap($textoLargo, 40);
$lineas = explode("\n", $dividido);
foreach ($lineas as $i => $linea) {
    $num = $i + 1;
    $long = mb_strlen($linea, 'UTF-8');
    echo "  Línea $num ($long chars): \"$linea\"\n";
}

?>
