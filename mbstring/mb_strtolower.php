<?php
// =============================================================================
// mb_strtolower() y mb_strtoupper() - Conversión de mayúsculas/minúsculas
// Manejo correcto de caracteres acentuados, ñ, turco İ y otros scripts
// =============================================================================

// =============================================================================
// Ejemplo 1: Diferencia entre strtolower y mb_strtolower
// strtolower falla con caracteres multibyte acentuados
// =============================================================================

echo "=== Ejemplo 1: strtolower vs mb_strtolower ===\n";

$textos = [
    "INFORMACIÓN",
    "ESPAÑA",
    "NIÑO",
    "ÑOÑO",
    "CAFÉ",
    "ÜBER",
];

echo str_pad("Original", 18) . str_pad("strtolower", 18) . "mb_strtolower\n";
echo str_repeat("-", 55) . "\n";

foreach ($textos as $texto) {
    echo str_pad($texto, 18);
    echo str_pad(strtolower($texto), 18);        // Puede fallar con UTF-8
    echo mb_strtolower($texto, 'UTF-8') . "\n";  // Siempre correcto
}
echo "\n";

// =============================================================================
// Ejemplo 2: mb_strtoupper con caracteres especiales del español
// Convertir a mayúsculas respetando todos los caracteres Unicode
// =============================================================================

echo "=== Ejemplo 2: mb_strtoupper con español ===\n";

$frases = [
    "el niño come piña",
    "¡hola! ¿cómo estás?",
    "canción de cuna",
    "señor y señora lópez",
    "programación en español",
];

foreach ($frases as $frase) {
    $mayusculas = mb_strtoupper($frase, 'UTF-8');
    echo "  \"$frase\"\n";
    echo "  \"$mayusculas\"\n\n";
}

// =============================================================================
// Ejemplo 3: El caso especial del turco: İ (I con punto) y ı (i sin punto)
// El turco tiene reglas de mayúsculas/minúsculas diferentes al resto
// =============================================================================

echo "=== Ejemplo 3: El caso especial del turco ===\n";

// En turco:
// I mayúscula (sin punto) -> ı minúscula (sin punto)
// İ mayúscula (con punto) -> i minúscula (con punto)
// Esto es diferente al inglés donde I -> i siempre

echo "Sistema de I/i en turco vs inglés:\n\n";

// I con punto mayúscula (İ) - específica del turco
$iConPuntoMay = "İ";  // U+0130 Latin Capital Letter I With Dot Above
$iSinPuntoMin = "ı";  // U+0131 Latin Small Letter Dotless I

echo "Carácter İ (I con punto, turco):\n";
echo "  mb_strtolower('İ', 'UTF-8'): \"" . mb_strtolower($iConPuntoMay, 'UTF-8') . "\"\n";
echo "  Esperado en turco: \"i\" (i normal con punto)\n\n";

echo "Carácter I (I estándar, inglés):\n";
echo "  mb_strtolower('I', 'UTF-8'): \"" . mb_strtolower("I", 'UTF-8') . "\"\n\n";

echo "Carácter ı (i sin punto, turco):\n";
echo "  mb_strtoupper('ı', 'UTF-8'): \"" . mb_strtoupper($iSinPuntoMin, 'UTF-8') . "\"\n";
echo "  Esperado en turco: \"I\" (I sin punto)\n\n";

// Ejemplo práctico con palabras turcas
$palabrasTurcas = [
    "İstanbul"    => "Ciudad turca (con İ)",
    "dışarı"      => "Afuera (con ı sin punto)",
    "Türkiye"     => "Turquía",
    "güneş"       => "Sol",
];

echo "Palabras turcas:\n";
foreach ($palabrasTurcas as $palabra => $descripcion) {
    $lower = mb_strtolower($palabra, 'UTF-8');
    $upper = mb_strtoupper($palabra, 'UTF-8');
    echo "  \"$palabra\" ($descripcion)\n";
    echo "    minúsculas: \"$lower\" | mayúsculas: \"$upper\"\n";
}
echo "\n";

// =============================================================================
// Ejemplo 4: Conversión de mayúsculas en diferentes scripts
// Griego, cirílico, alemán (ß), y otros alfabetos
// =============================================================================

echo "=== Ejemplo 4: Diferentes scripts y alfabetos ===\n";

$scripts = [
    'Español'  => ['minúsculas' => 'ñoño café', 'mayúsculas' => 'ÑOÑO CAFÉ'],
    'Francés'  => ['minúsculas' => 'être à côté', 'mayúsculas' => 'ÊTRE À CÔTÉ'],
    'Alemán'   => ['minúsculas' => 'straße größe', 'mayúsculas' => 'STRASSE GRÖSSE'],
    'Griego'   => ['minúsculas' => 'ελληνικά', 'mayúsculas' => 'ΕΛΛΗΝΙΚΑ'],
    'Ruso'     => ['minúsculas' => 'привет мир', 'mayúsculas' => 'ПРИВЕТ МИР'],
    'Polaco'   => ['minúsculas' => 'łódź źródło', 'mayúsculas' => 'ŁÓDŹ ŹRÓDŁO'],
];

foreach ($scripts as $idioma => $datos) {
    echo "  $idioma:\n";
    $minAMay = mb_strtoupper($datos['minúsculas'], 'UTF-8');
    $mayAMin = mb_strtolower($datos['mayúsculas'], 'UTF-8');
    echo "    \"" . $datos['minúsculas'] . "\" -> \"$minAMay\"\n";
    echo "    \"" . $datos['mayúsculas'] . "\" -> \"$mayAMin\"\n";
}

// Caso especial del alemán: ß (eszett)
echo "\n  Caso especial alemán (ß - eszett):\n";
$eszett = "straße";
$upper = mb_strtoupper($eszett, 'UTF-8');
$lowerAgain = mb_strtolower($upper, 'UTF-8');
echo "    \"$eszett\" -> mayúsculas: \"$upper\"\n";
echo "    \"$upper\" -> minúsculas: \"$lowerAgain\"\n";
echo "    Nota: ß se convierte a SS en mayúsculas (transformación no reversible).\n\n";

// =============================================================================
// Ejemplo 5: Comparación insensible a mayúsculas con mb_strtolower
// Normalizar texto para comparaciones
// =============================================================================

echo "=== Ejemplo 5: Comparación insensible a mayúsculas ===\n";

/**
 * Compara dos cadenas multibyte sin distinción de mayúsculas.
 *
 * @param string $a Primera cadena
 * @param string $b Segunda cadena
 * @return bool true si son iguales (ignorando mayúsculas/minúsculas)
 */
function mbIguales(string $a, string $b): bool
{
    return mb_strtolower($a, 'UTF-8') === mb_strtolower($b, 'UTF-8');
}

$comparaciones = [
    ['CAFÉ',     'café'],
    ['España',   'ESPAÑA'],
    ['señor',    'SEÑOR'],
    ['Ñoño',     'ñoño'],
    ['İstanbul', 'istanbul'],
    ['Über',     'ÜBER'],
    ['café',     'cafe'],  // Con y sin acento: NO son iguales
];

echo "Comparaciones insensibles a mayúsculas:\n";
foreach ($comparaciones as [$a, $b]) {
    $resultado = mbIguales($a, $b) ? "IGUALES" : "DIFERENTES";
    echo "  \"$a\" vs \"$b\" -> $resultado\n";
}
echo "\n";

// =============================================================================
// Ejemplo 6: Funciones prácticas de capitalización
// Capitalizar primera letra, cada palabra, tipo título, etc.
// =============================================================================

echo "=== Ejemplo 6: Funciones de capitalización ===\n";

/**
 * Capitaliza la primera letra de un texto (ucfirst multibyte).
 */
function mbUcFirst(string $texto): string
{
    $primera = mb_strtoupper(mb_substr($texto, 0, 1, 'UTF-8'), 'UTF-8');
    $resto = mb_substr($texto, 1, null, 'UTF-8');
    return $primera . $resto;
}

/**
 * Convierte a minúscula la primera letra (lcfirst multibyte).
 */
function mbLcFirst(string $texto): string
{
    $primera = mb_strtolower(mb_substr($texto, 0, 1, 'UTF-8'), 'UTF-8');
    $resto = mb_substr($texto, 1, null, 'UTF-8');
    return $primera . $resto;
}

/**
 * Capitaliza cada palabra del texto (ucwords multibyte).
 */
function mbUcWords(string $texto): string
{
    $palabras = explode(' ', mb_strtolower($texto, 'UTF-8'));
    return implode(' ', array_map('mbUcFirst', $palabras));
}

/**
 * Formato tipo título: capitaliza palabras principales.
 * Las preposiciones y artículos cortos quedan en minúsculas (excepto al inicio).
 */
function mbTitulo(string $texto): string
{
    $menores = ['de', 'del', 'la', 'las', 'el', 'los', 'un', 'una', 'y', 'o', 'en', 'a', 'con', 'por'];
    $palabras = explode(' ', mb_strtolower($texto, 'UTF-8'));
    $resultado = [];

    foreach ($palabras as $i => $palabra) {
        // La primera palabra siempre va en mayúscula
        if ($i === 0 || !in_array($palabra, $menores)) {
            $resultado[] = mbUcFirst($palabra);
        } else {
            $resultado[] = $palabra;
        }
    }

    return implode(' ', $resultado);
}

echo "mbUcFirst (primera letra mayúscula):\n";
$textos = ["ñoño comió", "información útil", "über cool", "éxito total"];
foreach ($textos as $t) {
    echo "  \"$t\" -> \"" . mbUcFirst($t) . "\"\n";
}

echo "\nmbLcFirst (primera letra minúscula):\n";
$textos = ["Ñoño", "INFORMACIÓN", "Über"];
foreach ($textos as $t) {
    echo "  \"$t\" -> \"" . mbLcFirst($t) . "\"\n";
}

echo "\nmbUcWords (cada palabra capitalizada):\n";
echo "  \"" . mbUcWords("CAFÉ CON LECHE Y PIÑA COLADA") . "\"\n";

echo "\nmbTitulo (formato tipo título):\n";
$titulos = [
    "EL SEÑOR DE LOS ANILLOS",
    "CRÓNICA DE UNA MUERTE ANUNCIADA",
    "LA CASA DE LOS ESPÍRITUS",
];
foreach ($titulos as $t) {
    echo "  \"$t\"\n  -> \"" . mbTitulo($t) . "\"\n";
}

?>
