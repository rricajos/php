<?php
// ============================================
// ATRIBUTOS EN PHP 8.0
// #[Attribute], atributos personalizados, ReflectionAttribute
// ============================================

// --- Ejemplo 1: Concepto básico de atributos ---
// Los atributos son metadatos estructurados que se asocian a declaraciones

// Definimos un atributo personalizado con #[Attribute]
#[Attribute]
class Ruta {
    public function __construct(
        public readonly string $metodo,
        public readonly string $path
    ) {}
}

#[Attribute]
class RequiereAutenticacion {
    public function __construct(
        public readonly string $rol = 'usuario'
    ) {}
}

// Usamos los atributos en una clase controladora
class UsuarioControlador {
    #[Ruta('GET', '/usuarios')]
    public function listar(): string {
        return "Lista de usuarios";
    }

    #[Ruta('GET', '/usuarios/{id}')]
    public function obtener(int $id): string {
        return "Usuario #{$id}";
    }

    #[Ruta('POST', '/usuarios')]
    #[RequiereAutenticacion(rol: 'admin')]
    public function crear(array $datos): string {
        return "Usuario creado";
    }
}

// Lectura de atributos con Reflection
$reflector = new ReflectionClass(UsuarioControlador::class);

echo "=== Rutas del controlador ===\n";
foreach ($reflector->getMethods() as $metodo) {
    $atributosRuta = $metodo->getAttributes(Ruta::class);

    foreach ($atributosRuta as $atributo) {
        // newInstance() crea una instancia del atributo con sus argumentos
        $ruta = $atributo->newInstance();
        echo "{$ruta->metodo} {$ruta->path} → {$metodo->getName()}()\n";
    }

    // Verificar si requiere autenticación
    $atributosAuth = $metodo->getAttributes(RequiereAutenticacion::class);
    if (!empty($atributosAuth)) {
        $auth = $atributosAuth[0]->newInstance();
        echo "  ↳ Requiere autenticación (rol: {$auth->rol})\n";
    }
}
// GET /usuarios → listar()
// GET /usuarios/{id} → obtener()
// POST /usuarios → crear()
//   ↳ Requiere autenticación (rol: admin)


// --- Ejemplo 2: Atributos con parámetros avanzados ---
// Atributo para validación de propiedades

#[Attribute(Attribute::TARGET_PROPERTY)]
class Validar {
    public function __construct(
        public readonly string $tipo,
        public readonly array $opciones = []
    ) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Longitud {
    public function __construct(
        public readonly int $min = 0,
        public readonly int $max = PHP_INT_MAX,
        public readonly string $mensaje = "Longitud fuera de rango"
    ) {}
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email {
    public function __construct(
        public readonly string $mensaje = "Email no válido"
    ) {}
}

class RegistroUsuario {
    #[Validar('requerido')]
    #[Longitud(min: 3, max: 50, mensaje: "El nombre debe tener entre 3 y 50 caracteres")]
    public string $nombre = '';

    #[Validar('requerido')]
    #[Email(mensaje: "Proporcione un email válido")]
    public string $email = '';

    #[Validar('requerido')]
    #[Longitud(min: 8, mensaje: "La contraseña debe tener al menos 8 caracteres")]
    public string $contrasena = '';
}

// Motor de validación que lee los atributos
class MotorValidacion {
    /** @return string[] Lista de errores */
    public function validar(object $objeto): array {
        $errores = [];
        $reflector = new ReflectionClass($objeto);

        foreach ($reflector->getProperties() as $propiedad) {
            $nombreProp = $propiedad->getName();
            $propiedad->setAccessible(true);
            $valor = $propiedad->getValue($objeto);

            // Verificar atributo Validar
            foreach ($propiedad->getAttributes(Validar::class) as $attr) {
                $validar = $attr->newInstance();
                if ($validar->tipo === 'requerido' && empty($valor)) {
                    $errores[] = "El campo '{$nombreProp}' es obligatorio";
                }
            }

            // Verificar atributo Longitud
            foreach ($propiedad->getAttributes(Longitud::class) as $attr) {
                $longitud = $attr->newInstance();
                $len = mb_strlen($valor);
                if (!empty($valor) && ($len < $longitud->min || $len > $longitud->max)) {
                    $errores[] = "{$nombreProp}: {$longitud->mensaje}";
                }
            }

            // Verificar atributo Email
            foreach ($propiedad->getAttributes(Email::class) as $attr) {
                $emailAttr = $attr->newInstance();
                if (!empty($valor) && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = "{$nombreProp}: {$emailAttr->mensaje}";
                }
            }
        }

        return $errores;
    }
}

$registro = new RegistroUsuario();
$registro->nombre = "AB";                  // Muy corto
$registro->email = "no-es-email";          // Formato inválido
$registro->contrasena = "123";             // Muy corta

$motor = new MotorValidacion();
$errores = $motor->validar($registro);

echo "\n=== Errores de validación ===\n";
foreach ($errores as $error) {
    echo "  ✗ {$error}\n";
}
// ✗ nombre: El nombre debe tener entre 3 y 50 caracteres
// ✗ email: Proporcione un email válido
// ✗ contrasena: La contraseña debe tener al menos 8 caracteres


// --- Ejemplo 3: Atributos con TARGET específico y repetición ---
// Controlamos dónde se puede usar el atributo y si es repetible

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RateLimiter {
    public function __construct(
        public readonly int $maxSolicitudes,
        public readonly int $periodoSegundos,
        public readonly string $identificador = 'ip'
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS)]
class Controlador {
    public function __construct(
        public readonly string $prefijo = ''
    ) {}
}

#[Controlador(prefijo: '/api/v1')]
class APIControlador {
    // IS_REPEATABLE permite usar el mismo atributo múltiples veces
    #[RateLimiter(maxSolicitudes: 100, periodoSegundos: 60, identificador: 'ip')]
    #[RateLimiter(maxSolicitudes: 1000, periodoSegundos: 3600, identificador: 'api_key')]
    public function listarRecursos(): array {
        return ['recurso1', 'recurso2'];
    }

    #[RateLimiter(maxSolicitudes: 10, periodoSegundos: 60)]
    public function crearRecurso(array $datos): array {
        return ['id' => 1, ...$datos];
    }
}

// Leer los atributos de la clase y sus métodos
$refClase = new ReflectionClass(APIControlador::class);

// Atributo de la clase
$attrControlador = $refClase->getAttributes(Controlador::class);
if (!empty($attrControlador)) {
    $ctrl = $attrControlador[0]->newInstance();
    echo "\nControlador con prefijo: {$ctrl->prefijo}\n";
}

// Atributos de los métodos
foreach ($refClase->getMethods() as $metodo) {
    $limitadores = $metodo->getAttributes(RateLimiter::class);
    if (!empty($limitadores)) {
        echo "\nMétodo: {$metodo->getName()}\n";
        foreach ($limitadores as $attr) {
            $rl = $attr->newInstance();
            echo "  Límite: {$rl->maxSolicitudes} solicitudes / {$rl->periodoSegundos}s (por {$rl->identificador})\n";
        }
    }
}
// Método: listarRecursos
//   Límite: 100 solicitudes / 60s (por ip)
//   Límite: 1000 solicitudes / 3600s (por api_key)
// Método: crearRecurso
//   Límite: 10 solicitudes / 60s (por ip)


// --- Ejemplo 4: Patrón práctico - Serialización con atributos ---
// Usar atributos para controlar cómo se serializa un objeto a JSON

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonPropiedad {
    public function __construct(
        public readonly ?string $nombre = null,    // Nombre en el JSON
        public readonly bool $ignorar = false,     // Excluir del JSON
        public readonly ?string $formato = null    // Formato especial
    ) {}
}

class PerfilUsuario {
    #[JsonPropiedad(nombre: 'user_id')]
    public int $id = 1;

    #[JsonPropiedad(nombre: 'full_name')]
    public string $nombreCompleto = "Sandra García";

    #[JsonPropiedad]
    public string $email = "sandra@ejemplo.com";

    #[JsonPropiedad(ignorar: true)]
    public string $contrasena = "hash_secreto";

    #[JsonPropiedad(nombre: 'registered_at', formato: 'fecha')]
    public string $registradoEn = "2024-01-15 10:30:00";
}

// Serializador que usa los atributos
class JsonSerializador {
    public function serializar(object $objeto): string {
        $datos = [];
        $reflector = new ReflectionClass($objeto);

        foreach ($reflector->getProperties() as $propiedad) {
            $atributos = $propiedad->getAttributes(JsonPropiedad::class);

            if (empty($atributos)) {
                // Sin atributo: usar nombre original
                $datos[$propiedad->getName()] = $propiedad->getValue($objeto);
                continue;
            }

            $jsonProp = $atributos[0]->newInstance();

            // Ignorar esta propiedad
            if ($jsonProp->ignorar) {
                continue;
            }

            $nombre = $jsonProp->nombre ?? $propiedad->getName();
            $valor = $propiedad->getValue($objeto);

            // Aplicar formato si se especificó
            if ($jsonProp->formato === 'fecha' && !empty($valor)) {
                $valor = date('c', strtotime($valor)); // Formato ISO 8601
            }

            $datos[$nombre] = $valor;
        }

        return json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

$perfil = new PerfilUsuario();
$serializador = new JsonSerializador();

echo "\n=== JSON serializado ===\n";
echo $serializador->serializar($perfil) . "\n";
// {
//     "user_id": 1,
//     "full_name": "Sandra García",
//     "email": "sandra@ejemplo.com",
//     "registered_at": "2024-01-15T10:30:00+00:00"
// }
// Nota: 'contrasena' fue excluida gracias a ignorar: true

?>
