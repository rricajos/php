<?php
// Ejemplo 1: Verificar fecha válida
echo "Ejemplo 1 (Fechas válidas):\n";
var_dump(checkdate(12, 31, 2026)); // true
var_dump(checkdate(2, 28, 2026));  // true
var_dump(checkdate(6, 15, 2026));  // true

// Ejemplo 2: Fechas inválidas
echo "\nEjemplo 2 (Fechas inválidas):\n";
var_dump(checkdate(13, 1, 2026));  // false (mes 13)
var_dump(checkdate(2, 30, 2026));  // false (febrero no tiene 30 días)
var_dump(checkdate(4, 31, 2026));  // false (abril tiene 30 días)
var_dump(checkdate(0, 1, 2026));   // false (mes 0)

// Ejemplo 3: Años bisiestos
echo "\nEjemplo 3 (Años bisiestos):\n";
echo "29 Feb 2024 (bisiesto): " . (checkdate(2, 29, 2024) ? "válida" : "inválida") . "\n";
echo "29 Feb 2025 (no bisiesto): " . (checkdate(2, 29, 2025) ? "válida" : "inválida") . "\n";
echo "29 Feb 2028 (bisiesto): " . (checkdate(2, 29, 2028) ? "válida" : "inválida") . "\n";

// Ejemplo 4: Uso práctico - validar input de usuario
function validarFecha(string $fecha): bool {
    $partes = explode("/", $fecha);
    if (count($partes) !== 3) return false;
    [$dia, $mes, $anio] = $partes;
    return checkdate((int)$mes, (int)$dia, (int)$anio);
}
echo "\nEjemplo 4 (Validar input):\n";
$fechas = ["15/06/2026", "31/02/2026", "29/02/2024", "00/01/2026"];
foreach ($fechas as $fecha) {
    $valida = validarFecha($fecha) ? "Válida" : "Inválida";
    echo "  $fecha: $valida\n";
}

// Ejemplo 5: checkdate(mes, día, año) - ¡ojo con el orden de parámetros!
echo "\nEjemplo 5 (Orden de parámetros):\n";
echo "checkdate(mes, día, año)\n";
echo "checkdate(6, 15, 2026) = junio 15, 2026\n";
?>
