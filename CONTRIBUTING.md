# Contribuir al proyecto

Gracias por tu interes en contribuir a este repositorio de ejemplos PHP.

## Configurar el entorno de desarrollo

1. Clona el repositorio:

   ```bash
   git clone https://github.com/rricajos/php.git
   cd php
   ```

2. Instala las dependencias con Composer:

   ```bash
   composer install
   ```

Se requiere PHP 8.1 o superior.

## Ejecutar los tests

```bash
composer test
```

Esto ejecuta PHPUnit con la configuracion definida en el proyecto.

## Ejecutar el linter

Con Composer:

```bash
composer lint
```

O directamente con PHP:

```bash
php lint.php
```

El linter recorre recursivamente todos los archivos `.php` y verifica que no haya errores de sintaxis.

## Agregar nuevos ejemplos

1. Identifica la categoria adecuada (por ejemplo, `array`, `json`, `datetime`). Si no existe, crea una carpeta nueva.
2. Sigue el estilo de los archivos existentes.
3. Agrega tu archivo al `INDEX.md` correspondiente para que quede documentado.

## Guia de estilo

- Los comentarios deben estar en **espanol**.
- Cada archivo debe contener entre **4 y 6 ejemplos** practicos sobre el tema.
- Todos los archivos deben usar las etiquetas de apertura y cierre: `<?php ?>`.
- Usa nombres de variables y funciones descriptivos.
- Incluye salida esperada en comentarios cuando sea util.
