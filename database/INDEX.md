# Base de datos

Acceso a bases de datos con PDO, SQLite y MySQLi, incluyendo un query builder desde cero.

## Orden de lectura

1. **`pdo_connection.php`** - Conexiones PDO: DSN, opciones, charset, conexiones persistentes y patron Singleton
2. **`pdo_error_handling.php`** - Manejo de errores PDO: ERRMODE_SILENT, WARNING, EXCEPTION y handler con log
3. **`pdo_prepared_statements.php`** - Sentencias preparadas: placeholders, bindParam vs bindValue y busquedas LIKE
4. **`pdo_fetch.php`** - Modos de fetch: ASSOC, OBJ, CLASS, COLUMN, KEY_PAIR, GROUP y paginacion
5. **`pdo_crud.php`** - Operaciones CRUD completas con INSERT, SELECT, UPDATE, DELETE y patron Repository
6. **`pdo_transactions.php`** - Transacciones: commit, rollback, savepoints y rendimiento en inserciones batch
7. **`pdo_advanced.php`** - Funcionalidades avanzadas: BLOBs, stored procedures, quote() vs prepare y connection pooling
8. **`sqlite.php`** - SQLite con PDO: base de datos sin servidor, modo WAL, funciones especiales y KeyValueStore
9. **`mysqli_basics.php`** - MySQLi: estilo procedimental y OO, prepared statements, multi_query y comparacion con PDO
10. **`query_builder.php`** - Constructor de consultas SQL con patron Builder, interfaz fluida y ejecucion via PDO
