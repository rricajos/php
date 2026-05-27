# PHP 8.3 In-depth study

This is an in-depth study of the PHP language repository, testing examples of each function of this object-oriented programming language from version 5 (currently 8.3).

PHP is interpreted; in other words, it is not compiled, except when using tools or extensions like Zend Opcache or HHVM (HipHop Virtual Machine) run engines.

In fact, my goal is to know various ways to achieve the same result, questioning which one is the most optimal. Example - ¿Should I use native php array like [1,2,3] or the ArrayObject to get data from an external API?

> **For English readers:** Excuse me if some examples are in Spanish and for my grammar errors in both languages! 😄

## Repository Structure

| Directory | Files | Description |
|-----------|-------|-------------|
| `array/` | 78 | Native PHP array functions (complete coverage) |
| `ArrayObject/` | 24 | ArrayObject class: 2 properties + 22 methods |
| `string/` | 49 | String manipulation, search, replace, format, encoding |
| `math/` | 16 | Math operations, rounding, random, base conversion |
| `variable/` | 19 | Type checking, casting, inspection and debug |
| `json/` | 3 | JSON encode, decode, error handling |
| `datetime/` | 10 | Date/time creation, formatting, diff, intervals |
| `regex/` | 6 | PCRE functions: match, replace, split |
| `filesystem/` | 16 | File I/O, directories, paths, glob |
| `http/` | 10 | Headers, cookies, sessions, superglobals, filters |
| `curl/` | 6 | cURL: init, options, POST, info, errors, multi |
| `mbstring/` | 8 | Multibyte string functions (UTF-8) |
| `oop/` | 10 | OOP: classes, inheritance, traits, interfaces, magic methods |
| `php8/` | 11 | PHP 8.x features: match, enums, fibers, readonly, attributes |
| `spl/` | 8 | SPL data structures and iterators |
| `error_handling/` | 6 | Exceptions, error handlers, finally |
| `output_buffering/` | 2 | Output buffering: ob_start, ob_get_clean, nested buffers |
| `encryption/` | 6 | Password hashing, OpenSSL, Sodium, hash functions, random |
| `database/` | 10 | PDO, prepared statements, transactions, SQLite, MySQLi, query builder |
| `xml/` | 4 | SimpleXML, DOMDocument, XMLReader/Writer, XPath |
| `generators/` | 4 | yield, yield from, send, pipelines, practical patterns |
| `design_patterns/` | 8 | Singleton, Factory, Observer, Strategy, Decorator, Repository, MVC, DI |
| `testing/` | 6 | PHPUnit basics, assertions, mocks/stubs, data providers, TDD |
| `closures/` | 2 | Anonymous functions, use keyword, arrow functions, middleware |
| `namespaces/` | 2 | Namespaces, PSR-4 autoloading, Composer config |
| `type_casting/` | 2 | Explicit casting, type juggling, strict_types, type declarations |
| `mini_project/` | 11 | CLI + REST API task manager (SQLite, JWT auth, patterns, PHP 8.x) |
| `tests/` | 4 | 93 PHPUnit tests (unit + integration) |
| **Total** | **349** | **Each directory includes an INDEX.md with recommended reading order** |

> Run `php run.php` from the project root to explore all examples interactively via CLI.
> Run `php lint.php` to validate syntax of all files.
> Run `php mini_project/app.php` to try the CLI task manager.
> Run `php -S localhost:8080 mini_project/api.php` to start the REST API.
> Run `composer test` to execute the 93 automated tests.

## Native PHP vs PHP Extensions/Modules

My current PHP version is **8.3.11**. To know yours, run:

```bash
php -v
```

> **Note:** If it throws an error, it is because you haven't set the environment variable for PHP (of course, install it before setting the environment variable).

To know which modules/extensions are in your PHP installation, run:

```bash
php -m
```

## My Preinstalled PHP Modules by default on windows 11

- **bcmath**: Funciones para cálculos matemáticos de precisión arbitraria.
- **calendar**: Manipulación de calendarios y cálculo de fechas.
- **Core**: Núcleo de PHP, contiene funciones básicas y motor del lenguaje.
- **ctype**: Verificación de tipos de caracteres (alfabéticos, numéricos, etc.).
- **date**: Manipulación y formateo de fechas y horas.
- **dom**: Interfaz para trabajar con documentos XML (DOM).
- **filter**: Validación y filtrado de datos de entrada.
- **hash**: Cálculo y verificación de resúmenes de mensajes.
- **iconv**: Conversión entre diferentes codificaciones de caracteres.
- **json**: Codificación y decodificación de datos en formato JSON.
- **libxml**: Soporte para manipular y validar XML.
- **mysqlnd**: Controlador nativo para MySQL, optimiza el rendimiento.
- **pcre**: Funciones para trabajar con expresiones regulares.
- **PDO**: Interfaz de acceso a bases de datos con API uniforme.
- **Phar**: Creación y manipulación de archivos `.phar`.
- **random**: Generación de números y selecciones aleatorias.
- **readline**: Interfaz para entrada de texto en línea de comandos.
- **Reflection**: Inspección y análisis de clases y métodos en tiempo de ejecución.
- **session**: Gestión de sesiones en aplicaciones web.
- **SimpleXML**: Lectura y manipulación sencilla de XML.
- **SPL**: Estructuras de datos y funciones útiles para OOP.
- **standard**: Funciones estándar para diversas operaciones.
- **tokenizer**: Análisis y tokenización de scripts PHP.
- **xml**: Funciones para manipular documentos XML.
- **xmlreader**: Lectura eficiente de XML mediante eventos.
- **xmlwriter**: Escritura programática de documentos XML.
- **zlib**: Compresión y descompresión de datos.

## PHP Data Types on 64-bit Systems

| Data Type         | Size (Bytes) | Minimum Value                  | Maximum Value                        |
|-------------------|--------------|--------------------------------|--------------------------------------|
| **Integer**       | 8            | -9,223,372,036,854,775,808     | 9,223,372,036,854,775,807            |
| **Float (Double)**| 8            | -1.79769313486232E+308         | 1.79769313486232E+308                |
| **Boolean**       | 1            | 0 (false)                      | 1 (true)                             |
| **String**        | Variable     | 0 (empty string)               | Up to 2GB                            |
| **Array**         | Variable     | 0 elements                     | Up to 2GB                            |
| **Object**        | Variable     | N/A                            | N/A (depends on properties)          |
| **Resource**      | Variable     | N/A                            | N/A (depends on the resource type)   |
| **NULL**          | 0            | N/A                            | N/A                                  |

## PHP Data Types on 32-bit Systems

| Data Type         | Size (Bytes) | Minimum Value                    | Maximum Value                           |
|-------------------|--------------|----------------------------------|-----------------------------------------|
| **Integer**       | 4            | -2,147,483,648                   | 2,147,483,647                           |
| **Float (Double)**| 8            | -1.79769313486232E+308           | 1.79769313486232E+308                   |
| **Boolean**       | 1            | 0 (false)                        | 1 (true)                                |
| **String**        | Variable     | 0 (empty string)                 | Up to 2GB (depends on available memory) |
| **Array**         | Variable     | 0 elements                       | Up to 2GB (depends on available memory) |
| **Object**        | Variable     | N/A                              | N/A (depends on the object properties)  |
| **Resource**      | Variable     | N/A                              | N/A (depends on the resource type)      |
| **NULL**          | 0            | N/A                              | N/A                                     |

## UTF 8 Character Sizes Table for PHP 8

| Range                           | Size            | Examples               |
|---------------------------------|-----------------|------------------------|
| ASCII (0-127)                   | 1 byte          | `'a'`, `'b'`, `'1'`    |
| Chars extended (128-2047)       | 2 bytes         | `'ñ'`, `'é'`           |
| Chars of 3 bytes (2048-65535)   | 3 bytes         | `ñ`, `ç`, `ú`          |
| Chars of 4 bytes (+65536)       | 4 bytes         | `🌍`, `𐍈`             |

## UTF 8 Char Sizes by Architecture examples

| Character      | 64 bits arch       | 32 bits architecture       |
|----------------|--------------------|----------------------------|
| ASCII `'a'`    | 24 bytes           | 14 bytes                   |
| UTF-8 `'ñ'`    | 25 bytes           | 15 bytes                   |
| UTF-8 `'é'`    | 26 bytes           | 16 bytes                   |
| UTF-8 `'🌍'`  | 27 bytes            | 17 bytes                    |

