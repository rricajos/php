<?php
// Ejemplo 1: Reemplazo simple con regex
$texto = "Hola mundo 2025";
echo "Ejemplo 1 (Reemplazo simple):\n";
echo preg_replace("/\d+/", "2026", $texto) . "\n"; // Hola mundo 2026

// Ejemplo 2: Eliminar todos los números
$texto2 = "abc123def456";
echo "\nEjemplo 2 (Eliminar números):\n";
echo preg_replace("/\d/", "", $texto2) . "\n"; // abcdef

// Ejemplo 3: Usar backreferences
$fecha = "26/05/2026";
echo "\nEjemplo 3 (Reformatear fecha con backreferences):\n";
echo preg_replace("/(\d{2})\/(\d{2})\/(\d{4})/", "$3-$2-$1", $fecha) . "\n"; // 2026-05-26

// Ejemplo 4: Reemplazo con arrays de patrones
$html = "<b>negrita</b> <i>cursiva</i> <u>subrayado</u>";
$patrones = ["/<b>(.+?)<\/b>/", "/<i>(.+?)<\/i>/", "/<u>(.+?)<\/u>/"];
$reemplazos = ["**$1**", "*$1*", "__$1__"];
echo "\nEjemplo 4 (HTML a Markdown):\n";
echo preg_replace($patrones, $reemplazos, $html) . "\n";

// Ejemplo 5: Limpiar espacios múltiples
$desordenado = "Hola   mundo    PHP";
echo "\nEjemplo 5 (Limpiar espacios múltiples):\n";
echo preg_replace("/\s+/", " ", $desordenado) . "\n";

// Ejemplo 6: Censurar palabras
$comentario = "Esto es maldito horrible y maldita basura";
echo "\nEjemplo 6 (Censurar palabras):\n";
echo preg_replace("/maldit[oa]/i", "***", $comentario) . "\n";

// Ejemplo 7: Límite de reemplazos
$texto7 = "aaa bbb aaa ccc aaa";
echo "\nEjemplo 7 (Límite de reemplazos):\n";
echo preg_replace("/aaa/", "xxx", $texto7, 2) . "\n"; // Solo reemplaza los primeros 2
?>
