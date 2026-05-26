<?php
/**
 * ============================================================
 * SimpleXML en PHP - Manipulacion sencilla de datos XML
 * ============================================================
 * SimpleXML convierte documentos XML en objetos PHP que se
 * pueden recorrer con las estructuras de control habituales.
 * Es la forma mas rapida y legible de trabajar con XML cuando
 * el documento cabe en memoria.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: Cargar XML desde cadena con simplexml_load_string
// ============================================================
// Caso mas comun: recibir XML como respuesta de una API o
// construirlo dinamicamente en el codigo.

echo "=== Ejemplo 1: simplexml_load_string ===\n\n";

$xmlCatalogo = '<?xml version="1.0" encoding="UTF-8"?>
<catalogo>
    <libro isbn="978-84-204-8499-5" idioma="es">
        <titulo>Cien anos de soledad</titulo>
        <autor>Gabriel Garcia Marquez</autor>
        <editorial>Alfaguara</editorial>
        <precio moneda="EUR">12.50</precio>
        <anio>1967</anio>
    </libro>
    <libro isbn="978-84-376-0494-7" idioma="es">
        <titulo>Don Quijote de la Mancha</titulo>
        <autor>Miguel de Cervantes</autor>
        <editorial>Catedra</editorial>
        <precio moneda="EUR">18.90</precio>
        <anio>1605</anio>
    </libro>
    <libro isbn="978-0-06-112008-4" idioma="en">
        <titulo>To Kill a Mockingbird</titulo>
        <autor>Harper Lee</autor>
        <editorial>HarperCollins</editorial>
        <precio moneda="USD">14.99</precio>
        <anio>1960</anio>
    </libro>
</catalogo>';

$catalogo = simplexml_load_string($xmlCatalogo);

// Verificar que la carga fue exitosa (siempre validar)
if ($catalogo === false) {
    echo "Error: No se pudo parsear el XML.\n";
    // En produccion, revisar libxml_get_errors() para detalles
    foreach (libxml_get_errors() as $error) {
        echo "  - {$error->message}\n";
    }
} else {
    echo "Catalogo cargado correctamente.\n";
    echo "Primer libro: {$catalogo->libro[0]->titulo}\n";
    echo "Autor: {$catalogo->libro[0]->autor}\n";
    echo "ISBN: {$catalogo->libro[0]['isbn']}\n";
    echo "Total de libros: " . count($catalogo->libro) . "\n";
}

// Caso borde: XML malformado
$xmlMalformado = '<raiz><elemento>sin cierre';
libxml_use_internal_errors(true); // Suprimir warnings de PHP
$resultado = simplexml_load_string($xmlMalformado);
if ($resultado === false) {
    echo "\nXML malformado detectado correctamente.\n";
    $errores = libxml_get_errors();
    foreach ($errores as $err) {
        echo "  Linea {$err->line}: " . trim($err->message) . "\n";
    }
    libxml_clear_errors();
}
libxml_use_internal_errors(false);

// ============================================================
// Ejemplo 2: Acceso a elementos, atributos e hijos
// ============================================================
// SimpleXML trata los elementos como propiedades del objeto
// y los atributos como indices de array.

echo "\n=== Ejemplo 2: Acceso a elementos y atributos ===\n\n";

$xmlTienda = '<?xml version="1.0" encoding="UTF-8"?>
<tienda nombre="ElectroMax" ciudad="Madrid">
    <categoria id="1" nombre="Smartphones">
        <producto sku="SM-001" disponible="true">
            <nombre>Galaxy S24 Ultra</nombre>
            <marca>Samsung</marca>
            <precio>1299.00</precio>
            <especificaciones>
                <pantalla>6.8 pulgadas</pantalla>
                <ram>12 GB</ram>
                <almacenamiento>256 GB</almacenamiento>
            </especificaciones>
        </producto>
        <producto sku="SM-002" disponible="false">
            <nombre>iPhone 15 Pro</nombre>
            <marca>Apple</marca>
            <precio>1199.00</precio>
            <especificaciones>
                <pantalla>6.1 pulgadas</pantalla>
                <ram>8 GB</ram>
                <almacenamiento>128 GB</almacenamiento>
            </especificaciones>
        </producto>
    </categoria>
</tienda>';

$tienda = simplexml_load_string($xmlTienda);

// Acceso directo a elementos (como propiedades)
echo "Tienda: {$tienda['nombre']} ({$tienda['ciudad']})\n";
echo "Primera categoria: {$tienda->categoria['nombre']}\n";

// Acceso a elementos anidados en profundidad
$primerProducto = $tienda->categoria->producto[0];
echo "Producto: {$primerProducto->nombre}\n";
echo "SKU: {$primerProducto['sku']}\n";
echo "Pantalla: {$primerProducto->especificaciones->pantalla}\n";

// IMPORTANTE: SimpleXML devuelve objetos, no strings.
// Para comparaciones estrictas, hacer casting explicito.
$precio = $primerProducto->precio;
echo "\nTipo sin casting: " . gettype($precio) . "\n"; // object
echo "Tipo con casting: " . gettype((string)$precio) . "\n"; // string
echo "Tipo numerico: " . gettype((float)$precio) . "\n"; // double

// Comparacion peligrosa vs segura
if ($precio == "1299.00") {
    echo "Comparacion laxa (==): coincide (PHP convierte internamente)\n";
}
$precioStr = (string)$precio;
if ($precioStr === "1299.00") {
    echo "Comparacion estricta (===): coincide con casting explicito\n";
}

// Acceso a todos los atributos de un elemento
echo "\nAtributos del primer producto:\n";
foreach ($primerProducto->attributes() as $atributo => $valor) {
    echo "  {$atributo} = {$valor}\n";
}

// ============================================================
// Ejemplo 3: Iteracion sobre nodos hijos
// ============================================================
// Los objetos SimpleXML son iterables: se pueden recorrer
// con foreach para procesar colecciones de elementos.

echo "\n=== Ejemplo 3: Iteracion sobre hijos ===\n\n";

$xmlEmpleados = '<?xml version="1.0" encoding="UTF-8"?>
<empresa>
    <departamento nombre="Ingenieria">
        <empleado id="101" activo="true">
            <nombre>Ana Martinez</nombre>
            <puesto>Desarrolladora Senior</puesto>
            <salario>52000</salario>
        </empleado>
        <empleado id="102" activo="true">
            <nombre>Carlos Ruiz</nombre>
            <puesto>DevOps Engineer</puesto>
            <salario>48000</salario>
        </empleado>
        <empleado id="103" activo="false">
            <nombre>Laura Gomez</nombre>
            <puesto>QA Lead</puesto>
            <salario>45000</salario>
        </empleado>
    </departamento>
    <departamento nombre="Marketing">
        <empleado id="201" activo="true">
            <nombre>Pedro Sanchez</nombre>
            <puesto>Director de Campanas</puesto>
            <salario>50000</salario>
        </empleado>
    </departamento>
</empresa>';

$empresa = simplexml_load_string($xmlEmpleados);

// Iterar todos los departamentos y sus empleados
foreach ($empresa->departamento as $depto) {
    echo "Departamento: {$depto['nombre']}\n";
    echo str_repeat("-", 40) . "\n";

    $totalSalarios = 0;
    $contadorActivos = 0;

    foreach ($depto->empleado as $emp) {
        $estado = ((string)$emp['activo'] === 'true') ? 'Activo' : 'Inactivo';
        echo "  [{$emp['id']}] {$emp->nombre} - {$emp->puesto} ({$estado})\n";
        echo "       Salario: EUR " . number_format((float)$emp->salario, 2) . "\n";

        $totalSalarios += (float)$emp->salario;
        if ((string)$emp['activo'] === 'true') {
            $contadorActivos++;
        }
    }

    echo "  Total nomina departamento: EUR " . number_format($totalSalarios, 2) . "\n";
    echo "  Empleados activos: {$contadorActivos}\n\n";
}

// Iterar hijos con children() - util cuando no conocemos los nombres
echo "Hijos directos de <empresa>:\n";
foreach ($empresa->children() as $nombreNodo => $nodo) {
    echo "  Nodo: <{$nombreNodo}> con atributo nombre=\"{$nodo['nombre']}\"\n";
}

// ============================================================
// Ejemplo 4: Consultas XPath con SimpleXML
// ============================================================
// SimpleXML soporta XPath a traves del metodo xpath(), que
// devuelve un array de objetos SimpleXMLElement.

echo "\n=== Ejemplo 4: XPath con SimpleXML ===\n\n";

// Reutilizamos el XML de empleados
$empresa = simplexml_load_string($xmlEmpleados);

// Buscar todos los empleados activos (sin importar departamento)
$activos = $empresa->xpath('//empleado[@activo="true"]');
echo "Empleados activos en toda la empresa:\n";
foreach ($activos as $emp) {
    echo "  - {$emp->nombre} ({$emp->puesto})\n";
}

// Buscar empleados con salario mayor a 48000
$salarioAlto = $empresa->xpath('//empleado[salario > 48000]');
echo "\nEmpleados con salario > 48000:\n";
foreach ($salarioAlto as $emp) {
    echo "  - {$emp->nombre}: EUR {$emp->salario}\n";
}

// Buscar solo en el departamento de Ingenieria
$ingenieros = $empresa->xpath('//departamento[@nombre="Ingenieria"]/empleado');
echo "\nEmpleados de Ingenieria: " . count($ingenieros) . "\n";

// Obtener solo los nombres (devuelve array de SimpleXMLElement)
$nombres = $empresa->xpath('//empleado/nombre');
echo "\nTodos los nombres:\n";
foreach ($nombres as $nombre) {
    echo "  - {$nombre}\n";
}

// XPath con posicion
$primerEmpleado = $empresa->xpath('//departamento[1]/empleado[1]');
if (!empty($primerEmpleado)) {
    echo "\nPrimer empleado del primer departamento: {$primerEmpleado[0]->nombre}\n";
}

// Caso borde: XPath que no encuentra resultados
$fantasmas = $empresa->xpath('//empleado[@id="999"]');
echo "Resultado vacio: " . (empty($fantasmas) ? "correcto, array vacio" : "inesperado") . "\n";

// ============================================================
// Ejemplo 5: Modificar XML y exportar con asXML()
// ============================================================
// SimpleXML permite modificar valores, agregar elementos y
// atributos, y luego exportar el resultado a string o archivo.

echo "\n=== Ejemplo 5: Modificar XML y asXML() ===\n\n";

$xmlConfig = '<?xml version="1.0" encoding="UTF-8"?>
<configuracion>
    <base_datos>
        <host>localhost</host>
        <puerto>3306</puerto>
        <nombre>mi_app_dev</nombre>
        <usuario>root</usuario>
    </base_datos>
    <cache>
        <habilitado>false</habilitado>
        <ttl>3600</ttl>
    </cache>
</configuracion>';

$config = simplexml_load_string($xmlConfig);

// Modificar valores existentes (asignacion directa)
$config->base_datos->host = 'db.produccion.ejemplo.com';
$config->base_datos->puerto = '5432';
$config->base_datos->nombre = 'mi_app_prod';
$config->base_datos->usuario = 'app_usuario';

// Modificar atributos
$config->cache->habilitado = 'true';
$config->cache->ttl = '7200';

// Agregar un nuevo elemento hijo
$config->base_datos->addChild('password', 'S3cur3P@ss!');
$config->base_datos->addChild('ssl_mode', 'require');

// Agregar un nuevo bloque completo
$logs = $config->addChild('logs');
$logs->addChild('nivel', 'warning');
$logs->addChild('archivo', '/var/log/mi_app/app.log');
$logs->addChild('rotacion', 'diaria');
$logs->addAttribute('habilitado', 'true');

// Exportar a cadena
$xmlModificado = $config->asXML();
echo "XML modificado (produccion):\n";
echo $xmlModificado . "\n";

// Exportar a archivo (ejemplo conceptual, no ejecutamos)
// $config->asXML('/ruta/al/archivo/config_produccion.xml');

// Eliminar un nodo (requiere unset con referencia del padre)
// Nota: SimpleXML no tiene un metodo directo para eliminar
unset($config->cache->ttl);
echo "Despues de eliminar TTL del cache:\n";
echo $config->cache->asXML() . "\n";

// ============================================================
// Ejemplo 6: Parseador de feeds RSS (caso practico)
// ============================================================
// RSS es XML estandarizado: este ejemplo muestra como parsear
// un feed real y extraer informacion util de cada articulo.

echo "\n=== Ejemplo 6: Parseador de RSS (practico) ===\n\n";

// Simulamos un feed RSS tipico (estructura real de RSS 2.0)
$rssFeed = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Blog de Tecnologia en Espanol</title>
        <link>https://ejemplo.com/blog</link>
        <description>Noticias y tutoriales de tecnologia</description>
        <language>es</language>
        <lastBuildDate>Mon, 26 May 2025 10:00:00 +0200</lastBuildDate>
        <item>
            <title>PHP 8.4: Novedades y mejoras de rendimiento</title>
            <link>https://ejemplo.com/blog/php-84-novedades</link>
            <description>Analizamos las nuevas caracteristicas de PHP 8.4 incluyendo property hooks y asymmetric visibility.</description>
            <pubDate>Mon, 26 May 2025 09:00:00 +0200</pubDate>
            <category>PHP</category>
            <category>Desarrollo Web</category>
            <guid>https://ejemplo.com/blog/php-84-novedades</guid>
        </item>
        <item>
            <title>Introduccion a los contenedores Docker para principiantes</title>
            <link>https://ejemplo.com/blog/docker-principiantes</link>
            <description>Guia paso a paso para empezar a usar Docker en tus proyectos de desarrollo.</description>
            <pubDate>Sun, 25 May 2025 14:30:00 +0200</pubDate>
            <category>DevOps</category>
            <guid>https://ejemplo.com/blog/docker-principiantes</guid>
        </item>
        <item>
            <title>Patrones de diseno en aplicaciones modernas</title>
            <link>https://ejemplo.com/blog/patrones-diseno</link>
            <description>Exploramos los patrones de diseno mas usados en arquitecturas de microservicios.</description>
            <pubDate>Sat, 24 May 2025 11:00:00 +0200</pubDate>
            <category>Arquitectura</category>
            <category>Buenas Practicas</category>
            <guid>https://ejemplo.com/blog/patrones-diseno</guid>
        </item>
    </channel>
</rss>';

/**
 * Parsear un feed RSS y devolver un array estructurado.
 *
 * @param string $xmlString Contenido XML del feed RSS.
 * @return array Datos del canal y sus articulos.
 */
function parsearFeedRSS(string $xmlString): array {
    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($xmlString);

    if ($rss === false) {
        $errores = array_map(fn($e) => trim($e->message), libxml_get_errors());
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return ['error' => 'XML invalido', 'detalles' => $errores];
    }
    libxml_use_internal_errors(false);

    $canal = $rss->channel;

    // Extraer informacion del canal
    $resultado = [
        'titulo'      => (string)$canal->title,
        'enlace'      => (string)$canal->link,
        'descripcion' => (string)$canal->description,
        'idioma'      => (string)$canal->language,
        'articulos'   => [],
    ];

    // Extraer cada articulo
    foreach ($canal->item as $item) {
        // Un item puede tener multiples categorias
        $categorias = [];
        foreach ($item->category as $cat) {
            $categorias[] = (string)$cat;
        }

        $resultado['articulos'][] = [
            'titulo'      => (string)$item->title,
            'enlace'      => (string)$item->link,
            'descripcion' => (string)$item->description,
            'fecha'       => (string)$item->pubDate,
            'timestamp'   => strtotime((string)$item->pubDate),
            'categorias'  => $categorias,
        ];
    }

    // Ordenar por fecha mas reciente primero
    usort($resultado['articulos'], fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

    return $resultado;
}

// Usar el parseador
$feed = parsearFeedRSS($rssFeed);

echo "Canal: {$feed['titulo']}\n";
echo "Enlace: {$feed['enlace']}\n";
echo "Idioma: {$feed['idioma']}\n";
echo str_repeat("=", 55) . "\n\n";

foreach ($feed['articulos'] as $i => $articulo) {
    $num = $i + 1;
    $fechaFormateada = date('d/m/Y H:i', $articulo['timestamp']);
    $cats = implode(', ', $articulo['categorias']);

    echo "Articulo #{$num}: {$articulo['titulo']}\n";
    echo "  Fecha: {$fechaFormateada}\n";
    echo "  Categorias: {$cats}\n";
    echo "  Enlace: {$articulo['enlace']}\n";
    echo "  Resumen: " . mb_substr($articulo['descripcion'], 0, 80) . "...\n\n";
}

// Caso borde: feed sin articulos
$feedVacio = '<?xml version="1.0"?><rss version="2.0"><channel>
    <title>Blog Vacio</title><link>https://ejemplo.com</link>
    <description>Sin contenido</description><language>es</language>
</channel></rss>';

$resultadoVacio = parsearFeedRSS($feedVacio);
echo "Feed sin articulos: " . count($resultadoVacio['articulos']) . " articulos encontrados.\n";

// Caso borde: XML invalido
$resultadoError = parsearFeedRSS('<rss><malformado>');
if (isset($resultadoError['error'])) {
    echo "Error controlado: {$resultadoError['error']}\n";
}

echo "\n// Nota: Para feeds reales, usar simplexml_load_file() con la URL:\n";
echo "// \$rss = simplexml_load_file('https://ejemplo.com/feed.xml');\n";
echo "// Tambien se puede usar file_get_contents() + simplexml_load_string()\n";
echo "// para tener mas control sobre headers HTTP y timeouts.\n";

?>
