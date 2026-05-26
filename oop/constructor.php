<?php
// ============================================
// CONSTRUCTORES Y DESTRUCTORES EN PHP
// __construct, __destruct, constructor con parámetros
// ============================================

// --- Ejemplo 1: Constructor básico con parámetros ---
// __construct se ejecuta automáticamente al crear una instancia

class Usuario {
    public string $nombre;
    public string $email;
    public string $fechaRegistro;

    // El constructor inicializa el objeto al momento de crearlo
    public function __construct(string $nombre, string $email) {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->fechaRegistro = date('Y-m-d H:i:s');
        echo "Usuario '{$this->nombre}' creado el {$this->fechaRegistro}\n";
    }

    public function mostrar(): string {
        return "{$this->nombre} ({$this->email})";
    }
}

// Al usar 'new', el constructor se invoca automáticamente
$usuario = new Usuario("Ana García", "ana@correo.com");
// Usuario 'Ana García' creado el 2024-01-15 10:30:00
echo $usuario->mostrar() . "\n"; // Ana García (ana@correo.com)


// --- Ejemplo 2: Constructor con parámetros opcionales y valores por defecto ---
// Podemos definir valores predeterminados para los parámetros

class Conexion {
    private string $host;
    private int $puerto;
    private string $baseDatos;
    private string $charset;

    public function __construct(
        string $baseDatos,
        string $host = "localhost",
        int $puerto = 3306,
        string $charset = "utf8mb4"
    ) {
        $this->baseDatos = $baseDatos;
        $this->host = $host;
        $this->puerto = $puerto;
        $this->charset = $charset;
        echo "Conexión configurada: {$this->host}:{$this->puerto}/{$this->baseDatos}\n";
    }

    public function obtenerDSN(): string {
        return "mysql:host={$this->host};port={$this->puerto};dbname={$this->baseDatos};charset={$this->charset}";
    }
}

// Solo pasamos el parámetro obligatorio, los demás usan valores por defecto
$db1 = new Conexion("mi_tienda");
// Conexión configurada: localhost:3306/mi_tienda

// Podemos sobrescribir los valores por defecto
$db2 = new Conexion("produccion", "192.168.1.100", 3307);
// Conexión configurada: 192.168.1.100:3307/produccion

echo $db1->obtenerDSN() . "\n";
// mysql:host=localhost;port=3306;dbname=mi_tienda;charset=utf8mb4


// --- Ejemplo 3: __destruct - el destructor ---
// __destruct se ejecuta cuando el objeto se destruye o el script termina

class ArchivoLog {
    private string $ruta;
    private array $mensajes = [];
    private int $totalEscrituras = 0;

    public function __construct(string $ruta) {
        $this->ruta = $ruta;
        echo "Log abierto: {$this->ruta}\n";
    }

    public function escribir(string $nivel, string $mensaje): void {
        $timestamp = date('Y-m-d H:i:s');
        $this->mensajes[] = "[{$timestamp}] [{$nivel}] {$mensaje}";
        $this->totalEscrituras++;
    }

    // El destructor se llama automáticamente al final del ciclo de vida
    public function __destruct() {
        // Simulamos guardar los mensajes al destruir el objeto
        echo "Guardando {$this->totalEscrituras} entradas en {$this->ruta}...\n";
        foreach ($this->mensajes as $msg) {
            echo "  > {$msg}\n";
        }
        echo "Log cerrado: {$this->ruta}\n";
    }
}

$log = new ArchivoLog("/var/log/app.log");
$log->escribir("INFO", "Aplicación iniciada");
$log->escribir("WARNING", "Memoria al 80%");
$log->escribir("ERROR", "Fallo en la conexión a la API");

// Al terminar el script o hacer unset($log), se llama a __destruct
unset($log);
// Guardando 3 entradas en /var/log/app.log...
//   > [2024-01-15 10:30:00] [INFO] Aplicación iniciada
//   > [2024-01-15 10:30:00] [WARNING] Memoria al 80%
//   > [2024-01-15 10:30:00] [ERROR] Fallo en la conexión a la API
// Log cerrado: /var/log/app.log


// --- Ejemplo 4: Constructor que valida datos ---
// Podemos lanzar excepciones desde el constructor si los datos no son válidos

class RangoFechas {
    private string $inicio;
    private string $fin;

    public function __construct(string $inicio, string $fin) {
        $fechaInicio = strtotime($inicio);
        $fechaFin = strtotime($fin);

        if ($fechaInicio === false || $fechaFin === false) {
            throw new InvalidArgumentException("Formato de fecha inválido");
        }

        if ($fechaInicio > $fechaFin) {
            throw new InvalidArgumentException(
                "La fecha de inicio ({$inicio}) no puede ser posterior a la fecha fin ({$fin})"
            );
        }

        $this->inicio = $inicio;
        $this->fin = $fin;
    }

    public function diasEntre(): int {
        $diff = abs(strtotime($this->fin) - strtotime($this->inicio));
        return (int) ceil($diff / 86400);
    }

    public function __toString(): string {
        return "{$this->inicio} → {$this->fin} ({$this->diasEntre()} días)";
    }
}

try {
    $vacaciones = new RangoFechas("2024-07-01", "2024-07-15");
    echo "Vacaciones: {$vacaciones}\n";
    // Vacaciones: 2024-07-01 → 2024-07-15 (14 días)

    // Esto lanzará una excepción
    $invalido = new RangoFechas("2024-12-25", "2024-01-01");
} catch (InvalidArgumentException $e) {
    echo "Error al crear rango: " . $e->getMessage() . "\n";
    // Error al crear rango: La fecha de inicio (2024-12-25) no puede ser posterior a la fecha fin (2024-01-01)
}


// --- Ejemplo 5: Constructor con inyección de dependencias ---
// Patrón común: inyectar dependencias a través del constructor

class ServicioEmail {
    public function enviar(string $destinatario, string $asunto, string $cuerpo): bool {
        echo "Enviando correo a {$destinatario}: {$asunto}\n";
        return true; // Simulamos envío exitoso
    }
}

class ServicioNotificaciones {
    private ServicioEmail $email;
    private string $remitente;

    // Inyectamos el servicio de email como dependencia
    public function __construct(ServicioEmail $email, string $remitente = "sistema@app.com") {
        $this->email = $email;
        $this->remitente = $remitente;
    }

    public function notificarBienvenida(string $usuario, string $correo): void {
        $asunto = "¡Bienvenido, {$usuario}!";
        $cuerpo = "Gracias por registrarte en nuestra plataforma.";
        $this->email->enviar($correo, $asunto, $cuerpo);
    }

    public function notificarCompra(string $correo, float $monto): void {
        $asunto = "Confirmación de compra";
        $cuerpo = "Tu compra por \${$monto} ha sido procesada.";
        $this->email->enviar($correo, $asunto, $cuerpo);
    }
}

// Creamos las dependencias y las inyectamos
$servicioEmail = new ServicioEmail();
$notificaciones = new ServicioNotificaciones($servicioEmail);

$notificaciones->notificarBienvenida("Pedro", "pedro@correo.com");
// Enviando correo a pedro@correo.com: ¡Bienvenido, Pedro!

$notificaciones->notificarCompra("pedro@correo.com", 59.99);
// Enviando correo a pedro@correo.com: Confirmación de compra

?>
