<?php
/**
 * ============================================================
 * XPath en PHP - Consultas avanzadas sobre documentos XML/HTML
 * ============================================================
 * XPath es un lenguaje de consultas para seleccionar nodos en
 * documentos XML. En PHP se usa principalmente a traves de
 * DOMXPath (con DOMDocument) y el metodo xpath() de SimpleXML.
 * Este archivo se centra en DOMXPath por ser mas completo.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: Fundamentos de DOMXPath
// ============================================================
// DOMXPath envuelve un DOMDocument y permite ejecutar consultas
// XPath que devuelven DOMNodeList con los resultados.

echo "=== Ejemplo 1: DOMXPath basico ===\n\n";

$xmlBiblioteca = '<?xml version="1.0" encoding="UTF-8"?>
<biblioteca nombre="Biblioteca Municipal de Sevilla" codigo="BMS-001">
    <seccion nombre="Narrativa" planta="1">
        <libro id="N001" disponible="true" prestamos_total="145">
            <titulo>La sombra del viento</titulo>
            <autor nacionalidad="espanola">Carlos Ruiz Zafon</autor>
            <anio>2001</anio>
            <genero>Novela</genero>
            <paginas>576</paginas>
            <idiomas><idioma>es</idioma><idioma>ca</idioma><idioma>en</idioma></idiomas>
        </libro>
        <libro id="N002" disponible="false" prestamos_total="89">
            <titulo>Cien anos de soledad</titulo>
            <autor nacionalidad="colombiana">Gabriel Garcia Marquez</autor>
            <anio>1967</anio>
            <genero>Realismo magico</genero>
            <paginas>471</paginas>
            <idiomas><idioma>es</idioma><idioma>en</idioma><idioma>fr</idioma></idiomas>
        </libro>
        <libro id="N003" disponible="true" prestamos_total="203">
            <titulo>Don Quijote de la Mancha</titulo>
            <autor nacionalidad="espanola">Miguel de Cervantes</autor>
            <anio>1605</anio>
            <genero>Novela</genero>
            <paginas>1345</paginas>
            <idiomas><idioma>es</idioma><idioma>en</idioma><idioma>de</idioma><idioma>fr</idioma></idiomas>
        </libro>
    </seccion>
    <seccion nombre="Ciencia" planta="2">
        <libro id="C001" disponible="true" prestamos_total="67">
            <titulo>Breve historia del tiempo</titulo>
            <autor nacionalidad="britanica">Stephen Hawking</autor>
            <anio>1988</anio>
            <genero>Divulgacion</genero>
            <paginas>256</paginas>
            <idiomas><idioma>es</idioma><idioma>en</idioma></idiomas>
        </libro>
        <libro id="C002" disponible="true" prestamos_total="42">
            <titulo>El gen egoista</titulo>
            <autor nacionalidad="britanica">Richard Dawkins</autor>
            <anio>1976</anio>
            <genero>Divulgacion</genero>
            <paginas>360</paginas>
            <idiomas><idioma>es</idioma><idioma>en</idioma></idiomas>
        </libro>
    </seccion>
    <seccion nombre="Infantil" planta="0">
        <libro id="I001" disponible="true" prestamos_total="312">
            <titulo>El Principito</titulo>
            <autor nacionalidad="francesa">Antoine de Saint-Exupery</autor>
            <anio>1943</anio>
            <genero>Fabula</genero>
            <paginas>96</paginas>
            <idiomas><idioma>es</idioma><idioma>fr</idioma><idioma>en</idioma><idioma>de</idioma><idioma>pt</idioma></idiomas>
        </libro>
    </seccion>
</biblioteca>';

$doc = new DOMDocument();
$doc->loadXML($xmlBiblioteca);
$xpath = new DOMXPath($doc);

// Seleccionar por nombre de etiqueta (todos los libros)
$todosLibros = $xpath->query('//libro');
echo "Total de libros en la biblioteca: {$todosLibros->length}\n";

// Seleccionar el elemento raiz
$raiz = $xpath->query('/biblioteca')->item(0);
echo "Biblioteca: {$raiz->getAttribute('nombre')}\n";

// Seleccionar hijos directos de la raiz
$secciones = $xpath->query('/biblioteca/seccion');
echo "Numero de secciones: {$secciones->length}\n";

// Ruta absoluta completa
$primerTitulo = $xpath->query('/biblioteca/seccion[1]/libro[1]/titulo')->item(0);
echo "Primer libro de la primera seccion: {$primerTitulo->textContent}\n";

// Obtener texto con evaluate (devuelve el valor directamente)
$nombreBiblio = $xpath->evaluate('string(/biblioteca/@nombre)');
echo "Nombre (con evaluate): {$nombreBiblio}\n";

// ============================================================
// Ejemplo 2: Seleccion por atributos y posicion
// ============================================================
// XPath permite filtrar nodos por sus atributos usando @ y por
// su posicion dentro del contexto con [n] o funciones.

echo "\n=== Ejemplo 2: Seleccion por atributos y posicion ===\n\n";

// Por valor de atributo exacto
$disponibles = $xpath->query('//libro[@disponible="true"]');
echo "Libros disponibles ({$disponibles->length}):\n";
foreach ($disponibles as $libro) {
    $titulo = $xpath->query('titulo', $libro)->item(0)->textContent;
    echo "  - {$titulo}\n";
}

// Por existencia de atributo (solo comprobar que existe)
$conPrestamos = $xpath->query('//libro[@prestamos_total]');
echo "\nLibros con registro de prestamos: {$conPrestamos->length}\n";

// Seleccion por posicion (1-indexado en XPath, no 0-indexado)
$segundoLibro = $xpath->query('//seccion[@nombre="Narrativa"]/libro[2]');
echo "\nSegundo libro de Narrativa: ";
echo $xpath->query('titulo', $segundoLibro->item(0))->item(0)->textContent . "\n";

// Ultimo elemento con last()
$ultimoLibro = $xpath->query('//seccion[@nombre="Narrativa"]/libro[last()]');
echo "Ultimo libro de Narrativa: ";
echo $xpath->query('titulo', $ultimoLibro->item(0))->item(0)->textContent . "\n";

// Posicion con operadores numericos
$primerosDos = $xpath->query('//seccion[@nombre="Narrativa"]/libro[position() <= 2]');
echo "\nPrimeros 2 libros de Narrativa:\n";
foreach ($primerosDos as $libro) {
    echo "  - " . $xpath->query('titulo', $libro)->item(0)->textContent . "\n";
}

// Seleccion por atributo numerico (prestamos > 100)
$populares = $xpath->query('//libro[@prestamos_total > 100]');
echo "\nLibros populares (>100 prestamos):\n";
foreach ($populares as $libro) {
    $titulo = $xpath->query('titulo', $libro)->item(0)->textContent;
    $prestamos = $libro->getAttribute('prestamos_total');
    echo "  - {$titulo} ({$prestamos} prestamos)\n";
}

// Combinar condiciones con and/or
$filtro = $xpath->query('//libro[@disponible="true" and @prestamos_total > 50]');
echo "\nDisponibles Y populares (>50 prestamos): {$filtro->length} libros\n";

// Seleccionar por hijo especifico
$sigloXX = $xpath->query('//libro[anio >= 1900 and anio < 2000]');
echo "\nLibros del siglo XX:\n";
foreach ($sigloXX as $libro) {
    $titulo = $xpath->query('titulo', $libro)->item(0)->textContent;
    $anio = $xpath->query('anio', $libro)->item(0)->textContent;
    echo "  - {$titulo} ({$anio})\n";
}

// ============================================================
// Ejemplo 3: Predicados y funciones XPath
// ============================================================
// XPath ofrece funciones de cadena, numericas y de conjunto
// para construir consultas complejas.

echo "\n=== Ejemplo 3: Funciones XPath (contains, starts-with, count, etc.) ===\n\n";

// contains() - buscar texto parcial
$conSombra = $xpath->query('//libro[contains(titulo, "sombra")]');
echo "Libros con 'sombra' en el titulo: {$conSombra->length}\n";
if ($conSombra->length > 0) {
    echo "  -> " . $xpath->query('titulo', $conSombra->item(0))->item(0)->textContent . "\n";
}

// starts-with() - texto que empieza con...
$empiezaEl = $xpath->query('//libro[starts-with(titulo, "El")]');
echo "\nLibros cuyo titulo empieza con 'El':\n";
foreach ($empiezaEl as $libro) {
    echo "  - " . $xpath->query('titulo', $libro)->item(0)->textContent . "\n";
}

// not() - negar condiciones
$noDisponibles = $xpath->query('//libro[not(@disponible="true")]');
echo "\nLibros NO disponibles:\n";
foreach ($noDisponibles as $libro) {
    echo "  - " . $xpath->query('titulo', $libro)->item(0)->textContent . "\n";
}

// count() - contar nodos hijos
// Libros disponibles en mas de 3 idiomas
$multiidioma = $xpath->query('//libro[count(idiomas/idioma) > 3]');
echo "\nLibros en mas de 3 idiomas:\n";
foreach ($multiidioma as $libro) {
    $titulo = $xpath->query('titulo', $libro)->item(0)->textContent;
    $numIdiomas = $xpath->evaluate('count(idiomas/idioma)', $libro);
    echo "  - {$titulo} ({$numIdiomas} idiomas)\n";
}

// string-length() - filtrar por longitud del texto
$titulosCortos = $xpath->query('//libro[string-length(titulo) < 20]');
echo "\nLibros con titulo corto (<20 caracteres):\n";
foreach ($titulosCortos as $libro) {
    $titulo = $xpath->query('titulo', $libro)->item(0)->textContent;
    echo "  - \"{$titulo}\" (" . strlen($titulo) . " chars)\n";
}

// sum() con evaluate - suma de valores numericos
$totalPaginas = $xpath->evaluate('sum(//libro/paginas)');
echo "\nTotal de paginas en toda la biblioteca: " . number_format($totalPaginas) . "\n";

$totalPrestamos = $xpath->evaluate('sum(//libro/@prestamos_total)');
echo "Total de prestamos historicos: " . number_format($totalPrestamos) . "\n";

// Contar libros por seccion usando evaluate
foreach ($xpath->query('//seccion') as $seccion) {
    $nombre = $seccion->getAttribute('nombre');
    $numLibros = $xpath->evaluate('count(libro)', $seccion);
    echo "  Seccion '{$nombre}': {$numLibros} libros\n";
}

// normalize-space() - limpiar espacios
$xmlConEspacios = new DOMDocument();
$xmlConEspacios->loadXML('<datos><campo>  texto  con   espacios  </campo></datos>');
$xpathEsp = new DOMXPath($xmlConEspacios);
$limpio = $xpathEsp->evaluate('normalize-space(//campo)');
echo "\nnormalize-space(): '{$limpio}'\n";

// ============================================================
// Ejemplo 4: XPath con espacios de nombres (namespaces)
// ============================================================
// Muchos XML reales (SOAP, RSS Atom, XHTML) usan namespaces.
// DOMXPath requiere registrarlos antes de usarlos en consultas.

echo "\n=== Ejemplo 4: XPath con namespaces ===\n\n";

$xmlConNamespace = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ws="http://ejemplo.com/servicio/v1">
    <soap:Header>
        <ws:autenticacion>
            <ws:token>abc123xyz789</ws:token>
            <ws:expiracion>2025-05-26T23:59:59Z</ws:expiracion>
        </ws:autenticacion>
    </soap:Header>
    <soap:Body>
        <ws:consultarClienteResponse>
            <ws:resultado codigo="200" exito="true">
                <ws:cliente id="CLI-001">
                    <ws:nombre>Elena Rodriguez</ws:nombre>
                    <ws:email>elena@ejemplo.com</ws:email>
                    <ws:tipo>premium</ws:tipo>
                </ws:cliente>
                <ws:cliente id="CLI-002">
                    <ws:nombre>Javier Moreno</ws:nombre>
                    <ws:email>javier@ejemplo.com</ws:email>
                    <ws:tipo>basico</ws:tipo>
                </ws:cliente>
            </ws:resultado>
        </ws:consultarClienteResponse>
    </soap:Body>
</soap:Envelope>';

$docNS = new DOMDocument();
$docNS->loadXML($xmlConNamespace);
$xpathNS = new DOMXPath($docNS);

// IMPORTANTE: Registrar los namespaces con prefijos para XPath
// Los prefijos en XPath no necesitan coincidir con los del XML,
// pero la URI del namespace SI debe coincidir exactamente.
$xpathNS->registerNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
$xpathNS->registerNamespace('w', 'http://ejemplo.com/servicio/v1');

// Ahora podemos consultar usando los prefijos registrados
$token = $xpathNS->evaluate('string(//w:token)');
echo "Token de autenticacion: {$token}\n";

$codigoResultado = $xpathNS->evaluate('string(//w:resultado/@codigo)');
echo "Codigo de respuesta: {$codigoResultado}\n";

// Obtener todos los clientes
$clientes = $xpathNS->query('//w:cliente');
echo "\nClientes encontrados ({$clientes->length}):\n";
foreach ($clientes as $cliente) {
    $id = $cliente->getAttribute('id');
    $nombre = $xpathNS->evaluate('string(w:nombre)', $cliente);
    $email = $xpathNS->evaluate('string(w:email)', $cliente);
    $tipo = $xpathNS->evaluate('string(w:tipo)', $cliente);
    echo "  [{$id}] {$nombre} <{$email}> (Plan: {$tipo})\n";
}

// Filtrar clientes premium
$premium = $xpathNS->query('//w:cliente[w:tipo="premium"]');
echo "\nClientes premium: {$premium->length}\n";

// Caso borde: consultar sin registrar namespace (no funciona)
$sinNS = $xpathNS->query('//cliente'); // No encontrara nada
echo "Busqueda sin namespace: {$sinNS->length} resultados (esperado: 0)\n";

// Alternativa con local-name() para ignorar namespaces (poco recomendado)
$conLocalName = $xpathNS->query('//*[local-name()="cliente"]');
echo "Busqueda con local-name(): {$conLocalName->length} resultados\n";

// Ejemplo con Atom (RSS moderno)
$xmlAtom = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Mi Blog Tecnico</title>
    <entry>
        <title>Novedades PHP 8.4</title>
        <link href="https://ejemplo.com/php84"/>
        <published>2025-05-20T10:00:00Z</published>
        <summary>Resumen de las nuevas caracteristicas de PHP 8.4</summary>
    </entry>
    <entry>
        <title>Guia de Docker Compose</title>
        <link href="https://ejemplo.com/docker-compose"/>
        <published>2025-05-18T14:00:00Z</published>
        <summary>Todo lo que necesitas saber sobre Docker Compose</summary>
    </entry>
</feed>';

$docAtom = new DOMDocument();
$docAtom->loadXML($xmlAtom);
$xpathAtom = new DOMXPath($docAtom);
$xpathAtom->registerNamespace('atom', 'http://www.w3.org/2005/Atom');

echo "\nFeed Atom - Entradas:\n";
$entradas = $xpathAtom->query('//atom:entry');
foreach ($entradas as $entrada) {
    $titulo = $xpathAtom->evaluate('string(atom:title)', $entrada);
    $enlace = $xpathAtom->evaluate('string(atom:link/@href)', $entrada);
    echo "  - {$titulo}: {$enlace}\n";
}

// ============================================================
// Ejemplo 5: Ejes XPath avanzados (axes)
// ============================================================
// Los ejes permiten navegar el arbol XML en todas las direcciones:
// padre, hermanos, ancestros, descendientes, etc.

echo "\n=== Ejemplo 5: Ejes XPath (navegacion avanzada) ===\n\n";

// Usamos el XML de la biblioteca del ejemplo 1
echo "Navegacion desde el libro 'Cien anos de soledad' (id=N002):\n\n";

// Punto de partida
$base = $xpath->query('//libro[@id="N002"]')->item(0);
$tituloBase = $xpath->query('titulo', $base)->item(0)->textContent;
echo "Punto de partida: {$tituloBase}\n\n";

// parent:: - nodo padre
$padre = $xpath->query('..', $base)->item(0); // Equivalente a parent::*
echo "Padre (parent): <{$padre->nodeName}> seccion '{$padre->getAttribute('nombre')}'\n";

// ancestor:: - todos los ancestros
$ancestros = $xpath->query('ancestor::*', $base);
echo "Ancestros ({$ancestros->length}):\n";
foreach ($ancestros as $anc) {
    $info = $anc->nodeName;
    if ($anc->hasAttribute('nombre')) {
        $info .= " [{$anc->getAttribute('nombre')}]";
    }
    echo "  -> {$info}\n";
}

// preceding-sibling:: - hermanos anteriores
$hermanosPrevios = $xpath->query('preceding-sibling::libro', $base);
echo "\nHermanos previos ({$hermanosPrevios->length}):\n";
foreach ($hermanosPrevios as $h) {
    echo "  - " . $xpath->query('titulo', $h)->item(0)->textContent . "\n";
}

// following-sibling:: - hermanos siguientes
$hermanosSiguientes = $xpath->query('following-sibling::libro', $base);
echo "Hermanos siguientes ({$hermanosSiguientes->length}):\n";
foreach ($hermanosSiguientes as $h) {
    echo "  - " . $xpath->query('titulo', $h)->item(0)->textContent . "\n";
}

// descendant:: - todos los descendientes
$descendientes = $xpath->query('descendant::*', $base);
echo "\nDescendientes del libro ({$descendientes->length} nodos):\n";
foreach ($descendientes as $d) {
    if ($d->childNodes->length === 1 && $d->firstChild->nodeType === XML_TEXT_NODE) {
        echo "  <{$d->nodeName}>: {$d->textContent}\n";
    }
}

// ============================================================
// Ejemplo 6: Caso practico - Extraer datos de tabla HTML
// ============================================================
// Escenario real: parsear una pagina web que contiene una tabla
// con datos de productos y extraer la informacion estructurada.

echo "\n=== Ejemplo 6: Scraping de tabla HTML (practico) ===\n\n";

$htmlTabla = '<!DOCTYPE html>
<html>
<head><title>Comparativa de Hosting</title></head>
<body>
<div id="contenido-principal">
    <h1>Comparativa de planes de hosting 2025</h1>
    <p class="actualizacion">Ultima revision: mayo 2025</p>

    <table id="tabla-comparativa" class="datos responsive">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>Plan</th>
                <th>Almacenamiento</th>
                <th>RAM</th>
                <th>Precio/mes</th>
                <th class="nota">Valoracion</th>
            </tr>
        </thead>
        <tbody>
            <tr class="fila destacado" data-proveedor="cloudmax">
                <td class="proveedor"><a href="https://cloudmax.es">CloudMax</a></td>
                <td class="plan">Business Pro</td>
                <td class="almacenamiento">100 GB SSD</td>
                <td class="ram">8 GB</td>
                <td class="precio" data-valor="29.99">29.99 EUR</td>
                <td class="nota" data-valor="4.8">4.8/5</td>
            </tr>
            <tr class="fila" data-proveedor="hostplus">
                <td class="proveedor"><a href="https://hostplus.com">HostPlus</a></td>
                <td class="plan">Premium</td>
                <td class="almacenamiento">50 GB SSD</td>
                <td class="ram">4 GB</td>
                <td class="precio" data-valor="19.99">19.99 EUR</td>
                <td class="nota" data-valor="4.2">4.2/5</td>
            </tr>
            <tr class="fila" data-proveedor="servnet">
                <td class="proveedor"><a href="https://servnet.es">ServNet</a></td>
                <td class="plan">Empresarial</td>
                <td class="almacenamiento">200 GB NVMe</td>
                <td class="ram">16 GB</td>
                <td class="precio" data-valor="49.99">49.99 EUR</td>
                <td class="nota" data-valor="4.6">4.6/5</td>
            </tr>
            <tr class="fila" data-proveedor="webrapid">
                <td class="proveedor"><a href="https://webrapid.io">WebRapid</a></td>
                <td class="plan">Starter Plus</td>
                <td class="almacenamiento">25 GB SSD</td>
                <td class="ram">2 GB</td>
                <td class="precio" data-valor="9.99">9.99 EUR</td>
                <td class="nota" data-valor="3.9">3.9/5</td>
            </tr>
            <tr class="fila destacado" data-proveedor="nimbus">
                <td class="proveedor"><a href="https://nimbus.cloud">Nimbus Cloud</a></td>
                <td class="plan">Scale Pro</td>
                <td class="almacenamiento">500 GB NVMe</td>
                <td class="ram">32 GB</td>
                <td class="precio" data-valor="89.99">89.99 EUR</td>
                <td class="nota" data-valor="4.9">4.9/5</td>
            </tr>
        </tbody>
    </table>

    <div class="resumen">
        <p>Total proveedores analizados: <span id="total">5</span></p>
    </div>
</div>
</body>
</html>';

/**
 * Extrae datos de una tabla HTML y los devuelve como array estructurado.
 * Usa XPath para navegar la estructura de forma robusta.
 *
 * @param string $html Contenido HTML con la tabla.
 * @param string $tablaSelector XPath para localizar la tabla.
 * @return array Datos extraidos y estadisticas.
 */
function extraerDatosTabla(string $html, string $tablaSelector): array {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($doc);

    // Localizar la tabla objetivo
    $tabla = $xpath->query($tablaSelector)->item(0);
    if (!$tabla) {
        return ['error' => 'Tabla no encontrada con el selector: ' . $tablaSelector];
    }

    // Extraer encabezados
    $encabezados = [];
    $thNodes = $xpath->query('.//thead/tr/th', $tabla);
    foreach ($thNodes as $th) {
        $encabezados[] = trim($th->textContent);
    }

    // Extraer filas del cuerpo
    $datos = [];
    $filas = $xpath->query('.//tbody/tr', $tabla);

    foreach ($filas as $fila) {
        $registro = [];
        $celdas = $xpath->query('.//td', $fila);

        foreach ($celdas as $i => $celda) {
            $clave = $encabezados[$i] ?? "columna_{$i}";

            // Extraer valor limpio
            $valor = trim($celda->textContent);

            // Extraer data-valor si existe (datos numericos reales)
            $dataValor = $celda->getAttribute('data-valor');

            // Extraer enlace si hay un <a> dentro
            $enlace = $xpath->query('.//a/@href', $celda);
            $url = ($enlace->length > 0) ? $enlace->item(0)->value : null;

            $registro[$clave] = [
                'texto'      => $valor,
                'data_valor' => $dataValor ?: null,
                'url'        => $url,
            ];
        }

        // Extraer metadata de la fila
        $registro['_meta'] = [
            'clases'    => $fila->getAttribute('class'),
            'proveedor' => $fila->getAttribute('data-proveedor'),
            'destacado' => str_contains($fila->getAttribute('class'), 'destacado'),
        ];

        $datos[] = $registro;
    }

    // Estadisticas calculadas con XPath
    $estadisticas = [
        'total_filas'       => $filas->length,
        'filas_destacadas'  => $xpath->query('.//tbody/tr[contains(@class, "destacado")]', $tabla)->length,
        'precio_min'        => null,
        'precio_max'        => null,
        'nota_promedio'     => 0,
    ];

    // Calcular estadisticas de precios y notas
    $precios = [];
    $notas = [];
    foreach ($datos as $reg) {
        if (isset($reg['Precio/mes']['data_valor'])) {
            $precios[] = (float)$reg['Precio/mes']['data_valor'];
        }
        if (isset($reg['Valoracion']['data_valor'])) {
            $notas[] = (float)$reg['Valoracion']['data_valor'];
        }
    }

    if (!empty($precios)) {
        $estadisticas['precio_min'] = min($precios);
        $estadisticas['precio_max'] = max($precios);
    }
    if (!empty($notas)) {
        $estadisticas['nota_promedio'] = round(array_sum($notas) / count($notas), 2);
    }

    return [
        'encabezados'  => $encabezados,
        'datos'        => $datos,
        'estadisticas' => $estadisticas,
    ];
}

// Ejecutar el scraper
$resultado = extraerDatosTabla($htmlTabla, '//table[@id="tabla-comparativa"]');

// Mostrar encabezados
echo "Columnas detectadas: " . implode(' | ', $resultado['encabezados']) . "\n\n";

// Mostrar datos extraidos en formato tabla
echo sprintf(
    "%-15s %-16s %-15s %-8s %10s %8s %s\n",
    'Proveedor', 'Plan', 'Almacenamiento', 'RAM', 'Precio', 'Nota', 'Dest.'
);
echo str_repeat("-", 85) . "\n";

foreach ($resultado['datos'] as $reg) {
    $destacado = $reg['_meta']['destacado'] ? ' *' : '';
    echo sprintf(
        "%-15s %-16s %-15s %-8s %10s %8s%s\n",
        $reg['Proveedor']['texto'],
        $reg['Plan']['texto'],
        $reg['Almacenamiento']['texto'],
        $reg['RAM']['texto'],
        $reg['Precio/mes']['texto'],
        $reg['Valoracion']['texto'],
        $destacado
    );
}
echo str_repeat("-", 85) . "\n";

// Estadisticas
$stats = $resultado['estadisticas'];
echo "\nEstadisticas:\n";
echo "  Total proveedores: {$stats['total_filas']}\n";
echo "  Destacados: {$stats['filas_destacadas']}\n";
echo "  Precio mas bajo: {$stats['precio_min']} EUR\n";
echo "  Precio mas alto: {$stats['precio_max']} EUR\n";
echo "  Valoracion media: {$stats['nota_promedio']}/5\n";

// Consultas adicionales sobre los datos extraidos
echo "\nProveedores con nota >= 4.5 y precio < 50 EUR:\n";
foreach ($resultado['datos'] as $reg) {
    $nota = (float)($reg['Valoracion']['data_valor'] ?? 0);
    $precio = (float)($reg['Precio/mes']['data_valor'] ?? 0);
    if ($nota >= 4.5 && $precio < 50) {
        echo "  -> {$reg['Proveedor']['texto']} ({$reg['Plan']['texto']}): ";
        echo "{$precio} EUR, nota {$nota}\n";
    }
}

echo "\nProveedores destacados con URL:\n";
foreach ($resultado['datos'] as $reg) {
    if ($reg['_meta']['destacado'] && $reg['Proveedor']['url']) {
        echo "  -> {$reg['Proveedor']['texto']}: {$reg['Proveedor']['url']}\n";
    }
}

?>
