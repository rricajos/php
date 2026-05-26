<?php
/**
 * Output Buffering - ob_start(), ob_end_flush(), ob_get_contents(), etc.
 *
 * El buffer de salida intercepta todo lo que PHP enviaría al navegador
 * (echo, print, HTML) y lo almacena en memoria para procesarlo antes.
 */

// ============================================
// Ejemplo 1: ob_start() y ob_end_flush() básico
// ============================================

echo "=== Ejemplo 1: Buffer básico ===\n";

// Iniciar el buffer de salida
ob_start();

// Todo lo que se imprime va al buffer, no a la pantalla
echo "Este texto está en el buffer.\n";
echo "Este también.\n";
print "Y este con print.\n";

// ob_end_flush() envía el contenido del buffer a la salida y cierra el buffer
ob_end_flush();

echo "Esto ya se imprime directamente (sin buffer).\n";

// ============================================
// Ejemplo 2: ob_get_contents() y ob_end_clean()
// ============================================

echo "\n=== Ejemplo 2: Capturar y descartar salida ===\n";

// Capturar salida en una variable
ob_start();
echo "Contenido que será capturado.\n";
echo "Línea adicional de contenido.\n";

// ob_get_contents() obtiene el contenido sin vaciar el buffer
$contenido = ob_get_contents();

// ob_end_clean() descarta el buffer sin enviarlo a la salida
ob_end_clean();

// El contenido capturado está en la variable
echo "El buffer fue descartado. Contenido capturado:\n";
echo "  [INICIO]\n";
echo "  " . str_replace("\n", "\n  ", trim($contenido)) . "\n";
echo "  [FIN]\n";

// ob_get_clean() = ob_get_contents() + ob_end_clean() en un solo paso
ob_start();
echo "Capturado con ob_get_clean()";
$capturado = ob_get_clean(); // Obtiene y limpia en un paso

echo "Variable contiene: '$capturado'\n";

// ============================================
// Ejemplo 3: Información del buffer y nivel
// ============================================

echo "\n=== Ejemplo 3: Estado del buffer ===\n";

echo "Nivel de buffer actual: " . ob_get_level() . "\n";

ob_start();
echo "Buffer nivel 1";

echo "Nivel dentro del buffer: " . ob_get_level() . "\n";
echo "Longitud del buffer: " . ob_get_length() . " bytes\n";

// ob_get_status() devuelve información sobre el buffer actual
$estado = ob_get_status();
echo "Estado del buffer:\n";
foreach ($estado as $clave => $valor) {
    if (is_scalar($valor)) {
        echo "  $clave: $valor\n";
    }
}

ob_end_clean();

// ============================================
// Ejemplo 4: Buffers anidados
// ============================================

echo "\n=== Ejemplo 4: Buffers anidados ===\n";

echo "Nivel inicial: " . ob_get_level() . "\n";

// Buffer nivel 1
ob_start();
echo "Contenido del buffer 1\n";
echo "Nivel actual: " . ob_get_level() . "\n";

// Buffer nivel 2 (anidado)
ob_start();
echo "Contenido del buffer 2\n";
echo "Nivel actual: " . ob_get_level() . "\n";

// Buffer nivel 3 (más anidado)
ob_start();
echo "Contenido del buffer 3\n";
echo "Nivel actual: " . ob_get_level() . "\n";

// Obtener contenido del buffer más interno (nivel 3)
$buffer3 = ob_get_clean();

// Obtener contenido del buffer nivel 2
$buffer2 = ob_get_clean();

// Obtener contenido del buffer nivel 1
$buffer1 = ob_get_clean();

echo "Contenido buffer 3:\n  " . trim($buffer3) . "\n\n";
echo "Contenido buffer 2:\n  " . trim($buffer2) . "\n\n";
echo "Contenido buffer 1:\n  " . trim($buffer1) . "\n";

// ob_get_status(true) muestra todos los buffers activos
echo "\nEstado de todos los buffers (debería estar vacío):\n";
$estados = ob_get_status(true);
echo "  Buffers activos: " . count($estados) . "\n";

// ============================================
// Ejemplo 5: ob_start() con callback de transformación
// ============================================

echo "\n=== Ejemplo 5: Buffer con callback ===\n";

/**
 * ob_start() acepta una función callback que procesa el contenido
 * del buffer antes de enviarlo a la salida.
 */

// Callback que convierte todo a mayúsculas
ob_start(function (string $buffer): string {
    return strtoupper($buffer);
});

echo "este texto se convertirá a mayúsculas\n";
echo "sin importar cómo se escriba\n";

ob_end_flush();

// Callback que agrega número de línea
echo "\nCallback con numeración de líneas:\n";
ob_start(function (string $buffer): string {
    $lineas = explode("\n", $buffer);
    $resultado = [];
    $num = 1;
    foreach ($lineas as $linea) {
        if (trim($linea) !== '') {
            $resultado[] = sprintf("  %02d | %s", $num++, $linea);
        }
    }
    return implode("\n", $resultado) . "\n";
});

echo "Primera línea del buffer\n";
echo "Segunda línea del buffer\n";
echo "Tercera línea del buffer\n";

ob_end_flush();

// Callback que minimiza HTML
echo "\nCallback para minimizar HTML:\n";
ob_start(function (string $buffer): string {
    // Eliminar espacios en blanco innecesarios
    $minificado = preg_replace('/\s+/', ' ', $buffer);
    $minificado = str_replace('> <', '><', $minificado);
    return trim($minificado);
});

echo "<div>
    <h1>  Título  </h1>
    <p>
        Párrafo con     espacios   extras.
    </p>
    <ul>
        <li> Item 1 </li>
        <li> Item 2 </li>
    </ul>
</div>";

$htmlMinificado = ob_get_clean();
echo "HTML minificado:\n  $htmlMinificado\n";

// ob_flush() vs ob_end_flush()
echo "\nDiferencia entre ob_flush() y ob_end_flush():\n";
echo "  ob_flush(): Envía el buffer a la salida pero MANTIENE el buffer activo.\n";
echo "  ob_end_flush(): Envía el buffer a la salida y CIERRA el buffer.\n";
echo "  ob_end_clean(): Descarta el buffer y lo CIERRA (no envía nada).\n";
echo "  ob_get_flush(): Obtiene el contenido, lo envía y CIERRA el buffer.\n";

?>
