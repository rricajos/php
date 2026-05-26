<?php
/**
 * PDO ERROR HANDLING - Manejo de errores con PDO
 *
 * PDO ofrece tres modos de manejo de errores. Elegir el correcto
 * es crucial para depuracion, seguridad y estabilidad.
 *
 * Temas cubiertos:
 * - ERRMODE_SILENT (default, peligroso)
 * - ERRMODE_WARNING (visible pero no detiene ejecucion)
 * - ERRMODE_EXCEPTION (recomendado, lanza excepciones)
 * - try/catch con PDOException
 * - errorCode() y errorInfo()
 * - Handler personalizado que registra errores en archivo
 */

echo "=== PDO ERROR HANDLING ===\n\n";

// ============================================================
// Ejemplo 1: ERRMODE_SILENT (modo por defecto, peligroso)
// ============================================================
// Los errores pasan desapercibidos, no se muestra nada

echo "=== Ejemplo 1: ERRMODE_SILENT (default) ===\n\n";

$db = new PDO('sqlite::memory:');
// ERRMODE_SILENT es el default, pero lo configuramos explicitamente
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

echo "Modo: ERRMODE_SILENT - Los errores NO se reportan automaticamente\n\n";

// Ejecutar una query con error de sintaxis
$resultado = $db->query("SELECT * FROM tabla_que_no_existe");

// $resultado es false, pero no hay error visible
echo "Resultado de query invalida: " . var_export($resultado, true) . "\n";

// Debemos verificar MANUALMENTE si hubo error
if ($resultado === false) {
    // errorCode() retorna el codigo SQLSTATE (5 caracteres)
    $codigoError = $db->errorCode();
    echo "Codigo de error (SQLSTATE): $codigoError\n";

    // errorInfo() retorna un array con mas detalle
    $infoError = $db->errorInfo();
    echo "Info de error:\n";
    echo "  [0] SQLSTATE: {$infoError[0]}\n";
    echo "  [1] Codigo driver: {$infoError[1]}\n";
    echo "  [2] Mensaje: {$infoError[2]}\n";
}

echo "\nPROBLEMA: Si olvidas verificar, el error pasa desapercibido\n";
echo "y puedes tener comportamiento inesperado en tu aplicacion.\n";

// Ejemplo del peligro: usar el resultado false como si fuera valido
$resultado = $db->query("SELECT * FROM tabla_fantasma");
// Esto causaria un error fatal si intentas: $resultado->fetch()
// Fatal error: Call to a member function fetch() on bool
echo "Si intentas \$resultado->fetch() con false, obtienes error fatal\n";

echo "\n";

// ============================================================
// Ejemplo 2: ERRMODE_WARNING (genera advertencias PHP)
// ============================================================
// Los errores generan E_WARNING pero la ejecucion continua

echo "=== Ejemplo 2: ERRMODE_WARNING ===\n\n";

$db2 = new PDO('sqlite::memory:');
$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

echo "Modo: ERRMODE_WARNING - Genera advertencias pero NO detiene la ejecucion\n\n";

// Capturar el warning con un error handler personalizado
$warningCapturado = null;
set_error_handler(function ($errno, $errstr) use (&$warningCapturado) {
    $warningCapturado = $errstr;
    return true;  // Prevenir que el warning se muestre normalmente
});

$resultado = $db2->query("SELECTO INVALIDO SINTAXIS");

restore_error_handler();

echo "Warning capturado: " . ($warningCapturado ?? 'ninguno') . "\n";
echo "Resultado: " . var_export($resultado, true) . "\n";
echo "\nPROBLEMA: La ejecucion continua despues del error.\n";
echo "Puedes tener datos corruptos si no verificas cada operacion.\n";

echo "\n";

// ============================================================
// Ejemplo 3: ERRMODE_EXCEPTION (recomendado)
// ============================================================
// Los errores lanzan PDOException que DEBE ser capturada

echo "=== Ejemplo 3: ERRMODE_EXCEPTION (RECOMENDADO) ===\n\n";

$db3 = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "Modo: ERRMODE_EXCEPTION - Lanza excepciones que DEBES capturar\n\n";

// Crear tabla de prueba
$db3->exec("
    CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        nombre TEXT NOT NULL
    )
");
$db3->exec("INSERT INTO usuarios (email, nombre) VALUES ('ana@test.com', 'Ana')");

// --- Error de sintaxis SQL ---
echo "--- Error de sintaxis SQL ---\n";
try {
    $db3->query("SELECCIONAR todo DE usuarios");
} catch (PDOException $e) {
    echo "PDOException capturada:\n";
    echo "  Mensaje: {$e->getMessage()}\n";
    echo "  Codigo SQLSTATE: {$e->getCode()}\n";
    echo "  Archivo: " . basename($e->getFile()) . ":{$e->getLine()}\n";
}

// --- Error de constraint (UNIQUE violation) ---
echo "\n--- Error de constraint UNIQUE ---\n";
try {
    $stmt = $db3->prepare("INSERT INTO usuarios (email, nombre) VALUES (:email, :nombre)");
    $stmt->execute([':email' => 'ana@test.com', ':nombre' => 'Ana Duplicada']);
} catch (PDOException $e) {
    echo "Violacion UNIQUE capturada:\n";
    echo "  Mensaje: {$e->getMessage()}\n";
    echo "  SQLSTATE: {$e->getCode()}\n";

    // Podemos actuar segun el tipo de error
    if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
        echo "  Accion: Email ya registrado, sugerir recuperacion de contrasena\n";
    }
}

// --- Error de tabla inexistente ---
echo "\n--- Error de tabla inexistente ---\n";
try {
    $db3->query("SELECT * FROM pedidos_inexistentes");
} catch (PDOException $e) {
    echo "Tabla no encontrada:\n";
    echo "  {$e->getMessage()}\n";
}

// --- Error en prepared statement ---
echo "\n--- Error en prepared statement ---\n";
try {
    $stmt = $db3->prepare("INSERT INTO usuarios (email, nombre) VALUES (:email, :nombre)");
    // Faltan parametros
    $stmt->execute([':email' => 'test@test.com']);
    // Nota: el comportamiento depende del driver, algunos permiten params faltantes como NULL
} catch (PDOException $e) {
    echo "Error en prepared statement:\n";
    echo "  {$e->getMessage()}\n";
}

echo "\nVENTAJA: No puedes ignorar accidentalmente un error.\n";
echo "La ejecucion se detiene si no capturas la excepcion.\n";

echo "\n";

// ============================================================
// Ejemplo 4: Manejo avanzado de PDOException
// ============================================================
// Extraer informacion detallada del error para depuracion

echo "=== Ejemplo 4: Analisis detallado de PDOException ===\n\n";

/**
 * Analizar un PDOException y retornar informacion estructurada
 *
 * @param PDOException $e La excepcion a analizar
 * @return array Informacion estructurada del error
 */
function analizarErrorPDO(PDOException $e): array
{
    $info = [
        'mensaje'    => $e->getMessage(),
        'sqlstate'   => $e->getCode(),    // Codigo SQLSTATE (string de 5 chars)
        'archivo'    => $e->getFile(),
        'linea'      => $e->getLine(),
        'traza'      => $e->getTraceAsString(),
    ];

    // Extraer informacion del driver desde errorInfo si esta disponible
    // El formato de errorInfo es:
    // [0] => SQLSTATE
    // [1] => Codigo de error especifico del driver
    // [2] => Mensaje de error especifico del driver
    if ($e->errorInfo ?? null) {
        $info['driver_code'] = $e->errorInfo[1] ?? null;
        $info['driver_message'] = $e->errorInfo[2] ?? null;
    }

    // Clasificar el tipo de error por SQLSTATE
    $info['tipo'] = match(true) {
        str_starts_with($info['sqlstate'], '23') => 'CONSTRAINT_VIOLATION',
        str_starts_with($info['sqlstate'], '42') => 'SYNTAX_ERROR',
        str_starts_with($info['sqlstate'], '08') => 'CONNECTION_ERROR',
        str_starts_with($info['sqlstate'], '22') => 'DATA_ERROR',
        str_starts_with($info['sqlstate'], '28') => 'AUTH_ERROR',
        $info['sqlstate'] === 'HY000'            => 'GENERAL_ERROR',
        default => 'UNKNOWN',
    };

    return $info;
}

// Provocar diferentes errores y analizarlos
$erroresPrueba = [
    "INSERT INTO usuarios (email, nombre) VALUES ('ana@test.com', 'Dup')",
    "SELECT * FROM tabla_fantasma",
    "INSERT INTO usuarios (email) VALUES ('sin_nombre')",  // Falta campo NOT NULL
];

foreach ($erroresPrueba as $i => $sql) {
    try {
        $db3->exec($sql);
    } catch (PDOException $e) {
        $analisis = analizarErrorPDO($e);
        echo "Error " . ($i + 1) . ":\n";
        echo "  SQL: $sql\n";
        echo "  Tipo: {$analisis['tipo']}\n";
        echo "  SQLSTATE: {$analisis['sqlstate']}\n";
        echo "  Mensaje: {$analisis['mensaje']}\n\n";
    }
}

echo "\n";

// ============================================================
// Ejemplo 5: errorCode() y errorInfo() en statements
// ============================================================
// Los errores pueden venir del objeto PDO o del PDOStatement

echo "=== Ejemplo 5: errorCode() y errorInfo() ===\n\n";

// Los errores estan disponibles tanto en PDO como en PDOStatement
echo "--- errorCode/errorInfo en PDO y PDOStatement ---\n\n";

// Crear una conexion en modo SILENT para demostrar errorCode/errorInfo
$dbSilent = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
]);

$dbSilent->exec("CREATE TABLE test (id INTEGER PRIMARY KEY, valor TEXT UNIQUE)");
$dbSilent->exec("INSERT INTO test VALUES (1, 'unico')");

// Error en PDO (via exec)
$resultado = $dbSilent->exec("INSERT INTO test VALUES (2, 'unico')");  // Duplicado UNIQUE

echo "Error en PDO:\n";
echo "  errorCode(): " . $dbSilent->errorCode() . "\n";
$info = $dbSilent->errorInfo();
echo "  errorInfo():\n";
echo "    SQLSTATE:    {$info[0]}\n";
echo "    Driver code: {$info[1]}\n";
echo "    Mensaje:     {$info[2]}\n\n";

// Error en PDOStatement (via execute)
$stmt = $dbSilent->prepare("INSERT INTO test VALUES (:id, :valor)");
$stmt->execute([':id' => 3, ':valor' => 'unico']);  // Duplicado

echo "Error en PDOStatement:\n";
echo "  errorCode(): " . $stmt->errorCode() . "\n";
$info = $stmt->errorInfo();
echo "  errorInfo():\n";
echo "    SQLSTATE:    {$info[0]}\n";
echo "    Driver code: {$info[1]}\n";
echo "    Mensaje:     {$info[2]}\n\n";

// Codigos SQLSTATE comunes
echo "Codigos SQLSTATE comunes:\n";
echo "  00000 - Sin error (exito)\n";
echo "  23000 - Violacion de constraint (UNIQUE, FOREIGN KEY, NOT NULL)\n";
echo "  42000 - Error de sintaxis o violacion de acceso\n";
echo "  42S02 - Tabla no encontrada\n";
echo "  08001 - No se puede conectar al servidor\n";
echo "  08004 - Servidor rechazo la conexion\n";
echo "  22001 - Dato demasiado largo para la columna\n";
echo "  HY000 - Error general\n";

echo "\n";

// ============================================================
// Ejemplo 6: Handler personalizado con log a archivo
// ============================================================
// Clase que captura errores PDO y los registra en un archivo

echo "=== Ejemplo 6: Handler personalizado con log a archivo ===\n\n";

/**
 * Clase para manejar errores de base de datos
 * Registra errores en archivo y proporciona mensajes seguros al usuario
 */
class DatabaseErrorHandler
{
    private string $archivoLog;
    private bool $modoDesarrollo;

    /**
     * @param string $archivoLog     Ruta del archivo de log
     * @param bool   $modoDesarrollo Si true, muestra detalles; si false, mensajes genericos
     */
    public function __construct(string $archivoLog, bool $modoDesarrollo = false)
    {
        $this->archivoLog = $archivoLog;
        $this->modoDesarrollo = $modoDesarrollo;
    }

    /**
     * Manejar un error PDO: loguear detalles y retornar mensaje seguro
     *
     * @param PDOException $e        La excepcion PDO
     * @param string       $contexto Descripcion de que se intentaba hacer
     * @param array        $datos    Datos adicionales para el log (sin informacion sensible)
     * @return array Respuesta segura para el usuario
     */
    public function manejar(PDOException $e, string $contexto = '', array $datos = []): array
    {
        // Construir entrada de log con toda la informacion
        $entrada = [
            'fecha'     => date('Y-m-d H:i:s'),
            'contexto'  => $contexto,
            'sqlstate'  => $e->getCode(),
            'mensaje'   => $e->getMessage(),
            'archivo'   => $e->getFile() . ':' . $e->getLine(),
            'datos'     => $datos,
            'traza'     => $e->getTraceAsString(),
        ];

        // Escribir al archivo de log
        $this->escribirLog($entrada);

        // Determinar mensaje seguro para el usuario
        $mensajeUsuario = $this->obtenerMensajeSeguro($e);

        // En modo desarrollo, incluir detalles tecnicos
        if ($this->modoDesarrollo) {
            return [
                'error'   => true,
                'mensaje' => $mensajeUsuario,
                'debug'   => [
                    'sqlstate'  => $e->getCode(),
                    'detalle'   => $e->getMessage(),
                    'archivo'   => $e->getFile() . ':' . $e->getLine(),
                ],
            ];
        }

        // En produccion, solo el mensaje generico
        return [
            'error'   => true,
            'mensaje' => $mensajeUsuario,
        ];
    }

    /**
     * Obtener un mensaje seguro para mostrar al usuario
     * NUNCA exponer detalles de SQL o estructura de BD al usuario
     */
    private function obtenerMensajeSeguro(PDOException $e): string
    {
        $sqlstate = (string) $e->getCode();
        $mensaje = $e->getMessage();

        // Mapear errores comunes a mensajes amigables
        return match(true) {
            // Violacion de UNIQUE constraint
            str_contains($mensaje, 'UNIQUE constraint failed') ||
            str_starts_with($sqlstate, '23')
                => 'El registro ya existe. Verifique los datos e intente nuevamente.',

            // Tabla no encontrada
            str_contains($mensaje, 'no such table') ||
            $sqlstate === '42S02'
                => 'Error interno del sistema. Contacte al administrador. [REF: TBL]',

            // Error de sintaxis
            str_starts_with($sqlstate, '42')
                => 'Error interno del sistema. Contacte al administrador. [REF: SYN]',

            // Error de conexion
            str_starts_with($sqlstate, '08')
                => 'No se pudo conectar al servidor. Intente mas tarde.',

            // Error de autenticacion
            str_starts_with($sqlstate, '28')
                => 'Error de configuracion del sistema. Contacte al administrador.',

            // Campo NOT NULL violado
            str_contains($mensaje, 'NOT NULL constraint')
                => 'Faltan datos obligatorios. Complete todos los campos requeridos.',

            // Error general
            default => 'Ocurrio un error inesperado. Intente nuevamente.',
        };
    }

    /**
     * Escribir entrada en el archivo de log
     */
    private function escribirLog(array $entrada): void
    {
        $linea = sprintf(
            "[%s] SQLSTATE[%s] %s | Contexto: %s | Archivo: %s\n",
            $entrada['fecha'],
            $entrada['sqlstate'],
            $entrada['mensaje'],
            $entrada['contexto'],
            $entrada['archivo']
        );

        if (!empty($entrada['datos'])) {
            $linea .= "  Datos: " . json_encode($entrada['datos'], JSON_UNESCAPED_UNICODE) . "\n";
        }

        // Escribir al archivo (crear si no existe, agregar al final)
        file_put_contents($this->archivoLog, $linea, FILE_APPEND | LOCK_EX);
    }

    /**
     * Leer las ultimas N lineas del log
     */
    public function leerUltimasEntradas(int $cantidad = 10): string
    {
        if (!file_exists($this->archivoLog)) {
            return "Archivo de log no encontrado\n";
        }

        $contenido = file_get_contents($this->archivoLog);
        $lineas = explode("\n", trim($contenido));
        $ultimas = array_slice($lineas, -$cantidad);
        return implode("\n", $ultimas);
    }

    /**
     * Limpiar el archivo de log
     */
    public function limpiarLog(): void
    {
        file_put_contents($this->archivoLog, '');
    }
}

// Crear el handler con archivo de log temporal
$archivoLog = sys_get_temp_dir() . '/pdo_error_log_' . date('Ymd') . '.log';
$handler = new DatabaseErrorHandler($archivoLog, modoDesarrollo: true);

// Conexion para pruebas
$db4 = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$db4->exec("CREATE TABLE clientes (id INTEGER PRIMARY KEY, email TEXT UNIQUE, nombre TEXT NOT NULL)");
$db4->exec("INSERT INTO clientes VALUES (1, 'maria@test.com', 'Maria')");

// --- Simular diferentes errores y manejarlos ---

// Error 1: Insertar email duplicado
echo "--- Error 1: Email duplicado ---\n";
try {
    $db4->prepare("INSERT INTO clientes (email, nombre) VALUES (?, ?)")
        ->execute(['maria@test.com', 'Maria 2']);
} catch (PDOException $e) {
    $respuesta = $handler->manejar($e, 'Registrar nuevo cliente', ['email' => 'maria@test.com']);
    echo "Respuesta al usuario: {$respuesta['mensaje']}\n";
    if (isset($respuesta['debug'])) {
        echo "Debug (solo en desarrollo): {$respuesta['debug']['detalle']}\n";
    }
}

// Error 2: Tabla inexistente
echo "\n--- Error 2: Tabla inexistente ---\n";
try {
    $db4->query("SELECT * FROM ordenes_compra");
} catch (PDOException $e) {
    $respuesta = $handler->manejar($e, 'Listar ordenes de compra');
    echo "Respuesta al usuario: {$respuesta['mensaje']}\n";
}

// Error 3: Campo NOT NULL
echo "\n--- Error 3: Campo obligatorio faltante ---\n";
try {
    $db4->exec("INSERT INTO clientes (email) VALUES ('test@test.com')");
} catch (PDOException $e) {
    $respuesta = $handler->manejar($e, 'Crear cliente sin nombre');
    echo "Respuesta al usuario: {$respuesta['mensaje']}\n";
}

// Mostrar el log generado
echo "\n--- Contenido del archivo de log ---\n";
echo "Archivo: $archivoLog\n\n";
echo $handler->leerUltimasEntradas(10) . "\n";

// Modo produccion: sin detalles tecnicos
echo "\n--- Modo produccion (sin debug) ---\n";
$handlerProd = new DatabaseErrorHandler($archivoLog, modoDesarrollo: false);
try {
    $db4->query("QUERY INVALIDA TOTAL");
} catch (PDOException $e) {
    $respuesta = $handlerProd->manejar($e, 'Query de prueba');
    echo "Respuesta al usuario: {$respuesta['mensaje']}\n";
    echo "Tiene debug? " . (isset($respuesta['debug']) ? 'SI' : 'NO') . "\n";
    echo "(En produccion NUNCA se exponen detalles tecnicos al usuario)\n";
}

// Limpiar archivo de log temporal
$handler->limpiarLog();
@unlink($archivoLog);

echo "\n=== Resumen de manejo de errores ===\n";
echo "1. SIEMPRE usar ERRMODE_EXCEPTION (nunca SILENT ni WARNING)\n";
echo "2. Envolver operaciones de BD en try/catch\n";
echo "3. NUNCA mostrar errores SQL al usuario en produccion\n";
echo "4. Registrar errores detallados en archivos de log\n";
echo "5. Usar SQLSTATE para clasificar y manejar errores especificos\n";
echo "6. Separar mensajes tecnicos (log) de mensajes de usuario (UI)\n";
?>
