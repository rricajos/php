<?php

declare(strict_types=1);

/**
 * Gestor de Tareas CLI - Punto de entrada de la aplicacion.
 *
 * Aplicacion interactiva de linea de comandos para gestionar tareas
 * usando SQLite como almacenamiento. Demuestra conceptos de PHP 8.x,
 * patrones de diseno y buenas practicas de programacion orientada a objetos.
 *
 * Uso: php app.php
 */

// ---------------------------------------------------------------
//  Autoloader simple (sin necesidad de Composer)
// ---------------------------------------------------------------

/**
 * Registra un autoloader basado en namespaces.
 *
 * Mapea el namespace MiniProject\ al directorio src/ del proyecto.
 * Asi podemos usar 'use MiniProject\...' sin require manuales.
 */
spl_autoload_register(function (string $className): void {
    // Prefijo del namespace del proyecto
    $prefix = 'MiniProject\\';
    $baseDir = __DIR__ . '/src/';

    // Verificar si la clase pertenece a nuestro namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return; // No es de nuestro namespace, dejar que otro autoloader lo maneje
    }

    // Obtener el nombre relativo de la clase sin el prefijo
    $relativeClass = substr($className, $len);

    // Mapa de clases que comparten archivo con otras definiciones.
    // Varias clases/enums/interfaces viven en el mismo archivo PHP,
    // por lo que necesitamos mapearlas al archivo correcto.
    $classMap = [
        // Enums definidos en Task.php
        'Priority'             => 'Task.php',
        'Status'               => 'Task.php',
        // Excepciones definidas en AppException.php
        'ValidationException'  => 'AppException.php',
        'NotFoundException'    => 'AppException.php',
        // Interfaz y exportadores definidos en ExportService.php
        'ExporterInterface'    => 'ExportService.php',
        'JsonExporter'         => 'ExportService.php',
        'CsvExporter'          => 'ExportService.php',
    ];

    // Usar el mapa si la clase esta registrada, sino intentar ruta directa
    if (isset($classMap[$relativeClass])) {
        $file = $baseDir . $classMap[$relativeClass];
    } else {
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    }

    // Cargar el archivo si existe
    if (file_exists($file)) {
        require_once $file;
    }
});

// Importar las clases necesarias
use MiniProject\Database;
use MiniProject\TaskRepository;
use MiniProject\TaskService;
use MiniProject\ExportService;
use MiniProject\AppException;
use MiniProject\ValidationException;
use MiniProject\NotFoundException;

// ---------------------------------------------------------------
//  Constantes de colores ANSI para la terminal
// ---------------------------------------------------------------

/** Colores y estilos ANSI para la salida en terminal */
const RESET     = "\033[0m";
const BOLD      = "\033[1m";
const DIM       = "\033[2m";
const RED       = "\033[31m";
const GREEN     = "\033[32m";
const YELLOW    = "\033[33m";
const BLUE      = "\033[34m";
const MAGENTA   = "\033[35m";
const CYAN      = "\033[36m";
const WHITE     = "\033[37m";
const BG_BLUE   = "\033[44m";
const BG_GREEN  = "\033[42m";

// ---------------------------------------------------------------
//  Funciones auxiliares de la interfaz CLI
// ---------------------------------------------------------------

/**
 * Limpia la pantalla de la terminal.
 */
function limpiarPantalla(): void
{
    // Usar secuencia ANSI compatible con la mayoria de terminales
    echo "\033[2J\033[H";
}

/**
 * Muestra el banner/logo de la aplicacion.
 */
function mostrarBanner(): void
{
    echo PHP_EOL;
    echo CYAN . BOLD . "  ╔══════════════════════════════════════════╗" . RESET . PHP_EOL;
    echo CYAN . BOLD . "  ║                                          ║" . RESET . PHP_EOL;
    echo CYAN . BOLD . "  ║     " . WHITE . BG_BLUE . " GESTOR DE TAREAS CLI " . RESET . CYAN . BOLD . "              ║" . RESET . PHP_EOL;
    echo CYAN . BOLD . "  ║                                          ║" . RESET . PHP_EOL;
    echo CYAN . BOLD . "  ║  " . DIM . "PHP 8 | SQLite | Patrones de Diseno" . RESET . CYAN . BOLD . " ║" . RESET . PHP_EOL;
    echo CYAN . BOLD . "  ║                                          ║" . RESET . PHP_EOL;
    echo CYAN . BOLD . "  ╚══════════════════════════════════════════╝" . RESET . PHP_EOL;
    echo PHP_EOL;
}

/**
 * Muestra el menu principal con opciones numeradas.
 */
function mostrarMenu(): void
{
    echo YELLOW . BOLD . "  ┌─────────── MENU PRINCIPAL ───────────┐" . RESET . PHP_EOL;
    echo YELLOW       . "  │                                       │" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "1." . RESET . " Agregar tarea                   " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "2." . RESET . " Listar tareas                   " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "3." . RESET . " Completar tarea                 " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "4." . RESET . " Eliminar tarea                  " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "5." . RESET . " Buscar tareas                   " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "6." . RESET . " Exportar tareas                 " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . GREEN  . "7." . RESET . " Estadisticas                    " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │  " . RED    . "0." . RESET . " Salir                           " . YELLOW . "│" . RESET . PHP_EOL;
    echo YELLOW       . "  │                                       │" . RESET . PHP_EOL;
    echo YELLOW . BOLD . "  └───────────────────────────────────────┘" . RESET . PHP_EOL;
    echo PHP_EOL;
}

/**
 * Lee una linea de entrada del usuario desde STDIN.
 *
 * @param string $prompt Texto a mostrar como prompt
 * @return string Texto ingresado por el usuario (sin salto de linea)
 */
function leerEntrada(string $prompt): string
{
    echo CYAN . "  {$prompt}" . RESET;
    $input = fgets(STDIN);

    // Si STDIN se cierra (ej: pipe, EOF), retornar string vacio
    if ($input === false) {
        return '';
    }

    return trim($input);
}

/**
 * Muestra un mensaje de exito en verde.
 *
 * @param string $mensaje Mensaje a mostrar
 */
function mostrarExito(string $mensaje): void
{
    echo PHP_EOL . GREEN . BOLD . "  [OK] " . RESET . GREEN . $mensaje . RESET . PHP_EOL;
}

/**
 * Muestra un mensaje de error en rojo.
 *
 * @param string $mensaje Mensaje de error a mostrar
 */
function mostrarError(string $mensaje): void
{
    echo PHP_EOL . RED . BOLD . "  [ERROR] " . RESET . RED . $mensaje . RESET . PHP_EOL;
}

/**
 * Muestra un mensaje informativo en azul.
 *
 * @param string $mensaje Mensaje informativo
 */
function mostrarInfo(string $mensaje): void
{
    echo BLUE . "  {$mensaje}" . RESET . PHP_EOL;
}

/**
 * Pausa la ejecucion hasta que el usuario presione Enter.
 */
function pausar(): void
{
    echo PHP_EOL;
    leerEntrada('Presiona Enter para continuar...');
}

/**
 * Muestra una lista de tareas formateada en la terminal.
 *
 * @param \MiniProject\Task[] $tareas Lista de tareas a mostrar
 */
function mostrarListaTareas(array $tareas): void
{
    if (empty($tareas)) {
        mostrarInfo('No se encontraron tareas.');
        return;
    }

    echo PHP_EOL;
    echo BOLD . "  " . str_pad('ID', 6) . str_pad('Estado', 6) . str_pad('Pri', 6)
       . str_pad('Titulo', 32) . 'Prioridad' . RESET . PHP_EOL;
    echo DIM . "  " . str_repeat('-', 60) . RESET . PHP_EOL;

    foreach ($tareas as $tarea) {
        echo $tarea->formatoLinea() . PHP_EOL;
    }

    echo DIM . "  " . str_repeat('-', 60) . RESET . PHP_EOL;
    echo DIM . "  Total: " . count($tareas) . " tarea(s)" . RESET . PHP_EOL;
}

// ---------------------------------------------------------------
//  Funciones de cada accion del menu
// ---------------------------------------------------------------

/**
 * Accion: Agregar una nueva tarea.
 * Solicita titulo, descripcion y prioridad al usuario.
 *
 * @param TaskService $service Servicio de tareas
 */
function accionAgregarTarea(TaskService $service): void
{
    echo PHP_EOL . MAGENTA . BOLD . "  === AGREGAR NUEVA TAREA ===" . RESET . PHP_EOL . PHP_EOL;

    // Solicitar datos al usuario
    $titulo = leerEntrada('Titulo: ');

    if ($titulo === '') {
        mostrarError('El titulo no puede estar vacio.');
        return;
    }

    $descripcion = leerEntrada('Descripcion (opcional): ');

    echo CYAN . "  Prioridad (" . RED . "alta" . RESET . CYAN . "/" . YELLOW . "media" . RESET . CYAN . "/" . GREEN . "baja" . RESET . CYAN . "): " . RESET;
    $prioridad = trim(fgets(STDIN) ?: 'media');

    // Si el usuario no ingresa nada, usar 'media' como predeterminado
    if ($prioridad === '') {
        $prioridad = 'media';
    }

    try {
        $tarea = $service->crearTarea(
            titulo: $titulo,
            descripcion: $descripcion,
            prioridad: $prioridad,
        );

        mostrarExito("Tarea #{$tarea->id} creada exitosamente.");
        echo PHP_EOL;
        echo $tarea->formatoDetalle() . PHP_EOL;
    } catch (ValidationException $e) {
        mostrarError($e->getMessage());
    }
}

/**
 * Accion: Listar tareas con filtro por estado.
 *
 * @param TaskService $service Servicio de tareas
 */
function accionListarTareas(TaskService $service): void
{
    echo PHP_EOL . MAGENTA . BOLD . "  === LISTAR TAREAS ===" . RESET . PHP_EOL . PHP_EOL;

    echo CYAN . "  Filtrar por estado (" . YELLOW . "pendiente" . RESET . CYAN . "/" . GREEN . "completada" . RESET . CYAN . "/" . BOLD . "todas" . RESET . CYAN . "): " . RESET;
    $filtro = trim(fgets(STDIN) ?: 'todas');

    if ($filtro === '') {
        $filtro = 'todas';
    }

    try {
        $tareas = $service->listarTareas($filtro);
        mostrarListaTareas($tareas);
    } catch (ValidationException $e) {
        mostrarError($e->getMessage());
    }
}

/**
 * Accion: Marcar una tarea como completada por ID.
 *
 * @param TaskService $service Servicio de tareas
 */
function accionCompletarTarea(TaskService $service): void
{
    echo PHP_EOL . MAGENTA . BOLD . "  === COMPLETAR TAREA ===" . RESET . PHP_EOL . PHP_EOL;

    // Primero mostrar las tareas pendientes
    try {
        $pendientes = $service->listarTareas('pendiente');
        if (empty($pendientes)) {
            mostrarInfo('No hay tareas pendientes para completar.');
            return;
        }
        mostrarListaTareas($pendientes);
    } catch (AppException $e) {
        mostrarError($e->getMessage());
        return;
    }

    echo PHP_EOL;
    $id = leerEntrada('ID de la tarea a completar: ');

    if ($id === '') {
        mostrarError('Debes ingresar un ID.');
        return;
    }

    try {
        $tarea = $service->completarTarea($id);
        mostrarExito("Tarea #{$tarea->id} marcada como completada.");
        echo PHP_EOL;
        echo $tarea->formatoDetalle() . PHP_EOL;
    } catch (ValidationException | NotFoundException $e) {
        mostrarError($e->getMessage());
    }
}

/**
 * Accion: Eliminar una tarea por ID.
 *
 * @param TaskService $service Servicio de tareas
 */
function accionEliminarTarea(TaskService $service): void
{
    echo PHP_EOL . MAGENTA . BOLD . "  === ELIMINAR TAREA ===" . RESET . PHP_EOL . PHP_EOL;

    // Mostrar todas las tareas para referencia
    try {
        $todas = $service->listarTareas('todas');
        if (empty($todas)) {
            mostrarInfo('No hay tareas para eliminar.');
            return;
        }
        mostrarListaTareas($todas);
    } catch (AppException $e) {
        mostrarError($e->getMessage());
        return;
    }

    echo PHP_EOL;
    $id = leerEntrada('ID de la tarea a eliminar: ');

    if ($id === '') {
        mostrarError('Debes ingresar un ID.');
        return;
    }

    // Confirmar eliminacion
    $confirmacion = leerEntrada("Seguro que deseas eliminar la tarea #{$id}? (s/n): ");

    if (strtolower($confirmacion) !== 's') {
        mostrarInfo('Eliminacion cancelada.');
        return;
    }

    try {
        $service->eliminarTarea($id);
        mostrarExito("Tarea #{$id} eliminada permanentemente.");
    } catch (ValidationException | NotFoundException $e) {
        mostrarError($e->getMessage());
    }
}

/**
 * Accion: Buscar tareas por palabra clave.
 *
 * @param TaskService $service Servicio de tareas
 */
function accionBuscarTareas(TaskService $service): void
{
    echo PHP_EOL . MAGENTA . BOLD . "  === BUSCAR TAREAS ===" . RESET . PHP_EOL . PHP_EOL;

    $keyword = leerEntrada('Palabra clave: ');

    if ($keyword === '') {
        mostrarError('Debes ingresar una palabra clave.');
        return;
    }

    try {
        $resultados = $service->buscarTareas($keyword);

        echo PHP_EOL . DIM . "  Resultados para: \"{$keyword}\"" . RESET . PHP_EOL;
        mostrarListaTareas($resultados);
    } catch (ValidationException $e) {
        mostrarError($e->getMessage());
    }
}

/**
 * Accion: Exportar tareas a archivo (JSON o CSV).
 *
 * @param TaskService $service Servicio de tareas
 * @param ExportService $exportService Servicio de exportacion
 */
function accionExportarTareas(TaskService $service, ExportService $exportService): void
{
    echo PHP_EOL . MAGENTA . BOLD . "  === EXPORTAR TAREAS ===" . RESET . PHP_EOL . PHP_EOL;

    // Mostrar formatos disponibles
    $formatos = $exportService->formatosDisponibles();
    $formatosStr = implode(', ', $formatos);
    echo CYAN . "  Formato ({$formatosStr}): " . RESET;
    $formato = trim(fgets(STDIN) ?: 'json');

    if ($formato === '') {
        $formato = 'json';
    }

    try {
        $tareas = $service->obtenerTodasParaExportar();

        if (empty($tareas)) {
            mostrarInfo('No hay tareas para exportar.');
            return;
        }

        $ruta = $exportService->exportar(tasks: $tareas, formato: $formato);
        mostrarExito("Tareas exportadas exitosamente.");
        mostrarInfo("Archivo: {$ruta}");
        mostrarInfo("Total exportadas: " . count($tareas) . " tarea(s)");
    } catch (ValidationException | AppException $e) {
        mostrarError($e->getMessage());
    }
}

/**
 * Accion: Mostrar estadisticas de las tareas.
 *
 * @param TaskService $service Servicio de tareas
 */
function accionEstadisticas(TaskService $service): void
{
    try {
        $stats = $service->obtenerEstadisticas();
        echo $service->formatearEstadisticas($stats);
    } catch (AppException $e) {
        mostrarError($e->getMessage());
    }
}

// ---------------------------------------------------------------
//  Bucle principal de la aplicacion
// ---------------------------------------------------------------

/**
 * Funcion principal que ejecuta el bucle de la aplicacion.
 *
 * Inicializa los servicios con inyeccion de dependencias y
 * gestiona el ciclo de vida del menu interactivo.
 */
function main(): void
{
    try {
        // Inicializar la base de datos (Singleton)
        Database::getInstance();

        // Crear servicios con inyeccion de dependencias
        $repository = new TaskRepository();
        $service = new TaskService(repository: $repository);
        $exportService = new ExportService(
            outputDir: __DIR__ . '/data',
        );

        // Bucle principal del menu
        $ejecutando = true;

        while ($ejecutando) {
            limpiarPantalla();
            mostrarBanner();
            mostrarMenu();

            $opcion = leerEntrada('Selecciona una opcion [0-7]: ');

            // Usar match para despachar la opcion seleccionada
            match ($opcion) {
                '1' => accionAgregarTarea($service),
                '2' => accionListarTareas($service),
                '3' => accionCompletarTarea($service),
                '4' => accionEliminarTarea($service),
                '5' => accionBuscarTareas($service),
                '6' => accionExportarTareas($service, $exportService),
                '7' => accionEstadisticas($service),
                '0', 'q', 'salir', 'exit' => $ejecutando = false,
                default => mostrarError("Opcion no valida: '{$opcion}'. Usa un numero del 0 al 7."),
            };

            // Pausar antes de volver al menu (excepto al salir)
            if ($ejecutando) {
                pausar();
            }
        }

        // Mensaje de despedida
        echo PHP_EOL;
        echo GREEN . BOLD . "  Hasta luego! Gracias por usar el Gestor de Tareas." . RESET . PHP_EOL;
        echo PHP_EOL;

    } catch (AppException $e) {
        // Error critico de la aplicacion
        echo PHP_EOL;
        echo RED . BOLD . "  ERROR CRITICO: " . RESET . RED . $e->getMessage() . RESET . PHP_EOL;
        echo DIM . "  Codigo: {$e->getCode()}" . RESET . PHP_EOL;
        echo PHP_EOL;
        exit(1);
    } catch (\Throwable $e) {
        // Error inesperado no manejado
        echo PHP_EOL;
        echo RED . BOLD . "  ERROR INESPERADO: " . RESET . RED . $e->getMessage() . RESET . PHP_EOL;
        echo DIM . "  Archivo: {$e->getFile()}:{$e->getLine()}" . RESET . PHP_EOL;
        echo PHP_EOL;
        exit(2);
    }
}

// Ejecutar la aplicacion
main();
