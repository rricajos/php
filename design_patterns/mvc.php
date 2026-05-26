<?php
/**
 * =============================================================================
 * PATRON MVC (MODEL-VIEW-CONTROLLER) EN PHP
 * =============================================================================
 *
 * MVC separa una aplicacion en tres componentes interconectados:
 *
 * - Model (Modelo): Gestiona los datos, logica de negocio y validacion.
 *   No sabe nada sobre como se muestran los datos.
 *
 * - View (Vista): Se encarga de presentar los datos al usuario.
 *   No contiene logica de negocio, solo logica de presentacion.
 *
 * - Controller (Controlador): Recibe las peticiones del usuario, interactua
 *   con el modelo y selecciona la vista apropiada para la respuesta.
 *
 * Ciclo de vida de una peticion:
 *   1. El usuario envia una peticion HTTP (GET /posts/1)
 *   2. El Router analiza la URL y la dirige al Controlador correcto
 *   3. El Controlador llama al Modelo para obtener/modificar datos
 *   4. El Modelo valida, procesa y devuelve los datos
 *   5. El Controlador pasa los datos a la Vista
 *   6. La Vista renderiza el HTML (o JSON) y se envia al usuario
 * =============================================================================
 */

// =============================================================================
// Ejemplo 1: Model (Modelo con Datos y Validacion)
// =============================================================================
// Problema: Los datos deben validarse y gestionarse de forma centralizada,
// separados de la presentacion y el control de flujo.

echo "=== Ejemplo 1: Modelo con Validacion ===\n\n";

// Clase base para modelos con funcionalidad comun
abstract class ModeloBase
{
    protected array $errores = [];
    protected array $atributos = [];
    protected array $atributosOriginales = [];

    abstract public function validar(): bool;
    abstract protected function reglasValidacion(): array;

    public function obtenerErrores(): array
    {
        return $this->errores;
    }

    public function tieneErrores(): bool
    {
        return !empty($this->errores);
    }

    // Verificar si el modelo fue modificado
    public function estaModificado(): bool
    {
        return $this->atributos !== $this->atributosOriginales;
    }
}

// Modelo concreto: Publicacion del blog
class Publicacion extends ModeloBase
{
    private ?int $id;
    private string $titulo;
    private string $contenido;
    private string $autor;
    private string $estado;     // 'borrador', 'publicado', 'archivado'
    private string $creadoEn;
    private ?string $publicadoEn;
    private array $etiquetas;

    public function __construct(
        ?int $id = null,
        string $titulo = '',
        string $contenido = '',
        string $autor = '',
        string $estado = 'borrador',
        array $etiquetas = []
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->contenido = $contenido;
        $this->autor = $autor;
        $this->estado = $estado;
        $this->etiquetas = $etiquetas;
        $this->creadoEn = date('Y-m-d H:i:s');
        $this->publicadoEn = null;

        $this->atributos = $this->aArray();
        $this->atributosOriginales = $this->atributos;
    }

    // --- Reglas de validacion declarativas ---
    protected function reglasValidacion(): array
    {
        return [
            'titulo'    => ['requerido' => true, 'min' => 5, 'max' => 200],
            'contenido' => ['requerido' => true, 'min' => 20],
            'autor'     => ['requerido' => true, 'min' => 2],
            'estado'    => ['en' => ['borrador', 'publicado', 'archivado']],
        ];
    }

    public function validar(): bool
    {
        $this->errores = [];
        $reglas = $this->reglasValidacion();

        // Validar titulo
        if (empty($this->titulo)) {
            $this->errores['titulo'][] = 'El titulo es obligatorio.';
        } elseif (mb_strlen($this->titulo) < $reglas['titulo']['min']) {
            $this->errores['titulo'][] = "El titulo debe tener al menos {$reglas['titulo']['min']} caracteres.";
        } elseif (mb_strlen($this->titulo) > $reglas['titulo']['max']) {
            $this->errores['titulo'][] = "El titulo no puede exceder {$reglas['titulo']['max']} caracteres.";
        }

        // Validar contenido
        if (empty($this->contenido)) {
            $this->errores['contenido'][] = 'El contenido es obligatorio.';
        } elseif (mb_strlen($this->contenido) < $reglas['contenido']['min']) {
            $this->errores['contenido'][] = "El contenido debe tener al menos {$reglas['contenido']['min']} caracteres.";
        }

        // Validar autor
        if (empty($this->autor)) {
            $this->errores['autor'][] = 'El autor es obligatorio.';
        }

        // Validar estado
        if (!in_array($this->estado, $reglas['estado']['en'])) {
            $this->errores['estado'][] = 'Estado no valido.';
        }

        return empty($this->errores);
    }

    // Logica de negocio: publicar una entrada
    public function publicar(): bool
    {
        if (!$this->validar()) {
            return false;
        }
        $this->estado = 'publicado';
        $this->publicadoEn = date('Y-m-d H:i:s');
        return true;
    }

    // Logica de negocio: archivar
    public function archivar(): void
    {
        $this->estado = 'archivado';
    }

    // Generar slug a partir del titulo
    public function obtenerSlug(): string
    {
        $slug = mb_strtolower($this->titulo);
        $slug = preg_replace('/[^a-z0-9\s]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', trim($slug));
        return $slug;
    }

    // Obtener resumen del contenido
    public function obtenerResumen(int $longitud = 150): string
    {
        if (mb_strlen($this->contenido) <= $longitud) {
            return $this->contenido;
        }
        return mb_substr($this->contenido, 0, $longitud) . '...';
    }

    // Getters
    public function obtenerId(): ?int { return $this->id; }
    public function obtenerTitulo(): string { return $this->titulo; }
    public function obtenerContenido(): string { return $this->contenido; }
    public function obtenerAutor(): string { return $this->autor; }
    public function obtenerEstado(): string { return $this->estado; }
    public function obtenerCreadoEn(): string { return $this->creadoEn; }
    public function obtenerPublicadoEn(): ?string { return $this->publicadoEn; }
    public function obtenerEtiquetas(): array { return $this->etiquetas; }

    // Setters
    public function establecerId(int $id): void { $this->id = $id; }
    public function establecerTitulo(string $titulo): void { $this->titulo = $titulo; }
    public function establecerContenido(string $contenido): void { $this->contenido = $contenido; }

    public function aArray(): array
    {
        return [
            'id'           => $this->id,
            'titulo'       => $this->titulo,
            'contenido'    => $this->contenido,
            'autor'        => $this->autor,
            'estado'       => $this->estado,
            'etiquetas'    => $this->etiquetas,
            'creado_en'    => $this->creadoEn,
            'publicado_en' => $this->publicadoEn,
        ];
    }
}

// Demostrar validacion del modelo
$postValido = new Publicacion(
    titulo: 'Introduccion al Patron MVC en PHP',
    contenido: 'El patron MVC es uno de los patrones arquitectonicos mas utilizados en el desarrollo web moderno.',
    autor: 'Sandra',
    etiquetas: ['php', 'mvc', 'patrones']
);

echo "  Post valido: " . ($postValido->validar() ? 'SI' : 'NO') . "\n";
echo "  Slug: " . $postValido->obtenerSlug() . "\n";
echo "  Resumen: " . $postValido->obtenerResumen(60) . "\n\n";

// Post con errores de validacion
$postInvalido = new Publicacion(titulo: 'Hola', contenido: 'Muy corto', autor: '');
echo "  Post invalido: " . ($postInvalido->validar() ? 'SI' : 'NO') . "\n";
echo "  Errores:\n";
foreach ($postInvalido->obtenerErrores() as $campo => $mensajes) {
    foreach ($mensajes as $msg) {
        echo "    - {$campo}: {$msg}\n";
    }
}
echo "\n";


// =============================================================================
// Ejemplo 2: View (Vistas / Templates)
// =============================================================================
// Problema: La presentacion de datos no debe estar mezclada con la logica
// de negocio. Las vistas deben ser simples y reutilizables.

echo "=== Ejemplo 2: Sistema de Vistas (Templates) ===\n\n";

class MotorVistas
{
    private string $directorioVistas;
    private array $datosGlobales = [];  // Variables disponibles en todas las vistas

    public function __construct(string $directorioVistas = '')
    {
        $this->directorioVistas = $directorioVistas;
    }

    // Establecer datos globales (nombre del sitio, usuario actual, etc.)
    public function compartir(string $clave, mixed $valor): void
    {
        $this->datosGlobales[$clave] = $valor;
    }

    /**
     * Renderizar una vista con datos.
     * En una aplicacion real, esto cargaria un archivo PHP/Twig/Blade.
     * Aqui simulamos las plantillas con strings para que el ejemplo funcione.
     */
    public function renderizar(string $vista, array $datos = []): string
    {
        $datos = array_merge($this->datosGlobales, $datos);

        // Simulamos diferentes plantillas
        return match ($vista) {
            'publicaciones.listado' => $this->vistaListado($datos),
            'publicaciones.detalle' => $this->vistaDetalle($datos),
            'publicaciones.formulario' => $this->vistaFormulario($datos),
            'errores.404' => $this->vistaError404($datos),
            'errores.validacion' => $this->vistaErroresValidacion($datos),
            default => "Vista no encontrada: {$vista}",
        };
    }

    // --- Plantillas simuladas (en produccion serian archivos separados) ---

    private function vistaListado(array $datos): string
    {
        $html = "<h1>{$datos['titulo_pagina']}</h1>\n";
        $html .= "<p>Total: {$datos['total']} publicaciones</p>\n";

        foreach ($datos['publicaciones'] as $post) {
            $html .= "<article>\n";
            $html .= "  <h2>{$post['titulo']}</h2>\n";
            $html .= "  <span class='meta'>Por {$post['autor']} | {$post['estado']}</span>\n";
            $html .= "  <p>{$post['resumen']}</p>\n";
            $html .= "  <a href='/posts/{$post['id']}'>Leer mas</a>\n";
            $html .= "</article>\n";
        }

        return $html;
    }

    private function vistaDetalle(array $datos): string
    {
        $post = $datos['publicacion'];
        $html = "<article class='post-detalle'>\n";
        $html .= "  <h1>{$post['titulo']}</h1>\n";
        $html .= "  <div class='meta'>Por {$post['autor']} | {$post['creado_en']}</div>\n";

        if (!empty($post['etiquetas'])) {
            $tags = implode(', ', $post['etiquetas']);
            $html .= "  <div class='tags'>Etiquetas: {$tags}</div>\n";
        }

        $html .= "  <div class='contenido'>{$post['contenido']}</div>\n";
        $html .= "</article>\n";

        return $html;
    }

    private function vistaFormulario(array $datos): string
    {
        $post = $datos['publicacion'] ?? [];
        $titulo = $post['titulo'] ?? '';
        $contenido = $post['contenido'] ?? '';
        $accion = isset($post['id']) ? 'Actualizar' : 'Crear';

        $html = "<form method='POST' action='{$datos['accion_url']}'>\n";
        $html .= "  <label>Titulo:</label>\n";
        $html .= "  <input name='titulo' value='{$titulo}' />\n";
        $html .= "  <label>Contenido:</label>\n";
        $html .= "  <textarea name='contenido'>{$contenido}</textarea>\n";
        $html .= "  <button type='submit'>{$accion}</button>\n";
        $html .= "</form>\n";

        return $html;
    }

    private function vistaError404(array $datos): string
    {
        $mensaje = $datos['mensaje'] ?? 'Pagina no encontrada';
        return "<div class='error-404'>\n  <h1>404</h1>\n  <p>{$mensaje}</p>\n</div>\n";
    }

    private function vistaErroresValidacion(array $datos): string
    {
        $html = "<div class='errores'>\n";
        $html .= "  <h3>Errores de validacion:</h3>\n  <ul>\n";
        foreach ($datos['errores'] as $campo => $mensajes) {
            foreach ($mensajes as $msg) {
                $html .= "    <li><strong>{$campo}:</strong> {$msg}</li>\n";
            }
        }
        $html .= "  </ul>\n</div>\n";

        return $html;
    }
}

$vistas = new MotorVistas();
$vistas->compartir('sitio_nombre', 'Mi Blog PHP');
$vistas->compartir('anio', date('Y'));

// Renderizar listado
$htmlListado = $vistas->renderizar('publicaciones.listado', [
    'titulo_pagina' => 'Ultimas Publicaciones',
    'total' => 2,
    'publicaciones' => [
        ['id' => 1, 'titulo' => 'Patron MVC', 'autor' => 'Sandra', 'estado' => 'publicado', 'resumen' => 'Aprende MVC...'],
        ['id' => 2, 'titulo' => 'Patron Observer', 'autor' => 'Carlos', 'estado' => 'borrador', 'resumen' => 'Eventos en PHP...'],
    ],
]);

echo "  Vista de Listado generada:\n";
echo "  " . str_replace("\n", "\n  ", trim($htmlListado)) . "\n\n";


// =============================================================================
// Ejemplo 3: Controller (Controlador con Enrutamiento y Logica)
// =============================================================================
// Problema: Necesitamos un componente que reciba las peticiones, coordine
// entre el modelo y la vista, y devuelva la respuesta apropiada.

echo "=== Ejemplo 3: Controlador con Enrutamiento ===\n\n";

// Objetos Request y Response para encapsular la comunicacion HTTP
class Peticion
{
    public function __construct(
        public readonly string $metodo,
        public readonly string $ruta,
        public readonly array $parametros = [],     // Parametros de la URL (/posts/{id})
        public readonly array $cuerpo = [],         // Datos del formulario (POST)
        public readonly array $consulta = []        // Query string (?page=2)
    ) {}
}

class Respuesta
{
    public function __construct(
        public string $cuerpo = '',
        public int $codigoEstado = 200,
        public array $cabeceras = ['Content-Type' => 'text/html'],
        public ?string $redireccion = null
    ) {}

    public static function html(string $cuerpo, int $codigo = 200): self
    {
        return new self($cuerpo, $codigo);
    }

    public static function json(array $datos, int $codigo = 200): self
    {
        return new self(
            json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $codigo,
            ['Content-Type' => 'application/json']
        );
    }

    public static function redirigir(string $url): self
    {
        return new self('', 302, [], $url);
    }

    public static function noEncontrado(string $mensaje = 'No encontrado'): self
    {
        return new self($mensaje, 404);
    }
}

// Repositorio simple para las publicaciones del blog
class RepositorioPublicaciones
{
    private array $publicaciones = [];
    private int $siguienteId = 1;

    public function buscarTodas(): array { return array_values($this->publicaciones); }

    public function buscarPorId(int $id): ?Publicacion
    {
        return $this->publicaciones[$id] ?? null;
    }

    public function guardar(Publicacion $post): Publicacion
    {
        if ($post->obtenerId() === null) {
            $post->establecerId($this->siguienteId++);
        }
        $this->publicaciones[$post->obtenerId()] = $post;
        return $post;
    }

    public function eliminar(int $id): bool
    {
        if (isset($this->publicaciones[$id])) {
            unset($this->publicaciones[$id]);
            return true;
        }
        return false;
    }

    public function contar(): int { return count($this->publicaciones); }
}

// Controlador del Blog: coordina modelo y vista
class ControladorPublicaciones
{
    public function __construct(
        private RepositorioPublicaciones $repositorio,
        private MotorVistas $vistas
    ) {}

    // GET /posts - Listar todas las publicaciones
    public function indice(Peticion $peticion): Respuesta
    {
        $publicaciones = $this->repositorio->buscarTodas();

        $datosVista = array_map(function (Publicacion $post) {
            return [
                'id'      => $post->obtenerId(),
                'titulo'  => $post->obtenerTitulo(),
                'autor'   => $post->obtenerAutor(),
                'estado'  => $post->obtenerEstado(),
                'resumen' => $post->obtenerResumen(100),
            ];
        }, $publicaciones);

        $html = $this->vistas->renderizar('publicaciones.listado', [
            'titulo_pagina'  => 'Todas las Publicaciones',
            'total'          => count($publicaciones),
            'publicaciones'  => $datosVista,
        ]);

        return Respuesta::html($html);
    }

    // GET /posts/{id} - Ver una publicacion
    public function mostrar(Peticion $peticion): Respuesta
    {
        $id = (int) ($peticion->parametros['id'] ?? 0);
        $post = $this->repositorio->buscarPorId($id);

        if ($post === null) {
            $html = $this->vistas->renderizar('errores.404', [
                'mensaje' => "La publicacion con ID {$id} no existe.",
            ]);
            return Respuesta::html($html, 404);
        }

        $html = $this->vistas->renderizar('publicaciones.detalle', [
            'publicacion' => $post->aArray(),
        ]);

        return Respuesta::html($html);
    }

    // GET /posts/crear - Formulario de creacion
    public function crear(Peticion $peticion): Respuesta
    {
        $html = $this->vistas->renderizar('publicaciones.formulario', [
            'accion_url' => '/posts',
            'publicacion' => [],
        ]);

        return Respuesta::html($html);
    }

    // POST /posts - Almacenar nueva publicacion
    public function almacenar(Peticion $peticion): Respuesta
    {
        $post = new Publicacion(
            titulo: $peticion->cuerpo['titulo'] ?? '',
            contenido: $peticion->cuerpo['contenido'] ?? '',
            autor: $peticion->cuerpo['autor'] ?? 'Anonimo',
            etiquetas: $peticion->cuerpo['etiquetas'] ?? []
        );

        if (!$post->validar()) {
            $html = $this->vistas->renderizar('errores.validacion', [
                'errores' => $post->obtenerErrores(),
            ]);
            return Respuesta::html($html, 422);
        }

        $post = $this->repositorio->guardar($post);
        return Respuesta::redirigir("/posts/{$post->obtenerId()}");
    }

    // DELETE /posts/{id} - Eliminar publicacion
    public function destruir(Peticion $peticion): Respuesta
    {
        $id = (int) ($peticion->parametros['id'] ?? 0);

        if ($this->repositorio->eliminar($id)) {
            return Respuesta::json(['mensaje' => 'Publicacion eliminada', 'id' => $id]);
        }

        return Respuesta::json(['error' => 'Publicacion no encontrada'], 404);
    }

    // GET /api/posts - Respuesta JSON para APIs
    public function apiListar(Peticion $peticion): Respuesta
    {
        $publicaciones = $this->repositorio->buscarTodas();
        $datos = array_map(fn(Publicacion $p) => $p->aArray(), $publicaciones);

        return Respuesta::json([
            'total' => count($datos),
            'publicaciones' => $datos,
        ]);
    }
}

// Simular operaciones del controlador
$repo = new RepositorioPublicaciones();
$vistas = new MotorVistas();
$controlador = new ControladorPublicaciones($repo, $vistas);

// Crear algunas publicaciones directamente en el repositorio
$repo->guardar(new Publicacion(
    titulo: 'Primer Post del Blog',
    contenido: 'Este es el contenido del primer post. Bienvenidos al blog sobre patrones de diseno en PHP.',
    autor: 'Sandra',
    estado: 'publicado',
    etiquetas: ['bienvenida', 'php']
));
$repo->guardar(new Publicacion(
    titulo: 'Patrones de Diseno Esenciales',
    contenido: 'En este articulo exploraremos los patrones de diseno mas importantes para todo desarrollador PHP.',
    autor: 'Carlos',
    etiquetas: ['patrones', 'php', 'avanzado']
));

// Simular peticion GET /posts
echo "  --- GET /posts (Listado) ---\n";
$respuesta = $controlador->indice(new Peticion('GET', '/posts'));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  " . str_replace("\n", "\n  ", trim($respuesta->cuerpo)) . "\n\n";

// Simular peticion GET /posts/1
echo "  --- GET /posts/1 (Detalle) ---\n";
$respuesta = $controlador->mostrar(new Peticion('GET', '/posts/1', ['id' => 1]));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  " . str_replace("\n", "\n  ", trim($respuesta->cuerpo)) . "\n\n";

// Simular POST /posts con datos invalidos
echo "  --- POST /posts (Datos invalidos) ---\n";
$respuesta = $controlador->almacenar(new Peticion('POST', '/posts', [], [
    'titulo' => 'Hey', 'contenido' => 'Corto', 'autor' => '',
]));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  " . str_replace("\n", "\n  ", trim($respuesta->cuerpo)) . "\n\n";


// =============================================================================
// Ejemplo 4: Mini Blog Completo con CRUD
// =============================================================================
// Problema: Integrar Model, View y Controller en un flujo completo con
// enrutamiento, peticiones y respuestas para un mini blog funcional.

echo "=== Ejemplo 4: Mini Blog Completo con CRUD ===\n\n";

// Router: mapea URLs a acciones del controlador
class EnrutadorSimple
{
    private array $rutas = [];

    public function registrar(string $metodo, string $patron, callable $manejador): void
    {
        $this->rutas[] = [
            'metodo'    => strtoupper($metodo),
            'patron'    => $patron,
            'manejador' => $manejador,
        ];
    }

    // Atajos para metodos HTTP comunes
    public function get(string $patron, callable $manejador): void
    {
        $this->registrar('GET', $patron, $manejador);
    }

    public function post(string $patron, callable $manejador): void
    {
        $this->registrar('POST', $patron, $manejador);
    }

    public function delete(string $patron, callable $manejador): void
    {
        $this->registrar('DELETE', $patron, $manejador);
    }

    // Resolver la ruta y ejecutar el manejador
    public function despachar(Peticion $peticion): Respuesta
    {
        foreach ($this->rutas as $ruta) {
            if ($ruta['metodo'] !== $peticion->metodo) {
                continue;
            }

            $parametros = $this->coincide($ruta['patron'], $peticion->ruta);
            if ($parametros !== null) {
                // Crear nueva peticion con los parametros extraidos
                $peticionConParams = new Peticion(
                    $peticion->metodo,
                    $peticion->ruta,
                    $parametros,
                    $peticion->cuerpo,
                    $peticion->consulta
                );
                return call_user_func($ruta['manejador'], $peticionConParams);
            }
        }

        return Respuesta::noEncontrado("Ruta no encontrada: {$peticion->metodo} {$peticion->ruta}");
    }

    // Verificar si la ruta coincide con el patron y extraer parametros
    private function coincide(string $patron, string $ruta): ?array
    {
        // Convertir /posts/{id} a regex: /posts/(?P<id>[^/]+)
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $patron);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $ruta, $coincidencias)) {
            // Extraer solo los grupos nombrados
            return array_filter($coincidencias, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return null;
    }
}

// Configurar la aplicacion completa
$repositorioBlog = new RepositorioPublicaciones();
$motorVistas = new MotorVistas();
$controladorBlog = new ControladorPublicaciones($repositorioBlog, $motorVistas);
$router = new EnrutadorSimple();

// Definir rutas
$router->get('/posts', fn(Peticion $p) => $controladorBlog->indice($p));
$router->get('/posts/crear', fn(Peticion $p) => $controladorBlog->crear($p));
$router->get('/posts/{id}', fn(Peticion $p) => $controladorBlog->mostrar($p));
$router->post('/posts', fn(Peticion $p) => $controladorBlog->almacenar($p));
$router->delete('/posts/{id}', fn(Peticion $p) => $controladorBlog->destruir($p));
$router->get('/api/posts', fn(Peticion $p) => $controladorBlog->apiListar($p));

// Simular ciclo de vida completo de peticiones

// 1. Crear un post via formulario
echo "  --- 1. POST /posts (Crear publicacion) ---\n";
$respuesta = $router->despachar(new Peticion('POST', '/posts', [], [
    'titulo'    => 'Guia Completa de PHP Moderno',
    'contenido' => 'PHP ha evolucionado enormemente en los ultimos anos. En esta guia cubrimos todas las novedades.',
    'autor'     => 'Sandra',
    'etiquetas' => ['php', 'guia', 'moderno'],
]));
echo "  Codigo: {$respuesta->codigoEstado}";
if ($respuesta->redireccion) {
    echo " -> Redirigiendo a: {$respuesta->redireccion}";
}
echo "\n\n";

// 2. Crear otro post
$router->despachar(new Peticion('POST', '/posts', [], [
    'titulo'    => 'Patrones de Diseno que Todo Developer Debe Conocer',
    'contenido' => 'Los patrones de diseno son soluciones probadas a problemas recurrentes en el desarrollo de software.',
    'autor'     => 'Carlos',
    'etiquetas' => ['patrones', 'arquitectura'],
]));

// 3. Ver listado via API
echo "  --- 2. GET /api/posts (API JSON) ---\n";
$respuesta = $router->despachar(new Peticion('GET', '/api/posts'));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  " . str_replace("\n", "\n  ", $respuesta->cuerpo) . "\n\n";

// 4. Ver detalle de un post
echo "  --- 3. GET /posts/1 (Detalle) ---\n";
$respuesta = $router->despachar(new Peticion('GET', '/posts/1'));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  " . str_replace("\n", "\n  ", trim($respuesta->cuerpo)) . "\n\n";

// 5. Intentar ver un post que no existe
echo "  --- 4. GET /posts/999 (No encontrado) ---\n";
$respuesta = $router->despachar(new Peticion('GET', '/posts/999'));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  " . str_replace("\n", "\n  ", trim($respuesta->cuerpo)) . "\n\n";

// 6. Eliminar un post
echo "  --- 5. DELETE /posts/2 ---\n";
$respuesta = $router->despachar(new Peticion('DELETE', '/posts/2'));
echo "  Codigo: {$respuesta->codigoEstado}\n";
echo "  {$respuesta->cuerpo}\n\n";


// =============================================================================
// Ejemplo 5: Ciclo de Vida de una Peticion (Explicacion)
// =============================================================================
// Resumen del flujo completo desde que el usuario hace clic hasta que recibe
// la respuesta.

echo "=== Ejemplo 5: Ciclo de Vida de una Peticion HTTP en MVC ===\n\n";

echo "  FLUJO DE UNA PETICION EN MVC:\n";
echo "  " . str_repeat('=', 58) . "\n\n";

echo "  1. PETICION DEL NAVEGADOR\n";
echo "     Usuario hace clic -> GET /posts/1\n";
echo "     El servidor web (Apache/Nginx) recibe la peticion\n\n";

echo "  2. PUNTO DE ENTRADA (index.php / Front Controller)\n";
echo "     Se carga la configuracion, autoload y el contenedor DI\n";
echo "     Se crea el objeto Peticion a partir de las superglobales\n\n";

echo "  3. ENRUTAMIENTO (Router)\n";
echo "     El Router analiza la URL: /posts/1\n";
echo "     Coincide con la ruta: GET /posts/{id}\n";
echo "     Extrae parametros: ['id' => 1]\n";
echo "     Determina: ControladorPublicaciones::mostrar()\n\n";

echo "  4. MIDDLEWARE (opcional)\n";
echo "     Autenticacion: verificar sesion/token\n";
echo "     Autorizacion: verificar permisos\n";
echo "     CORS, Rate Limiting, Logging, etc.\n\n";

echo "  5. CONTROLADOR\n";
echo "     ControladorPublicaciones::mostrar() se ejecuta\n";
echo "     Extrae el ID de los parametros de la peticion\n";
echo "     Llama al repositorio: \$repo->buscarPorId(1)\n\n";

echo "  6. MODELO / REPOSITORIO\n";
echo "     El repositorio ejecuta: SELECT * FROM posts WHERE id = 1\n";
echo "     Crea un objeto Publicacion con los datos\n";
echo "     Lo devuelve al controlador\n\n";

echo "  7. VISTA\n";
echo "     El controlador pasa los datos a la vista\n";
echo "     La vista renderiza el HTML con los datos del post\n";
echo "     Devuelve el HTML renderizado al controlador\n\n";

echo "  8. RESPUESTA\n";
echo "     El controlador crea un objeto Respuesta con:\n";
echo "       - Codigo de estado: 200 OK\n";
echo "       - Cabeceras: Content-Type: text/html\n";
echo "       - Cuerpo: HTML renderizado\n";
echo "     La respuesta se envia al navegador del usuario\n\n";

echo "  " . str_repeat('=', 58) . "\n";
echo "  Navegador -> Router -> Middleware -> Controller -> Model\n";
echo "     ^                                    |          |\n";
echo "     |                                    v          v\n";
echo "  Respuesta <------- Controller <------- View <--- Datos\n";

?>
