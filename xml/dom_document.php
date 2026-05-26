<?php
/**
 * ============================================================
 * DOMDocument en PHP - Manipulacion avanzada de XML y HTML
 * ============================================================
 * DOMDocument implementa la especificacion W3C DOM y ofrece
 * control total sobre la estructura del documento. Es mas
 * verboso que SimpleXML pero mucho mas potente para crear,
 * modificar y validar documentos complejos.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: Crear un documento XML desde cero
// ============================================================
// DOMDocument permite construir documentos nodo por nodo,
// lo cual es ideal para generar XML con estructura dinamica.

echo "=== Ejemplo 1: Crear XML con DOMDocument ===\n\n";

$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true; // Indentar la salida para legibilidad

// Crear el elemento raiz
$raiz = $doc->createElement('pedido');
$doc->appendChild($raiz);

// Agregar atributos al elemento raiz
$raiz->setAttribute('id', 'PED-2025-001');
$raiz->setAttribute('fecha', '2025-05-26');
$raiz->setAttribute('estado', 'procesando');

// Crear seccion de cliente
$cliente = $doc->createElement('cliente');
$raiz->appendChild($cliente);
$cliente->setAttribute('id', 'CLI-500');

$nombreCliente = $doc->createElement('nombre', 'Maria Fernandez Lopez');
$cliente->appendChild($nombreCliente);

$emailCliente = $doc->createElement('email', 'maria.fernandez@ejemplo.com');
$cliente->appendChild($emailCliente);

$telefonoCliente = $doc->createElement('telefono', '+34 612 345 678');
$cliente->appendChild($telefonoCliente);

// Crear seccion de productos con multiples items
$productos = $doc->createElement('productos');
$raiz->appendChild($productos);

$listaProductos = [
    ['sku' => 'LAP-001', 'nombre' => 'Laptop ProMax 15', 'cantidad' => 1, 'precio' => 1299.99],
    ['sku' => 'MOU-015', 'nombre' => 'Raton inalambrico ergonomico', 'cantidad' => 2, 'precio' => 34.50],
    ['sku' => 'USB-100', 'nombre' => 'Hub USB-C 7 puertos', 'cantidad' => 1, 'precio' => 45.00],
];

$totalPedido = 0;
foreach ($listaProductos as $prod) {
    $item = $doc->createElement('producto');
    $item->setAttribute('sku', $prod['sku']);
    $productos->appendChild($item);

    $item->appendChild($doc->createElement('nombre', $prod['nombre']));
    $item->appendChild($doc->createElement('cantidad', (string)$prod['cantidad']));
    $item->appendChild($doc->createElement('precio_unitario', number_format($prod['precio'], 2, '.', '')));

    $subtotal = $prod['cantidad'] * $prod['precio'];
    $item->appendChild($doc->createElement('subtotal', number_format($subtotal, 2, '.', '')));
    $totalPedido += $subtotal;
}

// Seccion de totales
$totales = $doc->createElement('totales');
$raiz->appendChild($totales);
$totales->appendChild($doc->createElement('subtotal', number_format($totalPedido, 2, '.', '')));
$totales->appendChild($doc->createElement('iva', number_format($totalPedido * 0.21, 2, '.', '')));
$totales->appendChild($doc->createElement('total', number_format($totalPedido * 1.21, 2, '.', '')));

echo $doc->saveXML();

// ============================================================
// Ejemplo 2: createAttribute y nodos de texto con CDATA
// ============================================================
// createAttribute ofrece un enfoque orientado a objetos para
// atributos. CDATA permite incluir texto con caracteres especiales
// sin necesidad de escaparlos.

echo "\n=== Ejemplo 2: Atributos avanzados y CDATA ===\n\n";

$doc2 = new DOMDocument('1.0', 'UTF-8');
$doc2->formatOutput = true;

$articulos = $doc2->createElement('articulos');
$doc2->appendChild($articulos);

// Metodo 1: setAttribute (mas conciso)
$art1 = $doc2->createElement('articulo');
$art1->setAttribute('id', '1');
$art1->setAttribute('tipo', 'tutorial');
$articulos->appendChild($art1);

// Metodo 2: createAttribute (mas control, reutilizable)
$art2 = $doc2->createElement('articulo');
$attrId = $doc2->createAttribute('id');
$attrId->value = '2';
$art2->appendChild($attrId);

$attrTipo = $doc2->createAttribute('tipo');
$attrTipo->value = 'referencia';
$art2->appendChild($attrTipo);
$articulos->appendChild($art2);

// CDATA: para contenido con HTML/caracteres especiales
$titulo1 = $doc2->createElement('titulo', 'Guia de inicio rapido');
$art1->appendChild($titulo1);

$contenido1 = $doc2->createElement('contenido');
$cdata = $doc2->createCDATASection(
    '<p>Este contenido tiene <strong>HTML</strong> dentro.</p>' .
    '<p>Caracteres especiales: & < > " \' sin problemas</p>'
);
$contenido1->appendChild($cdata);
$art1->appendChild($contenido1);

// Nodo de texto explicito
$titulo2 = $doc2->createElement('titulo');
$textoTitulo = $doc2->createTextNode('Referencia de funciones & metodos');
$titulo2->appendChild($textoTitulo);
$art2->appendChild($titulo2);

echo $doc2->saveXML();

// Comparacion: createTextNode escapa automaticamente & a &amp;
// CDATA mantiene el contenido tal cual dentro de <![CDATA[...]]>

// ============================================================
// Ejemplo 3: Cargar HTML con loadHTML
// ============================================================
// loadHTML es especialmente util para parsear paginas web reales,
// incluso si el HTML no es XML valido (tags sin cerrar, etc.).

echo "\n=== Ejemplo 3: loadHTML para parsear paginas web ===\n\n";

$html = '<!DOCTYPE html>
<html lang="es">
<head><title>Productos destacados</title></head>
<body>
    <div id="contenedor">
        <h1>Catalogo de Productos</h1>
        <table id="tabla-productos" class="datos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <tr class="fila disponible">
                    <td class="nombre">Monitor 4K 27"</td>
                    <td class="precio">€349.99</td>
                    <td class="stock">15</td>
                </tr>
                <tr class="fila disponible">
                    <td class="nombre">Teclado mecanico RGB</td>
                    <td class="precio">€89.90</td>
                    <td class="stock">42</td>
                </tr>
                <tr class="fila agotado">
                    <td class="nombre">Webcam 4K Pro</td>
                    <td class="precio">€129.00</td>
                    <td class="stock">0</td>
                </tr>
            </tbody>
        </table>
        <p class="info">Ultima actualizacion: <span id="fecha">26/05/2025</span></p>
    </div>
</body>
</html>';

// Suprimir warnings por HTML no estrictamente valido
libxml_use_internal_errors(true);
$domHtml = new DOMDocument();
$domHtml->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors(false);

// Acceso basico al DOM cargado
echo "Titulo de la pagina: " . $domHtml->getElementsByTagName('title')->item(0)->textContent . "\n";
echo "Encabezado H1: " . $domHtml->getElementsByTagName('h1')->item(0)->textContent . "\n";

// Contar filas de la tabla (sin contar thead)
$filas = $domHtml->getElementsByTagName('tr');
echo "Total filas (incluyendo encabezado): {$filas->length}\n";

// Extraer datos de cada fila del tbody
echo "\nProductos encontrados:\n";
$tbody = $domHtml->getElementsByTagName('tbody')->item(0);
if ($tbody) {
    foreach ($tbody->getElementsByTagName('tr') as $fila) {
        $celdas = $fila->getElementsByTagName('td');
        $nombre = $celdas->item(0)->textContent;
        $precio = $celdas->item(1)->textContent;
        $stock  = $celdas->item(2)->textContent;
        $clase  = $fila->getAttribute('class');

        $estado = str_contains($clase, 'agotado') ? '[AGOTADO]' : '[DISPONIBLE]';
        echo "  {$estado} {$nombre} - {$precio} (stock: {$stock})\n";
    }
}

// ============================================================
// Ejemplo 4: DOMXPath - Consultas avanzadas sobre el DOM
// ============================================================
// DOMXPath ofrece capacidades de busqueda mas potentes que
// getElementsByTagName, permitiendo filtros complejos.

echo "\n=== Ejemplo 4: DOMXPath para consultas avanzadas ===\n\n";

$xpath = new DOMXPath($domHtml);

// Buscar por clase CSS (no hay selector nativo, usar contains)
$disponibles = $xpath->query('//tr[contains(@class, "disponible")]');
echo "Productos disponibles ({$disponibles->length}):\n";
foreach ($disponibles as $fila) {
    $nombre = $xpath->query('.//td[@class="nombre"]', $fila)->item(0)->textContent;
    echo "  - {$nombre}\n";
}

// Buscar por ID
$fecha = $xpath->query('//*[@id="fecha"]')->item(0);
echo "\nFecha de actualizacion: {$fecha->textContent}\n";

// Buscar todas las celdas de precio
$precios = $xpath->query('//td[@class="precio"]');
echo "\nTodos los precios:\n";
foreach ($precios as $celda) {
    echo "  {$celda->textContent}\n";
}

// Combinar predicados: filas disponibles con stock > 20
// (XPath opera sobre texto, asi que comparamos como numero)
$stockAlto = $xpath->query('//tr[contains(@class,"disponible")]/td[@class="stock"][number(.) > 20]/..');
echo "\nProductos disponibles con stock > 20:\n";
foreach ($stockAlto as $fila) {
    $nombre = $xpath->query('.//td[@class="nombre"]', $fila)->item(0)->textContent;
    $stock = $xpath->query('.//td[@class="stock"]', $fila)->item(0)->textContent;
    echo "  - {$nombre} (stock: {$stock})\n";
}

// ============================================================
// Ejemplo 5: Modificar nodos en el DOM
// ============================================================
// DOMDocument permite modificar, reemplazar, eliminar y clonar
// nodos de forma granular.

echo "\n=== Ejemplo 5: Modificar nodos del DOM ===\n\n";

$xmlOriginal = '<?xml version="1.0" encoding="UTF-8"?>
<menu restaurante="La Paella Dorada">
    <seccion nombre="Entrantes">
        <plato id="E1"><nombre>Gazpacho andaluz</nombre><precio>6.50</precio></plato>
        <plato id="E2"><nombre>Croquetas caseras</nombre><precio>8.00</precio></plato>
    </seccion>
    <seccion nombre="Principales">
        <plato id="P1"><nombre>Paella valenciana</nombre><precio>14.90</precio></plato>
        <plato id="P2"><nombre>Merluza a la plancha</nombre><precio>16.50</precio></plato>
    </seccion>
</menu>';

$domMenu = new DOMDocument();
$domMenu->preserveWhiteSpace = false;
$domMenu->formatOutput = true;
$domMenu->loadXML($xmlOriginal);

$xpathMenu = new DOMXPath($domMenu);

// Modificar texto de un nodo existente
$gazpacho = $xpathMenu->query('//plato[@id="E1"]/nombre')->item(0);
$gazpacho->textContent = 'Gazpacho andaluz con crujiente de jamon';

// Modificar un atributo
$primerPlato = $xpathMenu->query('//plato[@id="E1"]')->item(0);
$primerPlato->setAttribute('destacado', 'true');

// Actualizar precio (encontrar y modificar)
$precioGazpacho = $xpathMenu->query('//plato[@id="E1"]/precio')->item(0);
$precioGazpacho->textContent = '7.50';

// Agregar un nuevo plato a Principales
$seccionPrincipal = $xpathMenu->query('//seccion[@nombre="Principales"]')->item(0);
$nuevoPlato = $domMenu->createElement('plato');
$nuevoPlato->setAttribute('id', 'P3');
$nuevoPlato->setAttribute('nuevo', 'true');
$nuevoPlato->appendChild($domMenu->createElement('nombre', 'Arroz negro con alioli'));
$nuevoPlato->appendChild($domMenu->createElement('precio', '15.90'));
$seccionPrincipal->appendChild($nuevoPlato);

// Eliminar un nodo (las croquetas se acabaron)
$croquetas = $xpathMenu->query('//plato[@id="E2"]')->item(0);
$croquetas->parentNode->removeChild($croquetas);

// Reemplazar un nodo completamente
$merluza = $xpathMenu->query('//plato[@id="P2"]')->item(0);
$reemplazo = $domMenu->createElement('plato');
$reemplazo->setAttribute('id', 'P2');
$reemplazo->appendChild($domMenu->createElement('nombre', 'Lubina al horno con verduras'));
$reemplazo->appendChild($domMenu->createElement('precio', '18.00'));
$merluza->parentNode->replaceChild($reemplazo, $merluza);

// Clonar un nodo (duplicar plato como base)
$clon = $nuevoPlato->cloneNode(true); // true = clonar hijos tambien
$clon->setAttribute('id', 'P4');
$xpathClon = new DOMXPath($domMenu);
$xpathClon->query('.//nombre', $clon)->item(0)->textContent = 'Arroz negro con sepia (para dos)';
$xpathClon->query('.//precio', $clon)->item(0)->textContent = '28.00';
$seccionPrincipal->appendChild($clon);

echo "Menu modificado:\n";
echo $domMenu->saveXML();

// ============================================================
// Ejemplo 6: Construir plantilla de email HTML (practico)
// ============================================================
// Caso real: generar emails HTML dinamicos usando DOMDocument,
// ideal para plantillas de confirmacion, newsletters, etc.

echo "\n=== Ejemplo 6: Plantilla de email HTML (practico) ===\n\n";

/**
 * Genera un email HTML de confirmacion de pedido.
 *
 * @param array $datosPedido Informacion del pedido.
 * @return string HTML completo del email.
 */
function generarEmailConfirmacion(array $datosPedido): string {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    // Estructura basica del email HTML
    $html = $doc->createElement('html');
    $doc->appendChild($html);

    $head = $doc->createElement('head');
    $html->appendChild($head);

    $meta = $doc->createElement('meta');
    $meta->setAttribute('charset', 'UTF-8');
    $head->appendChild($meta);

    // Estilos inline (obligatorio en emails HTML)
    $style = $doc->createElement('style',
        'body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; } ' .
        '.contenedor { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; } ' .
        '.cabecera { background: #2c3e50; color: white; padding: 20px; text-align: center; } ' .
        '.cuerpo { padding: 20px; } ' .
        '.tabla-productos { width: 100%; border-collapse: collapse; margin: 15px 0; } ' .
        '.tabla-productos th { background: #ecf0f1; padding: 10px; text-align: left; } ' .
        '.tabla-productos td { padding: 10px; border-bottom: 1px solid #eee; } ' .
        '.total { font-size: 18px; font-weight: bold; color: #27ae60; text-align: right; padding: 15px; } ' .
        '.pie { background: #ecf0f1; padding: 15px; text-align: center; font-size: 12px; color: #777; }'
    );
    $head->appendChild($style);

    $body = $doc->createElement('body');
    $html->appendChild($body);

    $contenedor = $doc->createElement('div');
    $contenedor->setAttribute('class', 'contenedor');
    $body->appendChild($contenedor);

    // Cabecera del email
    $cabecera = $doc->createElement('div');
    $cabecera->setAttribute('class', 'cabecera');
    $contenedor->appendChild($cabecera);
    $cabecera->appendChild($doc->createElement('h1', 'Confirmacion de Pedido'));
    $cabecera->appendChild($doc->createElement('p', "Pedido #{$datosPedido['numero']}"));

    // Cuerpo principal
    $cuerpo = $doc->createElement('div');
    $cuerpo->setAttribute('class', 'cuerpo');
    $contenedor->appendChild($cuerpo);

    $saludo = $doc->createElement('p', "Hola {$datosPedido['cliente']},");
    $cuerpo->appendChild($saludo);

    $mensaje = $doc->createElement('p', 'Hemos recibido tu pedido correctamente. Aqui tienes el resumen:');
    $cuerpo->appendChild($mensaje);

    // Tabla de productos
    $tabla = $doc->createElement('table');
    $tabla->setAttribute('class', 'tabla-productos');
    $cuerpo->appendChild($tabla);

    // Encabezado de tabla
    $thead = $doc->createElement('tr');
    $tabla->appendChild($thead);
    foreach (['Producto', 'Cant.', 'Precio', 'Subtotal'] as $encabezado) {
        $thead->appendChild($doc->createElement('th', $encabezado));
    }

    // Filas de productos
    $totalGeneral = 0;
    foreach ($datosPedido['productos'] as $prod) {
        $fila = $doc->createElement('tr');
        $tabla->appendChild($fila);

        $fila->appendChild($doc->createElement('td', $prod['nombre']));
        $fila->appendChild($doc->createElement('td', (string)$prod['cantidad']));
        $fila->appendChild($doc->createElement('td', number_format($prod['precio'], 2) . ' EUR'));

        $subtotal = $prod['cantidad'] * $prod['precio'];
        $fila->appendChild($doc->createElement('td', number_format($subtotal, 2) . ' EUR'));
        $totalGeneral += $subtotal;
    }

    // Total
    $divTotal = $doc->createElement('div');
    $divTotal->setAttribute('class', 'total');
    $divTotal->appendChild($doc->createTextNode(
        'Total (IVA incl.): ' . number_format($totalGeneral * 1.21, 2) . ' EUR'
    ));
    $cuerpo->appendChild($divTotal);

    // Informacion de envio
    $envio = $doc->createElement('div');
    $envio->setAttribute('style', 'background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 15px;');
    $cuerpo->appendChild($envio);
    $envio->appendChild($doc->createElement('h3', 'Direccion de envio'));
    $envio->appendChild($doc->createElement('p', $datosPedido['direccion']));
    $envio->appendChild($doc->createElement('p', "Entrega estimada: {$datosPedido['entrega_estimada']}"));

    // Pie del email
    $pie = $doc->createElement('div');
    $pie->setAttribute('class', 'pie');
    $contenedor->appendChild($pie);
    $pie->appendChild($doc->createElement('p', 'Este email fue generado automaticamente. No responder a esta direccion.'));
    $pie->appendChild($doc->createElement('p', 'ElectroMax S.L. - CIF B12345678 - Madrid, Espana'));

    // saveHTML genera HTML (no XML), lo cual es correcto para emails
    return $doc->saveHTML();
}

// Datos de ejemplo para generar el email
$pedido = [
    'numero'            => 'PED-2025-0847',
    'cliente'           => 'Roberto Garcia',
    'productos'         => [
        ['nombre' => 'Auriculares Bluetooth Pro', 'cantidad' => 1, 'precio' => 79.99],
        ['nombre' => 'Funda protectora universal', 'cantidad' => 2, 'precio' => 12.50],
        ['nombre' => 'Cable USB-C 2m (pack 3)', 'cantidad' => 1, 'precio' => 15.90],
    ],
    'direccion'         => 'Calle Gran Via 42, 3o Dcha, 28013 Madrid',
    'entrega_estimada'  => '28-30 mayo 2025',
];

$emailHtml = generarEmailConfirmacion($pedido);

// Mostrar un fragmento del HTML generado
echo "Email HTML generado (" . strlen($emailHtml) . " bytes):\n";
echo str_repeat("-", 50) . "\n";
// Mostrar las primeras lineas como muestra
$lineas = explode("\n", $emailHtml);
foreach (array_slice($lineas, 0, 25) as $linea) {
    echo $linea . "\n";
}
echo "... (continua) ...\n\n";

// Verificar que el HTML generado se puede parsear de vuelta
libxml_use_internal_errors(true);
$verificar = new DOMDocument();
$verificar->loadHTML($emailHtml);
$erroresHtml = libxml_get_errors();
libxml_clear_errors();
libxml_use_internal_errors(false);

echo "Validacion del HTML generado: ";
echo empty($erroresHtml) ? "sin errores\n" : count($erroresHtml) . " advertencias\n";
echo "Titulo del pedido: " . $verificar->getElementsByTagName('h1')->item(0)->textContent . "\n";

?>
