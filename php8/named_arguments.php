<?php
// ============================================
// ARGUMENTOS NOMBRADOS EN PHP 8.0
// Llamadas con nombre, saltar valores por defecto, legibilidad
// ============================================

// --- Ejemplo 1: Uso básico de argumentos nombrados ---
// Pasamos los argumentos por nombre en lugar de posición

function crearUsuario(
    string $nombre,
    string $email,
    int $edad = 0,
    string $rol = 'usuario',
    bool $activo = true,
    ?string $telefono = null
): void {
    echo "Creando usuario:\n";
    echo "  Nombre: {$nombre}\n";
    echo "  Email: {$email}\n";
    echo "  Edad: {$edad}\n";
    echo "  Rol: {$rol}\n";
    echo "  Activo: " . ($activo ? 'Sí' : 'No') . "\n";
    echo "  Teléfono: " . ($telefono ?? 'No proporcionado') . "\n\n";
}

// Forma tradicional (posicional): debemos pasar todos los parámetros en orden
crearUsuario("Ana", "ana@correo.com", 25, "admin", true, "+521234567");

// Con argumentos nombrados: más legible y podemos saltarnos valores por defecto
crearUsuario(
    nombre: "Carlos",
    email: "carlos@correo.com",
    rol: "editor",          // Saltamos 'edad', usa valor por defecto (0)
    telefono: "+529876543"  // Saltamos 'activo', usa valor por defecto (true)
);


// --- Ejemplo 2: Saltando parámetros con valores por defecto ---
// Sin argumentos nombrados, debemos pasar TODOS los anteriores

function generarReporte(
    string $titulo,
    string $formato = 'PDF',
    bool $incluirGraficos = true,
    bool $incluirTablas = true,
    string $orientacion = 'vertical',
    int $margen = 20,
    string $idioma = 'es'
): string {
    return "Reporte: '{$titulo}' | Formato: {$formato} | "
         . "Gráficos: " . ($incluirGraficos ? 'Sí' : 'No') . " | "
         . "Tablas: " . ($incluirTablas ? 'Sí' : 'No') . " | "
         . "Orientación: {$orientacion} | "
         . "Margen: {$margen}mm | Idioma: {$idioma}";
}

// Sin argumentos nombrados: queremos cambiar solo 'idioma' y 'margen'
// Tendríamos que escribir TODOS los anteriores:
// generarReporte("Ventas", "PDF", true, true, "vertical", 30, "en");

// Con argumentos nombrados: solo pasamos lo que necesitamos cambiar
echo generarReporte(
    titulo: "Ventas Mensuales",
    margen: 30,
    idioma: "en"
) . "\n\n";


// --- Ejemplo 3: Combinando argumentos posicionales y nombrados ---
// Los posicionales deben ir ANTES de los nombrados

function enviarNotificacion(
    string $destinatario,
    string $mensaje,
    string $canal = 'email',
    string $prioridad = 'normal',
    bool $registrar = true,
    ?string $plantilla = null
): void {
    echo "[{$canal}] [{$prioridad}] → {$destinatario}: {$mensaje}";
    if ($plantilla) {
        echo " (plantilla: {$plantilla})";
    }
    echo "\n";
}

// Primeros argumentos posicionales, luego nombrados
enviarNotificacion(
    "admin@empresa.com",             // posicional: $destinatario
    "Servidor reiniciado",           // posicional: $mensaje
    prioridad: "alta",               // nombrado: saltamos $canal
    plantilla: "alerta_sistema"      // nombrado: saltamos $registrar
);

// Todos nombrados: el orden no importa
enviarNotificacion(
    prioridad: "urgente",
    mensaje: "¡Sistema caído!",
    canal: "sms",
    destinatario: "+5215551234567"
);


// --- Ejemplo 4: Argumentos nombrados con funciones nativas de PHP ---
// Las funciones built-in también soportan argumentos nombrados

// array_slice: sin nombrados necesitamos pasar todos los parámetros
$numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Queremos preservar las claves, pero debemos pasar $length primero
// Forma posicional: array_slice($numeros, 2, null, true)
$resultado = array_slice($numeros, offset: 2, preserve_keys: true);
echo "array_slice: " . implode(', ', $resultado) . "\n";
// 3, 4, 5, 6, 7, 8, 9, 10

// str_contains, str_starts_with - más legible
$url = "https://api.ejemplo.com/v2/usuarios";
echo "¿Contiene 'api'? " . (str_contains(haystack: $url, needle: 'api') ? "Sí" : "No") . "\n";

// implode con claridad
$frutas = ['manzana', 'pera', 'naranja'];
echo implode(separator: ' | ', array: $frutas) . "\n";
// manzana | pera | naranja

// setcookie: antes había que recordar el orden de muchos parámetros
// setcookie("tema", "oscuro", 0, "/", "", false, true); // ¿Qué es cada cosa?
// Ahora con argumentos nombrados:
// setcookie("tema", "oscuro", httponly: true, secure: true, path: "/");

// htmlspecialchars con banderas específicas
$html = '<script>alert("hola")</script>';
$seguro = htmlspecialchars(
    string: $html,
    flags: ENT_QUOTES | ENT_HTML5,
    encoding: 'UTF-8',
    double_encode: false
);
echo "HTML seguro: {$seguro}\n";


// --- Ejemplo 5: Argumentos nombrados con el operador spread ---
// Podemos combinar el operador ... con argumentos nombrados

function crearConexion(
    string $host,
    int $puerto,
    string $baseDatos,
    string $usuario,
    string $contrasena,
    string $charset = 'utf8mb4'
): string {
    return "mysql:host={$host};port={$puerto};dbname={$baseDatos};charset={$charset} "
         . "user={$usuario}";
}

// Array asociativo con los argumentos nombrados
$configDB = [
    'host' => 'localhost',
    'puerto' => 3306,
    'baseDatos' => 'mi_app',
    'usuario' => 'root',
    'contrasena' => 'secreta',
];

// El operador spread (...) desempaqueta el array como argumentos nombrados
echo crearConexion(...$configDB) . "\n";
// mysql:host=localhost;port=3306;dbname=mi_app;charset=utf8mb4 user=root

// También podemos mezclar arrays y argumentos directos
$configBase = ['host' => '192.168.1.100', 'puerto' => 5432];
echo crearConexion(
    ...$configBase,
    baseDatos: 'produccion',
    usuario: 'admin',
    contrasena: 'clave_segura',
    charset: 'utf8'
) . "\n";

?>
