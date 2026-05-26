<?php
// =============================================================================
// mb_strpos() y mb_strrpos() - Búsqueda de posiciones en cadenas multibyte
// Encontrar la posición de subcadenas en texto UTF-8
// =============================================================================

// =============================================================================
// Ejemplo 1: Diferencia entre strpos y mb_strpos
// strpos devuelve la posición en bytes, mb_strpos en caracteres
// =============================================================================

echo "=== Ejemplo 1: strpos vs mb_strpos ===\n";

$texto = "Información sobre diseño";

// Buscar la palabra "sobre"
$posStrpos = strpos($texto, "sobre");
$posMbStrpos = mb_strpos($texto, "sobre", 0, 'UTF-8');

echo "Texto: \"$texto\"\n";
echo "Buscando \"sobre\":\n";
echo "  strpos():    posición $posStrpos (en bytes)\n";
echo "  mb_strpos(): posición $posMbStrpos (en caracteres)\n";
echo "  La diferencia se debe a que 'ó' ocupa 2 bytes en UTF-8.\n\n";

// Ejemplo más dramático con caracteres japoneses (3 bytes cada uno)
$textoJp = "東京タワーはTokyo Towerです";
$posBytes = strpos($textoJp, "Tokyo");
$posChars = mb_strpos($textoJp, "Tokyo", 0, 'UTF-8');

echo "Texto: \"$textoJp\"\n";
echo "Buscando \"Tokyo\":\n";
echo "  strpos():    posición $posBytes (en bytes)\n";
echo "  mb_strpos(): posición $posChars (en caracteres)\n\n";

// =============================================================================
// Ejemplo 2: Uso básico de mb_strpos con diferentes parámetros
// mb_strpos(pajar, aguja, inicio, codificación)
// =============================================================================

echo "=== Ejemplo 2: Parámetros de mb_strpos ===\n";

$frase = "El café colombiano es el mejor café del mundo";
echo "Frase: \"$frase\"\n\n";

// Búsqueda simple (primera ocurrencia)
$pos = mb_strpos($frase, "café", 0, 'UTF-8');
echo "Primera ocurrencia de 'café': posición $pos\n";

// Buscar desde una posición específica (encontrar la segunda ocurrencia)
$pos2 = mb_strpos($frase, "café", $pos + 1, 0, 'UTF-8');
if ($pos2 === false) {
    // Intentar con offset correcto
    $pos2 = mb_strpos($frase, "café", $pos + 1, 'UTF-8');
}
echo "Segunda ocurrencia de 'café': posición $pos2\n";

// Buscar algo que no existe (devuelve false)
$posNoExiste = mb_strpos($frase, "té", 0, 'UTF-8');
echo "Buscando 'té': " . ($posNoExiste === false ? "NO encontrado (false)" : "posición $posNoExiste") . "\n";

// IMPORTANTE: Usar === para comparar con false, ya que posición 0 es válida
$posInicio = mb_strpos("ñoño", "ñ", 0, 'UTF-8');
echo "'ñ' en 'ñoño': posición $posInicio (usar === false, no == false)\n\n";

// =============================================================================
// Ejemplo 3: mb_strrpos - Buscar la ÚLTIMA ocurrencia
// Similar a mb_strpos pero busca desde el final
// =============================================================================

echo "=== Ejemplo 3: mb_strrpos (última ocurrencia) ===\n";

$ruta = "carpeta/subcarpeta/archivó/documentó.txt";
echo "Ruta: \"$ruta\"\n";

// Encontrar la última barra para obtener el nombre del archivo
$ultimaBarra = mb_strrpos($ruta, "/", 0, 'UTF-8');
$nombreArchivo = mb_substr($ruta, $ultimaBarra + 1, null, 'UTF-8');
echo "Última '/': posición $ultimaBarra\n";
echo "Nombre del archivo: \"$nombreArchivo\"\n\n";

// Encontrar la última extensión
$ultimoPunto = mb_strrpos($ruta, ".", 0, 'UTF-8');
$extension = mb_substr($ruta, $ultimoPunto + 1, null, 'UTF-8');
echo "Último '.': posición $ultimoPunto\n";
echo "Extensión: \"$extension\"\n\n";

// Comparar mb_strpos y mb_strrpos
$texto = "España es el país de la paella y la piña";
echo "Texto: \"$texto\"\n";
echo "Primera 'la': posición " . mb_strpos($texto, "la", 0, 'UTF-8') . "\n";
echo "Última 'la':  posición " . mb_strrpos($texto, "la", 0, 'UTF-8') . "\n\n";

// =============================================================================
// Ejemplo 4: Encontrar todas las ocurrencias de una subcadena
// Iterar usando mb_strpos con offset incremental
// =============================================================================

echo "=== Ejemplo 4: Encontrar todas las ocurrencias ===\n";

/**
 * Encuentra todas las posiciones de una subcadena en un texto multibyte.
 *
 * @param string $pajar  Texto donde buscar
 * @param string $aguja  Subcadena a buscar
 * @param string $codif  Codificación (por defecto UTF-8)
 * @return array Lista de posiciones encontradas
 */
function mbBuscarTodas(string $pajar, string $aguja, string $codif = 'UTF-8'): array
{
    $posiciones = [];
    $offset = 0;
    $longitudAguja = mb_strlen($aguja, $codif);

    while (($pos = mb_strpos($pajar, $aguja, $offset, $codif)) !== false) {
        $posiciones[] = $pos;
        $offset = $pos + $longitudAguja; // Avanzar para encontrar la siguiente
    }

    return $posiciones;
}

$texto = "La niña y el niño fueron al cariño del abuelo. El niño sonrió.";
$buscar = "niñ";

$posiciones = mbBuscarTodas($texto, $buscar);
echo "Texto: \"$texto\"\n";
echo "Buscando \"$buscar\": encontrado en posiciones " . implode(', ', $posiciones) . "\n";
echo "Total de ocurrencias: " . count($posiciones) . "\n\n";

// Resaltar las ocurrencias
echo "Contexto de cada ocurrencia:\n";
foreach ($posiciones as $i => $pos) {
    $inicio = max(0, $pos - 5);
    $longitud = mb_strlen($buscar, 'UTF-8') + 10;
    $contexto = mb_substr($texto, $inicio, $longitud, 'UTF-8');
    echo "  Ocurrencia " . ($i + 1) . " (pos $pos): \"...$contexto...\"\n";
}
echo "\n";

// =============================================================================
// Ejemplo 5: Búsqueda insensible a mayúsculas/minúsculas con mb_stripos
// mb_stripos ignora diferencias entre mayúsculas y minúsculas
// =============================================================================

echo "=== Ejemplo 5: mb_stripos (sin distinción de mayúsculas) ===\n";

$texto = "CAFÉ con leche, Café con hielo, café solo";
echo "Texto: \"$texto\"\n\n";

// mb_strpos es sensible a mayúsculas
$posSensible = mb_strpos($texto, "café", 0, 'UTF-8');
echo "mb_strpos('café'):  posición " . ($posSensible === false ? "NO encontrado" : $posSensible) . "\n";

// mb_stripos es insensible a mayúsculas (la 'i' es por 'insensitive')
$posInsensible = mb_stripos($texto, "café", 0, 'UTF-8');
echo "mb_stripos('café'): posición " . ($posInsensible === false ? "NO encontrado" : $posInsensible) . "\n\n";

// Encontrar todas las ocurrencias sin importar mayúsculas
echo "Todas las ocurrencias de 'café' (insensible a mayúsculas):\n";
$offset = 0;
$ocurrencia = 1;
while (($pos = mb_stripos($texto, "café", $offset, 'UTF-8')) !== false) {
    $encontrado = mb_substr($texto, $pos, 4, 'UTF-8');
    echo "  #$ocurrencia: posición $pos -> \"$encontrado\"\n";
    $offset = $pos + 1;
    $ocurrencia++;
}
echo "\n";

// mb_strripos: última ocurrencia insensible a mayúsculas
$ultimaPos = mb_strripos($texto, "café", 0, 'UTF-8');
echo "Última ocurrencia (mb_strripos): posición $ultimaPos\n\n";

// =============================================================================
// Ejemplo 6: Función de búsqueda y reemplazo basada en posiciones
// Reemplazar ocurrencias usando mb_strpos y mb_substr
// =============================================================================

echo "=== Ejemplo 6: Buscar y reemplazar con mb_strpos ===\n";

/**
 * Reemplaza todas las ocurrencias de una subcadena, respetando multibyte.
 * Similar a str_replace pero usando funciones mb_*.
 *
 * @param string $buscar    Subcadena a buscar
 * @param string $reemplazo Texto de reemplazo
 * @param string $texto     Texto original
 * @return array ['resultado' => string, 'reemplazos' => int]
 */
function mbReemplazar(string $buscar, string $reemplazo, string $texto): array
{
    $resultado = '';
    $longBuscar = mb_strlen($buscar, 'UTF-8');
    $posAnterior = 0;
    $reemplazos = 0;

    while (($pos = mb_strpos($texto, $buscar, $posAnterior, 'UTF-8')) !== false) {
        // Agregar texto antes de la ocurrencia
        $resultado .= mb_substr($texto, $posAnterior, $pos - $posAnterior, 'UTF-8');
        // Agregar el reemplazo
        $resultado .= $reemplazo;
        $posAnterior = $pos + $longBuscar;
        $reemplazos++;
    }

    // Agregar el texto restante después de la última ocurrencia
    $resultado .= mb_substr($texto, $posAnterior, null, 'UTF-8');

    return ['resultado' => $resultado, 'reemplazos' => $reemplazos];
}

$textoOriginal = "El señor Muñoz y la señora Núñez visitaron España el año pasado.";
echo "Original: \"$textoOriginal\"\n\n";

// Reemplazar 'señor' por 'Sr.' y 'señora' por 'Sra.'
$paso1 = mbReemplazar("señora", "Sra.", $textoOriginal);
$paso2 = mbReemplazar("señor", "Sr.", $paso1['resultado']);

echo "Después de reemplazos:\n";
echo "  \"" . $paso2['resultado'] . "\"\n";
echo "  Reemplazos realizados: " . ($paso1['reemplazos'] + $paso2['reemplazos']) . "\n\n";

// Reemplazar emojis
$textoEmoji = "Me gusta 🍕 y también 🍕 pero prefiero 🍔";
$resultadoEmoji = mbReemplazar("🍕", "pizza", $textoEmoji);
echo "Original: \"$textoEmoji\"\n";
echo "Resultado: \"" . $resultadoEmoji['resultado'] . "\" (" . $resultadoEmoji['reemplazos'] . " reemplazos)\n";

?>
