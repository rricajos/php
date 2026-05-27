<?php
/**
 * Sodium (libsodium) - Criptografía moderna en PHP
 *
 * La extensión Sodium viene incluida desde PHP 7.2.
 * Provee funciones criptográficas modernas, seguras y fáciles de usar.
 * Es la alternativa RECOMENDADA sobre OpenSSL para nueva criptografía.
 */

// ============================================
// Ejemplo 1: Cifrado simétrico con secretbox
// ============================================

echo "=== Ejemplo 1: sodium_crypto_secretbox ===\n";

/**
 * sodium_crypto_secretbox() usa XSalsa20 para cifrado y
 * Poly1305 para autenticación. Es cifrado autenticado (AEAD).
 */

// Generar clave de 256 bits (SODIUM_CRYPTO_SECRETBOX_KEYBYTES = 32)
$clave = sodium_crypto_secretbox_keygen();
echo "Tamaño de clave: " . strlen($clave) . " bytes (" . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . " requeridos)\n";

// Generar nonce (número usado una sola vez, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES = 24)
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
echo "Tamaño de nonce: " . strlen($nonce) . " bytes (" . SODIUM_CRYPTO_SECRETBOX_NONCEBYTES . " requeridos)\n\n";

$mensajeOriginal = 'Este es un mensaje ultra-secreto cifrado con Sodium.';

// Cifrar
$cifrado = sodium_crypto_secretbox($mensajeOriginal, $nonce, $clave);
echo "Texto original: $mensajeOriginal\n";
echo "Cifrado (hex): " . bin2hex($cifrado) . "\n";
echo "Tamaño cifrado: " . strlen($cifrado) . " bytes (original + " . SODIUM_CRYPTO_SECRETBOX_MACBYTES . " bytes MAC)\n\n";

// Descifrar
$descifrado = sodium_crypto_secretbox_open($cifrado, $nonce, $clave);
echo "Descifrado: $descifrado\n";
echo "¿Coincide? " . ($mensajeOriginal === $descifrado ? 'Sí' : 'No') . "\n";

// Intentar descifrar con clave incorrecta
$claveIncorrecta = sodium_crypto_secretbox_keygen();
$resultado = sodium_crypto_secretbox_open($cifrado, $nonce, $claveIncorrecta);
echo "Descifrar con clave incorrecta: " . ($resultado === false ? 'FALLÓ (esperado)' : 'Inesperado') . "\n";

// Limpiar claves de la memoria (seguridad)
sodium_memzero($clave);
sodium_memzero($nonce);

// ============================================
// Ejemplo 2: Generación segura de claves
// ============================================

echo "\n=== Ejemplo 2: Generación de claves ===\n";

// Método 1: Generar clave aleatoria directa
$claveAleatoria = sodium_crypto_secretbox_keygen();
echo "Clave aleatoria (hex): " . bin2hex($claveAleatoria) . "\n\n";

// Método 2: Derivar clave desde contraseña con Argon2id
$password = 'mi_contraseña_secreta_2026';
$salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES); // 16 bytes

echo "Derivación de clave con Argon2id:\n";
echo "  Password: $password\n";
echo "  Salt (hex): " . bin2hex($salt) . "\n";

$claveDerivada = sodium_crypto_pwhash(
    SODIUM_CRYPTO_SECRETBOX_KEYBYTES,            // Longitud de la clave (32 bytes)
    $password,
    $salt,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,   // Operaciones (para uso interactivo)
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,   // Memoria (64MB)
    SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13          // Algoritmo Argon2id
);

echo "  Clave derivada (hex): " . bin2hex($claveDerivada) . "\n";
echo "  Tamaño: " . strlen($claveDerivada) . " bytes\n\n";

// Constantes de límites disponibles
echo "Límites disponibles:\n";
echo "  OPSLIMIT_INTERACTIVE: " . SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE . "\n";
echo "  OPSLIMIT_MODERATE: " . SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE . "\n";
echo "  OPSLIMIT_SENSITIVE: " . SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE . "\n";
echo "  MEMLIMIT_INTERACTIVE: " . number_format(SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE / 1024 / 1024) . " MB\n";
echo "  MEMLIMIT_MODERATE: " . number_format(SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE / 1024 / 1024) . " MB\n";
echo "  MEMLIMIT_SENSITIVE: " . number_format(SODIUM_CRYPTO_PWHASH_MEMLIMIT_SENSITIVE / 1024 / 1024) . " MB\n";

// Limpiar
sodium_memzero($claveAleatoria);
sodium_memzero($claveDerivada);

// ============================================
// Ejemplo 3: Cifrado de clave pública (asimétrico)
// ============================================

echo "\n=== Ejemplo 3: Cifrado asimétrico (clave pública) ===\n";

/**
 * Cifrado de clave pública: Alice cifra con la clave pública de Bob,
 * y solo Bob puede descifrar con su clave privada.
 * Usa Curve25519 + XSalsa20 + Poly1305.
 */

// Generar par de claves para Alice
$parAlice = sodium_crypto_box_keypair();
$privadaAlice = sodium_crypto_box_secretkey($parAlice);
$publicaAlice = sodium_crypto_box_publickey($parAlice);

// Generar par de claves para Bob
$parBob = sodium_crypto_box_keypair();
$privadaBob = sodium_crypto_box_secretkey($parBob);
$publicaBob = sodium_crypto_box_publickey($parBob);

echo "Claves generadas:\n";
echo "  Alice pública (hex): " . bin2hex($publicaAlice) . "\n";
echo "  Bob pública (hex):   " . bin2hex($publicaBob) . "\n\n";

// Alice envía mensaje a Bob
$mensaje = 'Hola Bob, este mensaje es solo para ti.';
$nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

// Alice cifra usando su clave privada + la clave pública de Bob
$parCifrado = sodium_crypto_box_keypair_from_secretkey_and_publickey($privadaAlice, $publicaBob);
$cifrado = sodium_crypto_box($mensaje, $nonce, $parCifrado);

echo "Alice -> Bob:\n";
echo "  Mensaje: $mensaje\n";
echo "  Cifrado (hex): " . substr(bin2hex($cifrado), 0, 60) . "...\n";

// Bob descifra usando su clave privada + la clave pública de Alice
$parDescifrado = sodium_crypto_box_keypair_from_secretkey_and_publickey($privadaBob, $publicaAlice);
$descifrado = sodium_crypto_box_open($cifrado, $nonce, $parDescifrado);

echo "  Bob descifra: $descifrado\n";
echo "  ¿Coincide? " . ($mensaje === $descifrado ? 'Sí' : 'No') . "\n";

// Limpiar claves privadas de la memoria
sodium_memzero($privadaAlice);
sodium_memzero($privadaBob);

// ============================================
// Ejemplo 4: Firmas digitales
// ============================================

echo "\n=== Ejemplo 4: Firmas digitales ===\n";

/**
 * Las firmas digitales permiten verificar la autenticidad e integridad
 * de un mensaje. Usa Ed25519 (curva elíptica Edwards).
 */

// Generar par de claves para firmas
$parFirma = sodium_crypto_sign_keypair();
$clavePrivadaFirma = sodium_crypto_sign_secretkey($parFirma);
$clavePublicaFirma = sodium_crypto_sign_publickey($parFirma);

echo "Par de claves de firma generado.\n";
echo "Clave pública (hex): " . bin2hex($clavePublicaFirma) . "\n\n";

$documento = 'Contrato: Se acuerda el pago de $10,000 por servicios.';

// Firmar el documento
$firma = sodium_crypto_sign_detached($documento, $clavePrivadaFirma);
echo "Documento: $documento\n";
echo "Firma (hex): " . bin2hex($firma) . "\n";
echo "Tamaño firma: " . strlen($firma) . " bytes\n\n";

// Verificar la firma con la clave pública
$esValida = sodium_crypto_sign_verify_detached($firma, $documento, $clavePublicaFirma);
echo "¿Firma válida? " . ($esValida ? 'SÍ' : 'NO') . "\n";

// Intentar verificar con documento modificado
$documentoModificado = 'Contrato: Se acuerda el pago de $100,000 por servicios.';
$esValida = sodium_crypto_sign_verify_detached($firma, $documentoModificado, $clavePublicaFirma);
echo "¿Firma válida (documento modificado)? " . ($esValida ? 'SÍ' : 'NO - Tampering detectado') . "\n";

// También se puede firmar directamente (mensaje + firma combinados)
$mensajeFirmado = sodium_crypto_sign($documento, $clavePrivadaFirma);
$mensajeVerificado = sodium_crypto_sign_open($mensajeFirmado, $clavePublicaFirma);
echo "\nMensaje firmado y verificado: " . ($mensajeVerificado !== false ? 'OK' : 'FALLÓ') . "\n";

sodium_memzero($clavePrivadaFirma);

// ============================================
// Ejemplo 5: Caso práctico - Clase de cifrado seguro
// ============================================

echo "\n=== Ejemplo 5: Clase de cifrado seguro ===\n";

/**
 * Clase que encapsula Sodium para proporcionar una API simple
 * de cifrado/descifrado con manejo seguro de claves.
 */
class CifradorSodium
{
    private string $clave;

    /**
     * Crear cifrador con clave directa o derivada de contraseña
     */
    public function __construct(string $claveOPassword, bool $esPassword = false)
    {
        if ($esPassword) {
            // Derivar clave de la contraseña
            $salt = hash('sha256', 'salt_fijo_para_demo', true); // En producción: salt aleatorio almacenado
            $salt = substr($salt, 0, SODIUM_CRYPTO_PWHASH_SALTBYTES);
            $this->clave = sodium_crypto_pwhash(
                SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
                $claveOPassword,
                $salt,
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
            );
        } else {
            // Usar clave directa (debe ser de 32 bytes)
            if (strlen($claveOPassword) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                $this->clave = hash('sha256', $claveOPassword, true);
            } else {
                $this->clave = $claveOPassword;
            }
        }
    }

    /**
     * Cifrar datos. Retorna string base64 con nonce incluido.
     */
    public function cifrar(string $textoPlano): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cifrado = sodium_crypto_secretbox($textoPlano, $nonce, $this->clave);

        // Nonce + cifrado, todo en base64
        return base64_encode($nonce . $cifrado);
    }

    /**
     * Descifrar datos previamente cifrados.
     */
    public function descifrar(string $datos): string
    {
        $decodificado = base64_decode($datos, true);
        if ($decodificado === false) {
            throw new RuntimeException('Datos base64 inválidos.');
        }

        $nonce = substr($decodificado, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cifrado = substr($decodificado, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $textoPlano = sodium_crypto_secretbox_open($cifrado, $nonce, $this->clave);
        if ($textoPlano === false) {
            throw new RuntimeException('Fallo al descifrar. Datos corruptos o clave incorrecta.');
        }

        return $textoPlano;
    }

    /**
     * Cifrar un array (se serializa a JSON)
     */
    public function cifrarArray(array $datos): string
    {
        return $this->cifrar(json_encode($datos, JSON_THROW_ON_ERROR));
    }

    /**
     * Descifrar a array
     */
    public function descifrarArray(string $datos): array
    {
        $json = $this->descifrar($datos);
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Destructor: limpiar clave de memoria
     */
    public function __destruct()
    {
        sodium_memzero($this->clave);
    }
}

// Uso con clave directa
$cifrador = new CifradorSodium('clave_secreta_de_la_aplicacion');

echo "Cifrado de textos:\n";
$textos = [
    'Información confidencial del cliente.',
    'Número de cuenta: 9876543210',
    'API Key: sk_live_abcdef123456',
];

foreach ($textos as $texto) {
    $cifrado = $cifrador->cifrar($texto);
    $descifrado = $cifrador->descifrar($cifrado);
    echo "  Original:    $texto\n";
    echo "  Cifrado:     " . substr($cifrado, 0, 40) . "...\n";
    echo "  Descifrado:  $descifrado\n";
    echo "  Integridad:  " . ($texto === $descifrado ? 'OK' : 'FALLO') . "\n\n";
}

// Cifrado de arrays/objetos
echo "Cifrado de arrays:\n";
$datosUsuario = [
    'nombre'   => 'Sandra García',
    'email'    => 'sandra@secreto.com',
    'telefono' => '+52-555-1234567',
    'tarjeta'  => '4111-1111-1111-1111',
];

$arrayCifrado = $cifrador->cifrarArray($datosUsuario);
echo "  Array cifrado: " . substr($arrayCifrado, 0, 50) . "...\n";

$arrayDescifrado = $cifrador->descifrarArray($arrayCifrado);
echo "  Array descifrado:\n";
foreach ($arrayDescifrado as $key => $val) {
    echo "    $key: $val\n";
}

?>
