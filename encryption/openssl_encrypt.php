<?php
/**
 * openssl_encrypt() / openssl_decrypt() - Cifrado simétrico
 *
 * OpenSSL proporciona cifrado simétrico fuerte.
 * AES-256-CBC es el estándar más usado para cifrado de datos.
 * Requiere una clave secreta y un vector de inicialización (IV).
 */

// ============================================
// Ejemplo 1: Cifrado y descifrado básico con AES-256-CBC
// ============================================

echo "=== Ejemplo 1: AES-256-CBC básico ===\n";

$metodo = 'AES-256-CBC';
$textoOriginal = 'Este es un mensaje secreto que debe ser cifrado.';

// Generar clave de 256 bits (32 bytes)
$clave = openssl_random_pseudo_bytes(32);

// Obtener el tamaño del IV requerido para el método
$longitudIV = openssl_cipher_iv_length($metodo);
echo "Método: $metodo\n";
echo "Tamaño de IV requerido: $longitudIV bytes\n";

// Generar IV aleatorio
$iv = openssl_random_pseudo_bytes($longitudIV);

// Cifrar
$textoCifrado = openssl_encrypt($textoOriginal, $metodo, $clave, 0, $iv);

echo "\nTexto original: $textoOriginal\n";
echo "Texto cifrado (base64): $textoCifrado\n";
echo "Tamaño cifrado: " . strlen($textoCifrado) . " caracteres\n";

// Descifrar
$textoDescifrado = openssl_decrypt($textoCifrado, $metodo, $clave, 0, $iv);

echo "Texto descifrado: $textoDescifrado\n";
echo "¿Coincide? " . ($textoOriginal === $textoDescifrado ? 'Sí' : 'No') . "\n";

// ============================================
// Ejemplo 2: Generar claves e IV correctamente
// ============================================

echo "\n=== Ejemplo 2: Generación de claves ===\n";

// Método 1: Clave aleatoria directa
$claveAleatoria = openssl_random_pseudo_bytes(32);
echo "Clave aleatoria (hex): " . bin2hex($claveAleatoria) . "\n";
echo "Tamaño: " . strlen($claveAleatoria) . " bytes (256 bits)\n\n";

// Método 2: Derivar clave desde una contraseña con PBKDF2
$password = 'mi_contraseña_secreta';
$salt = openssl_random_pseudo_bytes(16);
$iteraciones = 100000;

$claveDerivada = hash_pbkdf2('sha256', $password, $salt, $iteraciones, 32, true);
echo "Clave derivada con PBKDF2:\n";
echo "  Password: $password\n";
echo "  Salt (hex): " . bin2hex($salt) . "\n";
echo "  Iteraciones: $iteraciones\n";
echo "  Clave (hex): " . bin2hex($claveDerivada) . "\n\n";

// Listar métodos de cifrado disponibles
$metodos = openssl_get_cipher_methods();
$metodosUnicos = array_filter($metodos, fn($m) => $m === strtoupper($m)); // Solo mayúsculas (evitar duplicados)
echo "Algunos métodos disponibles:\n";
$metodosSeleccionados = ['AES-128-CBC', 'AES-256-CBC', 'AES-256-GCM', 'AES-128-GCM', 'CHACHA20-POLY1305'];
foreach ($metodosSeleccionados as $m) {
    if (in_array($m, $metodos)) {
        $ivLen = openssl_cipher_iv_length($m);
        echo "  $m (IV: {$ivLen} bytes)\n";
    }
}

// ============================================
// Ejemplo 3: AES-256-GCM con autenticación (AEAD)
// ============================================

echo "\n=== Ejemplo 3: AES-256-GCM (cifrado autenticado) ===\n";

/**
 * GCM (Galois/Counter Mode) proporciona cifrado Y autenticación.
 * Genera un tag de autenticación que verifica la integridad del mensaje.
 * Es el modo RECOMENDADO sobre CBC cuando está disponible.
 */

$metodoGCM = 'AES-256-GCM';
$textoGCM = 'Mensaje confidencial con autenticación.';
$claveGCM = openssl_random_pseudo_bytes(32);
$ivGCM = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodoGCM));
$aad = 'datos-adicionales-autenticados'; // AAD: datos que se autentican pero no se cifran
$tag = ''; // Se llenará durante el cifrado

// Cifrar con GCM (genera tag de autenticación)
$cifradoGCM = openssl_encrypt(
    $textoGCM,
    $metodoGCM,
    $claveGCM,
    0,          // opciones
    $ivGCM,
    $tag,       // tag de autenticación (salida)
    $aad,       // datos adicionales autenticados
    16          // longitud del tag (16 bytes = 128 bits)
);

echo "Texto original: $textoGCM\n";
echo "Cifrado (base64): $cifradoGCM\n";
echo "Tag (hex): " . bin2hex($tag) . "\n";
echo "AAD: $aad\n\n";

// Descifrar con verificación del tag
$descifradoGCM = openssl_decrypt(
    $cifradoGCM,
    $metodoGCM,
    $claveGCM,
    0,
    $ivGCM,
    $tag,   // Debe coincidir con el tag generado
    $aad    // Debe coincidir con el AAD original
);

echo "Descifrado: $descifradoGCM\n";

// Intentar descifrar con tag modificado (debería fallar)
$tagModificado = $tag;
$tagModificado[0] = chr(ord($tagModificado[0]) ^ 0xFF); // Modificar un byte
$descifradoFallido = openssl_decrypt($cifradoGCM, $metodoGCM, $claveGCM, 0, $ivGCM, $tagModificado, $aad);
echo "Descifrar con tag modificado: " . ($descifradoFallido === false ? 'FALLÓ (esperado)' : $descifradoFallido) . "\n";

// ============================================
// Ejemplo 4: Caso práctico - Clase de cifrado reutilizable
// ============================================

echo "\n=== Ejemplo 4: Clase de cifrado reutilizable ===\n";

/**
 * Clase que encapsula el cifrado/descifrado y almacena IV y tag
 * junto con el texto cifrado para facilitar el transporte.
 */
class Cifrador
{
    private string $metodo;
    private string $clave;

    public function __construct(string $clave, string $metodo = 'AES-256-GCM')
    {
        $this->metodo = $metodo;

        // Asegurar que la clave tenga el tamaño correcto
        // Para AES-256 necesitamos 32 bytes
        $this->clave = hash('sha256', $clave, true);
    }

    /**
     * Cifrar un mensaje. Retorna el paquete completo (IV + tag + cifrado).
     * Todo codificado en base64 para transporte seguro.
     */
    public function cifrar(string $textoPlano): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->metodo));
        $tag = '';

        $cifrado = openssl_encrypt(
            $textoPlano,
            $this->metodo,
            $this->clave,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($cifrado === false) {
            throw new RuntimeException('Error al cifrar: ' . openssl_error_string());
        }

        // Empaquetar: IV + tag + datos cifrados
        $paquete = $iv . $tag . $cifrado;
        return base64_encode($paquete);
    }

    /**
     * Descifrar un mensaje previamente cifrado con cifrar().
     */
    public function descifrar(string $paqueteCodificado): string
    {
        $paquete = base64_decode($paqueteCodificado);

        if ($paquete === false) {
            throw new RuntimeException('Datos en base64 inválidos.');
        }

        $ivLen = openssl_cipher_iv_length($this->metodo);

        // Desempaquetar: IV (ivLen bytes) + tag (16 bytes) + datos
        $iv = substr($paquete, 0, $ivLen);
        $tag = substr($paquete, $ivLen, 16);
        $cifrado = substr($paquete, $ivLen + 16);

        $descifrado = openssl_decrypt(
            $cifrado,
            $this->metodo,
            $this->clave,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($descifrado === false) {
            throw new RuntimeException('Error al descifrar. Datos corruptos o clave incorrecta.');
        }

        return $descifrado;
    }
}

$cifrador = new Cifrador('mi_clave_secreta_para_la_app');

// Cifrar varios mensajes
$mensajes = [
    'Número de tarjeta: 4111-1111-1111-1111',
    'Token de sesión: abc123xyz789',
    'Datos del paciente: Juan, 35 años, alergia penicilina',
];

echo "Cifrado y descifrado:\n";
foreach ($mensajes as $msg) {
    $cifrado = $cifrador->cifrar($msg);
    $descifrado = $cifrador->descifrar($cifrado);

    echo "\n  Original:    $msg\n";
    echo "  Cifrado:     " . substr($cifrado, 0, 40) . "...\n";
    echo "  Descifrado:  $descifrado\n";
    echo "  ¿Coincide?   " . ($msg === $descifrado ? 'Sí' : 'No') . "\n";
}

// Intentar descifrar con clave incorrecta
echo "\nDescifrar con clave incorrecta:\n";
$cifradorMalo = new Cifrador('clave_incorrecta');
try {
    $cifradorMalo->descifrar($cifrador->cifrar('Mensaje de prueba'));
} catch (RuntimeException $e) {
    echo "  Error esperado: " . $e->getMessage() . "\n";
}

// ============================================
// Ejemplo 5: Cifrado de archivos
// ============================================

echo "\n=== Ejemplo 5: Cifrado de archivos ===\n";

/**
 * Cifrar y descifrar el contenido de archivos.
 * En producción, para archivos grandes se usaría streaming.
 */
class CifradorArchivos
{
    private Cifrador $cifrador;

    public function __construct(string $clave)
    {
        $this->cifrador = new Cifrador($clave);
    }

    /**
     * Cifrar contenido y guardarlo en un archivo
     */
    public function cifrarArchivo(string $rutaOrigen, string $rutaDestino): void
    {
        $contenido = file_get_contents($rutaOrigen);
        if ($contenido === false) {
            throw new RuntimeException("No se pudo leer: $rutaOrigen");
        }

        $cifrado = $this->cifrador->cifrar($contenido);
        file_put_contents($rutaDestino, $cifrado);

        echo "  Archivo cifrado: $rutaOrigen -> $rutaDestino\n";
        echo "  Tamaño original: " . strlen($contenido) . " bytes\n";
        echo "  Tamaño cifrado: " . strlen($cifrado) . " bytes\n";
    }

    /**
     * Descifrar un archivo cifrado
     */
    public function descifrarArchivo(string $rutaCifrada, string $rutaDestino): void
    {
        $cifrado = file_get_contents($rutaCifrada);
        if ($cifrado === false) {
            throw new RuntimeException("No se pudo leer: $rutaCifrada");
        }

        $descifrado = $this->cifrador->descifrar($cifrado);
        file_put_contents($rutaDestino, $descifrado);

        echo "  Archivo descifrado: $rutaCifrada -> $rutaDestino\n";
        echo "  Tamaño: " . strlen($descifrado) . " bytes\n";
    }
}

// Crear archivo de prueba
$tmpDir = sys_get_temp_dir();
$archivoOriginal = $tmpDir . '/datos_sensibles.txt';
$archivoCifrado = $tmpDir . '/datos_sensibles.enc';
$archivoDescifrado = $tmpDir . '/datos_recuperados.txt';

$contenidoSensible = "Nombre: Sandra García\nCuenta: 1234567890\nSaldo: $50,000.00\n";
file_put_contents($archivoOriginal, $contenidoSensible);

$cifradorArchivos = new CifradorArchivos('clave_maestra_del_sistema');

$cifradorArchivos->cifrarArchivo($archivoOriginal, $archivoCifrado);
echo "\n";
$cifradorArchivos->descifrarArchivo($archivoCifrado, $archivoDescifrado);

// Verificar contenido
$contenidoRecuperado = file_get_contents($archivoDescifrado);
echo "\n  ¿El contenido es idéntico? " . ($contenidoSensible === $contenidoRecuperado ? 'Sí' : 'No') . "\n";

// Limpieza
unlink($archivoOriginal);
unlink($archivoCifrado);
unlink($archivoDescifrado);
echo "  Archivos temporales eliminados.\n";

?>
