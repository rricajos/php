<?php
// ============================================================================
// LA PALABRA CLAVE 'use' EN CLOSURES DE PHP
// ============================================================================
// La palabra clave 'use' permite que las closures capturen variables del
// ambito externo (scope padre). Sin 'use', las closures solo ven sus propios
// parametros y variables locales (a diferencia de JavaScript, donde las
// closures capturan automaticamente).
// ============================================================================

// ============================================================================
// Ejemplo 1: Captura por valor vs captura por referencia
// ============================================================================
// Por defecto, 'use' captura por VALOR (copia). Para capturar por referencia,
// se usa el operador & antes de la variable: use (&$variable).

echo "=== Ejemplo 1: Captura por valor vs captura por referencia ===\n\n";

// --- Captura por valor ---
$nombre = "Carlos";
$edad = 30;

$saludar = function () use ($nombre, $edad) {
    echo "  Hola, soy {$nombre} y tengo {$edad} anios.\n";
};

$saludar(); // Carlos, 30

// Modificar las variables originales NO afecta la closure (se copio el valor)
$nombre = "Pedro";
$edad = 45;

$saludar(); // Sigue siendo Carlos, 30 (la closure tiene su propia copia)
echo "  Variable externa ahora: {$nombre}, {$edad}\n\n";

// --- Captura por referencia ---
$intentos = 0;

$incrementarIntentos = function () use (&$intentos) {
    $intentos++;
    echo "  Intento #{$intentos}\n";
};

$incrementarIntentos(); // 1
$incrementarIntentos(); // 2
$incrementarIntentos(); // 3
echo "  Variable externa \$intentos: {$intentos}\n\n"; // 3 (la closure modifico la original)

// --- Mezclar captura por valor y por referencia ---
$prefijo = "[LOG]";
$mensajes = [];

$registrar = function (string $texto) use ($prefijo, &$mensajes) {
    // $prefijo es por valor (no cambia si el original cambia)
    // $mensajes es por referencia (las modificaciones se reflejan afuera)
    $mensajes[] = "{$prefijo} {$texto}";
};

$prefijo = "[ERROR]"; // Este cambio no afecta la closure
$registrar("Servidor iniciado");
$registrar("Conexion establecida");
$registrar("Peticion recibida");

echo "  Mensajes registrados:\n";
foreach ($mensajes as $msg) {
    echo "    {$msg}\n"; // Todos con [LOG], no [ERROR]
}
echo "\n";


// ============================================================================
// Ejemplo 2: Enlace temprano (early binding) - el valor se captura al definir
// ============================================================================
// La captura por valor ocurre en el momento de la DEFINICION de la closure,
// no en el momento de su ejecucion. Esto es crucial para entender su
// comportamiento.

echo "=== Ejemplo 2: Enlace temprano (early binding) ===\n\n";

$tasa_impuesto = 0.16;

$calcularImpuesto = function (float $monto) use ($tasa_impuesto): float {
    return $monto * $tasa_impuesto;
};

echo "  Impuesto de \$1000 con tasa 0.16: \$" . $calcularImpuesto(1000) . "\n";

// Cambiar la tasa despues de definir la closure no tiene efecto
$tasa_impuesto = 0.21;
echo "  Impuesto de \$1000 (tasa cambiada a 0.21, pero closure tiene 0.16): \$"
    . $calcularImpuesto(1000) . "\n\n";

// Demostracion con array - el array se copia al momento de la definicion
$configuracion = ['modo' => 'produccion', 'debug' => false];

$mostrarConfig = function () use ($configuracion) {
    echo "  Config en closure: modo={$configuracion['modo']}, debug=" .
        ($configuracion['debug'] ? 'si' : 'no') . "\n";
};

$configuracion['modo'] = 'desarrollo'; // No afecta la closure
$configuracion['debug'] = true;        // No afecta la closure

$mostrarConfig(); // modo=produccion, debug=no

// Con referencia, si se verian los cambios
$mostrarConfigRef = function () use (&$configuracion) {
    echo "  Config por ref: modo={$configuracion['modo']}, debug=" .
        ($configuracion['debug'] ? 'si' : 'no') . "\n";
};

$mostrarConfigRef(); // modo=desarrollo, debug=si

// Caso especial: objetos se copian por valor, pero el valor ES una referencia al objeto
// Esto significa que las modificaciones al objeto SI se ven en la closure
$usuario = new stdClass();
$usuario->nombre = "Ana";
$usuario->rol = "admin";

$mostrarUsuario = function () use ($usuario) {
    echo "  Usuario: {$usuario->nombre} ({$usuario->rol})\n";
};

$usuario->nombre = "Lucia"; // Se refleja porque el objeto es una referencia
$mostrarUsuario(); // Lucia (admin)

// Pero reasignar la variable a otro objeto no afecta la closure
$usuario = new stdClass();
$usuario->nombre = "Otro";
$mostrarUsuario(); // Sigue con Lucia
echo "\n";


// ============================================================================
// Ejemplo 3: La trampa clasica del bucle con closures
// ============================================================================
// Al crear closures dentro de un bucle, si se captura por valor, todas
// capturan el MISMO valor final. Es un error clasico que confunde.

echo "=== Ejemplo 3: La trampa clasica del bucle ===\n\n";

// --- EL PROBLEMA ---
echo "  --- Problema: todas las closures capturan el mismo valor final ---\n";
$funciones = [];
for ($i = 0; $i < 5; $i++) {
    // Cada closure captura $i por referencia implicitamente en el for
    // Al ejecutarse, $i ya vale 5 (el valor al terminar el ciclo)
    $funciones[] = function () use ($i) {
        return $i;
    };
}

// Todas retornan el valor que $i tenia cuando se DEFINIERON
// En realidad, use() captura por valor, asi que cada una captura el valor
// de $i en ESE momento de la iteracion
$resultados = array_map(fn($f) => $f(), $funciones);
echo "  Resultados con use(\$i) por valor: " . implode(', ', $resultados) . "\n";
// Resultado: 0, 1, 2, 3, 4 - Correcto porque captura por valor!

// --- PERO con referencia, tenemos el problema clasico ---
echo "\n  --- Con referencia: todas obtienen el valor final ---\n";
$funciones = [];
for ($i = 0; $i < 5; $i++) {
    $funciones[] = function () use (&$i) {
        return $i;
    };
}

$resultados = array_map(fn($f) => $f(), $funciones);
echo "  Resultados con use(&\$i) por referencia: " . implode(', ', $resultados) . "\n";
// Resultado: 5, 5, 5, 5, 5 - Todas ven $i = 5

// --- Solucion 1: capturar por valor (como vimos arriba, funciona bien en PHP) ---
echo "\n  --- Solucion 1: use por valor (comportamiento por defecto) ---\n";
$funciones = [];
for ($i = 0; $i < 5; $i++) {
    $funciones[] = function () use ($i) {
        return $i * $i;
    };
}
$resultados = array_map(fn($f) => $f(), $funciones);
echo "  Cuadrados: " . implode(', ', $resultados) . "\n"; // 0, 1, 4, 9, 16

// --- Solucion 2: variable intermedia (util si necesitas referencia a OTRO dato) ---
echo "\n  --- Solucion 2: variable intermedia ---\n";
$funciones = [];
$acumulador = 0;
for ($i = 0; $i < 5; $i++) {
    $valorActual = $i; // Copia independiente en cada iteracion
    $funciones[] = function () use ($valorActual, &$acumulador) {
        $acumulador += $valorActual;
        return $valorActual;
    };
}
foreach ($funciones as $f) {
    $f();
}
echo "  Acumulador total: {$acumulador}\n"; // 0+1+2+3+4 = 10

// --- Ejemplo practico: crear handlers para botones de un formulario ---
echo "\n  --- Ejemplo practico: handlers para botones ---\n";
$botones = ['Guardar', 'Cancelar', 'Eliminar', 'Exportar'];
$handlers = [];

foreach ($botones as $indice => $nombreBoton) {
    // Cada closure captura su propio $nombreBoton e $indice (por valor)
    $handlers[] = function () use ($nombreBoton, $indice) {
        return "Boton #{$indice} '{$nombreBoton}' presionado";
    };
}

foreach ($handlers as $handler) {
    echo "  " . $handler() . "\n";
}
echo "\n";


// ============================================================================
// Ejemplo 4: Fabrica de contadores con closures
// ============================================================================
// Las closures pueden encapsular estado privado, creando un patron similar
// a los objetos pero mas ligero. Cada llamada a la fabrica crea un nuevo
// ambito con su propio estado independiente.

echo "=== Ejemplo 4: Fabrica de contadores ===\n\n";

function crearContador(int $inicio = 0, int $paso = 1): array {
    $valor = $inicio;

    // Cada closure captura $valor por referencia, compartiendo el mismo estado
    $incrementar = function () use (&$valor, $paso): int {
        $valor += $paso;
        return $valor;
    };

    $decrementar = function () use (&$valor, $paso): int {
        $valor -= $paso;
        return $valor;
    };

    $obtenerValor = function () use (&$valor): int {
        return $valor;
    };

    $reiniciar = function () use (&$valor, $inicio): void {
        $valor = $inicio;
    };

    return [
        'incrementar' => $incrementar,
        'decrementar' => $decrementar,
        'valor' => $obtenerValor,
        'reiniciar' => $reiniciar,
    ];
}

// Crear dos contadores independientes
$contadorA = crearContador(0, 1);     // Empieza en 0, paso de 1
$contadorB = crearContador(100, 10);  // Empieza en 100, paso de 10

echo "  Contador A: " . $contadorA['incrementar']() . "\n"; // 1
echo "  Contador A: " . $contadorA['incrementar']() . "\n"; // 2
echo "  Contador A: " . $contadorA['incrementar']() . "\n"; // 3
echo "  Contador B: " . $contadorB['incrementar']() . "\n"; // 110
echo "  Contador B: " . $contadorB['incrementar']() . "\n"; // 120
echo "  Contador A (decrementar): " . $contadorA['decrementar']() . "\n"; // 2
echo "  Contador A (valor actual): " . $contadorA['valor']() . "\n"; // 2
$contadorA['reiniciar']();
echo "  Contador A (despues de reiniciar): " . $contadorA['valor']() . "\n"; // 0
echo "  Contador B (no afectado): " . $contadorB['valor']() . "\n"; // 120

// Fabrica de IDs unicos (caso practico)
function crearGeneradorId(string $prefijo = ''): Closure {
    $siguiente = 0;
    return function () use ($prefijo, &$siguiente): string {
        $siguiente++;
        return $prefijo . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    };
}

$generarIdUsuario = crearGeneradorId('USR-');
$generarIdPedido = crearGeneradorId('PED-');

echo "\n  IDs generados:\n";
echo "    " . $generarIdUsuario() . "\n"; // USR-0001
echo "    " . $generarIdUsuario() . "\n"; // USR-0002
echo "    " . $generarIdPedido() . "\n";  // PED-0001
echo "    " . $generarIdUsuario() . "\n"; // USR-0003
echo "    " . $generarIdPedido() . "\n";  // PED-0002
echo "\n";


// ============================================================================
// Ejemplo 5: Funcion de memoizacion con closures
// ============================================================================
// Memoizacion es una tecnica de optimizacion que cachea los resultados de
// funciones costosas. La closure mantiene el cache en su ambito cerrado.

echo "=== Ejemplo 5: Memoizacion con closures ===\n\n";

function memoizar(Closure $funcion): Closure {
    $cache = [];
    $llamadas = 0;

    return function () use ($funcion, &$cache, &$llamadas): mixed {
        $args = func_get_args();
        $clave = serialize($args); // Crear clave unica basada en los argumentos

        $llamadas++;

        if (array_key_exists($clave, $cache)) {
            echo "    [Cache HIT] Llamada #{$llamadas} - Resultado encontrado en cache\n";
            return $cache[$clave];
        }

        echo "    [Cache MISS] Llamada #{$llamadas} - Calculando resultado...\n";
        $resultado = $funcion(...$args);
        $cache[$clave] = $resultado;
        return $resultado;
    };
}

// Funcion costosa: calcular factorial (simulamos que es lenta)
$factorialMemo = memoizar(function (int $n): int {
    // Simulacion: en un caso real esto podria ser una consulta a API o BD
    if ($n <= 1) return 1;
    $resultado = 1;
    for ($i = 2; $i <= $n; $i++) {
        $resultado *= $i;
    }
    return $resultado;
});

echo "  Calculando factoriales:\n";
echo "  5! = " . $factorialMemo(5) . "\n";   // MISS - calcula
echo "  10! = " . $factorialMemo(10) . "\n";  // MISS - calcula
echo "  5! = " . $factorialMemo(5) . "\n";    // HIT - usa cache
echo "  10! = " . $factorialMemo(10) . "\n";  // HIT - usa cache
echo "  7! = " . $factorialMemo(7) . "\n";    // MISS - calcula
echo "  5! = " . $factorialMemo(5) . "\n";    // HIT - usa cache

// Memoizar una funcion de busqueda simulada
echo "\n  Buscando usuarios (simulado):\n";
$buscarUsuario = memoizar(function (int $id): array {
    // Simulamos consulta a base de datos
    $usuarios = [
        1 => ['nombre' => 'Ana Garcia', 'rol' => 'admin'],
        2 => ['nombre' => 'Luis Torres', 'rol' => 'editor'],
        3 => ['nombre' => 'Sofia Mendez', 'rol' => 'viewer'],
    ];
    return $usuarios[$id] ?? ['error' => 'No encontrado'];
});

echo "  Usuario 1: " . json_encode($buscarUsuario(1), JSON_UNESCAPED_UNICODE) . "\n";
echo "  Usuario 2: " . json_encode($buscarUsuario(2), JSON_UNESCAPED_UNICODE) . "\n";
echo "  Usuario 1: " . json_encode($buscarUsuario(1), JSON_UNESCAPED_UNICODE) . "\n";
echo "  Usuario 99: " . json_encode($buscarUsuario(99), JSON_UNESCAPED_UNICODE) . "\n";
echo "\n";


// ============================================================================
// Ejemplo 6: Constructor de configuracion con closures (Builder pattern)
// ============================================================================
// Usando closures para construir objetos de configuracion de forma fluida.
// Cada closure modifica la configuracion por referencia, permitiendo
// composicion y reutilizacion.

echo "=== Ejemplo 6: Constructor de configuracion con closures ===\n\n";

class ConfiguracionApp {
    private array $datos = [];
    private array $historialCambios = [];

    // Aplicar una closure que modifica la configuracion
    public function aplicar(Closure $modificador): self {
        $modificador($this);
        return $this;
    }

    // Establecer un valor usando notacion de puntos (database.host)
    public function set(string $clave, mixed $valor): self {
        $claves = explode('.', $clave);
        $ref = &$this->datos;

        foreach ($claves as $k) {
            if (!isset($ref[$k]) || !is_array($ref[$k])) {
                $ref[$k] = [];
            }
            $ref = &$ref[$k];
        }

        $anteriorStr = is_array($ref) && empty($ref) ? '(no definido)' : var_export($ref, true);
        $ref = $valor;
        $this->historialCambios[] = "'{$clave}': {$anteriorStr} -> " . var_export($valor, true);
        return $this;
    }

    // Obtener un valor usando notacion de puntos
    public function get(string $clave, mixed $default = null): mixed {
        $claves = explode('.', $clave);
        $ref = $this->datos;

        foreach ($claves as $k) {
            if (!isset($ref[$k])) {
                return $default;
            }
            $ref = $ref[$k];
        }

        return $ref;
    }

    public function getHistorial(): array {
        return $this->historialCambios;
    }

    public function toArray(): array {
        return $this->datos;
    }
}

// Definir closures reutilizables de configuracion para distintos entornos
$configBase = function (ConfiguracionApp $config) {
    $config->set('app.nombre', 'MiTienda')
           ->set('app.version', '2.1.0')
           ->set('app.timezone', 'America/Mexico_City')
           ->set('app.locale', 'es_MX');
};

$configBaseDatos = function (string $host, string $nombre) {
    return function (ConfiguracionApp $config) use ($host, $nombre) {
        $config->set('database.driver', 'mysql')
               ->set('database.host', $host)
               ->set('database.nombre', $nombre)
               ->set('database.charset', 'utf8mb4');
    };
};

$configCache = function (bool $habilitado, int $ttl = 3600) {
    return function (ConfiguracionApp $config) use ($habilitado, $ttl) {
        $config->set('cache.habilitado', $habilitado)
               ->set('cache.driver', $habilitado ? 'redis' : 'null')
               ->set('cache.ttl', $ttl);
    };
};

$configDesarrollo = function (ConfiguracionApp $config) {
    $config->set('app.debug', true)
           ->set('app.log_level', 'debug')
           ->set('mail.driver', 'log'); // No enviar emails reales
};

$configProduccion = function (ConfiguracionApp $config) {
    $config->set('app.debug', false)
           ->set('app.log_level', 'error')
           ->set('mail.driver', 'smtp');
};

// --- Construir configuracion de desarrollo ---
echo "  --- Configuracion de DESARROLLO ---\n";
$devConfig = new ConfiguracionApp();
$devConfig->aplicar($configBase)
          ->aplicar($configBaseDatos('localhost', 'mitienda_dev'))
          ->aplicar($configCache(false))
          ->aplicar($configDesarrollo);

echo "  App: " . $devConfig->get('app.nombre') . " v" . $devConfig->get('app.version') . "\n";
echo "  Debug: " . ($devConfig->get('app.debug') ? 'si' : 'no') . "\n";
echo "  BD: " . $devConfig->get('database.host') . "/" . $devConfig->get('database.nombre') . "\n";
echo "  Cache: " . ($devConfig->get('cache.habilitado') ? 'si' : 'no') . "\n";
echo "  Mail: " . $devConfig->get('mail.driver') . "\n";

// --- Construir configuracion de produccion ---
echo "\n  --- Configuracion de PRODUCCION ---\n";
$prodConfig = new ConfiguracionApp();
$prodConfig->aplicar($configBase)
           ->aplicar($configBaseDatos('db.mitienda.com', 'mitienda_prod'))
           ->aplicar($configCache(true, 7200))
           ->aplicar($configProduccion);

echo "  App: " . $prodConfig->get('app.nombre') . " v" . $prodConfig->get('app.version') . "\n";
echo "  Debug: " . ($prodConfig->get('app.debug') ? 'si' : 'no') . "\n";
echo "  BD: " . $prodConfig->get('database.host') . "/" . $prodConfig->get('database.nombre') . "\n";
echo "  Cache: " . ($prodConfig->get('cache.habilitado') ? 'si' : 'no') .
    " (TTL: " . $prodConfig->get('cache.ttl') . "s)\n";
echo "  Mail: " . $prodConfig->get('mail.driver') . "\n";

// Componer configuraciones adicionales con closures anonimas en linea
$prodConfig->aplicar(function (ConfiguracionApp $config) {
    $config->set('seguridad.csrf', true)
           ->set('seguridad.cors_origin', 'https://mitienda.com')
           ->set('seguridad.rate_limit', 100);
});

echo "\n  Seguridad habilitada - CORS: " . $prodConfig->get('seguridad.cors_origin') . "\n";
echo "  Rate limit: " . $prodConfig->get('seguridad.rate_limit') . " peticiones/min\n";

echo "\n  Historial de cambios (ultimos 5):\n";
$historial = $prodConfig->getHistorial();
foreach (array_slice($historial, -5) as $cambio) {
    echo "    - {$cambio}\n";
}
?>
