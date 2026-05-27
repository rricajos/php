# Gestor de Tareas CLI + API REST

Mini-proyecto para gestionar tareas con dos interfaces: linea de comandos (CLI) y API REST con autenticacion JWT. Desarrollado en PHP 8.x con SQLite como base de datos.

Demuestra conceptos avanzados de PHP trabajando juntos en una aplicacion real:

- **OOP y Namespaces**: Clases organizadas bajo el namespace `MiniProject\`
- **PHP 8 Enums**: `Priority` (Alta, Media, Baja) y `Status` (Pendiente, Completada)
- **Readonly Properties y Constructor Promotion**: Propiedades inmutables y constructores concisos
- **Named Arguments y Match Expression**: Codigo mas legible y expresivo
- **Union Types**: Parametros que aceptan multiples tipos (`int|string`)
- **Patron Singleton**: Conexion unica a la base de datos (`Database`)
- **Patron Repository**: Acceso a datos encapsulado (`TaskRepository`)
- **Patron Strategy**: Exportacion flexible a multiples formatos (`ExporterInterface`, `JsonExporter`, `CsvExporter`)
- **Inyeccion de Dependencias**: El servicio recibe sus dependencias por constructor
- **Excepciones Personalizadas**: Jerarquia de excepciones con codigos de error (`AppException`, `ValidationException`, `NotFoundException`)
- **Prepared Statements**: Prevencion de inyeccion SQL en todas las consultas
- **API REST**: Capa HTTP con enrutador propio, controlador y respuestas JSON
- **Autenticacion JWT**: Registro, login y tokens HMAC-SHA256 sin dependencias externas
- **CORS**: Cabeceras configuradas para desarrollo frontend

## Requisitos

- PHP 8.1 o superior
- Extension `pdo_sqlite` habilitada

## Uso

### Interfaz CLI

```bash
php mini_project/app.php
```

La aplicacion presenta un menu interactivo con las siguientes opciones:

1. **Agregar tarea** - Crear una nueva tarea con titulo, descripcion y prioridad (alta/media/baja)
2. **Listar tareas** - Ver tareas filtradas por estado (pendiente/completada/todas)
3. **Completar tarea** - Marcar una tarea como completada por su ID
4. **Eliminar tarea** - Eliminar una tarea permanentemente por su ID
5. **Buscar tareas** - Buscar por palabra clave en titulo y descripcion
6. **Exportar tareas** - Exportar a JSON o CSV en el directorio `data/`
7. **Estadisticas** - Ver resumen con total, completadas, pendientes y desglose por prioridad
0. **Salir** - Cerrar la aplicacion

### API REST

Iniciar el servidor:

```bash
php -S localhost:8080 mini_project/api.php
```

#### Autenticacion

Todas las rutas de `/tasks` requieren un token JWT en el header `Authorization: Bearer <token>`. Las rutas de `/auth` son publicas.

#### Endpoints

| Metodo | Ruta | Descripcion | Auth |
|--------|------|-------------|------|
| `POST` | `/auth/register` | Registrar usuario | No |
| `POST` | `/auth/login` | Login (obtener token JWT) | No |
| `GET` | `/tasks` | Listar tareas | Si |
| `GET` | `/tasks/{id}` | Obtener una tarea | Si |
| `POST` | `/tasks` | Crear tarea | Si |
| `PATCH` | `/tasks/{id}/complete` | Completar tarea | Si |
| `DELETE` | `/tasks/{id}` | Eliminar tarea | Si |
| `GET` | `/tasks/search?q=keyword` | Buscar tareas | Si |
| `GET` | `/tasks/stats` | Estadisticas | Si |
| `GET` | `/tasks/export?format=json\|csv` | Exportar tareas | Si |

#### Ejemplos con curl

**Registrar usuario:**

```bash
curl -X POST http://localhost:8080/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "secret123"}'
```

Respuesta:

```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "id": 1,
        "username": "admin",
        "created_at": "2024-01-15 10:30:00"
    }
}
```

**Login (obtener token):**

```bash
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "secret123"}'
```

Respuesta:

```json
{
    "success": true,
    "message": "Login exitoso",
    "data": {
        "token": "eyJhbGciOi...",
        "type": "Bearer",
        "expires_in": 86400,
        "user": {
            "id": 1,
            "username": "admin"
        }
    }
}
```

**Crear tarea (con token):**

```bash
curl -X POST http://localhost:8080/tasks \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJhbGciOi..." \
  -d '{"title": "Estudiar PHP 8", "description": "Repasar enums y match", "priority": "alta"}'
```

Respuesta:

```json
{
    "success": true,
    "message": "Tarea creada exitosamente",
    "data": {
        "id": 1,
        "titulo": "Estudiar PHP 8",
        "descripcion": "Repasar enums y match",
        "prioridad": "alta",
        "estado": "pendiente",
        "fecha_creacion": "2024-01-15 10:35:00",
        "fecha_completada": null
    }
}
```

**Listar tareas (con filtro opcional):**

```bash
curl http://localhost:8080/tasks?status=pendiente \
  -H "Authorization: Bearer eyJhbGciOi..."
```

**Completar tarea:**

```bash
curl -X PATCH http://localhost:8080/tasks/1/complete \
  -H "Authorization: Bearer eyJhbGciOi..."
```

**Eliminar tarea:**

```bash
curl -X DELETE http://localhost:8080/tasks/1 \
  -H "Authorization: Bearer eyJhbGciOi..."
```

**Buscar tareas:**

```bash
curl "http://localhost:8080/tasks/search?q=PHP" \
  -H "Authorization: Bearer eyJhbGciOi..."
```

**Estadisticas:**

```bash
curl http://localhost:8080/tasks/stats \
  -H "Authorization: Bearer eyJhbGciOi..."
```

**Exportar tareas:**

```bash
curl "http://localhost:8080/tasks/export?format=csv" \
  -H "Authorization: Bearer eyJhbGciOi..."
```

#### Codigos de estado HTTP

| Codigo | Significado |
|--------|-------------|
| `200` | Operacion exitosa |
| `201` | Recurso creado (registro, nueva tarea) |
| `204` | Sin contenido (preflight OPTIONS) |
| `400` | Peticion malformada (JSON invalido) |
| `401` | No autenticado (token faltante o invalido) |
| `404` | Recurso no encontrado |
| `405` | Metodo HTTP no permitido |
| `409` | Conflicto (usuario ya existe) |
| `422` | Error de validacion (datos incorrectos) |
| `500` | Error interno del servidor |

## Estructura del Proyecto

```
mini_project/
├── app.php                  # Punto de entrada CLI con menu interactivo
├── api.php                  # Punto de entrada API REST (servidor HTTP)
├── README.md                # Este archivo
├── data/                    # Directorio de datos (BD y exportaciones)
│   ├── .gitkeep
│   └── tasks.db             # Base de datos SQLite (se crea automaticamente)
└── src/
    ├── ApiController.php    # Controlador REST: delega a TaskService
    ├── AppException.php     # Jerarquia de excepciones personalizadas
    ├── AuthService.php      # Autenticacion: registro, login, JWT
    ├── Database.php         # Conexion Singleton a SQLite via PDO
    ├── ExportService.php    # Patron Strategy: exportadores JSON y CSV
    ├── JsonResponse.php     # Helper para respuestas JSON con HTTP status
    ├── Router.php           # Enrutador HTTP con parametros dinamicos
    ├── Task.php             # Entidad Task con enums Priority y Status
    ├── TaskRepository.php   # Patron Repository: acceso a datos
    └── TaskService.php      # Capa de logica de negocio con validacion
```

## Notas

- La base de datos `tasks.db` se crea automaticamente en el directorio `data/` la primera vez que se ejecuta la aplicacion (CLI o API).
- La tabla `users` se crea automaticamente cuando se inicia la API por primera vez.
- Ambas interfaces (CLI y API) comparten la misma base de datos y las mismas clases de negocio.
- Los archivos exportados se guardan en `data/` con nombres que incluyen la fecha y hora.
- No requiere Composer; incluye un autoloader simple basado en `spl_autoload_register`.
- Los colores ANSI en la terminal funcionan en la mayoria de terminales modernas (incluyendo Windows Terminal, Git Bash, WSL).
- El JWT usa HMAC-SHA256 con una clave secreta hardcodeada (en produccion, usar variable de entorno).
- Los tokens expiran en 24 horas por defecto.
