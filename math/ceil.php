<?php
// Ejemplo 1: Redondear hacia arriba
echo "Ejemplo 1 (Redondear arriba):\n";
echo "ceil(4.1): " . ceil(4.1) . "\n"; // 5
echo "ceil(4.9): " . ceil(4.9) . "\n"; // 5
echo "ceil(4.0): " . ceil(4.0) . "\n"; // 4

// Ejemplo 2: Con negativos
echo "\nEjemplo 2 (Negativos):\n";
echo "ceil(-4.1): " . ceil(-4.1) . "\n"; // -4
echo "ceil(-4.9): " . ceil(-4.9) . "\n"; // -4

// Ejemplo 3: Uso práctico - calcular páginas necesarias
$total_items = 47;
$items_por_pagina = 10;
$paginas = ceil($total_items / $items_por_pagina);
echo "\nEjemplo 3 (Paginación):\n";
echo "$total_items items / $items_por_pagina por página = $paginas páginas\n";

// Ejemplo 4: Comparar ceil, floor, round
$num = 4.5;
echo "\nEjemplo 4 (ceil vs floor vs round con $num):\n";
echo "ceil:  " . ceil($num) . "\n";   // 5
echo "floor: " . floor($num) . "\n";  // 4
echo "round: " . round($num) . "\n";  // 5
?>
