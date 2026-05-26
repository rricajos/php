<?php
/**
 * MySQLi BASICS - Fundamentos de MySQLi (MySQL Improved)
 *
 * MySQLi es la extension nativa de PHP para MySQL/MariaDB.
 * Ofrece una interfaz procedimental y orientada a objetos.
 *
 * Temas cubiertos:
 * - mysqli_connect: estilo procedimental vs orientado a objetos
 * - Consultas simples (query)
 * - Prepared statements con bind_param y bind_result
 * - multi_query para multiples consultas
 * - affected_rows y otros metadatos
 * - Comparacion detallada PDO vs MySQLi
 *
 * NOTA: MySQLi requiere un servidor MySQL/MariaDB corriendo.
 * Los ejemplos estan envueltos en try/catch y comentados donde
 * no pueden ejecutarse sin servidor.
 */

echo "=== MySQLi BASICS ===\n\n";

// ============================================================
// Ejemplo 1: Conexion - Procedimental vs Orientado a Objetos
// ============================================================

echo "=== Ejemplo 1: Conexion procedimental vs OO ===\n\n";

// --- Estilo Procedimental ---
echo "--- Estilo Procedimental ---\n\n";

// mysqli_connect() retorna un recurso de conexion o false
// Parametros: host, usuario, contrasena, base_de_datos, puerto, socket
/*
$conexion = mysqli_connect('localhost', 'root', 'password', 'mi_base_datos', 3306);

if (!$conexion) {
    // mysqli_connect_error() retorna el ultimo error de conexion
    die("Error de conexion: " . mysqli_connect_error() . " (codigo: " . mysqli_connect_errno() . ")");
}

echo "Conectado exitosamente (procedimental)\n";
echo "Info del servidor: " . mysqli_get_server_info($conexion) . "\n";
echo "Charset: " . mysqli_character_set_name($conexion) . "\n";

// Configurar charset (IMPORTANTE para seguridad)
mysqli_set_charset($conexion, 'utf8mb4');

// Cerrar conexion
mysqli_close($conexion);
*/

echo "Sintaxis: \$conn = mysqli_connect('host', 'user', 'pass', 'db');\n";
echo "Verificar: if (!\$conn) die(mysqli_connect_error());\n";
echo "Cerrar: mysqli_close(\$conn);\n";

echo "\n--- Estilo Orientado a Objetos ---\n\n";

// new mysqli() crea un objeto de conexion
// Lanza una excepcion si la conexion falla (PHP 8.1+)
/*
try {
    $db = new mysqli('localhost', 'root', 'password', 'mi_base_datos', 3306);

    // En versiones anteriores a PHP 8.1, verificar manualmente:
    // if ($db->connect_error) {
    //     die("Error: {$db->connect_error} (codigo: {$db->connect_errno})");
    // }

    echo "Conectado exitosamente (OO)\n";
    echo "Info del servidor: " . $db->server_info . "\n";
    echo "Version del servidor: " . $db->server_version . "\n";

    // Configurar charset
    $db->set_charset('utf8mb4');
    echo "Charset configurado: " . $db->character_set_name() . "\n";

    // Activar reporte de errores como excepciones (recomendado)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db->close();
} catch (mysqli_sql_exception $e) {
    echo "Error de conexion: {$e->getMessage()}\n";
}
*/

echo "Sintaxis: \$db = new mysqli('host', 'user', 'pass', 'db');\n";
echo "Charset: \$db->set_charset('utf8mb4');\n";
echo "Errores: mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);\n";
echo "Cerrar: \$db->close();\n";

// Conexion con opciones avanzadas
echo "\n--- Conexion con opciones avanzadas ---\n\n";
echo "Sintaxis para opciones avanzadas:\n";
echo <<<'PHP'
  $db = mysqli_init();
  $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);     // Timeout 5 segundos
  $db->options(MYSQLI_OPT_READ_TIMEOUT, 30);        // Timeout de lectura
  $db->options(MYSQLI_INIT_COMMAND, "SET NAMES utf8mb4");
  $db->ssl_set(null, null, '/path/ca-cert.pem', null, null);  // SSL
  $db->real_connect('localhost', 'root', 'pass', 'mi_db');
PHP;

echo "\n\n";

// ============================================================
// Ejemplo 2: Consultas simples con query()
// ============================================================

echo "=== Ejemplo 2: Consultas simples ===\n\n";

// Para demostrar la sintaxis, mostramos el codigo con comentarios
// ya que no tenemos un servidor MySQL disponible

echo "--- SELECT simple (estilo OO) ---\n";
echo <<<'PHP'
  // query() ejecuta una consulta SQL y retorna un objeto mysqli_result
  $resultado = $db->query("SELECT id, nombre, email FROM usuarios WHERE activo = 1");

  // Verificar si la consulta fue exitosa
  if ($resultado) {
      // num_rows: cantidad de filas retornadas
      echo "Filas encontradas: {$resultado->num_rows}\n";

      // fetch_assoc(): obtener una fila como array asociativo
      while ($fila = $resultado->fetch_assoc()) {
          echo "{$fila['id']} - {$fila['nombre']} ({$fila['email']})\n";
      }

      // Otros modos de fetch:
      // $fila = $resultado->fetch_object();       // Objeto stdClass
      // $fila = $resultado->fetch_array();        // Array mixto (MYSQLI_BOTH)
      // $fila = $resultado->fetch_array(MYSQLI_NUM);   // Array numerico
      // $filas = $resultado->fetch_all(MYSQLI_ASSOC);  // Todas las filas

      // IMPORTANTE: liberar el resultado despues de usarlo
      $resultado->free();
  }
PHP;

echo "\n\n--- INSERT, UPDATE, DELETE (estilo OO) ---\n";
echo <<<'PHP'
  // INSERT
  $db->query("INSERT INTO usuarios (nombre, email) VALUES ('Ana', 'ana@test.com')");
  echo "ID insertado: {$db->insert_id}\n";         // Ultimo ID autoincrement
  echo "Filas afectadas: {$db->affected_rows}\n";   // Filas modificadas

  // UPDATE
  $db->query("UPDATE usuarios SET nombre = 'Ana M.' WHERE id = 1");
  echo "Filas actualizadas: {$db->affected_rows}\n";

  // DELETE
  $db->query("DELETE FROM usuarios WHERE id = 5");
  echo "Filas eliminadas: {$db->affected_rows}\n";
PHP;

echo "\n\n--- Estilo Procedimental ---\n";
echo <<<'PHP'
  $resultado = mysqli_query($conn, "SELECT * FROM usuarios");
  while ($fila = mysqli_fetch_assoc($resultado)) {
      echo $fila['nombre'] . "\n";
  }
  echo "Filas: " . mysqli_num_rows($resultado) . "\n";
  mysqli_free_result($resultado);
PHP;

echo "\n\n";

// ============================================================
// Ejemplo 3: Prepared Statements con bind_param y bind_result
// ============================================================
// La forma segura de ejecutar queries con datos del usuario

echo "=== Ejemplo 3: Prepared Statements ===\n\n";

echo "--- bind_param: Vincular parametros de entrada ---\n\n";
echo <<<'PHP'
  // Preparar la sentencia (? son placeholders)
  $stmt = $db->prepare("SELECT id, nombre, email FROM usuarios WHERE edad > ? AND rol = ?");

  // bind_param vincula variables a los placeholders
  // El primer argumento son los tipos: i=integer, s=string, d=double, b=blob
  $edadMinima = 25;
  $rol = 'admin';
  $stmt->bind_param('is', $edadMinima, $rol);  // 'is' = integer, string

  // Ejecutar
  $stmt->execute();

  // Obtener resultado
  $resultado = $stmt->get_result();
  while ($fila = $resultado->fetch_assoc()) {
      echo "{$fila['nombre']} ({$fila['email']})\n";
  }
  $stmt->close();
PHP;

echo "\n\n--- bind_result: Vincular variables a columnas del resultado ---\n\n";
echo <<<'PHP'
  // Alternativa: bind_result vincula las columnas a variables PHP
  $stmt = $db->prepare("SELECT id, nombre, salario FROM empleados WHERE depto_id = ?");
  $deptoId = 1;
  $stmt->bind_param('i', $deptoId);
  $stmt->execute();

  // Vincular columnas del resultado a variables
  $stmt->bind_result($id, $nombre, $salario);

  // Cada llamada a fetch() llena las variables vinculadas
  while ($stmt->fetch()) {
      echo "[$id] $nombre - \$$salario\n";
  }
  $stmt->close();
PHP;

echo "\n\n--- Comparacion: bind_param vs bind_result vs get_result ---\n\n";
echo "bind_param + get_result (RECOMENDADO):\n";
echo "  + Permite usar fetch_assoc(), fetch_object(), etc.\n";
echo "  + Mas flexible y legible\n";
echo "  + Requiere mysqlnd (driver nativo de MySQL para PHP)\n\n";

echo "bind_param + bind_result:\n";
echo "  + Funciona sin mysqlnd (libmysqlclient)\n";
echo "  - Menos flexible, debes declarar todas las variables\n";
echo "  - Si agregas columnas al SELECT, debes actualizar bind_result\n\n";

echo "--- Tipos de bind_param ---\n\n";
echo <<<'PHP'
  // Los tipos se especifican como string en el primer argumento:
  // 'i' = integer (numeros enteros)
  // 'd' = double  (numeros decimales / flotantes)
  // 's' = string  (cadenas de texto)
  // 'b' = blob    (datos binarios)

  // Ejemplo con todos los tipos:
  $stmt = $db->prepare("INSERT INTO datos (entero, decimal_val, texto, binario) VALUES (?, ?, ?, ?)");
  $entero = 42;
  $decimal = 3.14159;
  $texto = "Hola mundo";
  $binario = file_get_contents('imagen.jpg');

  $stmt->bind_param('idss', $entero, $decimal, $texto, $binario);
  // Nota: para BLOBs grandes, usar send_long_data():
  // $stmt->send_long_data(3, file_get_contents('archivo_grande.bin'));
  $stmt->execute();
PHP;

echo "\n\n";

// ============================================================
// Ejemplo 4: multi_query - Ejecutar multiples consultas
// ============================================================

echo "=== Ejemplo 4: multi_query ===\n\n";

echo "--- Ejecutar multiples consultas en una llamada ---\n\n";
echo <<<'PHP'
  // multi_query() permite ejecutar varias SQL separadas por ;
  // CUIDADO: es vulnerable a inyeccion SQL si no se sanitiza correctamente
  $sql = "
      SELECT * FROM usuarios WHERE activo = 1;
      SELECT * FROM productos WHERE stock > 0;
      SELECT COUNT(*) as total FROM pedidos WHERE fecha = CURDATE()
  ";

  if ($db->multi_query($sql)) {
      $setNumero = 1;

      do {
          // Obtener el resultado del query actual
          $resultado = $db->store_result();

          if ($resultado) {
              echo "--- Resultado set #$setNumero ({$resultado->num_rows} filas) ---\n";
              while ($fila = $resultado->fetch_assoc()) {
                  print_r($fila);
              }
              $resultado->free();
          }
          $setNumero++;

          // Avanzar al siguiente resultado
          // more_results() verifica si hay mas, next_result() avanza
      } while ($db->more_results() && $db->next_result());
  }

  // Verificar errores despues de multi_query
  if ($db->errno) {
      echo "Error: {$db->error}\n";
  }
PHP;

echo "\n\nPrecauciones con multi_query:\n";
echo "  - NUNCA usar con datos del usuario (alto riesgo de inyeccion SQL)\n";
echo "  - Debe consumir TODOS los result sets antes de ejecutar otra query\n";
echo "  - Si no se consumen, la siguiente query fallara\n";
echo "  - Preferir ejecutar queries individuales con query() o prepare()\n";

echo "\n";

// ============================================================
// Ejemplo 5: affected_rows y metadatos de la consulta
// ============================================================

echo "=== Ejemplo 5: affected_rows y metadatos ===\n\n";

echo <<<'PHP'
  // --- affected_rows ---
  // Retorna el numero de filas afectadas por la ultima operacion

  // Con INSERT
  $db->query("INSERT INTO usuarios (nombre) VALUES ('Test')");
  echo "INSERT - affected_rows: {$db->affected_rows}\n";  // 1

  // Con UPDATE (solo cuenta filas REALMENTE cambiadas)
  $db->query("UPDATE usuarios SET nombre = 'Mismo' WHERE nombre = 'Mismo'");
  echo "UPDATE sin cambio - affected_rows: {$db->affected_rows}\n";  // 0

  // Con DELETE
  $db->query("DELETE FROM usuarios WHERE activo = 0");
  echo "DELETE - affected_rows: {$db->affected_rows}\n";

  // -1 indica error en la query
  $db->query("INVALID SQL HERE");
  echo "Error - affected_rows: {$db->affected_rows}\n";  // -1

  // --- insert_id ---
  $db->query("INSERT INTO usuarios (nombre) VALUES ('Nuevo')");
  echo "Ultimo ID: {$db->insert_id}\n";

  // --- info ---
  // Informacion sobre la ultima query (solo para ciertas operaciones)
  $db->query("INSERT INTO test VALUES (1,'a'), (2,'b'), (3,'c')");
  echo "Info: {$db->info}\n";
  // Ejemplo: "Records: 3  Duplicates: 0  Warnings: 0"

  // --- Metadatos del resultado ---
  $resultado = $db->query("SELECT id, nombre, email FROM usuarios LIMIT 1");

  // Informacion de campos/columnas
  $campos = $resultado->fetch_fields();
  foreach ($campos as $campo) {
      echo "Columna: {$campo->name}\n";
      echo "  Tabla: {$campo->table}\n";
      echo "  Tipo: {$campo->type}\n";
      echo "  Longitud: {$campo->length}\n";
      echo "  Flags: {$campo->flags}\n";
  }

  // Numero de campos
  echo "Total columnas: {$resultado->field_count}\n";

  $resultado->free();

  // --- warning_count ---
  echo "Warnings: {$db->warning_count}\n";
  if ($db->warning_count > 0) {
      $warnings = $db->query("SHOW WARNINGS")->fetch_all(MYSQLI_ASSOC);
      print_r($warnings);
  }
PHP;

echo "\n\n";

// ============================================================
// Ejemplo 6: Comparacion detallada PDO vs MySQLi
// ============================================================

echo "=== Ejemplo 6: Comparacion PDO vs MySQLi ===\n\n";

echo "=================================================================\n";
echo "| Caracteristica          | PDO              | MySQLi           |\n";
echo "=================================================================\n";
echo "| Bases de datos          | 12+ drivers      | Solo MySQL       |\n";
echo "| Interfaz OO             | SI               | SI               |\n";
echo "| Interfaz procedimental  | NO               | SI               |\n";
echo "| Named placeholders      | SI (:nombre)     | NO (solo ?)      |\n";
echo "| Positional placeholders | SI (?)           | SI (?)           |\n";
echo "| Mapeo a objetos         | SI (FETCH_CLASS) | SI (fetch_object)|\n";
echo "| Prepared statements     | SI               | SI               |\n";
echo "| Client-side prepare     | SI (emulado)     | NO               |\n";
echo "| Transacciones           | SI               | SI               |\n";
echo "| Stored procedures       | SI               | SI               |\n";
echo "| Multi-query             | Limitado         | SI (nativo)      |\n";
echo "| Non-blocking queries    | NO               | SI (async)       |\n";
echo "| Connection pooling      | Persistente      | Persistente      |\n";
echo "| LOAD DATA LOCAL         | Limitado         | SI               |\n";
echo "=================================================================\n\n";

// Ejemplo lado a lado
echo "--- Misma operacion en PDO y MySQLi ---\n\n";

echo "INSERTAR un registro:\n\n";
echo "PDO:\n";
echo <<<'PHP'
  $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)");
  $stmt->execute([':nombre' => 'Ana', ':email' => 'ana@test.com']);
  $id = $pdo->lastInsertId();
PHP;

echo "\n\nMySQLi:\n";
echo <<<'PHP'
  $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, email) VALUES (?, ?)");
  $stmt->bind_param('ss', $nombre, $email);
  $nombre = 'Ana';
  $email = 'ana@test.com';
  $stmt->execute();
  $id = $mysqli->insert_id;
PHP;

echo "\n\nSELECCIONAR registros:\n\n";
echo "PDO:\n";
echo <<<'PHP'
  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE rol = :rol");
  $stmt->execute([':rol' => 'admin']);
  $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
PHP;

echo "\n\nMySQLi:\n";
echo <<<'PHP'
  $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE rol = ?");
  $stmt->bind_param('s', $rol);
  $rol = 'admin';
  $stmt->execute();
  $resultado = $stmt->get_result();
  $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
PHP;

echo "\n\n--- Recomendacion ---\n\n";
echo "Usar PDO si:\n";
echo "  - Necesitas soporte para multiples bases de datos\n";
echo "  - Prefieres named placeholders (:nombre)\n";
echo "  - Quieres un API mas limpio y consistente\n";
echo "  - Trabajas con un framework (Laravel, Symfony, etc.)\n\n";

echo "Usar MySQLi si:\n";
echo "  - Solo usaras MySQL/MariaDB\n";
echo "  - Necesitas funcionalidades exclusivas de MySQL\n";
echo "  - Requieres queries asincronas (non-blocking)\n";
echo "  - Necesitas multi_query nativo\n\n";

echo "En general: PDO es la opcion RECOMENDADA para nuevos proyectos.\n";
echo "MySQLi es valido si estas 100%% seguro de que solo usaras MySQL.\n";
?>
