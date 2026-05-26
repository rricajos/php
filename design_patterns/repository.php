<?php
/**
 * =============================================================================
 * PATRON REPOSITORY (REPOSITORIO) EN PHP
 * =============================================================================
 *
 * El patron Repository actua como una capa de abstraccion entre la logica
 * de negocio y la capa de acceso a datos. Encapsula la logica necesaria
 * para acceder a las fuentes de datos (BD, API, archivos, memoria).
 *
 * Ventajas:
 * - Desacopla la logica de negocio de la tecnologia de persistencia
 * - Facilita cambiar de base de datos o fuente de datos sin afectar el negocio
 * - Permite usar implementaciones en memoria para pruebas rapidas
 * - Centraliza y estandariza el acceso a datos
 * - Hace que el codigo de negocio sea mas limpio y enfocado
 *
 * Componentes:
 * - Repository Interface: contrato comun para todas las implementaciones
 * - ConcreteRepository: implementaciones especificas (BD, memoria, API, etc.)
 * - Entity/Model: objetos de dominio que el repositorio maneja
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Interfaz Repository para Abstraccion de Acceso a Datos
// =============================================================================
// Problema: La logica de negocio no deberia saber SI los datos vienen de
// MySQL, PostgreSQL, un archivo JSON o una API externa. El repositorio
// abstrae esos detalles.

echo "=== Ejemplo 1: Interfaz Repository Generica ===\n\n";

// Entidad de dominio: Usuario
class Usuario
{
    public function __construct(
        private ?int $id,
        private string $nombre,
        private string $email,
        private string $rol = 'usuario',
        private ?string $creadoEn = null,
        private ?string $actualizadoEn = null
    ) {
        $this->creadoEn = $creadoEn ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function obtenerId(): ?int { return $this->id; }
    public function obtenerNombre(): string { return $this->nombre; }
    public function obtenerEmail(): string { return $this->email; }
    public function obtenerRol(): string { return $this->rol; }
    public function obtenerCreadoEn(): string { return $this->creadoEn; }
    public function obtenerActualizadoEn(): ?string { return $this->actualizadoEn; }

    // Setters (para modificaciones)
    public function establecerId(int $id): void { $this->id = $id; }
    public function establecerNombre(string $nombre): void { $this->nombre = $nombre; }
    public function establecerEmail(string $email): void { $this->email = $email; }
    public function establecerRol(string $rol): void { $this->rol = $rol; }
    public function marcarActualizado(): void { $this->actualizadoEn = date('Y-m-d H:i:s'); }

    public function __toString(): string
    {
        return "[{$this->id}] {$this->nombre} <{$this->email}> ({$this->rol})";
    }
}

/**
 * Interfaz del repositorio: define el contrato que TODAS las implementaciones
 * deben cumplir. La logica de negocio solo depende de esta interfaz.
 */
interface RepositorioUsuario
{
    // Operaciones CRUD basicas
    public function buscarPorId(int $id): ?Usuario;
    public function buscarPorEmail(string $email): ?Usuario;
    public function buscarTodos(): array;
    public function guardar(Usuario $usuario): Usuario;
    public function eliminar(int $id): bool;

    // Consultas especializadas
    public function buscarPorRol(string $rol): array;
    public function contar(): int;
    public function existeEmail(string $email): bool;
}

echo "  Interfaz RepositorioUsuario definida con los metodos:\n";
echo "    - buscarPorId(int): ?Usuario\n";
echo "    - buscarPorEmail(string): ?Usuario\n";
echo "    - buscarTodos(): array\n";
echo "    - guardar(Usuario): Usuario\n";
echo "    - eliminar(int): bool\n";
echo "    - buscarPorRol(string): array\n";
echo "    - contar(): int\n";
echo "    - existeEmail(string): bool\n\n";


// =============================================================================
// Ejemplo 2: InMemoryRepository para Pruebas
// =============================================================================
// Problema: Las pruebas unitarias no deben depender de una base de datos real.
// Un repositorio en memoria es rapido, predecible y no tiene efectos secundarios.

echo "=== Ejemplo 2: Repositorio en Memoria (para Pruebas) ===\n\n";

class RepositorioUsuarioEnMemoria implements RepositorioUsuario
{
    /** @var Usuario[] */
    private array $usuarios = [];
    private int $siguienteId = 1;

    public function buscarPorId(int $id): ?Usuario
    {
        return $this->usuarios[$id] ?? null;
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        foreach ($this->usuarios as $usuario) {
            if ($usuario->obtenerEmail() === $email) {
                return $usuario;
            }
        }
        return null;
    }

    public function buscarTodos(): array
    {
        return array_values($this->usuarios);
    }

    public function guardar(Usuario $usuario): Usuario
    {
        if ($usuario->obtenerId() === null) {
            // Nuevo usuario: asignar ID
            $usuario->establecerId($this->siguienteId++);
            $this->usuarios[$usuario->obtenerId()] = $usuario;
        } else {
            // Actualizar existente
            $usuario->marcarActualizado();
            $this->usuarios[$usuario->obtenerId()] = $usuario;
        }
        return $usuario;
    }

    public function eliminar(int $id): bool
    {
        if (isset($this->usuarios[$id])) {
            unset($this->usuarios[$id]);
            return true;
        }
        return false;
    }

    public function buscarPorRol(string $rol): array
    {
        return array_values(array_filter(
            $this->usuarios,
            fn(Usuario $u) => $u->obtenerRol() === $rol
        ));
    }

    public function contar(): int
    {
        return count($this->usuarios);
    }

    public function existeEmail(string $email): bool
    {
        return $this->buscarPorEmail($email) !== null;
    }
}

// Demostrar el uso del repositorio en memoria
$repo = new RepositorioUsuarioEnMemoria();

// Crear usuarios
$usuario1 = $repo->guardar(new Usuario(null, 'Ana Garcia', 'ana@email.com', 'admin'));
$usuario2 = $repo->guardar(new Usuario(null, 'Luis Perez', 'luis@email.com', 'usuario'));
$usuario3 = $repo->guardar(new Usuario(null, 'Maria Lopez', 'maria@email.com', 'usuario'));

echo "  Usuarios creados: {$repo->contar()}\n";

// Buscar
$encontrado = $repo->buscarPorId(1);
echo "  Buscar ID 1: {$encontrado}\n";

$porEmail = $repo->buscarPorEmail('luis@email.com');
echo "  Buscar por email: {$porEmail}\n";

// Actualizar
$usuario1->establecerNombre('Ana Garcia de Martinez');
$repo->guardar($usuario1);
echo "  Actualizado: {$repo->buscarPorId(1)}\n";

// Buscar por rol
$usuariosNormales = $repo->buscarPorRol('usuario');
echo "  Usuarios con rol 'usuario': " . count($usuariosNormales) . "\n";

// Eliminar
$eliminado = $repo->eliminar(3);
echo "  Eliminar ID 3: " . ($eliminado ? 'OK' : 'No encontrado') . "\n";
echo "  Total despues de eliminar: {$repo->contar()}\n\n";


// =============================================================================
// Ejemplo 3: Repositorio con Base de Datos (PDO Simulado)
// =============================================================================
// Problema: En produccion necesitamos persistencia real con base de datos.
// Esta implementacion usa la misma interfaz pero con consultas SQL via PDO.

echo "=== Ejemplo 3: Repositorio con Base de Datos (PDO) ===\n\n";

/**
 * NOTA: Esta implementacion simula las operaciones de PDO para que el ejemplo
 * funcione sin una base de datos real. En produccion, se usaria un PDO real
 * con prepared statements para prevenir inyeccion SQL.
 */

class RepositorioUsuarioBD implements RepositorioUsuario
{
    // En produccion: private \PDO $conexion;
    // Simulamos con almacenamiento interno
    private array $tablaBD = [];
    private int $autoIncrement = 1;

    public function __construct(
        // En produccion se recibiria la conexion PDO real:
        // private \PDO $conexion
    ) {
        echo "  [BD] Conexion a base de datos establecida.\n";
    }

    public function buscarPorId(int $id): ?Usuario
    {
        /**
         * SQL real con PDO:
         *
         *   $stmt = $this->conexion->prepare(
         *       'SELECT * FROM usuarios WHERE id = :id LIMIT 1'
         *   );
         *   $stmt->execute([':id' => $id]);
         *   $fila = $stmt->fetch(\PDO::FETCH_ASSOC);
         *
         *   if (!$fila) return null;
         *   return $this->hidratarUsuario($fila);
         */

        if (!isset($this->tablaBD[$id])) {
            return null;
        }

        $fila = $this->tablaBD[$id];
        return new Usuario($fila['id'], $fila['nombre'], $fila['email'], $fila['rol'], $fila['creado_en']);
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        /**
         * SQL real:
         *   $stmt = $this->conexion->prepare(
         *       'SELECT * FROM usuarios WHERE email = :email LIMIT 1'
         *   );
         *   $stmt->execute([':email' => $email]);
         */

        foreach ($this->tablaBD as $fila) {
            if ($fila['email'] === $email) {
                return new Usuario($fila['id'], $fila['nombre'], $fila['email'], $fila['rol']);
            }
        }
        return null;
    }

    public function buscarTodos(): array
    {
        /**
         * SQL real:
         *   $stmt = $this->conexion->query('SELECT * FROM usuarios ORDER BY id ASC');
         *   $filas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
         *   return array_map([$this, 'hidratarUsuario'], $filas);
         */

        $usuarios = [];
        foreach ($this->tablaBD as $fila) {
            $usuarios[] = new Usuario($fila['id'], $fila['nombre'], $fila['email'], $fila['rol']);
        }
        return $usuarios;
    }

    public function guardar(Usuario $usuario): Usuario
    {
        if ($usuario->obtenerId() === null) {
            /**
             * SQL real (INSERT):
             *   $stmt = $this->conexion->prepare(
             *       'INSERT INTO usuarios (nombre, email, rol, creado_en)
             *        VALUES (:nombre, :email, :rol, NOW())'
             *   );
             *   $stmt->execute([
             *       ':nombre' => $usuario->obtenerNombre(),
             *       ':email'  => $usuario->obtenerEmail(),
             *       ':rol'    => $usuario->obtenerRol(),
             *   ]);
             *   $usuario->establecerId((int) $this->conexion->lastInsertId());
             */

            $id = $this->autoIncrement++;
            $usuario->establecerId($id);

            $this->tablaBD[$id] = [
                'id'        => $id,
                'nombre'    => $usuario->obtenerNombre(),
                'email'     => $usuario->obtenerEmail(),
                'rol'       => $usuario->obtenerRol(),
                'creado_en' => $usuario->obtenerCreadoEn(),
            ];
        } else {
            /**
             * SQL real (UPDATE):
             *   $stmt = $this->conexion->prepare(
             *       'UPDATE usuarios SET nombre = :nombre, email = :email,
             *        rol = :rol, actualizado_en = NOW() WHERE id = :id'
             *   );
             */

            $this->tablaBD[$usuario->obtenerId()] = [
                'id'        => $usuario->obtenerId(),
                'nombre'    => $usuario->obtenerNombre(),
                'email'     => $usuario->obtenerEmail(),
                'rol'       => $usuario->obtenerRol(),
                'creado_en' => $usuario->obtenerCreadoEn(),
            ];
        }

        return $usuario;
    }

    public function eliminar(int $id): bool
    {
        /**
         * SQL real:
         *   $stmt = $this->conexion->prepare('DELETE FROM usuarios WHERE id = :id');
         *   $stmt->execute([':id' => $id]);
         *   return $stmt->rowCount() > 0;
         */

        if (isset($this->tablaBD[$id])) {
            unset($this->tablaBD[$id]);
            return true;
        }
        return false;
    }

    public function buscarPorRol(string $rol): array
    {
        return array_values(array_filter(
            $this->buscarTodos(),
            fn(Usuario $u) => $u->obtenerRol() === $rol
        ));
    }

    public function contar(): int
    {
        /**
         * SQL real:
         *   $stmt = $this->conexion->query('SELECT COUNT(*) FROM usuarios');
         *   return (int) $stmt->fetchColumn();
         */
        return count($this->tablaBD);
    }

    public function existeEmail(string $email): bool
    {
        return $this->buscarPorEmail($email) !== null;
    }
}

$repoBD = new RepositorioUsuarioBD();
$repoBD->guardar(new Usuario(null, 'Roberto Sanchez', 'roberto@corp.com', 'admin'));
$repoBD->guardar(new Usuario(null, 'Elena Ruiz', 'elena@corp.com', 'editor'));

echo "  Total en BD: {$repoBD->contar()}\n";
echo "  Buscar por email: {$repoBD->buscarPorEmail('roberto@corp.com')}\n";
echo "  Existe elena@corp.com: " . ($repoBD->existeEmail('elena@corp.com') ? 'SI' : 'NO') . "\n\n";


// =============================================================================
// Ejemplo 4: UserRepository Completo con find/save/delete
// =============================================================================
// Problema: Necesitamos un repositorio mas completo con criterios de busqueda
// avanzados, paginacion y operaciones en lote.

echo "=== Ejemplo 4: Repositorio Avanzado con Criterios y Paginacion ===\n\n";

// Objeto de criterios para busquedas flexibles
class CriteriosBusqueda
{
    private array $filtros = [];
    private ?string $ordenarPor = null;
    private string $direccionOrden = 'ASC';
    private int $limite = 0;
    private int $desplazamiento = 0;

    public function donde(string $campo, string $operador, mixed $valor): self
    {
        $this->filtros[] = compact('campo', 'operador', 'valor');
        return $this;
    }

    public function ordenarPor(string $campo, string $direccion = 'ASC'): self
    {
        $this->ordenarPor = $campo;
        $this->direccionOrden = strtoupper($direccion);
        return $this;
    }

    public function limitar(int $limite, int $desplazamiento = 0): self
    {
        $this->limite = $limite;
        $this->desplazamiento = $desplazamiento;
        return $this;
    }

    public function obtenerFiltros(): array { return $this->filtros; }
    public function obtenerOrden(): ?array
    {
        return $this->ordenarPor
            ? ['campo' => $this->ordenarPor, 'direccion' => $this->direccionOrden]
            : null;
    }
    public function obtenerLimite(): int { return $this->limite; }
    public function obtenerDesplazamiento(): int { return $this->desplazamiento; }
}

// Resultado paginado
class ResultadoPaginado
{
    public function __construct(
        public readonly array $elementos,
        public readonly int $total,
        public readonly int $pagina,
        public readonly int $porPagina,
        public readonly int $totalPaginas
    ) {}
}

// Interfaz extendida del repositorio
interface RepositorioUsuarioAvanzado extends RepositorioUsuario
{
    public function buscarPorCriterios(CriteriosBusqueda $criterios): array;
    public function paginar(int $pagina, int $porPagina): ResultadoPaginado;
    public function guardarLote(array $usuarios): array;
    public function eliminarLote(array $ids): int;
}

class RepositorioUsuarioAvanzadoMemoria extends RepositorioUsuarioEnMemoria implements RepositorioUsuarioAvanzado
{
    public function buscarPorCriterios(CriteriosBusqueda $criterios): array
    {
        $resultados = $this->buscarTodos();

        // Aplicar filtros
        foreach ($criterios->obtenerFiltros() as $filtro) {
            $resultados = array_filter($resultados, function (Usuario $u) use ($filtro) {
                $valor = match ($filtro['campo']) {
                    'nombre' => $u->obtenerNombre(),
                    'email'  => $u->obtenerEmail(),
                    'rol'    => $u->obtenerRol(),
                    default  => null,
                };

                return match ($filtro['operador']) {
                    '='        => $valor === $filtro['valor'],
                    '!='       => $valor !== $filtro['valor'],
                    'contiene' => str_contains(strtolower($valor), strtolower($filtro['valor'])),
                    'empieza'  => str_starts_with(strtolower($valor), strtolower($filtro['valor'])),
                    default    => true,
                };
            });
        }

        $resultados = array_values($resultados);

        // Aplicar ordenamiento
        $orden = $criterios->obtenerOrden();
        if ($orden) {
            usort($resultados, function (Usuario $a, Usuario $b) use ($orden) {
                $valorA = match ($orden['campo']) {
                    'nombre' => $a->obtenerNombre(),
                    'email'  => $a->obtenerEmail(),
                    default  => '',
                };
                $valorB = match ($orden['campo']) {
                    'nombre' => $b->obtenerNombre(),
                    'email'  => $b->obtenerEmail(),
                    default  => '',
                };

                $comparacion = strcasecmp($valorA, $valorB);
                return $orden['direccion'] === 'DESC' ? -$comparacion : $comparacion;
            });
        }

        // Aplicar limite y desplazamiento
        if ($criterios->obtenerLimite() > 0) {
            $resultados = array_slice($resultados, $criterios->obtenerDesplazamiento(), $criterios->obtenerLimite());
        }

        return $resultados;
    }

    public function paginar(int $pagina, int $porPagina): ResultadoPaginado
    {
        $todos = $this->buscarTodos();
        $total = count($todos);
        $totalPaginas = (int) ceil($total / $porPagina);
        $desplazamiento = ($pagina - 1) * $porPagina;

        $elementos = array_slice($todos, $desplazamiento, $porPagina);

        return new ResultadoPaginado($elementos, $total, $pagina, $porPagina, $totalPaginas);
    }

    public function guardarLote(array $usuarios): array
    {
        return array_map(fn(Usuario $u) => $this->guardar($u), $usuarios);
    }

    public function eliminarLote(array $ids): int
    {
        $eliminados = 0;
        foreach ($ids as $id) {
            if ($this->eliminar($id)) {
                $eliminados++;
            }
        }
        return $eliminados;
    }
}

// Uso del repositorio avanzado
$repoAvanzado = new RepositorioUsuarioAvanzadoMemoria();

// Insertar lote de usuarios
$repoAvanzado->guardarLote([
    new Usuario(null, 'Andrea Torres', 'andrea@empresa.com', 'admin'),
    new Usuario(null, 'Bruno Castro', 'bruno@empresa.com', 'editor'),
    new Usuario(null, 'Carmen Diaz', 'carmen@empresa.com', 'usuario'),
    new Usuario(null, 'Daniel Flores', 'daniel@empresa.com', 'usuario'),
    new Usuario(null, 'Eva Gonzalez', 'eva@empresa.com', 'editor'),
    new Usuario(null, 'Fernando Herrera', 'fernando@empresa.com', 'usuario'),
    new Usuario(null, 'Gabriela Ibarra', 'gabriela@empresa.com', 'admin'),
]);

echo "  Total usuarios: {$repoAvanzado->contar()}\n\n";

// Busqueda con criterios
echo "  --- Busqueda: editores ordenados por nombre ---\n";
$criterios = (new CriteriosBusqueda())
    ->donde('rol', '=', 'editor')
    ->ordenarPor('nombre', 'ASC');

$editores = $repoAvanzado->buscarPorCriterios($criterios);
foreach ($editores as $editor) {
    echo "    {$editor}\n";
}

echo "\n  --- Busqueda: nombre contiene 'a', limitado a 3 ---\n";
$criterios2 = (new CriteriosBusqueda())
    ->donde('nombre', 'contiene', 'a')
    ->limitar(3);

$conA = $repoAvanzado->buscarPorCriterios($criterios2);
foreach ($conA as $usuario) {
    echo "    {$usuario}\n";
}

echo "\n  --- Paginacion: pagina 2, 3 por pagina ---\n";
$pagina = $repoAvanzado->paginar(pagina: 2, porPagina: 3);
echo "    Pagina {$pagina->pagina} de {$pagina->totalPaginas} (total: {$pagina->total})\n";
foreach ($pagina->elementos as $usuario) {
    echo "    {$usuario}\n";
}

// Eliminacion en lote
$eliminados = $repoAvanzado->eliminarLote([5, 6]);
echo "\n  Eliminados en lote: {$eliminados}\n";
echo "  Total restante: {$repoAvanzado->contar()}\n\n";


// =============================================================================
// Ejemplo 5: Intercambiando Implementaciones sin Cambiar Logica de Negocio
// =============================================================================
// Problema: Demostrar que el mismo servicio de negocio funciona identicamente
// con cualquier implementacion del repositorio (memoria, BD, API, etc.).

echo "=== Ejemplo 5: Servicio de Negocio Desacoplado del Repositorio ===\n\n";

/**
 * Servicio de negocio: contiene la logica de la aplicacion.
 * SOLO depende de la interfaz RepositorioUsuario, no de implementaciones.
 * Esto permite inyectar cualquier implementacion sin modificar este codigo.
 */
class ServicioGestionUsuarios
{
    public function __construct(
        private RepositorioUsuario $repositorio
    ) {}

    // Registrar un nuevo usuario con validaciones de negocio
    public function registrar(string $nombre, string $email, string $rol = 'usuario'): Usuario
    {
        // Validacion de negocio: email unico
        if ($this->repositorio->existeEmail($email)) {
            throw new \RuntimeException("El email {$email} ya esta registrado.");
        }

        // Validacion de negocio: roles permitidos
        $rolesPermitidos = ['admin', 'editor', 'usuario'];
        if (!in_array($rol, $rolesPermitidos)) {
            throw new \InvalidArgumentException("Rol no permitido: {$rol}");
        }

        $usuario = new Usuario(null, $nombre, $email, $rol);
        return $this->repositorio->guardar($usuario);
    }

    // Cambiar rol con regla de negocio
    public function cambiarRol(int $id, string $nuevoRol): Usuario
    {
        $usuario = $this->repositorio->buscarPorId($id);
        if ($usuario === null) {
            throw new \RuntimeException("Usuario con ID {$id} no encontrado.");
        }

        // Regla de negocio: siempre debe haber al menos un admin
        if ($usuario->obtenerRol() === 'admin' && $nuevoRol !== 'admin') {
            $admins = $this->repositorio->buscarPorRol('admin');
            if (count($admins) <= 1) {
                throw new \RuntimeException("No se puede quitar el ultimo administrador.");
            }
        }

        $usuario->establecerRol($nuevoRol);
        return $this->repositorio->guardar($usuario);
    }

    // Obtener estadisticas
    public function obtenerEstadisticas(): array
    {
        $todos = $this->repositorio->buscarTodos();
        $porRol = [];

        foreach ($todos as $usuario) {
            $rol = $usuario->obtenerRol();
            $porRol[$rol] = ($porRol[$rol] ?? 0) + 1;
        }

        return [
            'total_usuarios' => $this->repositorio->contar(),
            'por_rol'        => $porRol,
        ];
    }

    // Eliminar con regla de negocio
    public function desactivar(int $id): bool
    {
        $usuario = $this->repositorio->buscarPorId($id);
        if ($usuario === null) {
            return false;
        }

        // Regla de negocio: no se puede eliminar admins directamente
        if ($usuario->obtenerRol() === 'admin') {
            throw new \RuntimeException("Los admins no pueden ser eliminados directamente.");
        }

        return $this->repositorio->eliminar($id);
    }
}

/**
 * La clave del patron Repository es esta capacidad de intercambio.
 * Usamos el MISMO servicio con diferentes repositorios:
 */

echo "  --- Prueba 1: Servicio con Repositorio en Memoria ---\n";
$repoMemoria = new RepositorioUsuarioEnMemoria();
$servicioMem = new ServicioGestionUsuarios($repoMemoria);

$servicioMem->registrar('Patricia Reyes', 'patricia@test.com', 'admin');
$servicioMem->registrar('Oscar Mendoza', 'oscar@test.com', 'editor');
$servicioMem->registrar('Luisa Vargas', 'luisa@test.com', 'usuario');

$stats = $servicioMem->obtenerEstadisticas();
echo "  Estadisticas: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n";

// Probar regla de negocio: email duplicado
try {
    $servicioMem->registrar('Otra Patricia', 'patricia@test.com');
} catch (\RuntimeException $e) {
    echo "  Error esperado: {$e->getMessage()}\n";
}

// Probar regla de negocio: no eliminar al unico admin
try {
    $servicioMem->cambiarRol(1, 'usuario');
} catch (\RuntimeException $e) {
    echo "  Error esperado: {$e->getMessage()}\n";
}

echo "\n  --- Prueba 2: Mismo Servicio con Repositorio BD ---\n";
$repoDB = new RepositorioUsuarioBD();
$servicioBD = new ServicioGestionUsuarios($repoDB);

// Exactamente el mismo codigo de negocio, diferente almacenamiento
$servicioBD->registrar('Patricia Reyes', 'patricia@test.com', 'admin');
$servicioBD->registrar('Oscar Mendoza', 'oscar@test.com', 'editor');

$stats = $servicioBD->obtenerEstadisticas();
echo "  Estadisticas: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n\n";

echo "  CONCLUSION:\n";
echo "  El ServicioGestionUsuarios no cambio NI UNA LINEA.\n";
echo "  La unica diferencia es que repositorio se inyecta en el constructor.\n";
echo "  En pruebas: RepositorioUsuarioEnMemoria (rapido, sin BD).\n";
echo "  En produccion: RepositorioUsuarioBD (con persistencia real).\n";
echo "  Este es el poder del patron Repository + Inyeccion de Dependencias.\n";

?>
