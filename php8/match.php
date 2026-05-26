<?php
// ============================================
// EXPRESIÓN MATCH EN PHP 8.0
// match vs switch, sin break, comparación estricta, condiciones múltiples
// ============================================

// --- Ejemplo 1: match básico vs switch ---
// match es una expresión (retorna un valor), switch es una sentencia

// Forma tradicional con switch
function obtenerDiaSwitchTradicional(int $numero): string {
    switch ($numero) {
        case 1:
            return "Lunes";
        case 2:
            return "Martes";
        case 3:
            return "Miércoles";
        case 4:
            return "Jueves";
        case 5:
            return "Viernes";
        case 6:
            return "Sábado";
        case 7:
            return "Domingo";
        default:
            return "Día inválido";
    }
}

// Equivalente con match: más conciso, sin necesidad de break
function obtenerDia(int $numero): string {
    return match ($numero) {
        1 => "Lunes",
        2 => "Martes",
        3 => "Miércoles",
        4 => "Jueves",
        5 => "Viernes",
        6 => "Sábado",
        7 => "Domingo",
        default => "Día inválido",
    };
}

echo obtenerDia(3) . "\n";  // Miércoles
echo obtenerDia(10) . "\n"; // Día inválido


// --- Ejemplo 2: Comparación estricta (===) ---
// match usa comparación estricta, a diferencia de switch que usa ==

$valor = "0";

// switch compara con == (comparación flexible)
// case 0: coincidiría con "0" porque "0" == 0 es true
$resultadoSwitch = "no coincide";
switch ($valor) {
    case 0:
        $resultadoSwitch = "switch dice: es cero numérico";
        break;
    case "0":
        $resultadoSwitch = "switch dice: es string '0'";
        break;
}

// match compara con === (comparación estricta)
$resultadoMatch = match ($valor) {
    0 => "match dice: es cero numérico",
    "0" => "match dice: es string '0'",
};

echo $resultadoSwitch . "\n"; // switch dice: es cero numérico (¡incorrecto!)
echo $resultadoMatch . "\n";  // match dice: es string '0' (correcto)

// Otro ejemplo de comparación estricta
$entrada = true;
$resultado = match ($entrada) {
    1 => "Es el número 1",
    "1" => "Es el string '1'",
    true => "Es booleano true",
    default => "Otro valor",
};
echo $resultado . "\n"; // Es booleano true


// --- Ejemplo 3: Múltiples condiciones en una sola rama ---
// Podemos agrupar varios valores que comparten el mismo resultado

function clasificarCaracter(string $char): string {
    return match (true) {
        in_array($char, ['a', 'e', 'i', 'o', 'u']) => "Vocal minúscula",
        in_array($char, ['A', 'E', 'I', 'O', 'U']) => "Vocal mayúscula",
        ctype_lower($char) => "Consonante minúscula",
        ctype_upper($char) => "Consonante mayúscula",
        ctype_digit($char) => "Dígito",
        $char === ' ' => "Espacio",
        default => "Carácter especial",
    };
}

echo clasificarCaracter('a') . "\n"; // Vocal minúscula
echo clasificarCaracter('Z') . "\n"; // Consonante mayúscula
echo clasificarCaracter('5') . "\n"; // Dígito
echo clasificarCaracter('@') . "\n"; // Carácter especial

// Agrupar valores con coma para el mismo resultado
function obtenerTemporada(int $mes): string {
    return match ($mes) {
        12, 1, 2 => "Invierno",
        3, 4, 5 => "Primavera",
        6, 7, 8 => "Verano",
        9, 10, 11 => "Otoño",
        default => throw new InvalidArgumentException("Mes inválido: {$mes}"),
    };
}

echo obtenerTemporada(7) . "\n";  // Verano
echo obtenerTemporada(11) . "\n"; // Otoño

try {
    obtenerTemporada(13);
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n"; // Error: Mes inválido: 13
}


// --- Ejemplo 4: match sin argumento (como cadena if/elseif) ---
// match(true) permite evaluar expresiones complejas

function calcularDescuento(float $total, bool $esMiembro, int $antiguedad): array {
    $porcentajeDescuento = match (true) {
        $esMiembro && $antiguedad >= 5 && $total > 500 => 25,
        $esMiembro && $antiguedad >= 5 => 20,
        $esMiembro && $antiguedad >= 2 => 15,
        $esMiembro => 10,
        $total > 1000 => 8,
        $total > 500 => 5,
        default => 0,
    };

    $descuento = round($total * $porcentajeDescuento / 100, 2);

    return [
        'subtotal' => $total,
        'descuento_porcentaje' => $porcentajeDescuento,
        'descuento_monto' => $descuento,
        'total_final' => $total - $descuento,
    ];
}

print_r(calcularDescuento(800.00, true, 6));
// descuento_porcentaje => 25, total_final => 600

print_r(calcularDescuento(300.00, false, 0));
// descuento_porcentaje => 0, total_final => 300

print_r(calcularDescuento(1500.00, false, 0));
// descuento_porcentaje => 8, total_final => 1380


// --- Ejemplo 5: match con expresiones y funciones ---
// Las ramas de match pueden contener llamadas a funciones y expresiones

class Respuesta {
    public function __construct(
        public readonly int $codigo,
        public readonly string $cuerpo
    ) {}
}

function procesarRespuesta(Respuesta $respuesta): string {
    return match (true) {
        $respuesta->codigo >= 200 && $respuesta->codigo < 300
            => "Éxito: {$respuesta->cuerpo}",

        $respuesta->codigo === 301 || $respuesta->codigo === 302
            => "Redirección detectada (código {$respuesta->codigo})",

        $respuesta->codigo === 404
            => "Recurso no encontrado",

        $respuesta->codigo === 429
            => "Demasiadas solicitudes. Intenta más tarde.",

        $respuesta->codigo >= 500
            => "Error del servidor (código {$respuesta->codigo}): contactar soporte",

        default => "Respuesta inesperada: código {$respuesta->codigo}",
    };
}

$respuestas = [
    new Respuesta(200, '{"datos": "ok"}'),
    new Respuesta(301, 'Moved'),
    new Respuesta(404, 'Not Found'),
    new Respuesta(500, 'Internal Server Error'),
];

foreach ($respuestas as $resp) {
    echo procesarRespuesta($resp) . "\n";
}
// Éxito: {"datos": "ok"}
// Redirección detectada (código 301)
// Recurso no encontrado
// Error del servidor (código 500): contactar soporte

?>
