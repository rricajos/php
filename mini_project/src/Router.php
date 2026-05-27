<?php

declare(strict_types=1);

namespace MiniProject;

/**
 * Enrutador simple para la API REST.
 *
 * Registra rutas con metodo HTTP y patron URI, resuelve la ruta
 * correspondiente a una peticion entrante, y extrae parametros
 * dinamicos de la URI (ej: /tasks/{id}).
 *
 * Caracteristicas PHP 8: constructor promotion, readonly, named arguments,
 * match, enums
 */

/**
 * Representa una ruta registrada en el enrutador.
 *
 * Almacena el metodo HTTP, patron de la URI, el handler (callback)
 * y la expresion regular compilada para el matching.
 *
 * Caracteristicas PHP 8: readonly, constructor promotion
 */
readonly class Route
{
    /** @var string Expresion regular compilada a partir del patron */
    public string $regex;

    /** @var string[] Nombres de los parametros dinamicos extraidos del patron */
    public array $paramNames;

    /**
     * @param string $method Metodo HTTP (GET, POST, PATCH, DELETE)
     * @param string $pattern Patron de URI con placeholders (ej: /tasks/{id})
     * @param \Closure $handler Funcion que maneja la peticion
     */
    public function __construct(
        public string $method,
        public string $pattern,
        public \Closure $handler,
    ) {
        // Extraer nombres de parametros dinamicos del patron
        preg_match_all('/{(\w+)}/', $this->pattern, $matches);
        $this->paramNames = $matches[1];

        // Compilar el patron a una expresion regular
        // Reemplazar {param} por grupos de captura con nombre
        $regex = preg_replace('/{(\w+)}/', '(?P<$1>[^/]+)', $this->pattern);
        $this->regex = '#^' . $regex . '$#';
    }
}

/**
 * Resultado de la resolucion de una ruta.
 *
 * Contiene el handler a ejecutar y los parametros extraidos de la URI.
 *
 * Caracteristicas PHP 8: readonly, constructor promotion
 */
readonly class RouteMatch
{
    /**
     * @param \Closure $handler Funcion que maneja la peticion
     * @param array<string, string> $params Parametros extraidos de la URI
     */
    public function __construct(
        public \Closure $handler,
        public array $params = [],
    ) {}
}

/**
 * Enrutador principal de la API REST.
 *
 * Gestiona el registro y resolucion de rutas HTTP. Soporta
 * parametros dinamicos en la URI y devuelve respuestas JSON
 * apropiadas para rutas no encontradas o metodos no permitidos.
 */
class Router
{
    /** @var Route[] Rutas registradas */
    private array $routes = [];

    /**
     * Registra una ruta GET.
     *
     * @param string $pattern Patron de URI
     * @param \Closure $handler Funcion manejadora
     * @return self Para encadenamiento fluido
     */
    public function get(string $pattern, \Closure $handler): self
    {
        return $this->addRoute(method: 'GET', pattern: $pattern, handler: $handler);
    }

    /**
     * Registra una ruta POST.
     *
     * @param string $pattern Patron de URI
     * @param \Closure $handler Funcion manejadora
     * @return self Para encadenamiento fluido
     */
    public function post(string $pattern, \Closure $handler): self
    {
        return $this->addRoute(method: 'POST', pattern: $pattern, handler: $handler);
    }

    /**
     * Registra una ruta PATCH.
     *
     * @param string $pattern Patron de URI
     * @param \Closure $handler Funcion manejadora
     * @return self Para encadenamiento fluido
     */
    public function patch(string $pattern, \Closure $handler): self
    {
        return $this->addRoute(method: 'PATCH', pattern: $pattern, handler: $handler);
    }

    /**
     * Registra una ruta DELETE.
     *
     * @param string $pattern Patron de URI
     * @param \Closure $handler Funcion manejadora
     * @return self Para encadenamiento fluido
     */
    public function delete(string $pattern, \Closure $handler): self
    {
        return $this->addRoute(method: 'DELETE', pattern: $pattern, handler: $handler);
    }

    /**
     * Registra una ruta con metodo y patron especificos.
     *
     * @param string $method Metodo HTTP
     * @param string $pattern Patron de URI
     * @param \Closure $handler Funcion manejadora
     * @return self Para encadenamiento fluido
     */
    private function addRoute(string $method, string $pattern, \Closure $handler): self
    {
        $this->routes[] = new Route(
            method: strtoupper($method),
            pattern: $pattern,
            handler: $handler,
        );

        return $this;
    }

    /**
     * Resuelve la ruta que coincide con el metodo y URI proporcionados.
     *
     * Busca entre las rutas registradas una que coincida con el metodo HTTP
     * y el patron de URI. Si encuentra una coincidencia de patron pero con
     * metodo incorrecto, devuelve error 405. Si no hay coincidencia,
     * devuelve error 404.
     *
     * @param string $method Metodo HTTP de la peticion
     * @param string $uri URI de la peticion (sin query string)
     * @return RouteMatch Resultado con handler y parametros extraidos
     */
    public function resolve(string $method, string $uri): RouteMatch
    {
        $method = strtoupper($method);

        // Normalizar la URI: eliminar barra final (excepto la raiz)
        $uri = rtrim($uri, '/') ?: '/';

        // Rastrear si alguna ruta coincide con el patron pero no con el metodo
        $patronCoincide = false;
        $metodosPermitidos = [];

        foreach ($this->routes as $route) {
            if (preg_match($route->regex, $uri, $matches)) {
                // El patron coincide, verificar el metodo
                if ($route->method === $method) {
                    // Extraer solo los parametros con nombre (no los numericos)
                    $params = array_filter(
                        $matches,
                        fn(string $key): bool => !is_numeric($key),
                        ARRAY_FILTER_USE_KEY,
                    );

                    return new RouteMatch(
                        handler: $route->handler,
                        params: $params,
                    );
                }

                // Patron coincide pero metodo incorrecto
                $patronCoincide = true;
                $metodosPermitidos[] = $route->method;
            }
        }

        // Si algun patron coincidio pero el metodo no era correcto: 405
        if ($patronCoincide) {
            $permitidos = implode(', ', array_unique($metodosPermitidos));
            header("Allow: {$permitidos}");
            JsonResponse::error(
                message: "Metodo {$method} no permitido. Metodos aceptados: {$permitidos}",
                code: 405,
            );
        }

        // Ninguna ruta coincidio: 404
        JsonResponse::error(
            message: "Ruta no encontrada: {$method} {$uri}",
            code: 404,
        );
    }
}
