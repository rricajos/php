# HTTP

Funciones y superglobales de PHP para manejar peticiones HTTP, cabeceras, cookies y sesiones.

## Orden de lectura

1. **`superglobals_get.php`** - Acceder a parámetros enviados por GET (`$_GET`)
2. **`superglobals_post.php`** - Acceder a parámetros enviados por POST (`$_POST`)
3. **`superglobals_server.php`** - Acceder a información del servidor y la petición (`$_SERVER`)
4. **`header.php`** - Enviar cabeceras HTTP al navegador
5. **`http_response_code.php`** - Establecer u obtener el código de respuesta HTTP
6. **`setcookie.php`** - Enviar una cookie al navegador
7. **`session_start.php`** - Iniciar o reanudar una sesión
8. **`session_destroy.php`** - Destruir todos los datos de la sesión
9. **`filter_input.php`** - Obtener y filtrar una variable de entrada externa
10. **`filter_var.php`** - Filtrar y validar una variable con un filtro específico
