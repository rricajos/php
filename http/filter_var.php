<?php
// ============================================================
// filter_var() - Filtrar y validar variables individuales
// ============================================================
// filter_var() filtra una variable con un filtro específico.
// A diferencia de filter_input(), trabaja con cualquier variable,
// no solo con datos de entrada HTTP.
// Sintaxis: filter_var(variable, filtro, opciones)
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Validar direcciones de email
// ------------------------------------------------------------
// FILTER_VALIDATE_EMAIL verifica que una cadena tenga formato
// de email válido según RFC 822.

$emails = [
    "usuario@ejemplo.com",
    "maria.garcia@empresa.co",
    "nombre+etiqueta@gmail.com",
    "correo inválido",
    "@sin-usuario.com",
    "sin-dominio@",
    "",
    "admin@192.168.1.1",
    "user@sub.dominio.ejemplo.com",
];

echo "=== Validación de emails ===\n\n";

foreach ($emails as $email) {
    $resultado = filter_var($email, FILTER_VALIDATE_EMAIL);

    if ($resultado !== false) {
        echo "  VALIDO   : $resultado\n";
    } else {
        $mostrar = $email === "" ? "(vacío)" : $email;
        echo "  INVALIDO : $mostrar\n";
    }
}

// Función auxiliar para validar y normalizar email
function validarEmail(string $email): ?string {
    $email = trim($email);
    $email = strtolower($email);

    $resultado = filter_var($email, FILTER_VALIDATE_EMAIL);
    return $resultado !== false ? $resultado : null;
}

echo "\nEmail normalizado: " . (validarEmail("  USUARIO@Ejemplo.COM  ") ?? "inválido") . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Validar URLs
// ------------------------------------------------------------
// FILTER_VALIDATE_URL verifica que una cadena sea una URL válida.

$urls = [
    "https://www.ejemplo.com",
    "http://ejemplo.com/ruta/pagina?id=42",
    "https://api.ejemplo.com/v2/usuarios",
    "ftp://archivos.ejemplo.com/documento.pdf",
    "www.ejemplo.com",            // Falta el esquema
    "ejemplo.com",                // Falta el esquema
    "https://",                   // Falta el host
    "://ejemplo.com",             // Esquema vacío
    "https://ejemplo.com:8080/api",
];

echo "=== Validación de URLs ===\n\n";

foreach ($urls as $url) {
    $resultado = filter_var($url, FILTER_VALIDATE_URL);

    if ($resultado !== false) {
        echo "  VALIDA   : $resultado\n";
    } else {
        echo "  INVALIDA : $url\n";
    }
}

// Validar URL con flags adicionales
echo "\n--- Validación con flags ---\n";

$urlPrueba = "https://www.ejemplo.com/ruta?param=valor";

// Requiere que tenga esquema (http, https, ftp, etc.)
$conEsquema = filter_var($urlPrueba, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
echo "Tiene esquema: " . ($conEsquema !== false ? "sí" : "no") . "\n";

// Requiere que tenga host (dominio)
$conHost = filter_var($urlPrueba, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
echo "Tiene host: " . ($conHost !== false ? "sí" : "no") . "\n";

// Requiere que tenga ruta (path)
$conRuta = filter_var($urlPrueba, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
echo "Tiene ruta: " . ($conRuta !== false ? "sí" : "no") . "\n";

// Requiere que tenga query string
$conQuery = filter_var($urlPrueba, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED);
echo "Tiene query: " . ($conQuery !== false ? "sí" : "no") . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Validar direcciones IP
// ------------------------------------------------------------
// FILTER_VALIDATE_IP verifica direcciones IPv4 e IPv6.

$direccionesIp = [
    "192.168.1.1",        // IPv4 privada
    "10.0.0.1",           // IPv4 privada
    "8.8.8.8",            // IPv4 pública (Google DNS)
    "255.255.255.255",    // Broadcast
    "0.0.0.0",            // Dirección nula
    "999.999.999.999",    // Inválida
    "::1",                // IPv6 loopback
    "2001:0db8:85a3:0000:0000:8a2e:0370:7334", // IPv6
    "fe80::1",            // IPv6 link-local
    "no-es-una-ip",
];

echo "=== Validación de direcciones IP ===\n\n";

foreach ($direccionesIp as $ip) {
    $esValida    = filter_var($ip, FILTER_VALIDATE_IP) !== false;
    $esIpv4      = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    $esIpv6      = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    $esPublica   = filter_var($ip, FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;

    $tipo = $esIpv4 ? "IPv4" : ($esIpv6 ? "IPv6" : "N/A");
    $acceso = $esPublica ? "pública" : "privada/reservada";

    if ($esValida) {
        echo "  VALIDA   : $ip ($tipo, $acceso)\n";
    } else {
        echo "  INVALIDA : $ip\n";
    }
}

// Función práctica: obtener y validar IP del cliente
function obtenerIpCliente(): string {
    $posiblesIps = [
        $_SERVER["HTTP_X_FORWARDED_FOR"] ?? null,
        $_SERVER["HTTP_X_REAL_IP"] ?? null,
        $_SERVER["HTTP_CLIENT_IP"] ?? null,
        $_SERVER["REMOTE_ADDR"] ?? null,
    ];

    foreach ($posiblesIps as $ip) {
        if ($ip === null) continue;
        // Tomar la primera IP si hay múltiples
        $ip = trim(explode(",", $ip)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
            return $ip;
        }
    }

    return "0.0.0.0";
}

echo "\nIP del cliente: " . obtenerIpCliente() . "\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Sanitizar cadenas de texto
// ------------------------------------------------------------
// Los filtros FILTER_SANITIZE_* limpian datos eliminando
// caracteres no deseados o peligrosos.

echo "=== Filtros de sanitización ===\n\n";

// --- FILTER_SANITIZE_SPECIAL_CHARS ---
// Escapa caracteres HTML especiales (convierte a entidades)
$textoHtml = '<script>alert("XSS")</script><p>Hola "mundo"</p>';
$limpio = filter_var($textoHtml, FILTER_SANITIZE_SPECIAL_CHARS);
echo "SPECIAL_CHARS:\n";
echo "  Original:  $textoHtml\n";
echo "  Limpio:    $limpio\n\n";

// --- FILTER_SANITIZE_EMAIL ---
// Elimina caracteres no permitidos en emails
$emailSucio = "usu ario!#\$%@ejem plo.com";
$emailLimpio = filter_var($emailSucio, FILTER_SANITIZE_EMAIL);
echo "SANITIZE_EMAIL:\n";
echo "  Original:  $emailSucio\n";
echo "  Limpio:    $emailLimpio\n\n";

// --- FILTER_SANITIZE_URL ---
// Elimina caracteres no permitidos en URLs
$urlSucia = "https://ejemplo.com/ruta con espacios/página<>#";
$urlLimpia = filter_var($urlSucia, FILTER_SANITIZE_URL);
echo "SANITIZE_URL:\n";
echo "  Original:  $urlSucia\n";
echo "  Limpia:    $urlLimpia\n\n";

// --- FILTER_SANITIZE_NUMBER_INT ---
// Elimina todo excepto dígitos, signo + y -
$textoConNumeros = "Tengo 25 años y mido 1.75m";
$soloEntero = filter_var($textoConNumeros, FILTER_SANITIZE_NUMBER_INT);
echo "SANITIZE_NUMBER_INT:\n";
echo "  Original:  $textoConNumeros\n";
echo "  Limpio:    $soloEntero\n\n";

// --- FILTER_SANITIZE_NUMBER_FLOAT ---
// Elimina todo excepto dígitos, +, - y opcionalmente . y ,
$textoConDecimal = "Precio: $1,234.56 USD";
$soloFloat = filter_var($textoConDecimal, FILTER_SANITIZE_NUMBER_FLOAT, [
    "flags" => FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND
]);
echo "SANITIZE_NUMBER_FLOAT:\n";
echo "  Original:  $textoConDecimal\n";
echo "  Limpio:    $soloFloat\n\n";

// --- FILTER_SANITIZE_ADD_SLASHES ---
// Añade barras invertidas antes de ', ", \ y NUL
$textoComillas = 'Ella dijo "hola" y él dijo \'adiós\'';
$escapado = filter_var($textoComillas, FILTER_SANITIZE_ADD_SLASHES);
echo "SANITIZE_ADD_SLASHES:\n";
echo "  Original:  $textoComillas\n";
echo "  Escapado:  $escapado\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: FILTER_VALIDATE_* - Validaciones diversas
// ------------------------------------------------------------
// filter_var() ofrece validadores para tipos de datos comunes.

echo "=== Validaciones diversas con FILTER_VALIDATE_* ===\n\n";

// --- Validar enteros ---
$valores = ["42", "-7", "3.14", "0", "abc", "", "0xFF"];
echo "VALIDATE_INT:\n";
foreach ($valores as $v) {
    $res = filter_var($v, FILTER_VALIDATE_INT);
    $display = $v === "" ? "(vacío)" : $v;
    echo "  '$display' -> " . ($res !== false ? "válido ($res)" : "inválido") . "\n";
}

// Entero con rango
$nota = filter_var("85", FILTER_VALIDATE_INT, [
    "options" => ["min_range" => 0, "max_range" => 100]
]);
echo "  Nota 85 (rango 0-100): " . ($nota !== false ? "válida" : "inválida") . "\n\n";

// --- Validar flotantes ---
echo "VALIDATE_FLOAT:\n";
$flotantes = ["3.14", "-2.5", "1000", "1,234.56", "abc"];
foreach ($flotantes as $f) {
    $res = filter_var($f, FILTER_VALIDATE_FLOAT);
    echo "  '$f' -> " . ($res !== false ? "válido ($res)" : "inválido") . "\n";
}

// Float con separador de miles
$conMiles = filter_var("1,234.56", FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
echo "  '1,234.56' (con miles): " . ($conMiles !== false ? "válido" : "inválido") . "\n\n";

// --- Validar booleanos ---
echo "VALIDATE_BOOLEAN:\n";
$booleanos = ["true", "false", "1", "0", "yes", "no", "on", "off", ""];
foreach ($booleanos as $b) {
    $res = filter_var($b, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $display = $b === "" ? "(vacío)" : $b;
    echo "  '$display' -> " . ($res === null ? "null" : ($res ? "true" : "false")) . "\n";
}

echo "\n";

// --- Validar dominio ---
$dominios = ["ejemplo.com", "sub.dominio.com", "localhost", "-invalido.com"];
echo "VALIDATE_DOMAIN:\n";
foreach ($dominios as $d) {
    $res = filter_var($d, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    echo "  '$d' -> " . ($res !== false ? "válido" : "inválido") . "\n";
}

// --- Validar dirección MAC ---
echo "\nVALIDATE_MAC:\n";
$macs = ["00:1A:2B:3C:4D:5E", "00-1A-2B-3C-4D-5E", "ZZZZ"];
foreach ($macs as $mac) {
    $res = filter_var($mac, FILTER_VALIDATE_MAC);
    echo "  '$mac' -> " . ($res !== false ? "válida" : "inválida") . "\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Función de validación/sanitización reutilizable
// ------------------------------------------------------------
// Combinando filter_var con una interfaz limpia para uso en
// aplicaciones reales.

class Validador {
    private array $errores = [];
    private array $datosLimpios = [];

    // Validar y sanitizar un campo
    public function campo(string $nombre, mixed $valor, array $reglas): self {
        $valor = is_string($valor) ? trim($valor) : $valor;

        foreach ($reglas as $regla => $parametro) {
            switch ($regla) {
                case "requerido":
                    if ($parametro && ($valor === "" || $valor === null)) {
                        $this->errores[$nombre] = "El campo '$nombre' es obligatorio.";
                        return $this;
                    }
                    break;

                case "email":
                    if ($parametro && !empty($valor)) {
                        if (filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
                            $this->errores[$nombre] = "El email no es válido.";
                            return $this;
                        }
                        $valor = strtolower($valor);
                    }
                    break;

                case "url":
                    if ($parametro && !empty($valor)) {
                        if (filter_var($valor, FILTER_VALIDATE_URL) === false) {
                            $this->errores[$nombre] = "La URL no es válida.";
                            return $this;
                        }
                    }
                    break;

                case "entero":
                    if ($parametro && !empty($valor)) {
                        $opciones = [];
                        if (isset($reglas["min"])) $opciones["min_range"] = $reglas["min"];
                        if (isset($reglas["max"])) $opciones["max_range"] = $reglas["max"];

                        $resultado = filter_var($valor, FILTER_VALIDATE_INT,
                            empty($opciones) ? [] : ["options" => $opciones]);

                        if ($resultado === false) {
                            $this->errores[$nombre] = "Debe ser un número entero válido.";
                            return $this;
                        }
                        $valor = $resultado;
                    }
                    break;

                case "ip":
                    if ($parametro && !empty($valor)) {
                        if (filter_var($valor, FILTER_VALIDATE_IP) === false) {
                            $this->errores[$nombre] = "La dirección IP no es válida.";
                            return $this;
                        }
                    }
                    break;

                case "sanitizar":
                    if (!empty($valor)) {
                        $valor = filter_var($valor, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                    break;
            }
        }

        $this->datosLimpios[$nombre] = $valor;
        return $this;
    }

    public function esValido(): bool {
        return empty($this->errores);
    }

    public function obtenerErrores(): array {
        return $this->errores;
    }

    public function obtenerDatos(): array {
        return $this->datosLimpios;
    }
}

// Uso del validador
$v = new Validador();

$v->campo("nombre", "María García", ["requerido" => true, "sanitizar" => true])
  ->campo("email", "maria@ejemplo.com", ["requerido" => true, "email" => true])
  ->campo("sitio_web", "https://maria.dev", ["url" => true])
  ->campo("edad", "28", ["entero" => true, "min" => 18, "max" => 120])
  ->campo("ip_registro", "192.168.1.100", ["ip" => true]);

if ($v->esValido()) {
    echo "Datos válidos:\n";
    foreach ($v->obtenerDatos() as $campo => $valor) {
        echo "  $campo: $valor\n";
    }
} else {
    echo "Errores encontrados:\n";
    foreach ($v->obtenerErrores() as $campo => $error) {
        echo "  $campo: $error\n";
    }
}
?>
