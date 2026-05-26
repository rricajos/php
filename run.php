<?php

// ============================================================================
// run.php — Ejecutor interactivo de ejemplos PHP desde la línea de comandos
// ============================================================================
// Uso: php run.php
// Muestra un menu de categorias (directorios) y permite ejecutar cualquier
// archivo .php del proyecto de forma interactiva.
// ============================================================================

// --- Colores ANSI para la terminal ---
define('COLOR_RESET',   "\033[0m");
define('COLOR_GREEN',   "\033[1;32m");
define('COLOR_CYAN',    "\033[0;36m");
define('COLOR_RED',     "\033[1;31m");
define('COLOR_YELLOW',  "\033[1;33m");
define('COLOR_WHITE',   "\033[1;37m");
define('COLOR_GRAY',    "\033[0;90m");

// Directorio raiz del proyecto
define('PROJECT_ROOT', __DIR__);

// --- Funciones auxiliares ---

/**
 * Limpia la pantalla de la terminal.
 */
function limpiarPantalla(): void
{
    // Compatibilidad Windows y Unix
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('cls');
    } else {
        system('clear');
    }
}

/**
 * Muestra la cabecera del programa.
 */
function mostrarCabecera(): void
{
    echo COLOR_GREEN . str_repeat('=', 60) . COLOR_RESET . "\n";
    echo COLOR_GREEN . "   PHP Ejemplos — Ejecutor Interactivo" . COLOR_RESET . "\n";
    echo COLOR_GREEN . str_repeat('=', 60) . COLOR_RESET . "\n";
    echo COLOR_GRAY  . "   Directorio: " . PROJECT_ROOT . COLOR_RESET . "\n\n";
}

/**
 * Lee una linea de entrada del usuario desde stdin.
 */
function leerEntrada(string $prompt = ''): string
{
    if ($prompt !== '') {
        echo $prompt;
    }
    $linea = fgets(STDIN);
    return $linea !== false ? trim($linea) : '';
}

/**
 * Comprueba si un directorio contiene archivos .php (directa o recursivamente).
 */
function contienePhp(string $ruta): bool
{
    $elementos = @scandir($ruta);
    if ($elementos === false) {
        return false;
    }

    foreach ($elementos as $elemento) {
        // Ignorar entradas ocultas y las referencias . y ..
        if (str_starts_with($elemento, '.')) {
            continue;
        }

        $rutaCompleta = $ruta . DIRECTORY_SEPARATOR . $elemento;

        if (is_file($rutaCompleta) && str_ends_with($elemento, '.php')) {
            return true;
        }

        // Buscar recursivamente en subdirectorios
        if (is_dir($rutaCompleta) && contienePhp($rutaCompleta)) {
            return true;
        }
    }

    return false;
}

/**
 * Obtiene las categorias (directorios con archivos .php) del directorio dado.
 * Devuelve un array asociativo [nombre => rutaCompleta].
 */
function obtenerCategorias(string $directorio): array
{
    $categorias = [];
    $elementos  = @scandir($directorio);

    if ($elementos === false) {
        return $categorias;
    }

    foreach ($elementos as $elemento) {
        // Ignorar ocultos, el propio run.php y archivos normales
        if (str_starts_with($elemento, '.')) {
            continue;
        }

        $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $elemento;

        if (is_dir($rutaCompleta) && contienePhp($rutaCompleta)) {
            $categorias[$elemento] = $rutaCompleta;
        }
    }

    // Ordenar alfabeticamente por nombre
    ksort($categorias);
    return $categorias;
}

/**
 * Obtiene los archivos .php de un directorio (sin recursion).
 * Devuelve un array asociativo [nombre => rutaCompleta].
 */
function obtenerArchivos(string $directorio): array
{
    $archivos  = [];
    $elementos = @scandir($directorio);

    if ($elementos === false) {
        return $archivos;
    }

    foreach ($elementos as $elemento) {
        if (str_starts_with($elemento, '.')) {
            continue;
        }

        $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $elemento;

        // Excluir el propio run.php
        if ($rutaCompleta === PROJECT_ROOT . DIRECTORY_SEPARATOR . 'run.php') {
            continue;
        }

        if (is_file($rutaCompleta) && str_ends_with($elemento, '.php')) {
            $archivos[$elemento] = $rutaCompleta;
        }
    }

    // Ordenar alfabeticamente
    ksort($archivos);
    return $archivos;
}

/**
 * Obtiene los subdirectorios que contienen archivos .php.
 * Devuelve un array asociativo [nombre => rutaCompleta].
 */
function obtenerSubdirectorios(string $directorio): array
{
    $subdirs   = [];
    $elementos = @scandir($directorio);

    if ($elementos === false) {
        return $subdirs;
    }

    foreach ($elementos as $elemento) {
        if (str_starts_with($elemento, '.')) {
            continue;
        }

        $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $elemento;

        if (is_dir($rutaCompleta) && contienePhp($rutaCompleta)) {
            $subdirs[$elemento] = $rutaCompleta;
        }
    }

    ksort($subdirs);
    return $subdirs;
}

/**
 * Ejecuta un archivo PHP dentro de un bloque try/catch y captura la salida.
 */
function ejecutarArchivo(string $rutaArchivo): void
{
    echo "\n" . COLOR_GREEN . str_repeat('-', 60) . COLOR_RESET . "\n";
    echo COLOR_WHITE . " Ejecutando: " . COLOR_CYAN . $rutaArchivo . COLOR_RESET . "\n";
    echo COLOR_GREEN . str_repeat('-', 60) . COLOR_RESET . "\n\n";

    if (!file_exists($rutaArchivo)) {
        echo COLOR_RED . " Error: El archivo no existe." . COLOR_RESET . "\n";
        return;
    }

    if (!is_readable($rutaArchivo)) {
        echo COLOR_RED . " Error: No se puede leer el archivo." . COLOR_RESET . "\n";
        return;
    }

    // Registrar manejador de errores para capturar warnings/notices
    $erroresCapturados = [];
    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use (&$erroresCapturados): bool {
        $erroresCapturados[] = [
            'tipo'    => $errno,
            'mensaje' => $errstr,
            'archivo' => $errfile,
            'linea'   => $errline,
        ];
        return true;
    });

    try {
        ob_start();
        require $rutaArchivo;
        $salida = ob_get_clean();

        if ($salida !== false && $salida !== '') {
            echo $salida;
            // Asegurar salto de linea al final
            if (!str_ends_with($salida, "\n")) {
                echo "\n";
            }
        }
    } catch (\Throwable $e) {
        // Limpiar buffer si quedo algo pendiente
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        echo COLOR_RED . " Excepcion capturada:" . COLOR_RESET . "\n";
        echo COLOR_RED . "   Tipo:    " . get_class($e) . COLOR_RESET . "\n";
        echo COLOR_RED . "   Mensaje: " . $e->getMessage() . COLOR_RESET . "\n";
        echo COLOR_RED . "   Archivo: " . $e->getFile() . ":" . $e->getLine() . COLOR_RESET . "\n";
        echo COLOR_GRAY . "   Traza:\n" . $e->getTraceAsString() . COLOR_RESET . "\n";
    }

    // Restaurar manejador de errores por defecto
    restore_error_handler();

    // Mostrar errores capturados si los hubo
    if (!empty($erroresCapturados)) {
        echo "\n" . COLOR_YELLOW . " Avisos durante la ejecucion:" . COLOR_RESET . "\n";
        foreach ($erroresCapturados as $error) {
            $tipoStr = match ($error['tipo']) {
                E_WARNING         => 'Warning',
                E_NOTICE          => 'Notice',
                E_DEPRECATED      => 'Deprecated',
                E_USER_WARNING    => 'User Warning',
                E_USER_NOTICE     => 'User Notice',
                E_USER_DEPRECATED => 'User Deprecated',
                default           => 'Error (' . $error['tipo'] . ')',
            };
            echo COLOR_YELLOW . "   [{$tipoStr}] {$error['mensaje']}" . COLOR_RESET . "\n";
            echo COLOR_GRAY  . "   en {$error['archivo']}:{$error['linea']}" . COLOR_RESET . "\n";
        }
    }
}

/**
 * Muestra un menu de elementos numerados y devuelve la seleccion del usuario.
 * Retorna: indice seleccionado (0-based), 'back' para volver, 'quit' para salir.
 */
function mostrarMenu(string $titulo, array $elementos, bool $mostrarVolver = true): string|int
{
    echo COLOR_GREEN . " $titulo" . COLOR_RESET . "\n\n";

    $nombres = array_keys($elementos);

    foreach ($nombres as $indice => $nombre) {
        $numero = $indice + 1;
        echo "   " . COLOR_CYAN . str_pad("[$numero]", 6) . COLOR_RESET . " $nombre\n";
    }

    echo "\n";

    if ($mostrarVolver) {
        echo "   " . COLOR_YELLOW . "[0/b]" . COLOR_RESET . " Volver\n";
    }
    echo "   " . COLOR_RED . "[q]" . COLOR_RESET . "   Salir\n";
    echo "\n";

    $entrada = leerEntrada(COLOR_WHITE . " > " . COLOR_RESET);

    // Comprobar si quiere salir
    if (strtolower($entrada) === 'q') {
        return 'quit';
    }

    // Comprobar si quiere volver
    if ($mostrarVolver && ($entrada === '0' || strtolower($entrada) === 'b')) {
        return 'back';
    }

    // Comprobar si es un numero valido
    if (is_numeric($entrada)) {
        $numero = (int) $entrada;
        if ($numero >= 1 && $numero <= count($nombres)) {
            return $numero - 1;
        }
    }

    return -1; // Seleccion invalida
}

/**
 * Menu de archivos para un directorio dado. Permite navegar subdirectorios.
 */
function menuArchivos(string $directorio, string $nombreCategoria): string
{
    while (true) {
        limpiarPantalla();
        mostrarCabecera();

        $archivos      = obtenerArchivos($directorio);
        $subdirectorios = obtenerSubdirectorios($directorio);

        // Construir lista combinada: primero subdirectorios, luego archivos
        $elementos = [];

        foreach ($subdirectorios as $nombre => $ruta) {
            $elementos["📁 $nombre/"] = ['tipo' => 'dir', 'ruta' => $ruta, 'nombre' => $nombre];
        }

        foreach ($archivos as $nombre => $ruta) {
            $elementos["   $nombre"] = ['tipo' => 'archivo', 'ruta' => $ruta];
        }

        if (empty($elementos)) {
            echo COLOR_YELLOW . " No se encontraron archivos .php en esta categoria." . COLOR_RESET . "\n\n";
            leerEntrada(COLOR_GRAY . " Pulsa Enter para continuar..." . COLOR_RESET);
            return 'back';
        }

        $titulo = "Selecciona un archivo: [$nombreCategoria]";
        $seleccion = mostrarMenu($titulo, $elementos);

        if ($seleccion === 'quit') {
            return 'quit';
        }

        if ($seleccion === 'back') {
            return 'back';
        }

        if ($seleccion === -1) {
            echo COLOR_RED . "\n Opcion no valida. Intenta de nuevo." . COLOR_RESET . "\n";
            sleep(1);
            continue;
        }

        $claves  = array_keys($elementos);
        $elegido = $elementos[$claves[$seleccion]];

        if ($elegido['tipo'] === 'dir') {
            // Navegar al subdirectorio
            $resultado = menuArchivos($elegido['ruta'], $nombreCategoria . ' > ' . $elegido['nombre']);
            if ($resultado === 'quit') {
                return 'quit';
            }
            // Si es 'back', volvemos a este menu
            continue;
        }

        // Ejecutar el archivo seleccionado
        limpiarPantalla();
        mostrarCabecera();
        ejecutarArchivo($elegido['ruta']);

        echo "\n" . COLOR_GREEN . str_repeat('-', 60) . COLOR_RESET . "\n";
        leerEntrada(COLOR_GRAY . " Pulsa Enter para continuar..." . COLOR_RESET);
    }
}

// ============================================================================
// --- Bucle principal ---
// ============================================================================

while (true) {
    limpiarPantalla();
    mostrarCabecera();

    $categorias = obtenerCategorias(PROJECT_ROOT);

    if (empty($categorias)) {
        echo COLOR_RED . " No se encontraron categorias con archivos .php." . COLOR_RESET . "\n";
        exit(1);
    }

    $seleccion = mostrarMenu('Selecciona una categoria:', $categorias, false);

    if ($seleccion === 'quit') {
        limpiarPantalla();
        echo COLOR_GREEN . "\n Hasta luego!\n" . COLOR_RESET . "\n";
        exit(0);
    }

    if ($seleccion === -1) {
        echo COLOR_RED . "\n Opcion no valida. Intenta de nuevo." . COLOR_RESET . "\n";
        sleep(1);
        continue;
    }

    // Obtener la categoria seleccionada
    $nombres         = array_keys($categorias);
    $nombreCategoria = $nombres[$seleccion];
    $rutaCategoria   = $categorias[$nombreCategoria];

    // Mostrar menu de archivos de la categoria
    $resultado = menuArchivos($rutaCategoria, $nombreCategoria);

    if ($resultado === 'quit') {
        limpiarPantalla();
        echo COLOR_GREEN . "\n Hasta luego!\n" . COLOR_RESET . "\n";
        exit(0);
    }
    // Si es 'back', volvemos al menu principal
}

?>
