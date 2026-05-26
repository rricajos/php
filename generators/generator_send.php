<?php
/**
 * ============================================================
 * Generator::send() y Generator::throw() en PHP
 * Comunicacion bidireccional con generadores
 * ============================================================
 * Normalmente un generador solo envia valores hacia fuera
 * (con yield). Pero send() permite enviar valores HACIA
 * el generador, y throw() permite inyectar excepciones.
 * Esto convierte a los generadores en corrutinas.
 * ============================================================
 */

// ============================================================
// Ejemplo 1: Generator::send() basico
// ============================================================
// send($valor) reanuda el generador y hace que la expresion
// yield devuelva $valor. Es decir, yield puede ser tanto una
// sentencia (emitir) como una expresion (recibir).

echo "=== Ejemplo 1: send() basico ===\n\n";

/**
 * Generador que recibe numeros y calcula promedios acumulados.
 * Cada vez que se le envia un numero, emite el promedio actual.
 */
function calculadoraPromedio(): Generator {
    $suma = 0;
    $contador = 0;

    // El primer yield es especial: no recibe nada (solo pausa)
    // El valor enviado con send() es lo que yield devuelve
    while (true) {
        $valor = yield ($contador > 0 ? $suma / $contador : 0);

        // $valor contiene lo que se envio con send()
        if ($valor === null) {
            break; // Senal de parada
        }

        $suma += $valor;
        $contador++;
    }

    return "Procesados {$contador} valores. Suma total: {$suma}";
}

$calc = calculadoraPromedio();

// current() o rewind() inicia el generador hasta el primer yield
$calc->current(); // Necesario antes del primer send()

// Enviar valores y recibir el promedio acumulado
$valores = [10, 20, 30, 15, 25];
echo "Calculadora de promedio acumulado:\n";
foreach ($valores as $v) {
    $promedio = $calc->send($v);
    echo "  Enviado: {$v} -> Promedio actual: " . round($promedio, 2) . "\n";
}

// Enviar null para terminar
$calc->send(null);
echo "Resultado final: " . $calc->getReturn() . "\n";

// Explicacion del flujo send():
echo "\nFlujo de send():\n";
echo "  1. Generador se pausa en 'yield \$expresion'\n";
echo "  2. send(\$valor) hace que esa expresion yield devuelva \$valor\n";
echo "  3. El generador continua hasta el siguiente yield\n";
echo "  4. send() retorna lo que el generador emite con yield\n";

// ============================================================
// Ejemplo 2: Generator::throw() para inyectar excepciones
// ============================================================
// throw() lanza una excepcion DENTRO del generador, en el punto
// donde esta pausado. El generador puede capturarla con try/catch.

echo "\n=== Ejemplo 2: throw() - inyeccion de excepciones ===\n\n";

/**
 * Procesador de tareas que puede manejar errores inyectados.
 */
function procesadorTareas(): Generator {
    $completadas = 0;
    $errores = 0;

    while (true) {
        try {
            // yield pausa aqui; send() o throw() lo reanudan
            $tarea = yield "Esperando tarea... (completadas: {$completadas}, errores: {$errores})";

            if ($tarea === null) {
                break;
            }

            echo "  Procesando: {$tarea}\n";
            $completadas++;

        } catch (RuntimeException $e) {
            // El generador captura la excepcion inyectada con throw()
            echo "  [ERROR CAPTURADO] {$e->getMessage()}\n";
            $errores++;
            // El generador sigue vivo despues de manejar la excepcion

        } catch (InvalidArgumentException $e) {
            echo "  [ERROR FATAL] {$e->getMessage()}\n";
            $errores++;
            // Podemos re-lanzar si no queremos manejarla
            // throw $e;
        }
    }

    return ['completadas' => $completadas, 'errores' => $errores];
}

$proc = procesadorTareas();
$estado = $proc->current(); // Iniciar
echo "Estado: {$estado}\n";

// Enviar tareas normales
$proc->send('Generar reporte mensual');
$proc->send('Enviar notificaciones');

// Inyectar un error: el generador lo captura y continua
$estado = $proc->throw(new RuntimeException('Conexion a base de datos perdida'));
echo "Estado despues del error: {$estado}\n";

// El generador sigue funcionando despues del error
$proc->send('Reintentar operacion fallida');

// Otro tipo de error
$proc->throw(new InvalidArgumentException('Formato de datos invalido'));

// Terminar normalmente
$proc->send(null);
$resumen = $proc->getReturn();
echo "\nResumen: Completadas={$resumen['completadas']}, Errores={$resumen['errores']}\n";

// ============================================================
// Ejemplo 3: Corrutina - Logger configurable
// ============================================================
// Una corrutina es un generador que recibe datos con send()
// y los procesa internamente. Es un patron clasico para
// receptores (consumers) de datos.

echo "\n=== Ejemplo 3: Corrutina - Sistema de logging ===\n\n";

/**
 * Corrutina que actua como logger con niveles configurables.
 * Recibe mensajes via send() y los formatea y almacena.
 *
 * @param string $nivelMinimo Nivel minimo para registrar.
 * @param string|null $archivo Si se indica, escribe a archivo.
 * @return Generator Corrutina del logger.
 */
function crearLogger(string $nivelMinimo = 'debug', ?string $archivo = null): Generator {
    $niveles = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];
    $umbral = $niveles[$nivelMinimo] ?? 0;
    $registros = [];
    $contadores = array_fill_keys(array_keys($niveles), 0);
    $handle = null;

    if ($archivo) {
        $handle = fopen($archivo, 'a');
    }

    try {
        while (true) {
            // Recibir mensaje como array ['nivel' => '...', 'mensaje' => '...']
            $entrada = yield count($registros);

            if ($entrada === null) {
                break; // Senal de cierre
            }

            $nivel = $entrada['nivel'] ?? 'info';
            $mensaje = $entrada['mensaje'] ?? '';
            $contexto = $entrada['contexto'] ?? [];

            // Verificar si el nivel cumple el umbral
            $prioridadMensaje = $niveles[$nivel] ?? 0;
            $contadores[$nivel] = ($contadores[$nivel] ?? 0) + 1;

            if ($prioridadMensaje < $umbral) {
                continue; // No registrar mensajes por debajo del umbral
            }

            // Formatear el registro
            $timestamp = date('Y-m-d H:i:s');
            $nivelUpper = strtoupper($nivel);
            $contextoStr = !empty($contexto)
                ? ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE)
                : '';

            $lineaFormateada = "[{$timestamp}] [{$nivelUpper}] {$mensaje}{$contextoStr}";
            $registros[] = $lineaFormateada;

            // Escribir a consola (coloreado segun nivel)
            $prefijo = match ($nivel) {
                'error', 'critical' => '!!',
                'warning'           => '**',
                'info'              => '--',
                default             => '  ',
            };
            echo "  {$prefijo} {$lineaFormateada}\n";

            // Escribir a archivo si esta configurado
            if ($handle) {
                fwrite($handle, $lineaFormateada . "\n");
            }
        }
    } finally {
        if ($handle) {
            fclose($handle);
        }
    }

    return [
        'total_registros'   => count($registros),
        'contadores'        => $contadores,
        'nivel_configurado' => $nivelMinimo,
    ];
}

// Crear logger con nivel minimo 'info' (ignora debug)
$logger = crearLogger('info');
$logger->current(); // Inicializar

// Enviar mensajes de diferentes niveles
$logger->send(['nivel' => 'debug', 'mensaje' => 'Variable $x = 42']);        // Ignorado (debajo del umbral)
$logger->send(['nivel' => 'info', 'mensaje' => 'Usuario autenticado', 'contexto' => ['user_id' => 1001]]);
$logger->send(['nivel' => 'info', 'mensaje' => 'Pagina cargada en 0.23s']);
$logger->send(['nivel' => 'warning', 'mensaje' => 'Cache miss para clave "productos"']);
$logger->send(['nivel' => 'error', 'mensaje' => 'Fallo al conectar con servicio externo', 'contexto' => ['servicio' => 'API_pagos', 'codigo' => 503]]);
$logger->send(['nivel' => 'info', 'mensaje' => 'Reintentando conexion (intento 2/3)']);
$logger->send(['nivel' => 'critical', 'mensaje' => 'Servicio de pagos no disponible']);

// Cerrar y obtener estadisticas
$numRegistros = $logger->send(null); // Senal de cierre
$estadisticas = $logger->getReturn();

echo "\nEstadisticas del logger:\n";
echo "  Registros almacenados: {$estadisticas['total_registros']}\n";
echo "  Nivel configurado: {$estadisticas['nivel_configurado']}\n";
echo "  Mensajes por nivel:\n";
foreach ($estadisticas['contadores'] as $nivel => $count) {
    if ($count > 0) {
        echo "    {$nivel}: {$count}\n";
    }
}

// ============================================================
// Ejemplo 4: Comunicacion bidireccional (ping-pong)
// ============================================================
// El generador emite datos, el consumidor responde con send(),
// y el generador ajusta su comportamiento segun la respuesta.

echo "\n=== Ejemplo 4: Comunicacion bidireccional ===\n\n";

/**
 * Generador que propone precios y acepta ofertas/rechazos.
 * Simula una negociacion de precio entre vendedor y comprador.
 */
function negociadorPrecio(float $precioInicial, float $precioMinimo): Generator {
    $precioActual = $precioInicial;
    $intentos = 0;
    $maxIntentos = 10;

    while ($intentos < $maxIntentos) {
        $intentos++;

        // Emitir la oferta actual del vendedor
        // Recibir la contraoferta del comprador via send()
        $contraoferta = yield [
            'tipo'    => 'oferta_vendedor',
            'precio'  => $precioActual,
            'intento' => $intentos,
            'mensaje' => "Mi precio es {$precioActual} EUR",
        ];

        if ($contraoferta === null) {
            // Comprador se fue
            yield ['tipo' => 'resultado', 'exito' => false, 'razon' => 'Comprador abandono'];
            return;
        }

        $ofertaComprador = (float)$contraoferta;

        if ($ofertaComprador >= $precioActual) {
            // Aceptar: la oferta es igual o mayor al precio pedido
            yield ['tipo' => 'resultado', 'exito' => true, 'precio_final' => $precioActual, 'intentos' => $intentos];
            return;
        }

        if ($ofertaComprador >= $precioMinimo) {
            // La oferta esta entre el minimo y el actual: acercar posiciones
            $precioActual = round(($precioActual + $ofertaComprador) / 2, 2);

            if ($precioActual <= $precioMinimo) {
                $precioActual = $precioMinimo;
            }
        } else {
            // Oferta demasiado baja: bajar un poco pero no hasta el minimo
            $reduccion = ($precioActual - $precioMinimo) * 0.15;
            $precioActual = round($precioActual - $reduccion, 2);

            if ($precioActual < $precioMinimo) {
                $precioActual = $precioMinimo;
            }
        }
    }

    yield ['tipo' => 'resultado', 'exito' => false, 'razon' => 'Maximo de intentos alcanzado'];
}

// Simular una negociacion
$vendedor = negociadorPrecio(1000.00, 700.00);
$ofertaVendedor = $vendedor->current(); // Obtener primera oferta

echo "Negociacion de precio:\n";
echo str_repeat("-", 50) . "\n";

// El comprador ofrece precios incrementales
$ofertasComprador = [500, 600, 650, 720, 800];
$indiceOferta = 0;

while ($ofertaVendedor !== null && isset($ofertaVendedor['tipo'])) {
    if ($ofertaVendedor['tipo'] === 'oferta_vendedor') {
        echo "  Vendedor (intento {$ofertaVendedor['intento']}): {$ofertaVendedor['mensaje']}\n";

        if ($indiceOferta < count($ofertasComprador)) {
            $miOferta = $ofertasComprador[$indiceOferta];
            echo "  Comprador: Ofrezco {$miOferta} EUR\n";
            $ofertaVendedor = $vendedor->send($miOferta);
            $indiceOferta++;
        } else {
            $vendedor->send(null); // Abandonar
            break;
        }
    } elseif ($ofertaVendedor['tipo'] === 'resultado') {
        if ($ofertaVendedor['exito']) {
            echo "\n  ACUERDO alcanzado: {$ofertaVendedor['precio_final']} EUR ";
            echo "(en {$ofertaVendedor['intentos']} intentos)\n";
        } else {
            echo "\n  SIN ACUERDO: {$ofertaVendedor['razon']}\n";
        }
        break;
    }
}

// ============================================================
// Ejemplo 5: Maquina de estados con generadores (practico)
// ============================================================
// Los generadores son perfectos para implementar maquinas de
// estados porque mantienen su estado interno entre yields.

echo "\n=== Ejemplo 5: Maquina de estados (practico) ===\n\n";

/**
 * Maquina de estados para un pedido en una tienda online.
 * Cada estado acepta eventos especificos que causan transiciones.
 *
 * Estados: creado -> pagado -> preparando -> enviado -> entregado
 *          creado -> cancelado
 *          pagado -> reembolsado
 *          enviado -> devuelto
 */
function maquinaEstadoPedido(string $idPedido): Generator {
    $historial = [];
    $estadoActual = 'creado';
    $timestamp = fn() => date('H:i:s');

    $registrar = function(string $de, string $a, string $evento) use (&$historial, $timestamp) {
        $historial[] = [
            'hora'   => $timestamp(),
            'de'     => $de,
            'a'      => $a,
            'evento' => $evento,
        ];
    };

    // Definir transiciones validas por estado
    $transiciones = [
        'creado' => [
            'pagar'    => 'pagado',
            'cancelar' => 'cancelado',
        ],
        'pagado' => [
            'preparar'   => 'preparando',
            'reembolsar' => 'reembolsado',
        ],
        'preparando' => [
            'enviar' => 'enviado',
        ],
        'enviado' => [
            'entregar' => 'entregado',
            'devolver' => 'devuelto',
        ],
        // Estados finales: no tienen transiciones
        'entregado'   => [],
        'cancelado'   => [],
        'reembolsado' => [],
        'devuelto'    => [],
    ];

    $registrar('-', 'creado', 'inicializacion');

    while (true) {
        // Emitir estado actual y esperar un evento
        $evento = yield [
            'pedido'        => $idPedido,
            'estado'        => $estadoActual,
            'transiciones'  => array_keys($transiciones[$estadoActual] ?? []),
            'es_final'      => empty($transiciones[$estadoActual]),
            'historial'     => $historial,
        ];

        if ($evento === null || $evento === 'fin') {
            break;
        }

        // Verificar si la transicion es valida
        $posibles = $transiciones[$estadoActual] ?? [];

        if (!isset($posibles[$evento])) {
            // Transicion invalida: inyectar error informativo
            echo "  [!] Transicion invalida: '{$evento}' desde estado '{$estadoActual}'\n";
            echo "      Eventos validos: " . (empty($posibles) ? 'ninguno (estado final)' : implode(', ', array_keys($posibles))) . "\n";
            continue;
        }

        $estadoAnterior = $estadoActual;
        $estadoActual = $posibles[$evento];
        $registrar($estadoAnterior, $estadoActual, $evento);
    }

    return [
        'estado_final' => $estadoActual,
        'historial'    => $historial,
        'num_transiciones' => count($historial),
    ];
}

// Simular el ciclo de vida de un pedido
$pedido = maquinaEstadoPedido('PED-2025-001');
$info = $pedido->current(); // Estado inicial

echo "Pedido: {$info['pedido']}\n";
echo "Estado inicial: {$info['estado']}\n";
echo "Eventos posibles: " . implode(', ', $info['transiciones']) . "\n\n";

// Ejecutar la secuencia completa de eventos
$eventos = ['pagar', 'preparar', 'cancelar', 'enviar', 'entregar'];

foreach ($eventos as $evento) {
    echo "-> Evento: '{$evento}'\n";
    $info = $pedido->send($evento);

    if ($info !== null) {
        $esFinal = $info['es_final'] ? ' [ESTADO FINAL]' : '';
        echo "   Estado: {$info['estado']}{$esFinal}\n";

        if (!empty($info['transiciones'])) {
            echo "   Siguientes: " . implode(', ', $info['transiciones']) . "\n";
        }
    }
    echo "\n";
}

// Terminar y obtener resumen
$pedido->send('fin');
$resumen = $pedido->getReturn();

echo "Resumen del pedido:\n";
echo "  Estado final: {$resumen['estado_final']}\n";
echo "  Transiciones realizadas: {$resumen['num_transiciones']}\n";
echo "  Historial:\n";
foreach ($resumen['historial'] as $h) {
    echo "    [{$h['hora']}] {$h['de']} -> {$h['a']} (evento: {$h['evento']})\n";
}

// Segundo pedido: con cancelacion
echo "\n--- Segundo pedido (cancelacion) ---\n\n";

$pedido2 = maquinaEstadoPedido('PED-2025-002');
$pedido2->current();

$pedido2->send('cancelar');
$info2 = $pedido2->send('pagar'); // Intentar pagar despues de cancelar

$pedido2->send('fin');
$resumen2 = $pedido2->getReturn();
echo "Pedido 2 finalizo en estado: {$resumen2['estado_final']}\n";

// ============================================================
// Ejemplo 6: Multiplexor de generadores con send()
// ============================================================
// Patron avanzado: un generador central que recibe datos y
// los distribuye entre multiples sub-generadores.

echo "\n=== Ejemplo 6: Multiplexor con send() ===\n\n";

/**
 * Receptor de mensajes por categoria.
 * Corrutina que acumula mensajes de una categoria especifica.
 */
function receptorCategoria(string $categoria): Generator {
    $mensajes = [];

    while (true) {
        $mensaje = yield count($mensajes);

        if ($mensaje === null) {
            break;
        }

        $mensajes[] = $mensaje;
        echo "    [{$categoria}] Recibido: {$mensaje}\n";
    }

    return ['categoria' => $categoria, 'total' => count($mensajes), 'mensajes' => $mensajes];
}

/**
 * Multiplexor que distribuye mensajes a receptores segun categoria.
 * Demuestra como coordinar multiples corrutinas.
 */
function multiplexor(array $categorias): Generator {
    // Crear un receptor por cada categoria
    $receptores = [];
    foreach ($categorias as $cat) {
        $receptor = receptorCategoria($cat);
        $receptor->current(); // Inicializar
        $receptores[$cat] = $receptor;
    }

    $totalDistribuidos = 0;
    $descartados = 0;

    while (true) {
        // Recibir un mensaje con su categoria
        $entrada = yield $totalDistribuidos;

        if ($entrada === null) {
            break;
        }

        $cat = $entrada['categoria'] ?? 'desconocida';
        $msg = $entrada['mensaje'] ?? '';

        if (isset($receptores[$cat])) {
            $receptores[$cat]->send($msg);
            $totalDistribuidos++;
        } else {
            echo "    [DESCARTADO] Categoria desconocida '{$cat}': {$msg}\n";
            $descartados++;
        }
    }

    // Cerrar todos los receptores y recoger sus resultados
    $resultados = [];
    foreach ($receptores as $cat => $receptor) {
        $receptor->send(null); // Cerrar
        $resultados[$cat] = $receptor->getReturn();
    }

    return [
        'total_distribuidos' => $totalDistribuidos,
        'descartados'        => $descartados,
        'por_categoria'      => $resultados,
    ];
}

// Crear multiplexor con 3 categorias
$mux = multiplexor(['ventas', 'soporte', 'sistema']);
$mux->current();

// Enviar mensajes de diferentes categorias
$mensajesEntrantes = [
    ['categoria' => 'ventas',   'mensaje' => 'Nuevo pedido #1234'],
    ['categoria' => 'soporte',  'mensaje' => 'Ticket abierto: error login'],
    ['categoria' => 'ventas',   'mensaje' => 'Pedido #1234 pagado'],
    ['categoria' => 'sistema',  'mensaje' => 'CPU al 85%'],
    ['categoria' => 'marketing','mensaje' => 'Campana lanzada'],  // Categoria inexistente
    ['categoria' => 'soporte',  'mensaje' => 'Ticket resuelto: error login'],
    ['categoria' => 'sistema',  'mensaje' => 'Backup completado'],
    ['categoria' => 'ventas',   'mensaje' => 'Pedido #1234 enviado'],
];

echo "Distribuyendo mensajes:\n";
foreach ($mensajesEntrantes as $msg) {
    $mux->send($msg);
}

// Cerrar y obtener resultados
$mux->send(null);
$resultado = $mux->getReturn();

echo "\nResultado del multiplexor:\n";
echo "  Total distribuidos: {$resultado['total_distribuidos']}\n";
echo "  Descartados: {$resultado['descartados']}\n";
echo "  Por categoria:\n";
foreach ($resultado['por_categoria'] as $cat => $info) {
    echo "    {$cat}: {$info['total']} mensajes\n";
}

?>
