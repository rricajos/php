<?php
/**
 * PDO PREPARED STATEMENTS - Sentencias preparadas con PDO
 *
 * Las sentencias preparadas son FUNDAMENTALES para la seguridad.
 * Separan la estructura SQL de los datos, previniendo inyeccion SQL.
 *
 * Temas cubiertos:
 * - Placeholders posicionales (?)
 * - Placeholders con nombre (:nombre)
 * - bindParam vs bindValue
 * - Tipos de binding (PDO::PARAM_INT, PARAM_STR, etc.)
 * - Multiples ejecuciones con diferentes parametros
 * - LIKE con sentencias preparadas
 */

// Configurar base de datos SQLite en memoria para los ejemplos
$db = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// Crear tabla de ejemplo con datos
$db->exec("
    CREATE TABLE productos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        categoria TEXT NOT NULL,
        precio REAL NOT NULL,
        stock INTEGER NOT NULL DEFAULT 0,
        activo INTEGER NOT NULL DEFAULT 1
    )
");

// Insertar datos de prueba
$db->exec("
    INSERT INTO productos (nombre, categoria, precio, stock) VALUES
    ('Laptop HP ProBook', 'Electronica', 1299.99, 15),
    ('Mouse Logitech MX', 'Perifericos', 79.50, 200),
    ('Teclado Mecanico', 'Perifericos', 149.99, 85),
    ('Monitor 27 pulgadas', 'Electronica', 449.00, 30),
    ('Cable HDMI 2m', 'Accesorios', 12.99, 500),
    ('Webcam HD 1080p', 'Perifericos', 59.99, 120)
");

echo "Base de datos creada con 6 productos de prueba\n\n";

// ============================================================
// Ejemplo 1: Placeholders posicionales (?)
// ============================================================
// Se usan signos de interrogacion, y los valores se pasan en orden

echo "=== Ejemplo 1: Placeholders posicionales (?) ===\n\n";

// Buscar productos por categoria y precio minimo
$sql = "SELECT nombre, precio FROM productos WHERE categoria = ? AND precio > ?";
$stmt = $db->prepare($sql);

// Ejecutar pasando un array de valores (en orden)
$stmt->execute(['Perifericos', 50.00]);
$resultados = $stmt->fetchAll();

echo "Perifericos con precio > 50:\n";
foreach ($resultados as $producto) {
    echo "  - {$producto['nombre']}: \${$producto['precio']}\n";
}

// Reutilizar la misma sentencia con diferentes parametros
$stmt->execute(['Electronica', 500.00]);
$resultados = $stmt->fetchAll();

echo "\nElectronica con precio > 500:\n";
foreach ($resultados as $producto) {
    echo "  - {$producto['nombre']}: \${$producto['precio']}\n";
}

// IMPORTANTE: El orden de los valores DEBE coincidir con los ? en el SQL
// INCORRECTO: $stmt->execute([50.00, 'Perifericos']); // Orden invertido!
echo "\nNota: El orden de valores debe coincidir con el orden de los '?'\n";

echo "\n";

// ============================================================
// Ejemplo 2: Placeholders con nombre (:nombre)
// ============================================================
// Mas legibles, el orden no importa, se referencian por nombre

echo "=== Ejemplo 2: Placeholders con nombre (:nombre) ===\n\n";

// Buscar productos por rango de precio
$sql = "SELECT nombre, precio, stock
        FROM productos
        WHERE precio BETWEEN :precio_min AND :precio_max
        AND stock >= :stock_minimo
        ORDER BY precio ASC";

$stmt = $db->prepare($sql);

// Ejecutar pasando un array asociativo (el orden NO importa)
$stmt->execute([
    ':stock_minimo' => 10,
    ':precio_max'   => 500.00,  // Orden diferente al SQL, y eso esta bien
    ':precio_min'   => 50.00,
]);

$resultados = $stmt->fetchAll();
echo "Productos entre \$50 y \$500 con stock >= 10:\n";
foreach ($resultados as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']} (stock: {$p['stock']})\n";
}

// Tambien funciona sin los dos puntos en las claves del array
$stmt->execute([
    'precio_min'   => 10.00,
    'precio_max'   => 100.00,
    'stock_minimo' => 50,
]);

$resultados = $stmt->fetchAll();
echo "\nProductos entre \$10 y \$100 con stock >= 50:\n";
foreach ($resultados as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']} (stock: {$p['stock']})\n";
}

echo "\n";

// ============================================================
// Ejemplo 3: bindParam vs bindValue (diferencia crucial)
// ============================================================
// bindValue: vincula el VALOR actual de la variable
// bindParam: vincula una REFERENCIA a la variable (se evalua al ejecutar)

echo "=== Ejemplo 3: bindParam vs bindValue ===\n\n";

// --- bindValue: captura el valor en el momento del bind ---
echo "--- bindValue (captura el valor al hacer bind) ---\n";
$stmt = $db->prepare("SELECT nombre FROM productos WHERE id = :id");

$id = 1;
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$id = 999;  // Cambiar el valor DESPUES del bind
$stmt->execute();
$resultado = $stmt->fetch();
echo "Con bindValue, id=1, luego cambio a 999:\n";
echo "  Resultado: " . ($resultado ? $resultado['nombre'] : 'No encontrado') . "\n";
echo "  (Usa el valor 1, el momento del bind)\n\n";

// --- bindParam: captura una REFERENCIA, se evalua al ejecutar ---
echo "--- bindParam (referencia, se evalua al ejecutar) ---\n";
$stmt = $db->prepare("SELECT nombre FROM productos WHERE id = :id");

$id = 1;
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$id = 3;  // Cambiar el valor DESPUES del bind pero ANTES de execute
$stmt->execute();
$resultado = $stmt->fetch();
echo "Con bindParam, id=1, luego cambio a 3:\n";
echo "  Resultado: " . ($resultado ? $resultado['nombre'] : 'No encontrado') . "\n";
echo "  (Usa el valor 3, evaluado al momento del execute)\n\n";

// Caso practico: iterar con bindParam (eficiente para bucles)
echo "--- Caso practico: iterar con bindParam ---\n";
$stmt = $db->prepare("SELECT nombre, precio FROM productos WHERE id = :id");
$stmt->bindParam(':id', $idActual, PDO::PARAM_INT);

for ($idActual = 1; $idActual <= 3; $idActual++) {
    $stmt->execute();  // Usa el valor actual de $idActual
    $producto = $stmt->fetch();
    echo "  ID $idActual: {$producto['nombre']} (\${$producto['precio']})\n";
}

echo "\n";

// ============================================================
// Ejemplo 4: Tipos de binding (PDO::PARAM_*)
// ============================================================
// Especificar el tipo de dato asegura el tratamiento correcto

echo "=== Ejemplo 4: Tipos de binding PDO::PARAM_* ===\n\n";

// Crear tabla para demostrar tipos
$db->exec("
    CREATE TABLE configuracion (
        clave TEXT PRIMARY KEY,
        valor_texto TEXT,
        valor_entero INTEGER,
        valor_booleano INTEGER,
        datos_binarios BLOB
    )
");

$stmt = $db->prepare("
    INSERT INTO configuracion (clave, valor_texto, valor_entero, valor_booleano, datos_binarios)
    VALUES (:clave, :texto, :entero, :booleano, :binario)
");

// PDO::PARAM_STR - Cadenas de texto (el mas comun)
$stmt->bindValue(':clave', 'app_nombre', PDO::PARAM_STR);
$stmt->bindValue(':texto', 'Mi Aplicacion v2.0', PDO::PARAM_STR);

// PDO::PARAM_INT - Enteros
$stmt->bindValue(':entero', 42, PDO::PARAM_INT);

// PDO::PARAM_BOOL - Booleanos (se almacenan como 0 o 1)
$stmt->bindValue(':booleano', true, PDO::PARAM_BOOL);

// PDO::PARAM_LOB - Objetos grandes binarios (BLOBs)
$datosBinarios = random_bytes(16);  // 16 bytes aleatorios
$stmt->bindValue(':binario', $datosBinarios, PDO::PARAM_LOB);

$stmt->execute();

// PDO::PARAM_NULL - Valores nulos
$stmt2 = $db->prepare("
    INSERT INTO configuracion (clave, valor_texto, valor_entero, valor_booleano, datos_binarios)
    VALUES (:clave, :texto, :entero, :booleano, :binario)
");
$stmt2->bindValue(':clave', 'sin_datos', PDO::PARAM_STR);
$stmt2->bindValue(':texto', null, PDO::PARAM_NULL);
$stmt2->bindValue(':entero', null, PDO::PARAM_NULL);
$stmt2->bindValue(':booleano', null, PDO::PARAM_NULL);
$stmt2->bindValue(':binario', null, PDO::PARAM_NULL);
$stmt2->execute();

// Verificar los datos
$resultado = $db->query("SELECT * FROM configuracion")->fetchAll();
echo "Tipos de datos almacenados:\n";
foreach ($resultado as $fila) {
    echo "  Clave: {$fila['clave']}\n";
    echo "    Texto: " . ($fila['valor_texto'] ?? 'NULL') . "\n";
    echo "    Entero: " . ($fila['valor_entero'] ?? 'NULL') . "\n";
    echo "    Booleano: " . ($fila['valor_booleano'] ?? 'NULL') . "\n";
    echo "    Binario: " . ($fila['datos_binarios'] ? bin2hex($fila['datos_binarios']) : 'NULL') . "\n\n";
}

// Comparacion: sin tipo vs con tipo
echo "IMPORTANTE - Diferencia de tipos:\n";
echo "  Sin tipo:  \$stmt->bindValue(':id', '5')    -> trata '5' como STRING\n";
echo "  Con tipo:  \$stmt->bindValue(':id', 5, PDO::PARAM_INT) -> trata 5 como INTEGER\n";
echo "  Esto importa especialmente en LIMIT/OFFSET con MySQL nativo\n";

echo "\n";

// ============================================================
// Ejemplo 5: Multiples ejecuciones con diferentes parametros
// ============================================================
// Una sentencia preparada se compila una vez y se ejecuta muchas veces

echo "=== Ejemplo 5: Multiples ejecuciones con diferentes parametros ===\n\n";

// Crear tabla de log
$db->exec("
    CREATE TABLE log_eventos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT NOT NULL,
        mensaje TEXT NOT NULL,
        fecha TEXT NOT NULL
    )
");

// Preparar UNA VEZ, ejecutar MUCHAS veces (eficiente)
$stmt = $db->prepare("
    INSERT INTO log_eventos (tipo, mensaje, fecha)
    VALUES (:tipo, :mensaje, :fecha)
");

$eventos = [
    ['tipo' => 'INFO',  'mensaje' => 'Usuario inicio sesion',      'fecha' => '2026-05-26 08:00:00'],
    ['tipo' => 'WARN',  'mensaje' => 'Intento de acceso fallido',  'fecha' => '2026-05-26 08:05:23'],
    ['tipo' => 'ERROR', 'mensaje' => 'Error de conexion a la BD',  'fecha' => '2026-05-26 08:10:45'],
    ['tipo' => 'INFO',  'mensaje' => 'Backup completado',          'fecha' => '2026-05-26 09:00:00'],
    ['tipo' => 'INFO',  'mensaje' => 'Usuario cerro sesion',       'fecha' => '2026-05-26 17:30:00'],
];

$inicio = microtime(true);
foreach ($eventos as $evento) {
    $stmt->execute($evento);  // Reutiliza la sentencia compilada
}
$tiempoPreparado = microtime(true) - $inicio;

echo "Insertados " . count($eventos) . " eventos usando sentencia preparada\n";
echo "Tiempo: " . number_format($tiempoPreparado * 1000, 4) . " ms\n\n";

// Comparacion: sin preparar (inseguro y mas lento para multiples inserciones)
// NOTA: Esto es solo para demostrar la diferencia, NUNCA concatenar datos del usuario
$db->exec("DELETE FROM log_eventos");  // Limpiar para re-insertar

$inicio = microtime(true);
foreach ($eventos as $evento) {
    // INSEGURO: No hacer esto con datos del usuario
    $tipo = $db->quote($evento['tipo']);
    $mensaje = $db->quote($evento['mensaje']);
    $fecha = $db->quote($evento['fecha']);
    $db->exec("INSERT INTO log_eventos (tipo, mensaje, fecha) VALUES ($tipo, $mensaje, $fecha)");
}
$tiempoSinPreparar = microtime(true) - $inicio;

echo "Insertados " . count($eventos) . " eventos sin sentencia preparada\n";
echo "Tiempo: " . number_format($tiempoSinPreparar * 1000, 4) . " ms\n\n";

// Con bindParam en un bucle (alternativa elegante)
echo "--- Alternativa con bindParam y referencia ---\n";
$db->exec("DELETE FROM log_eventos");

$stmt = $db->prepare("
    INSERT INTO log_eventos (tipo, mensaje, fecha)
    VALUES (:tipo, :mensaje, :fecha)
");

// Vincular referencias a variables
$stmt->bindParam(':tipo', $tipo);
$stmt->bindParam(':mensaje', $mensaje);
$stmt->bindParam(':fecha', $fecha);

foreach ($eventos as $evento) {
    // Al cambiar las variables, bindParam usa los nuevos valores
    $tipo    = $evento['tipo'];
    $mensaje = $evento['mensaje'];
    $fecha   = $evento['fecha'];
    $stmt->execute();
}

$total = $db->query("SELECT COUNT(*) FROM log_eventos")->fetchColumn();
echo "Total eventos insertados con bindParam: $total\n";

echo "\n";

// ============================================================
// Ejemplo 6: LIKE con sentencias preparadas
// ============================================================
// Los comodines % y _ deben ir en el VALOR, no en el SQL

echo "=== Ejemplo 6: LIKE con sentencias preparadas ===\n\n";

// CORRECTO: El comodin va en el valor del parametro
echo "--- Busqueda con LIKE (forma correcta) ---\n";

// Buscar productos que contengan una palabra
$busqueda = 'Logitech';
$stmt = $db->prepare("SELECT nombre, precio FROM productos WHERE nombre LIKE :busqueda");
$stmt->execute([':busqueda' => "%$busqueda%"]);  // % en el VALOR

$resultados = $stmt->fetchAll();
echo "Buscar '%$busqueda%':\n";
foreach ($resultados as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']}\n";
}

// Buscar productos que empiecen con una letra
$stmt->execute([':busqueda' => 'M%']);  // Empieza con M
$resultados = $stmt->fetchAll();
echo "\nProductos que empiezan con 'M':\n";
foreach ($resultados as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']}\n";
}

// Buscar productos que terminen con cierto texto
$stmt->execute([':busqueda' => '%1080p']);  // Termina con 1080p
$resultados = $stmt->fetchAll();
echo "\nProductos que terminan con '1080p':\n";
foreach ($resultados as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']}\n";
}

// INCORRECTO: Poner % en el SQL (no funciona con prepared statements)
// $stmt = $db->prepare("SELECT * FROM productos WHERE nombre LIKE '%:busqueda%'");
// Esto NO funciona porque :busqueda esta dentro de una cadena literal

// CORRECTO con concatenacion en SQL (alternativa menos comun)
// $stmt = $db->prepare("SELECT * FROM productos WHERE nombre LIKE '%' || :busqueda || '%'");
// $stmt->execute([':busqueda' => 'Logitech']);

// --- Escapar caracteres especiales de LIKE ---
echo "\n--- Escapar caracteres especiales de LIKE ---\n";

/**
 * Escapa los caracteres especiales de LIKE en una busqueda
 * Los caracteres % y _ son comodines en LIKE, debemos escaparlos
 * si el usuario los incluye en su busqueda
 *
 * @param string $valor  El texto de busqueda del usuario
 * @return string Texto con caracteres LIKE escapados
 */
function escaparLike(string $valor): string
{
    // Escapar \, %, y _ (en ese orden para no re-escapar)
    return str_replace(
        ['\\', '%', '_'],
        ['\\\\', '\\%', '\\_'],
        $valor
    );
}

// El usuario busca literalmente "27_pulgadas" o "50%"
$busquedaUsuario = '27';
$busquedaSegura = escaparLike($busquedaUsuario);

$stmt = $db->prepare("
    SELECT nombre, precio
    FROM productos
    WHERE nombre LIKE :busqueda ESCAPE '\\'
");
$stmt->execute([':busqueda' => "%$busquedaSegura%"]);

$resultados = $stmt->fetchAll();
echo "Busqueda segura con '$busquedaUsuario':\n";
foreach ($resultados as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']}\n";
}

echo "\n=== Resumen de sentencias preparadas ===\n";
echo "1. Siempre usar prepared statements con datos externos\n";
echo "2. Usar :nombre para queries complejas (mas legible)\n";
echo "3. Usar ? para queries simples con pocos parametros\n";
echo "4. bindParam = referencia (bueno para bucles)\n";
echo "5. bindValue = valor actual (bueno para valores puntuales)\n";
echo "6. LIKE: los comodines % van en el VALOR, no en el SQL\n";
?>
