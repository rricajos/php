<?php
/**
 * ============================================================
 * XMLReader y XMLWriter en PHP - Procesamiento en streaming
 * ============================================================
 * XMLReader lee XML nodo por nodo sin cargar todo el documento
 * en memoria (ideal para archivos de cientos de MB).
 * XMLWriter genera XML de forma incremental, tambien sin
 * requerir que el documento completo exista en memoria.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: XMLReader para lectura en streaming
// ============================================================
// XMLReader usa un cursor que avanza nodo por nodo. Se consulta
// el tipo de nodo actual (nodeType) para decidir que hacer.

echo "=== Ejemplo 1: XMLReader basico ===\n\n";

// Para el ejemplo, escribimos un XML temporal que simula un
// archivo grande de transacciones bancarias
$xmlTransacciones = '<?xml version="1.0" encoding="UTF-8"?>
<extracto_bancario cuenta="ES91 2100 0418 4502 0005 1332" titular="Ana Morales">
    <periodo desde="2025-05-01" hasta="2025-05-26"/>
    <transacciones>
        <transaccion id="TXN-001" tipo="ingreso">
            <fecha>2025-05-01</fecha>
            <concepto>Nomina mayo 2025</concepto>
            <importe>2450.00</importe>
            <saldo_posterior>5230.50</saldo_posterior>
        </transaccion>
        <transaccion id="TXN-002" tipo="gasto">
            <fecha>2025-05-03</fecha>
            <concepto>Alquiler vivienda</concepto>
            <importe>-850.00</importe>
            <saldo_posterior>4380.50</saldo_posterior>
        </transaccion>
        <transaccion id="TXN-003" tipo="gasto">
            <fecha>2025-05-05</fecha>
            <concepto>Supermercado Mercadona</concepto>
            <importe>-67.35</importe>
            <saldo_posterior>4313.15</saldo_posterior>
        </transaccion>
        <transaccion id="TXN-004" tipo="ingreso">
            <fecha>2025-05-10</fecha>
            <concepto>Transferencia recibida - Pedro Lopez</concepto>
            <importe>150.00</importe>
            <saldo_posterior>4463.15</saldo_posterior>
        </transaccion>
        <transaccion id="TXN-005" tipo="gasto">
            <fecha>2025-05-15</fecha>
            <concepto>Recibo electricidad Iberdrola</concepto>
            <importe>-112.40</importe>
            <saldo_posterior>4350.75</saldo_posterior>
        </transaccion>
    </transacciones>
</extracto_bancario>';

// Guardar en archivo temporal para simular lectura de archivo grande
$archivoTemp = tempnam(sys_get_temp_dir(), 'xml_');
file_put_contents($archivoTemp, $xmlTransacciones);

$reader = new XMLReader();
$reader->open($archivoTemp);

echo "Leyendo extracto bancario con XMLReader:\n";
echo str_repeat("-", 55) . "\n";

// Tipos de nodo mas comunes de XMLReader:
// XMLReader::ELEMENT (1)           - Apertura de etiqueta
// XMLReader::END_ELEMENT (15)      - Cierre de etiqueta
// XMLReader::TEXT (3)              - Contenido de texto
// XMLReader::CDATA (4)            - Seccion CDATA
// XMLReader::SIGNIFICANT_WHITESPACE (14)

$transaccionActual = [];
$campoActual = '';

while ($reader->read()) {
    switch ($reader->nodeType) {
        case XMLReader::ELEMENT:
            if ($reader->localName === 'extracto_bancario') {
                $cuenta = $reader->getAttribute('cuenta');
                $titular = $reader->getAttribute('titular');
                echo "Cuenta: {$cuenta}\n";
                echo "Titular: {$titular}\n\n";
            }

            if ($reader->localName === 'periodo') {
                $desde = $reader->getAttribute('desde');
                $hasta = $reader->getAttribute('hasta');
                echo "Periodo: {$desde} al {$hasta}\n";
                echo str_repeat("-", 55) . "\n";
            }

            if ($reader->localName === 'transaccion') {
                $transaccionActual = [
                    'id'   => $reader->getAttribute('id'),
                    'tipo' => $reader->getAttribute('tipo'),
                ];
            }

            // Registrar que campo estamos leyendo
            if (in_array($reader->localName, ['fecha', 'concepto', 'importe', 'saldo_posterior'])) {
                $campoActual = $reader->localName;
            }
            break;

        case XMLReader::TEXT:
            // Asignar el texto al campo actual de la transaccion
            if (!empty($campoActual) && !empty($transaccionActual)) {
                $transaccionActual[$campoActual] = trim($reader->value);
                $campoActual = '';
            }
            break;

        case XMLReader::END_ELEMENT:
            if ($reader->localName === 'transaccion') {
                // Transaccion completa, procesarla
                $signo = ($transaccionActual['tipo'] === 'ingreso') ? '+' : '';
                echo "[{$transaccionActual['id']}] {$transaccionActual['fecha']} ";
                echo "| {$signo}{$transaccionActual['importe']} EUR ";
                echo "| {$transaccionActual['concepto']}\n";
                echo "  Saldo: {$transaccionActual['saldo_posterior']} EUR\n";

                $transaccionActual = [];
            }
            break;
    }
}

$reader->close();
unlink($archivoTemp); // Limpiar archivo temporal

// ============================================================
// Ejemplo 2: Extraer datos selectivos mientras se lee
// ============================================================
// La ventaja de XMLReader es que podemos saltar nodos que no
// nos interesan y solo procesar los relevantes.

echo "\n=== Ejemplo 2: Extraccion selectiva con XMLReader ===\n\n";

$xmlProductos = '<?xml version="1.0" encoding="UTF-8"?>
<inventario>
    <categoria nombre="Electronica">
        <producto sku="ELEC-001" activo="true">
            <nombre>Television LED 55"</nombre>
            <precio>599.99</precio>
            <stock>23</stock>
        </producto>
        <producto sku="ELEC-002" activo="false">
            <nombre>Reproductor DVD</nombre>
            <precio>49.99</precio>
            <stock>0</stock>
        </producto>
        <producto sku="ELEC-003" activo="true">
            <nombre>Barra de sonido</nombre>
            <precio>189.90</precio>
            <stock>8</stock>
        </producto>
    </categoria>
    <categoria nombre="Informatica">
        <producto sku="INFO-001" activo="true">
            <nombre>Portatil ultraligero 14"</nombre>
            <precio>899.00</precio>
            <stock>12</stock>
        </producto>
        <producto sku="INFO-002" activo="true">
            <nombre>Monitor curvo 32"</nombre>
            <precio>449.50</precio>
            <stock>5</stock>
        </producto>
    </categoria>
</inventario>';

$reader2 = new XMLReader();
$reader2->XML($xmlProductos); // Cargar desde cadena en vez de archivo

// Objetivo: extraer solo productos activos con stock > 0
// y calcular el valor total del inventario activo

$productosActivos = [];
$valorTotal = 0;

while ($reader2->read()) {
    // Solo nos interesan los elementos <producto>
    if ($reader2->nodeType === XMLReader::ELEMENT && $reader2->localName === 'producto') {

        $activo = $reader2->getAttribute('activo');
        $sku = $reader2->getAttribute('sku');

        if ($activo !== 'true') {
            // Saltar este producto entero (no leer sus hijos)
            // Nota: esto no existe como metodo nativo, pero podemos
            // usar expand() + SimpleXML, o simplemente continuar
            continue;
        }

        // Convertir el nodo actual a SimpleXML para lectura facil
        // expand() crea un DOMNode del subarbol actual
        $nodo = $reader2->expand();
        $simple = simplexml_import_dom($nodo);

        $stock = (int)$simple->stock;
        $precio = (float)$simple->precio;

        if ($stock > 0) {
            $productosActivos[] = [
                'sku'    => $sku,
                'nombre' => (string)$simple->nombre,
                'precio' => $precio,
                'stock'  => $stock,
                'valor'  => $precio * $stock,
            ];
            $valorTotal += $precio * $stock;
        }
    }
}
$reader2->close();

echo "Productos activos con stock:\n";
echo str_repeat("-", 65) . "\n";
echo sprintf("%-12s %-30s %8s %5s %10s\n", 'SKU', 'Producto', 'Precio', 'Stock', 'Valor');
echo str_repeat("-", 65) . "\n";

foreach ($productosActivos as $p) {
    echo sprintf(
        "%-12s %-30s %8.2f %5d %10.2f\n",
        $p['sku'], $p['nombre'], $p['precio'], $p['stock'], $p['valor']
    );
}
echo str_repeat("-", 65) . "\n";
echo sprintf("%57s %10.2f EUR\n", 'Valor total inventario activo:', $valorTotal);

// ============================================================
// Ejemplo 3: XMLWriter para generar XML
// ============================================================
// XMLWriter construye XML de forma fluida con metodos para
// abrir/cerrar elementos, escribir atributos y texto.

echo "\n=== Ejemplo 3: XMLWriter para generar documentos ===\n\n";

$writer = new XMLWriter();
$writer->openMemory(); // Escribir en memoria (tambien puede ser archivo)
$writer->setIndent(true);
$writer->setIndentString('    '); // 4 espacios de indentacion

// Iniciar el documento XML
$writer->startDocument('1.0', 'UTF-8');

// Elemento raiz
$writer->startElement('factura');
$writer->writeAttribute('numero', 'FAC-2025-1234');
$writer->writeAttribute('fecha', '2025-05-26');

// Datos del emisor
$writer->startElement('emisor');
$writer->writeElement('nombre', 'Soluciones Tech S.L.');
$writer->writeElement('cif', 'B98765432');
$writer->writeElement('direccion', 'Calle Innovacion 15, 28001 Madrid');
$writer->writeElement('email', 'facturacion@soltech.es');
$writer->endElement(); // </emisor>

// Datos del receptor
$writer->startElement('receptor');
$writer->writeElement('nombre', 'Empresa Cliente S.A.');
$writer->writeElement('cif', 'A12345678');
$writer->writeElement('direccion', 'Av. Comercio 88, 08001 Barcelona');
$writer->endElement(); // </receptor>

// Conceptos de la factura
$conceptos = [
    ['desc' => 'Desarrollo web - portal corporativo', 'horas' => 80, 'tarifa' => 65.00],
    ['desc' => 'Diseno UX/UI - prototipado',          'horas' => 40, 'tarifa' => 55.00],
    ['desc' => 'Configuracion servidor y despliegue',  'horas' => 16, 'tarifa' => 75.00],
];

$writer->startElement('conceptos');

$baseImponible = 0;
foreach ($conceptos as $i => $concepto) {
    $importe = $concepto['horas'] * $concepto['tarifa'];
    $baseImponible += $importe;

    $writer->startElement('concepto');
    $writer->writeAttribute('linea', (string)($i + 1));
    $writer->writeElement('descripcion', $concepto['desc']);
    $writer->writeElement('horas', (string)$concepto['horas']);
    $writer->writeElement('tarifa_hora', number_format($concepto['tarifa'], 2, '.', ''));
    $writer->writeElement('importe', number_format($importe, 2, '.', ''));
    $writer->endElement(); // </concepto>
}

$writer->endElement(); // </conceptos>

// Totales
$iva = $baseImponible * 0.21;
$total = $baseImponible + $iva;

$writer->startElement('totales');
$writer->writeElement('base_imponible', number_format($baseImponible, 2, '.', ''));
$writer->writeElement('iva_porcentaje', '21');
$writer->writeElement('iva_importe', number_format($iva, 2, '.', ''));
$writer->writeElement('total', number_format($total, 2, '.', ''));
$writer->writeElement('moneda', 'EUR');
$writer->endElement(); // </totales>

// Metodo de pago
$writer->startElement('pago');
$writer->writeElement('metodo', 'Transferencia bancaria');
$writer->writeElement('iban', 'ES91 2100 0418 4502 0005 1332');
$writer->writeElement('vencimiento', '2025-06-26');
$writer->endElement(); // </pago>

// Notas con CDATA (para texto que puede contener caracteres especiales)
$writer->startElement('notas');
$writer->writeCdata('Factura generada automaticamente. Condiciones: pago a 30 dias. ' .
    'Penalizacion por retraso: 1.5% mensual sobre el importe pendiente.');
$writer->endElement(); // </notas>

$writer->endElement(); // </factura>
$writer->endDocument();

// Obtener el XML generado
$xmlFactura = $writer->outputMemory();
echo $xmlFactura;

// ============================================================
// Ejemplo 4: XMLWriter a archivo (sin cargar todo en memoria)
// ============================================================
// Para archivos muy grandes, XMLWriter puede escribir
// directamente a disco sin acumular en memoria.

echo "\n=== Ejemplo 4: XMLWriter a archivo ===\n\n";

$archivoSalida = tempnam(sys_get_temp_dir(), 'xmlw_');

$writerArchivo = new XMLWriter();
$writerArchivo->openURI($archivoSalida); // Escribir directamente al archivo
$writerArchivo->setIndent(true);
$writerArchivo->setIndentString('  ');

$writerArchivo->startDocument('1.0', 'UTF-8');
$writerArchivo->startElement('registros');
$writerArchivo->writeAttribute('generado', date('Y-m-d H:i:s'));

// Simular escritura de muchos registros (en produccion podrian ser miles)
$totalRegistros = 50;
for ($i = 1; $i <= $totalRegistros; $i++) {
    $writerArchivo->startElement('registro');
    $writerArchivo->writeAttribute('id', (string)$i);
    $writerArchivo->writeElement('timestamp', date('Y-m-d H:i:s', strtotime("+{$i} minutes")));
    $writerArchivo->writeElement('nivel', ['info', 'warning', 'error'][array_rand(['info', 'warning', 'error'])]);
    $writerArchivo->writeElement('mensaje', "Evento de prueba numero {$i}");

    // flush() escribe al disco y libera memoria periodicamente
    if ($i % 10 === 0) {
        $writerArchivo->flush();
    }

    $writerArchivo->endElement(); // </registro>
}

$writerArchivo->endElement(); // </registros>
$writerArchivo->endDocument();
$writerArchivo->flush();

$tamano = filesize($archivoSalida);
echo "Archivo generado: {$archivoSalida}\n";
echo "Tamano: " . number_format($tamano) . " bytes\n";
echo "Registros escritos: {$totalRegistros}\n";

// Verificar integridad leyendo con XMLReader
$readerVerifica = new XMLReader();
$readerVerifica->open($archivoSalida);
$contadorVerifica = 0;
while ($readerVerifica->read()) {
    if ($readerVerifica->nodeType === XMLReader::ELEMENT && $readerVerifica->localName === 'registro') {
        $contadorVerifica++;
    }
}
$readerVerifica->close();
echo "Verificacion: {$contadorVerifica} registros leidos correctamente.\n";

unlink($archivoSalida);

// ============================================================
// Ejemplo 5: Combinando XMLReader y XMLWriter (transformacion)
// ============================================================
// Patron comun: leer un XML grande, transformar datos y escribir
// otro XML de salida, todo sin cargar el documento completo.

echo "\n=== Ejemplo 5: Transformacion Reader -> Writer ===\n\n";

$xmlEntrada = '<?xml version="1.0" encoding="UTF-8"?>
<pedidos>
    <pedido id="1" fecha="2025-05-20">
        <cliente>Carlos Mendez</cliente>
        <producto>Laptop Gaming</producto>
        <precio_base>1200.00</precio_base>
        <descuento_porcentaje>10</descuento_porcentaje>
    </pedido>
    <pedido id="2" fecha="2025-05-21">
        <cliente>Laura Jimenez</cliente>
        <producto>Monitor UltraWide</producto>
        <precio_base>650.00</precio_base>
        <descuento_porcentaje>5</descuento_porcentaje>
    </pedido>
    <pedido id="3" fecha="2025-05-22">
        <cliente>Miguel Torres</cliente>
        <producto>Silla Ergonomica</producto>
        <precio_base>400.00</precio_base>
        <descuento_porcentaje>0</descuento_porcentaje>
    </pedido>
</pedidos>';

// Leer con XMLReader y escribir version procesada con XMLWriter
$reader3 = new XMLReader();
$reader3->XML($xmlEntrada);

$writer3 = new XMLWriter();
$writer3->openMemory();
$writer3->setIndent(true);
$writer3->startDocument('1.0', 'UTF-8');
$writer3->startElement('reporte_ventas');
$writer3->writeAttribute('generado', '2025-05-26');

$totalVentas = 0;
$numPedidos = 0;

while ($reader3->read()) {
    if ($reader3->nodeType === XMLReader::ELEMENT && $reader3->localName === 'pedido') {
        $idPedido = $reader3->getAttribute('id');
        $fecha = $reader3->getAttribute('fecha');

        // Expandir el nodo para leerlo completo como arbol
        $nodo = simplexml_import_dom($reader3->expand());

        $precioBase = (float)$nodo->precio_base;
        $descuento = (float)$nodo->descuento_porcentaje;
        $precioFinal = $precioBase * (1 - $descuento / 100);
        $iva = $precioFinal * 0.21;
        $totalConIva = $precioFinal + $iva;
        $totalVentas += $totalConIva;
        $numPedidos++;

        // Escribir version enriquecida del pedido
        $writer3->startElement('venta');
        $writer3->writeAttribute('pedido_id', $idPedido);
        $writer3->writeAttribute('fecha', $fecha);
        $writer3->writeElement('cliente', (string)$nodo->cliente);
        $writer3->writeElement('producto', (string)$nodo->producto);
        $writer3->writeElement('precio_base', number_format($precioBase, 2, '.', ''));
        $writer3->writeElement('descuento', number_format($precioBase - $precioFinal, 2, '.', ''));
        $writer3->writeElement('precio_neto', number_format($precioFinal, 2, '.', ''));
        $writer3->writeElement('iva', number_format($iva, 2, '.', ''));
        $writer3->writeElement('total', number_format($totalConIva, 2, '.', ''));
        $writer3->endElement();
    }
}

// Resumen al final
$writer3->startElement('resumen');
$writer3->writeElement('total_pedidos', (string)$numPedidos);
$writer3->writeElement('importe_total', number_format($totalVentas, 2, '.', ''));
$writer3->writeElement('ticket_medio', number_format($totalVentas / max($numPedidos, 1), 2, '.', ''));
$writer3->endElement();

$writer3->endElement(); // </reporte_ventas>
$writer3->endDocument();

$reader3->close();

echo "Reporte de ventas transformado:\n";
echo $writer3->outputMemory();

// ============================================================
// Ejemplo 6: Comparativa SimpleXML vs DOM vs XMLReader
// ============================================================
// Cada API tiene su caso de uso ideal. Elegir la correcta
// depende del tamano del documento y las operaciones necesarias.

echo "\n=== Ejemplo 6: Comparativa de APIs XML ===\n\n";

echo "COMPARATIVA: SimpleXML vs DOMDocument vs XMLReader/XMLWriter\n";
echo str_repeat("=", 70) . "\n\n";

$comparativa = [
    [
        'criterio'  => 'Modelo de acceso',
        'simplexml' => 'Arbol en memoria (objeto)',
        'dom'       => 'Arbol en memoria (W3C DOM)',
        'reader'    => 'Cursor secuencial (streaming)',
    ],
    [
        'criterio'  => 'Uso de memoria',
        'simplexml' => 'Alto (carga todo)',
        'dom'       => 'Alto (carga todo)',
        'reader'    => 'Bajo (nodo por nodo)',
    ],
    [
        'criterio'  => 'Facilidad de uso',
        'simplexml' => 'Muy facil (pythonic)',
        'dom'       => 'Medio (verboso)',
        'reader'    => 'Dificil (bajo nivel)',
    ],
    [
        'criterio'  => 'Modificacion XML',
        'simplexml' => 'Basica (add/modify)',
        'dom'       => 'Completa (CRUD total)',
        'reader'    => 'No (solo lectura)',
    ],
    [
        'criterio'  => 'XPath',
        'simplexml' => 'Si (metodo xpath())',
        'dom'       => 'Si (DOMXPath completo)',
        'reader'    => 'No nativo',
    ],
    [
        'criterio'  => 'Archivos grandes',
        'simplexml' => 'No recomendado (>50MB)',
        'dom'       => 'No recomendado (>50MB)',
        'reader'    => 'Ideal (sin limite)',
    ],
    [
        'criterio'  => 'Validacion DTD/XSD',
        'simplexml' => 'No',
        'dom'       => 'Si (validate, schemaValidate)',
        'reader'    => 'Si (setSchema)',
    ],
    [
        'criterio'  => 'Crear XML nuevo',
        'simplexml' => 'Limitado',
        'dom'       => 'Completo',
        'reader'    => 'No (usar XMLWriter)',
    ],
];

echo sprintf("%-22s %-18s %-22s %-18s\n", 'Criterio', 'SimpleXML', 'DOMDocument', 'XMLReader');
echo str_repeat("-", 80) . "\n";

foreach ($comparativa as $fila) {
    echo sprintf(
        "%-22s %-18s %-22s %-18s\n",
        $fila['criterio'],
        $fila['simplexml'],
        $fila['dom'],
        $fila['reader']
    );
}

echo "\nRECOMENDACIONES:\n";
echo str_repeat("-", 70) . "\n";
echo "- Archivo pequeno (<10MB), lectura simple    -> SimpleXML\n";
echo "- Archivo pequeno, modificar/crear XML       -> DOMDocument\n";
echo "- Archivo grande (>50MB), solo lectura        -> XMLReader\n";
echo "- Generar XML grande                          -> XMLWriter\n";
echo "- Parsear HTML real (no XML estricto)         -> DOMDocument + loadHTML\n";
echo "- Transformar XML grande                      -> XMLReader + XMLWriter\n";
echo "- Interoperabilidad (convertir entre APIs):\n";
echo "    simplexml_import_dom(\$domNode)   DOM -> SimpleXML\n";
echo "    dom_import_simplexml(\$sxeNode)   SimpleXML -> DOM\n";
echo "    \$reader->expand() + import       XMLReader -> DOM/SimpleXML\n";

// Ejemplo rapido de interoperabilidad
echo "\nInteroperabilidad (ejemplo):\n";
$sxe = simplexml_load_string('<dato>valor</dato>');
$domNodo = dom_import_simplexml($sxe); // SimpleXML -> DOM
echo "  SimpleXML -> DOM: " . get_class($domNodo) . " con valor '{$domNodo->textContent}'\n";

$domDoc = new DOMDocument();
$domDoc->loadXML('<otro>ejemplo</otro>');
$sxeConvertido = simplexml_import_dom($domDoc); // DOM -> SimpleXML
echo "  DOM -> SimpleXML: " . get_class($sxeConvertido) . " con valor '{$sxeConvertido}'\n";

?>
