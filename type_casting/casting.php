<?php
// ============================================================================
// TYPE CASTING (CONVERSION DE TIPOS) EN PHP
// ============================================================================
// PHP es un lenguaje de tipado debil (weakly typed), lo que significa que
// convierte automaticamente entre tipos segun el contexto. Esto se llama
// "type juggling" o malabarismo de tipos. Tambien podemos convertir
// explicitamente con operadores de cast o funciones especificas.
//
// Entender las reglas de conversion es CRITICO para evitar bugs sutiles,
// especialmente al procesar datos de usuario (formularios, APIs, bases de datos).
// ============================================================================

// ============================================================================
// Ejemplo 1: Casting explicito con operadores (int), (float), (string), (bool)
// ============================================================================
// Los operadores de cast se colocan antes del valor: (tipo) $valor
// Crean un NUEVO valor del tipo indicado sin modificar el original.

echo "=== Ejemplo 1: Casting explicito con operadores ===\n\n";

// --- Casting a int ---
echo "--- Casting a (int) / (integer) ---\n";
$valores = [
    '42',           // String numerico entero
    '3.99',         // String numerico decimal -> se trunca (no redondea)
    '123abc',       // String que empieza con numero -> toma la parte numerica
    'abc',          // String sin numero al inicio -> 0
    '',             // String vacio -> 0
    true,           // Booleano true -> 1
    false,          // Booleano false -> 0
    null,           // Null -> 0
    3.7,            // Float -> se trunca a 3 (no redondea)
    -2.9,           // Float negativo -> se trunca a -2
    INF,            // Infinito -> 0 (comportamiento indefinido!)
    NAN,            // Not a Number -> 0
];

foreach ($valores as $valor) {
    $tipo = gettype($valor);
    $repr = match(true) {
        is_null($valor) => 'null',
        is_bool($valor) => $valor ? 'true' : 'false',
        is_float($valor) && is_nan($valor) => 'NAN',
        is_float($valor) && is_infinite($valor) => 'INF',
        is_string($valor) && $valor === '' => "'' (vacio)",
        is_string($valor) => "'{$valor}'",
        default => (string)$valor,
    };
    echo "  (int) {$repr} ({$tipo}) = " . (int)$valor . "\n";
}
echo "\n";

// --- Casting a float ---
echo "--- Casting a (float) / (double) ---\n";
$valoresFloat = ['3.14', '1e5', '0xFF', '  42.5  ', 'abc', true, null, ''];
foreach ($valoresFloat as $valor) {
    $repr = is_null($valor) ? 'null' : (is_bool($valor) ? ($valor ? 'true' : 'false') :
        ($valor === '' ? "'' (vacio)" : "'{$valor}'"));
    echo "  (float) {$repr} = " . var_export((float)$valor, true) . "\n";
}
echo "\n";

// --- Casting a string ---
echo "--- Casting a (string) ---\n";
$valoresString = [42, 3.14, true, false, null, 0, -5, 1e10, INF, NAN];
foreach ($valoresString as $valor) {
    $repr = match(true) {
        is_null($valor) => 'null',
        is_bool($valor) => $valor ? 'true' : 'false',
        is_float($valor) && is_nan($valor) => 'NAN',
        is_float($valor) && is_infinite($valor) => 'INF',
        default => var_export($valor, true),
    };
    echo "  (string) {$repr} = '" . (string)$valor . "'\n";
}
echo "  NOTA: true->'1', false->'' (vacio), null->'' (vacio)\n";
echo "        NAN->'NAN', INF->'INF'\n\n";

// --- Casting a bool ---
echo "--- Casting a (bool) ---\n";
echo "  Valores que se convierten a FALSE (falsy):\n";
$falsy = [0, 0.0, -0.0, '', '0', [], null, false];
foreach ($falsy as $valor) {
    $repr = match(true) {
        is_null($valor) => 'null',
        is_bool($valor) => 'false',
        is_array($valor) => '[] (array vacio)',
        is_string($valor) && $valor === '' => "'' (string vacio)",
        is_string($valor) => "'{$valor}'",
        is_float($valor) && $valor == 0 => var_export($valor, true),
        default => var_export($valor, true),
    };
    echo "    (bool) {$repr} = " . var_export((bool)$valor, true) . "\n";
}
echo "\n  Todo lo demas es TRUE (truthy):\n";
$truthy = [1, -1, 0.1, 'a', '00', ' ', 'false', [0], new stdClass()];
foreach ($truthy as $valor) {
    $repr = match(true) {
        is_object($valor) => 'new stdClass()',
        is_array($valor) => '[0] (array con elementos)',
        is_string($valor) => "'{$valor}'",
        default => var_export($valor, true),
    };
    echo "    (bool) {$repr} = " . var_export((bool)$valor, true) . "\n";
}
echo "  CUIDADO: '0' es false, pero '00', ' ' y 'false' son true!\n\n";


// ============================================================================
// Ejemplo 2: intval(), floatval(), strval() vs operadores de cast
// ============================================================================
// Las funciones de conversion ofrecen funcionalidad adicional, como
// especificar la base numerica con intval().

echo "=== Ejemplo 2: Funciones de conversion vs operadores ===\n\n";

// --- intval() puede especificar la base numerica ---
echo "--- intval() con diferentes bases ---\n";
echo "  intval('0xFF', 16) = " . intval('0xFF', 16) . " (hexadecimal)\n";      // 255
echo "  intval('0xFF')     = " . intval('0xFF') . " (auto-detecta hex con 0x)\n"; // 255
echo "  intval('FF', 16)   = " . intval('FF', 16) . " (hex sin prefijo)\n";     // 255
echo "  intval('0b1010', 2)= " . intval('0b1010', 2) . " (binario)\n";          // 10
echo "  intval('1010', 2)  = " . intval('1010', 2) . " (binario sin prefijo)\n"; // 10
echo "  intval('0777', 8)  = " . intval('0777', 8) . " (octal)\n";              // 511
echo "  intval('777', 8)   = " . intval('777', 8) . " (octal sin prefijo)\n";   // 511
echo "  intval('42', 10)   = " . intval('42', 10) . " (decimal explicito)\n\n";  // 42

// Diferencia sutil: (int) ignora la base, intval() la respeta
echo "  (int) '0xFF'       = " . (int)'0xFF' . " (cast ignora hex!)\n";         // 0
echo "  intval('0xFF', 16) = " . intval('0xFF', 16) . " (funcion respeta hex)\n\n"; // 255

// --- floatval() / doubleval() ---
echo "--- floatval() ---\n";
echo "  floatval('1,234.56') = " . floatval('1,234.56') . " (coma como miles)\n";
echo "  floatval('1.234,56') = " . floatval('1.234,56') . " (formato europeo - MAL!)\n";
echo "  floatval('  3.14  ') = " . floatval('  3.14  ') . " (ignora espacios)\n";
echo "  floatval('1e5')      = " . floatval('1e5') . " (notacion cientifica)\n";
echo "  floatval('abc')      = " . floatval('abc') . "\n\n";

// --- strval() vs (string) ---
echo "--- strval() ---\n";
echo "  strval(42)    = '" . strval(42) . "'\n";
echo "  strval(3.14)  = '" . strval(3.14) . "'\n";
echo "  strval(true)  = '" . strval(true) . "'\n";
echo "  strval(false) = '" . strval(false) . "' (vacio)\n";
echo "  strval(null)  = '" . strval(null) . "' (vacio)\n";
// strval() en arrays genera un error; (string) en arrays tambien
// echo strval([1,2,3]); // Error!
echo "  strval(array) -> genera un error (no se puede convertir)\n\n";

// --- settype() modifica la variable original (a diferencia de los casts) ---
echo "--- settype() - modifica la variable in-place ---\n";
$dato = "42.7";
echo "  Antes: \$dato = '{$dato}' (tipo: " . gettype($dato) . ")\n";

settype($dato, 'integer'); // Modifica $dato directamente
echo "  Despues de settype(\$dato, 'integer'): {$dato} (tipo: " . gettype($dato) . ")\n";

$dato2 = 0;
settype($dato2, 'boolean');
echo "  settype(0, 'boolean'): " . var_export($dato2, true) . " (tipo: " . gettype($dato2) . ")\n";

$dato3 = "hola";
settype($dato3, 'array');
echo "  settype('hola', 'array'): " . json_encode($dato3) . " (tipo: " . gettype($dato3) . ")\n\n";


// ============================================================================
// Ejemplo 3: Casting a (array) y (object)
// ============================================================================
// Las conversiones entre array y object tienen comportamientos interesantes,
// especialmente con propiedades privadas/protegidas.

echo "=== Ejemplo 3: Casting a (array) y (object) ===\n\n";

// --- Escalar a array ---
echo "--- Escalares a array ---\n";
echo "  (array) 42       = " . json_encode((array)42) . "\n";         // [42]
echo "  (array) 'hola'   = " . json_encode((array)'hola') . "\n";     // ["hola"]
echo "  (array) true     = " . json_encode((array)true) . "\n";       // [true]
echo "  (array) null     = " . json_encode((array)null) . "\n";       // []
echo "  NOTA: null a array da array VACIO, no [null]\n\n";

// --- Objeto a array ---
echo "--- Objeto a array ---\n";
$objeto = new stdClass();
$objeto->nombre = 'Laptop';
$objeto->precio = 15000;
$objeto->disponible = true;

$arrayDesdeObjeto = (array)$objeto;
echo "  stdClass a array: " . json_encode($arrayDesdeObjeto) . "\n";

// Con clases que tienen propiedades privadas/protegidas: las claves se modifican
class Vehiculo {
    public string $marca = 'Toyota';
    protected string $modelo = 'Corolla';
    private int $anio = 2024;
}

$vehiculo = new Vehiculo();
$arrayVehiculo = (array)$vehiculo;
echo "\n  Vehiculo a array (con propiedades privadas/protegidas):\n";
foreach ($arrayVehiculo as $clave => $valor) {
    // Las propiedades protegidas tienen prefijo \0*\0
    // Las privadas tienen prefijo \0NombreClase\0
    $claveVisible = str_replace("\0", '\\0', $clave);
    echo "    Clave: '{$claveVisible}' => {$valor}\n";
}
echo "  NOTA: Las claves de propiedades privadas incluyen el nombre de la clase\n";
echo "        con caracteres null (\\0). Esto hace dificil acceder a ellas.\n\n";

// --- Array a objeto ---
echo "--- Array a objeto ---\n";
$datos = ['nombre' => 'Maria', 'edad' => 28, 'activo' => true];
$objetoDesdeDatos = (object)$datos;
echo "  Array asociativo a objeto:\n";
echo "    nombre: {$objetoDesdeDatos->nombre}\n";
echo "    edad: {$objetoDesdeDatos->edad}\n";
echo "    activo: " . var_export($objetoDesdeDatos->activo, true) . "\n";

// Arrays con claves numericas
$numerico = ['primero', 'segundo', 'tercero'];
$objNumerico = (object)$numerico;
echo "\n  Array numerico a objeto: no se puede acceder con ->0\n";
echo "  Pero si con llaves: \$obj->{'0'} = " . $objNumerico->{'0'} . "\n";

// Array anidado a objeto (solo convierte el primer nivel)
$anidado = ['persona' => ['nombre' => 'Juan', 'edad' => 35], 'pais' => 'Mexico'];
$objAnidado = (object)$anidado;
echo "\n  Array anidado: solo el primer nivel se convierte a objeto\n";
echo "  \$obj->pais = {$objAnidado->pais}\n";
echo "  \$obj->persona sigue siendo array: " . gettype($objAnidado->persona) . "\n";
echo "  \$obj->persona['nombre'] = {$objAnidado->persona['nombre']}\n\n";


// ============================================================================
// Ejemplo 4: Type juggling en comparaciones (== vs ===)
// ============================================================================
// El operador == hace conversion de tipos antes de comparar (loose comparison).
// El operador === compara valor Y tipo (strict comparison).
// Esto causa muchas sorpresas y bugs si no se entiende bien.

echo "=== Ejemplo 4: Type juggling en comparaciones ===\n\n";

// --- Tabla de comparaciones sorprendentes con == ---
echo "--- Comparaciones sorprendentes con == (loose) ---\n";
$comparaciones = [
    [0, 'abc',       "0 == 'abc'"],        // true en PHP < 8, false en PHP 8+
    [0, '',          "0 == ''"],            // true en PHP < 8, false en PHP 8+
    [0, null,        "0 == null"],          // true
    [0, false,       "0 == false"],         // true
    ['', null,       "'' == null"],         // true
    ['', false,      "'' == false"],        // true
    [null, false,    "null == false"],      // true
    ['0', null,      "'0' == null"],        // false (esto es diferente!)
    ['0', false,     "'0' == false"],       // true
    ['1', true,      "'1' == true"],        // true
    ['0', true,      "'0' == true"],        // false (PHP 8+)
    [1, '1',         "1 == '1'"],           // true
    [1, '01',        "1 == '01'"],          // true
    [1, '1.0',       "1 == '1.0'"],        // true
    [100, '1e2',     "100 == '1e2'"],       // true (notacion cientifica!)
    ['0x1A', 26,     "'0x1A' == 26"],       // false en PHP 8+ (true en PHP < 8)
    ['php', 0,       "'php' == 0"],         // false en PHP 8+ (true en PHP < 8)
    [[], false,      "[] == false"],        // true
    [[0], [false],   "[0] == [false]"],     // true (compara recursivamente con ==)
];

foreach ($comparaciones as [$a, $b, $etiqueta]) {
    $resultado = $a == $b ? 'true ' : 'false';
    $estricto = $a === $b ? 'true ' : 'false';
    echo "  {$etiqueta}  ==: {$resultado}  ===: {$estricto}\n";
}

echo "\n  REGLA DE ORO: Siempre usa === a menos que tengas una razon\n";
echo "  muy especifica para usar ==.\n\n";

// --- Demostrar bugs reales causados por type juggling ---
echo "--- Bugs reales causados por type juggling ---\n\n";

// Bug 1: Busqueda en array con in_array
$roles = ['admin', 'editor', 'viewer'];
echo "  Bug 1: in_array(0, ['admin', 'editor', 'viewer'])\n";
echo "  Resultado sin strict: " . var_export(in_array(0, $roles), true) . "\n";
echo "  Resultado con strict: " . var_export(in_array(0, $roles, true), true) . "\n";
echo "  0 == 'admin' es " . var_export(0 == 'admin', true) . " en PHP 8+\n\n";

// Bug 2: switch/case usa comparacion loose (==)
echo "  Bug 2: switch/case usa comparacion loose\n";
$input = '0';
switch ($input) {
    case false:
        echo "  switch('0'): Entro al case false (porque '0' == false)\n";
        break;
    case '0':
        echo "  switch('0'): Entro al case '0' (correcto en PHP 8+)\n";
        break;
}
echo "  Solucion: usar match() en PHP 8+ (usa comparacion estricta)\n";

$inputMatch = '0';
$resultadoMatch = match($inputMatch) {
    false => "case false",
    '0' => "case '0' (match usa ===, mas predecible)",
    default => "otro"
};
echo "  match('0'): {$resultadoMatch}\n\n";

// Bug 3: Comparar strings numericos
echo "  Bug 3: Comparar strings que parecen numeros\n";
echo "  '0x1A' == '26': " . var_export('0x1A' == '26', true) . "\n"; // PHP 8+: false
echo "  '1e2' == '100': " . var_export('1e2' == '100', true) . "\n"; // true (ambos son 100.0)
echo "  '1' == '01': " . var_export('1' == '01', true) . "\n";       // true
echo "  '10' == '1e1': " . var_export('10' == '1e1', true) . "\n\n"; // true


// ============================================================================
// Ejemplo 5: Casos extremos (edge cases) de conversion
// ============================================================================
// Recopilacion de conversiones contraintuitivas que todo desarrollador
// de PHP deberia conocer para evitar bugs.

echo "=== Ejemplo 5: Edge cases de conversion ===\n\n";

// --- Strings parcialmente numericos ---
echo "--- Strings parcialmente numericos ---\n";
echo "  (int) '123abc'     = " . (int)'123abc' . " (toma la parte numerica)\n";
echo "  (int) '0x1A'       = " . (int)'0x1A' . " (no detecta hex con cast!)\n";
echo "  (int) '  42  '     = " . (int)'  42  ' . " (ignora espacios)\n";
echo "  (int) '3.14'       = " . (int)'3.14' . " (trunca decimal)\n";
echo "  (int) '+42'        = " . (int)'+42' . " (acepta signo positivo)\n";
echo "  (int) '--42'       = " . (int)'--42' . " (doble negativo: 0)\n";
echo "  (int) '1_000_000'  = " . (int)'1_000_000' . " (no soporta separadores en strings)\n";
echo "  Pero en codigo: 1_000_000 = " . 1_000_000 . " (si soporta en literales PHP 7.4+)\n\n";

// --- Flotantes y precision ---
echo "--- Precision de flotantes ---\n";
echo "  0.1 + 0.2 == 0.3: " . var_export(0.1 + 0.2 == 0.3, true) . " (CUIDADO!)\n";
echo "  0.1 + 0.2 = " . sprintf('%.20f', 0.1 + 0.2) . "\n";
echo "  0.3       = " . sprintf('%.20f', 0.3) . "\n";
echo "  Solucion: usar bcmath o comparar con epsilon\n";
$epsilon = 0.00001;
echo "  abs((0.1 + 0.2) - 0.3) < epsilon: " .
    var_export(abs((0.1 + 0.2) - 0.3) < $epsilon, true) . "\n\n";

// --- Integer overflow ---
echo "--- Desbordamiento de enteros ---\n";
echo "  PHP_INT_MAX   = " . PHP_INT_MAX . "\n";
echo "  PHP_INT_MAX+1 = " . (PHP_INT_MAX + 1) . " (se convierte a float!)\n";
echo "  Tipo de PHP_INT_MAX+1: " . gettype(PHP_INT_MAX + 1) . "\n";
echo "  (int)(PHP_INT_MAX + 1) = " . (int)(PHP_INT_MAX + 1) . " (overflow!)\n\n";

// --- null vs '' vs 0 vs false vs '0' vs [] ---
echo "--- La familia de los 'falsy' values ---\n";
echo "  ┌────────────┬──────────┬────────────┬───────────┬──────────┐\n";
echo "  │ Valor      │ (bool)   │ (int)      │ (string)  │ (array)  │\n";
echo "  ├────────────┼──────────┼────────────┼───────────┼──────────┤\n";

$falsyValues = [
    ['null', null],
    ["''", ''],
    ['0', 0],
    ['0.0', 0.0],
    ['false', false],
    ["'0'", '0'],
    ['[]', []],
];

foreach ($falsyValues as [$etiqueta, $valor]) {
    $boolStr = var_export((bool)$valor, true);
    $intStr = is_array($valor) ? '0*' : (string)(int)$valor;
    $strStr = is_array($valor) ? 'error' : "'" . (string)$valor . "'";
    $arrStr = json_encode((array)$valor);
    echo sprintf("  │ %-10s │ %-8s │ %-10s │ %-9s │ %-8s │\n",
        $etiqueta, $boolStr, $intStr, $strStr, $arrStr);
}
echo "  └────────────┴──────────┴────────────┴───────────┴──────────┘\n";
echo "  * (int)[] genera error, 0 es el resultado con aviso\n\n";


// ============================================================================
// Ejemplo 6: Sanitizacion de entrada de usuario con casting apropiado
// ============================================================================
// Caso practico: procesar datos de un formulario web, aplicando las
// conversiones correctas para evitar inyecciones, errores de tipo
// y comportamientos inesperados.

echo "=== Ejemplo 6: Sanitizacion de entrada con casting ===\n\n";

// Simular datos que llegan de un formulario o API (todo es string en HTTP)
$datosEntrada = [
    'nombre' => '  Maria Garcia  ',
    'edad' => '28',
    'email' => 'MARIA@Example.COM',
    'precio' => '1,299.50',
    'cantidad' => '3abc',          // Dato contaminado
    'descuento' => '15.5',
    'activo' => 'true',            // Booleano como string
    'tags' => 'php,laravel,mysql', // Lista separada por comas
    'codigo_postal' => '01234',    // Codigo postal que empieza con 0
    'telefono' => '+52 (55) 1234-5678',
    'notas' => '',                 // Campo vacio
    'acepta_terminos' => '1',      // Checkbox tipico
    'pagina' => '-3',              // Numero invalido (negativo)
    'limite' => '999999999999',    // Potencialmente muy grande
];

echo "  Datos crudos (como llegan del formulario):\n";
foreach ($datosEntrada as $campo => $valor) {
    echo "    {$campo}: '{$valor}'\n";
}
echo "\n";

// Funciones de sanitizacion reutilizables
function sanitizarString(string $valor, int $maxLength = 255): string {
    $valor = trim($valor);                    // Quitar espacios al inicio y final
    $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); // Prevenir XSS
    return mb_substr($valor, 0, $maxLength);  // Limitar longitud
}

function sanitizarEntero(string $valor, int $min = 0, int $max = PHP_INT_MAX): int {
    $entero = (int) $valor;
    return max($min, min($max, $entero)); // Clamping al rango valido
}

function sanitizarDecimal(string $valor, int $decimales = 2): float {
    // Manejar formato con comas como separador de miles
    $limpio = str_replace(',', '', $valor);
    $decimal = (float) $limpio;
    return round($decimal, $decimales);
}

function sanitizarBooleano(string $valor): bool {
    // Aceptar multiples formatos de booleano
    $valorLower = strtolower(trim($valor));
    return in_array($valorLower, ['true', '1', 'yes', 'si', 'on'], true);
}

function sanitizarEmail(string $valor): ?string {
    $email = strtolower(trim($valor));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function sanitizarTelefono(string $valor): string {
    // Solo mantener digitos y el signo +
    return preg_replace('/[^\d+]/', '', $valor);
}

function sanitizarLista(string $valor, string $separador = ','): array {
    if (trim($valor) === '') return [];
    return array_map('trim', explode($separador, $valor));
}

function sanitizarCodigoPostal(string $valor): string {
    // Los codigos postales NO se convierten a int (perderian ceros iniciales)
    return preg_replace('/\D/', '', $valor);
}

// Aplicar sanitizacion
$datosSanitizados = [
    'nombre' => sanitizarString($datosEntrada['nombre'], 100),
    'edad' => sanitizarEntero($datosEntrada['edad'], 0, 150),
    'email' => sanitizarEmail($datosEntrada['email']),
    'precio' => sanitizarDecimal($datosEntrada['precio']),
    'cantidad' => sanitizarEntero($datosEntrada['cantidad'], 1, 1000),
    'descuento' => sanitizarDecimal($datosEntrada['descuento']),
    'activo' => sanitizarBooleano($datosEntrada['activo']),
    'tags' => sanitizarLista($datosEntrada['tags']),
    'codigo_postal' => sanitizarCodigoPostal($datosEntrada['codigo_postal']),
    'telefono' => sanitizarTelefono($datosEntrada['telefono']),
    'notas' => sanitizarString($datosEntrada['notas']),
    'acepta_terminos' => sanitizarBooleano($datosEntrada['acepta_terminos']),
    'pagina' => sanitizarEntero($datosEntrada['pagina'], 1, 10000),
    'limite' => sanitizarEntero($datosEntrada['limite'], 1, 100),
];

echo "  Datos sanitizados:\n";
foreach ($datosSanitizados as $campo => $valor) {
    $valorStr = match(true) {
        is_bool($valor) => var_export($valor, true),
        is_array($valor) => json_encode($valor),
        is_null($valor) => 'null (invalido)',
        default => var_export($valor, true),
    };
    $tipo = gettype($valor);
    echo "    {$campo}: {$valorStr} ({$tipo})\n";
}

echo "\n  Observaciones:\n";
echo "    - nombre: espacios eliminados, HTML escapado\n";
echo "    - edad: convertido a entero, limitado 0-150\n";
echo "    - email: normalizado a minusculas, validado\n";
echo "    - precio: comas eliminadas, redondeado a 2 decimales\n";
echo "    - cantidad: '3abc' se convierte a 3 (parte numerica)\n";
echo "    - activo: 'true' como string se convierte a bool true\n";
echo "    - tags: string se convierte a array\n";
echo "    - codigo_postal: se mantiene como STRING (no int) para conservar el 0 inicial\n";
echo "    - telefono: solo digitos y +\n";
echo "    - pagina: -3 se limita a 1 (minimo)\n";
echo "    - limite: 999999999999 se limita a 100 (maximo)\n";
?>
