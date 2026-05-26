<?php
// Ejemplo 1: Número aleatorio criptográficamente seguro
echo "Ejemplo 1 (random_int básico):\n";
echo "random_int(1, 100): " . random_int(1, 100) . "\n";

// Ejemplo 2: Rango grande
echo "\nEjemplo 2 (Rango grande):\n";
echo "random_int(1, PHP_INT_MAX): " . random_int(1, PHP_INT_MAX) . "\n";

// Ejemplo 3: Uso práctico - generar código de verificación
$codigo = random_int(100000, 999999);
echo "\nEjemplo 3 (Código de verificación):\n";
echo "Código: $codigo\n";

// Ejemplo 4: Uso práctico - generar token seguro
$bytes = random_bytes(32);
$token = bin2hex($bytes);
echo "\nEjemplo 4 (Token seguro):\n";
echo "Token: $token\n";

// Ejemplo 5: Comparar seguridad
echo "\nEjemplo 5 (Niveles de seguridad):\n";
echo "rand():       NO seguro (predecible)\n";
echo "mt_rand():    NO seguro (predecible)\n";
echo "random_int(): SÍ seguro (criptográfico)\n";

// Ejemplo 6: Uso práctico - selección aleatoria segura de un array
$premios = ["Coche", "Viaje", "TV", "Móvil", "Nada"];
$indice = random_int(0, count($premios) - 1);
echo "\nEjemplo 6 (Selección aleatoria segura):\n";
echo "Premio: " . $premios[$indice] . "\n";
?>
