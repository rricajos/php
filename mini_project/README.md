# Gestor de Tareas CLI

Mini-proyecto de linea de comandos para gestionar tareas, desarrollado en PHP 8.x con SQLite como base de datos.

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

## Requisitos

- PHP 8.1 o superior
- Extension `pdo_sqlite` habilitada

## Uso

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

## Estructura del Proyecto

```
mini_project/
├── app.php                  # Punto de entrada con menu interactivo
├── README.md                # Este archivo
├── data/                    # Directorio de datos (BD y exportaciones)
│   ├── .gitkeep
│   └── tasks.db             # Base de datos SQLite (se crea automaticamente)
└── src/
    ├── AppException.php     # Jerarquia de excepciones personalizadas
    ├── Database.php         # Conexion Singleton a SQLite via PDO
    ├── ExportService.php    # Patron Strategy: exportadores JSON y CSV
    ├── Task.php             # Entidad Task con enums Priority y Status
    ├── TaskRepository.php   # Patron Repository: acceso a datos
    └── TaskService.php      # Capa de logica de negocio con validacion
```

## Notas

- La base de datos `tasks.db` se crea automaticamente en el directorio `data/` la primera vez que se ejecuta la aplicacion.
- Los archivos exportados se guardan en `data/` con nombres que incluyen la fecha y hora.
- No requiere Composer; incluye un autoloader simple basado en `spl_autoload_register`.
- Los colores ANSI en la terminal funcionan en la mayoria de terminales modernas (incluyendo Windows Terminal, Git Bash, WSL).
