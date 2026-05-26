<?php
/**
 * Iteradores SPL - Ejemplos con ArrayIterator, FilterIterator,
 * LimitIterator y RegexIterator.
 *
 * Los iteradores SPL proporcionan formas flexibles y reutilizables
 * de recorrer y manipular colecciones de datos.
 */

// ============================================
// Ejemplo 1: ArrayIterator - Iterar y manipular arrays como objetos
// ============================================

echo "=== Ejemplo 1: ArrayIterator ===\n";

$datos = ['PHP', 'Python', 'JavaScript', 'Go', 'Rust'];
$iterador = new ArrayIterator($datos);

// Iteración básica con foreach
echo "Iteración con foreach:\n";
foreach ($iterador as $clave => $valor) {
    echo "  [$clave] => $valor\n";
}

// Iteración manual con control del cursor
echo "\nIteración manual:\n";
$iterador->rewind();
while ($iterador->valid()) {
    echo "  Clave: " . $iterador->key() . " | Valor: " . $iterador->current() . "\n";
    $iterador->next();
}

// Manipular durante la iteración
echo "\nManipulación del iterador:\n";
$iterador->append('C#');           // Agregar al final
$iterador->offsetSet(1, 'Ruby');   // Reemplazar en posición 1
$iterador->offsetUnset(3);         // Eliminar posición 3

echo "Después de modificaciones:\n";
foreach ($iterador as $clave => $valor) {
    echo "  [$clave] => $valor\n";
}

// Ordenar el iterador
$iterador->asort(); // Ordenar por valor manteniendo claves
echo "\nOrdenado alfabéticamente:\n";
foreach ($iterador as $clave => $valor) {
    echo "  [$clave] => $valor\n";
}

// ArrayIterator con arrays asociativos
echo "\nCon array asociativo:\n";
$config = new ArrayIterator([
    'host'     => 'localhost',
    'puerto'   => 3306,
    'usuario'  => 'admin',
    'base'     => 'mi_app',
]);

echo "Cantidad: " . $config->count() . "\n";
echo "¿Existe 'host'? " . ($config->offsetExists('host') ? 'Sí' : 'No') . "\n";
echo "Valor de 'puerto': " . $config->offsetGet('puerto') . "\n";

// ============================================
// Ejemplo 2: FilterIterator - Filtrar elementos durante la iteración
// ============================================

echo "\n=== Ejemplo 2: FilterIterator ===\n";

/**
 * FilterIterator es abstracto. Debemos extenderlo e implementar accept().
 * accept() retorna true para los elementos que pasan el filtro.
 */

// Filtro para números pares
class FiltroPares extends FilterIterator
{
    public function accept(): bool
    {
        return $this->current() % 2 === 0;
    }
}

$numeros = new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
$pares = new FiltroPares($numeros);

echo "Números pares (FilterIterator):\n";
foreach ($pares as $clave => $valor) {
    echo "  [$clave] => $valor\n";
}

// Filtro para strings que comienzan con mayúscula
class FiltroMayuscula extends FilterIterator
{
    public function accept(): bool
    {
        $primer = $this->current()[0] ?? '';
        return ctype_upper($primer);
    }
}

$palabras = new ArrayIterator(['Hola', 'mundo', 'PHP', 'es', 'Genial', 'siempre']);
$mayusculas = new FiltroMayuscula($palabras);

echo "\nPalabras que inician con mayúscula:\n";
foreach ($mayusculas as $palabra) {
    echo "  - $palabra\n";
}

// CallbackFilterIterator - Filtro con función anónima (más flexible)
echo "\nCallbackFilterIterator (productos caros > $50):\n";

$productos = new ArrayIterator([
    ['nombre' => 'Mouse', 'precio' => 29.99],
    ['nombre' => 'Teclado', 'precio' => 79.99],
    ['nombre' => 'Monitor', 'precio' => 299.99],
    ['nombre' => 'Cable USB', 'precio' => 9.99],
    ['nombre' => 'Webcam', 'precio' => 59.99],
]);

$caros = new CallbackFilterIterator($productos, function ($item) {
    return $item['precio'] > 50;
});

foreach ($caros as $producto) {
    echo "  {$producto['nombre']}: \${$producto['precio']}\n";
}

// ============================================
// Ejemplo 3: LimitIterator - Paginación y límites
// ============================================

echo "\n=== Ejemplo 3: LimitIterator ===\n";

$todos = new ArrayIterator(range(1, 20)); // Números del 1 al 20

// LimitIterator(iterador, offset, cantidad)
// Obtener los primeros 5 elementos
$primeros5 = new LimitIterator($todos, 0, 5);
echo "Primeros 5 elementos:\n";
foreach ($primeros5 as $num) {
    echo "  $num";
}
echo "\n";

// Obtener elementos del 6 al 10 (offset 5, cantidad 5)
$pagina2 = new LimitIterator($todos, 5, 5);
echo "\nPágina 2 (elementos 6-10):\n";
foreach ($pagina2 as $num) {
    echo "  $num";
}
echo "\n";

// Caso práctico: Sistema de paginación
echo "\nSistema de paginación:\n";

$registros = new ArrayIterator([
    'Registro A', 'Registro B', 'Registro C', 'Registro D',
    'Registro E', 'Registro F', 'Registro G', 'Registro H',
    'Registro I', 'Registro J', 'Registro K', 'Registro L',
]);

$porPagina = 4;
$totalRegistros = count($registros);
$totalPaginas = ceil($totalRegistros / $porPagina);

for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
    $offset = ($pagina - 1) * $porPagina;
    $paginada = new LimitIterator($registros, $offset, $porPagina);

    echo "  Página $pagina de $totalPaginas: ";
    $items = [];
    foreach ($paginada as $item) {
        $items[] = $item;
    }
    echo implode(', ', $items) . "\n";
}

// Combinar FilterIterator con LimitIterator
echo "\nCombinar filtro + límite (primeros 3 pares de 1-20):\n";
$numerosGrandes = new ArrayIterator(range(1, 20));
$soloPares = new CallbackFilterIterator($numerosGrandes, fn($n) => $n % 2 === 0);
$primeros3Pares = new LimitIterator($soloPares, 0, 3);

foreach ($primeros3Pares as $num) {
    echo "  $num";
}
echo "\n";

// ============================================
// Ejemplo 4: RegexIterator - Filtrar con expresiones regulares
// ============================================

echo "\n=== Ejemplo 4: RegexIterator ===\n";

// Filtrar archivos por extensión
$archivos = new ArrayIterator([
    'index.php', 'styles.css', 'app.js', 'config.php',
    'logo.png', 'helper.php', 'script.js', 'data.json',
    'template.html', 'database.php',
]);

// Obtener solo archivos PHP
$archivosPHP = new RegexIterator($archivos, '/\.php$/');
echo "Archivos PHP:\n";
foreach ($archivosPHP as $archivo) {
    echo "  $archivo\n";
}

// Filtrar emails válidos
$emails = new ArrayIterator([
    'usuario@mail.com',
    'invalido@',
    'admin@servidor.org',
    'sin-arroba.com',
    'test@ejemplo.co',
    '@sinusuario.com',
]);

$emailsValidos = new RegexIterator($emails, '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
echo "\nEmails válidos:\n";
foreach ($emailsValidos as $email) {
    echo "  $email\n";
}

// RegexIterator con modo MATCH - capturar grupos
echo "\nModo GET_MATCH - Extraer partes de URLs:\n";
$urls = new ArrayIterator([
    'https://www.google.com/search',
    'http://localhost:8080/api',
    'https://github.com/php/php-src',
    'ftp://files.example.com/docs',
]);

$regexUrls = new RegexIterator(
    $urls,
    '/^(https?):\/\/([^\/]+)(\/.*)?$/',
    RegexIterator::GET_MATCH
);

foreach ($regexUrls as $match) {
    $protocolo = $match[1];
    $dominio = $match[2];
    $ruta = $match[3] ?? '/';
    echo "  Protocolo: $protocolo | Dominio: $dominio | Ruta: $ruta\n";
}

// RegexIterator con modo REPLACE
echo "\nModo REPLACE - Censurar teléfonos:\n";
$textos = new ArrayIterator([
    'Llámame al 555-1234',
    'Mi número es 800-999-0000',
    'Sin teléfono aquí',
    'Contacto: 123-456-7890',
]);

$censurado = new RegexIterator($textos, '/\d{3}[-]?\d{3,4}[-]?\d{0,4}/');
$censurado->replacement = '***-***-****';
$censurado->setMode(RegexIterator::REPLACE);

foreach ($censurado as $texto) {
    echo "  $texto\n";
}

// ============================================
// Ejemplo 5: Caso práctico - Pipeline de iteradores
// ============================================

echo "\n=== Ejemplo 5: Pipeline de iteradores ===\n";

/**
 * Combinar múltiples iteradores para crear un pipeline de procesamiento.
 * Simulamos un sistema que procesa registros de log.
 */

$logs = new ArrayIterator([
    '[2026-01-15 08:30:00] INFO: Sistema iniciado',
    '[2026-01-15 08:31:00] ERROR: Conexión fallida a BD',
    '[2026-01-15 08:32:00] WARNING: Memoria alta (85%)',
    '[2026-01-15 08:33:00] INFO: Usuario admin conectado',
    '[2026-01-15 08:34:00] ERROR: Timeout en API externa',
    '[2026-01-15 08:35:00] DEBUG: Consulta SQL ejecutada',
    '[2026-01-15 08:36:00] ERROR: Archivo no encontrado',
    '[2026-01-15 08:37:00] INFO: Reporte generado',
    '[2026-01-15 08:38:00] WARNING: Disco al 90%',
    '[2026-01-15 08:39:00] ERROR: Permiso denegado',
]);

// Paso 1: Filtrar solo errores con RegexIterator
$soloErrores = new RegexIterator($logs, '/ERROR/');

// Paso 2: Limitar a los primeros 3 errores
$primeros3Errores = new LimitIterator($soloErrores, 0, 3);

echo "Pipeline: Logs -> Filtrar ERROR -> Limitar a 3:\n";
foreach ($primeros3Errores as $log) {
    echo "  $log\n";
}

// Otro pipeline: Extraer timestamps de todos los logs
echo "\nExtraer timestamps con RegexIterator GET_MATCH:\n";
$timestamps = new RegexIterator(
    $logs,
    '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+):/',
    RegexIterator::GET_MATCH
);

// Limitar a 5 resultados
$limitado = new LimitIterator($timestamps, 0, 5);
foreach ($limitado as $match) {
    echo "  Hora: {$match[1]} | Nivel: {$match[2]}\n";
}

// Append Iterator: combinar múltiples iteradores en secuencia
echo "\nAppendIterator - Combinar múltiples fuentes:\n";
$fuente1 = new ArrayIterator(['Dato 1A', 'Dato 1B']);
$fuente2 = new ArrayIterator(['Dato 2A', 'Dato 2B', 'Dato 2C']);
$fuente3 = new ArrayIterator(['Dato 3A']);

$combinado = new AppendIterator();
$combinado->append($fuente1);
$combinado->append($fuente2);
$combinado->append($fuente3);

foreach ($combinado as $dato) {
    echo "  $dato\n";
}
echo "Total combinado: " . iterator_count($combinado) . " elementos\n";

?>
