<?php
// ============================================================
// $_GET - Acceder a parámetros de URL (query string)
// ============================================================
// $_GET es un arreglo superglobal que contiene los parámetros
// enviados en la URL después del signo "?".
// Ejemplo: pagina.php?nombre=Ana&edad=25
// $_GET["nombre"] = "Ana", $_GET["edad"] = "25"
// ============================================================


// ------------------------------------------------------------
// Ejemplo 1: Acceder a parámetros básicos de URL
// ------------------------------------------------------------
// URL: pagina.php?nombre=María&ciudad=Bogotá

// Acceso directo (puede generar advertencia si no existe)
// $nombre = $_GET["nombre"]; // No recomendado sin verificación

// Acceso seguro verificando existencia
if (isset($_GET["nombre"])) {
    $nombre = $_GET["nombre"];
    echo "Hola, $nombre!\n";
} else {
    echo "No se proporcionó un nombre en la URL.\n";
}

// Verificar si la ciudad fue proporcionada
if (!empty($_GET["ciudad"])) {
    echo "Ciudad: " . $_GET["ciudad"] . "\n";
}

// Mostrar todos los parámetros GET recibidos
echo "\nParámetros GET recibidos:\n";
if (count($_GET) > 0) {
    foreach ($_GET as $clave => $valor) {
        echo "  $clave = $valor\n";
    }
} else {
    echo "  (ninguno)\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 2: Valores por defecto con operador de fusión null
// ------------------------------------------------------------
// El operador ?? devuelve el valor izquierdo si existe y no es null,
// o el valor derecho como respaldo.

// URL: productos.php?pagina=3&orden=precio&categoria=electronica

// Obtener parámetros con valores por defecto
$pagina     = $_GET["pagina"] ?? 1;          // Por defecto: página 1
$porPagina  = $_GET["por_pagina"] ?? 20;     // Por defecto: 20 resultados
$orden      = $_GET["orden"] ?? "nombre";    // Por defecto: ordenar por nombre
$direccion  = $_GET["dir"] ?? "asc";         // Por defecto: ascendente
$busqueda   = $_GET["q"] ?? "";              // Por defecto: sin búsqueda
$categoria  = $_GET["categoria"] ?? "todas"; // Por defecto: todas

echo "Configuración de búsqueda:\n";
echo "  Página: $pagina\n";
echo "  Resultados por página: $porPagina\n";
echo "  Ordenar por: $orden ($direccion)\n";
echo "  Búsqueda: " . ($busqueda ?: "(sin filtro)") . "\n";
echo "  Categoría: $categoria\n";

// Construir la URL para la siguiente página
$siguientePagina = $pagina + 1;
$urlSiguiente = "productos.php?pagina=$siguientePagina&por_pagina=$porPagina&orden=$orden";
echo "\nURL siguiente página: $urlSiguiente\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 3: Validar parámetros numéricos
// ------------------------------------------------------------
// Los valores de $_GET siempre son cadenas de texto.
// Deben validarse y convertirse al tipo correcto.

// URL: articulo.php?id=42&pagina=3

// Validar que el ID es un número entero positivo
$idArticulo = $_GET["id"] ?? null;

if ($idArticulo === null) {
    echo "Error: Se requiere el parámetro 'id'.\n";
} elseif (!ctype_digit($idArticulo)) {
    echo "Error: El ID debe ser un número entero positivo.\n";
} else {
    $idArticulo = (int) $idArticulo;
    echo "Mostrando artículo #$idArticulo\n";
}

// Validar un rango numérico
$pagina = $_GET["pagina"] ?? "1";

if (!ctype_digit($pagina) || (int)$pagina < 1) {
    $pagina = 1; // Valor por defecto si es inválido
    echo "Página inválida, usando página 1.\n";
} else {
    $pagina = (int) $pagina;
    echo "Mostrando página $pagina.\n";
}

// Validar que un precio está en un rango aceptable
$precioMinimo = $_GET["precio_min"] ?? "0";
$precioMaximo = $_GET["precio_max"] ?? "99999";

$precioMinimo = is_numeric($precioMinimo) ? (float)$precioMinimo : 0;
$precioMaximo = is_numeric($precioMaximo) ? (float)$precioMaximo : 99999;

// Asegurar que el mínimo no sea mayor que el máximo
if ($precioMinimo > $precioMaximo) {
    [$precioMinimo, $precioMaximo] = [$precioMaximo, $precioMinimo];
}

echo "Rango de precio: \$$precioMinimo - \$$precioMaximo\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 4: Validar y sanitizar cadenas de texto
// ------------------------------------------------------------
// NUNCA confiar en datos de $_GET. Siempre sanitizar para
// prevenir ataques XSS e inyección.

// URL: buscar.php?q=<script>alert('xss')</script>&categoria=ropa

// Sanitizar texto de búsqueda para prevenir XSS
$busqueda = $_GET["q"] ?? "";

// htmlspecialchars() convierte caracteres especiales a entidades HTML
$busquedaSegura = htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8");

// strip_tags() elimina todas las etiquetas HTML y PHP
$busquedaLimpia = strip_tags($busqueda);

// Limitar la longitud del texto
$busquedaFinal = mb_substr($busquedaLimpia, 0, 100); // Máximo 100 caracteres

echo "Entrada original: $busqueda\n";       // Podría contener HTML malicioso
echo "Con htmlspecialchars: $busquedaSegura\n"; // Segura para mostrar en HTML
echo "Con strip_tags: $busquedaLimpia\n";       // Sin etiquetas HTML
echo "Texto final (limitado): $busquedaFinal\n";

// Validar contra una lista de valores permitidos
$categoriasPermitidas = ["ropa", "electronica", "hogar", "deportes", "libros"];
$categoria = $_GET["categoria"] ?? "";

if (in_array($categoria, $categoriasPermitidas, true)) {
    echo "Categoría válida: $categoria\n";
} else {
    echo "Categoría no válida. Categorías disponibles: "
        . implode(", ", $categoriasPermitidas) . "\n";
}
?>

<?php
// ------------------------------------------------------------
// Ejemplo 5: Parámetros con múltiples valores (arreglos)
// ------------------------------------------------------------
// PHP permite recibir arreglos en GET usando corchetes [].
// URL: filtro.php?colores[]=rojo&colores[]=azul&tallas[]=M&tallas[]=L

// Acceder a parámetros tipo arreglo
$colores = $_GET["colores"] ?? [];
$tallas  = $_GET["tallas"] ?? [];

// Verificar que son arreglos (seguridad)
if (!is_array($colores)) {
    $colores = [$colores];
}
if (!is_array($tallas)) {
    $tallas = [$tallas];
}

echo "Filtros seleccionados:\n";

// Mostrar colores seleccionados
if (!empty($colores)) {
    echo "  Colores: " . implode(", ", $colores) . "\n";
} else {
    echo "  Colores: (todos)\n";
}

// Mostrar tallas seleccionadas
if (!empty($tallas)) {
    echo "  Tallas: " . implode(", ", $tallas) . "\n";
} else {
    echo "  Tallas: (todas)\n";
}

// Validar que los valores están en una lista permitida
$coloresPermitidos = ["rojo", "azul", "verde", "negro", "blanco"];
$coloresValidos = array_filter($colores, function($color) use ($coloresPermitidos) {
    return in_array($color, $coloresPermitidos, true);
});

echo "  Colores válidos después de filtrar: " . implode(", ", $coloresValidos) . "\n";

// Construir URL con parámetros de arreglo
$parametros = http_build_query([
    "colores" => $coloresValidos,
    "tallas"  => $tallas,
    "pagina"  => 1
]);
echo "  URL generada: filtro.php?$parametros\n";
?>

<?php
// ------------------------------------------------------------
// Ejemplo 6: Función auxiliar para obtener y validar parámetros
// ------------------------------------------------------------
// Una función reutilizable que simplifica la obtención y validación
// de parámetros GET en toda la aplicación.

function obtenerParametroGet(
    string $nombre,
    mixed $valorDefecto = null,
    string $tipo = "string",
    array $opciones = []
): mixed {
    // Verificar si el parámetro existe
    if (!isset($_GET[$nombre]) || $_GET[$nombre] === "") {
        return $valorDefecto;
    }

    $valor = $_GET[$nombre];

    // Validar y convertir según el tipo esperado
    switch ($tipo) {
        case "int":
            if (!ctype_digit(ltrim($valor, "-"))) {
                return $valorDefecto;
            }
            $valor = (int) $valor;
            // Validar rango si se especificó
            if (isset($opciones["min"]) && $valor < $opciones["min"]) return $valorDefecto;
            if (isset($opciones["max"]) && $valor > $opciones["max"]) return $valorDefecto;
            break;

        case "float":
            if (!is_numeric($valor)) return $valorDefecto;
            $valor = (float) $valor;
            break;

        case "email":
            $valor = filter_var($valor, FILTER_VALIDATE_EMAIL);
            if ($valor === false) return $valorDefecto;
            break;

        case "enum":
            if (!in_array($valor, $opciones["valores"] ?? [], true)) {
                return $valorDefecto;
            }
            break;

        case "string":
        default:
            $valor = htmlspecialchars(strip_tags($valor), ENT_QUOTES, "UTF-8");
            $maxLongitud = $opciones["max_longitud"] ?? 255;
            $valor = mb_substr($valor, 0, $maxLongitud);
            break;
    }

    return $valor;
}

// Uso de la función auxiliar
$id = obtenerParametroGet("id", 0, "int", ["min" => 1, "max" => 99999]);
$nombre = obtenerParametroGet("nombre", "Invitado", "string", ["max_longitud" => 50]);
$email = obtenerParametroGet("email", null, "email");
$orden = obtenerParametroGet("orden", "fecha", "enum", ["valores" => ["fecha", "nombre", "precio"]]);
$precio = obtenerParametroGet("precio", 0.0, "float");

echo "Parámetros procesados:\n";
echo "  ID: $id\n";
echo "  Nombre: $nombre\n";
echo "  Email: " . ($email ?? "no proporcionado") . "\n";
echo "  Orden: $orden\n";
echo "  Precio: $precio\n";
?>
