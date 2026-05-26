<?php
// Ejemplo 1: Extraer variables de un array asociativo
$datos = ["nombre" => "Ana", "edad" => 30, "ciudad" => "Madrid"];
extract($datos);
echo "Ejemplo 1 (Extraer variables):\n";
echo "Nombre: $nombre\n";
echo "Edad: $edad\n";
echo "Ciudad: $ciudad\n";

// Ejemplo 2: extract con EXTR_PREFIX_ALL (añade prefijo a todas las variables)
$config = ["host" => "localhost", "port" => 3306, "user" => "admin"];
extract($config, EXTR_PREFIX_ALL, "db");
echo "\nEjemplo 2 (EXTR_PREFIX_ALL):\n";
echo "Host: $db_host\n";
echo "Puerto: $db_port\n";
echo "Usuario: $db_user\n";

// Ejemplo 3: extract con EXTR_SKIP (no sobrescribe variables existentes)
$color = "rojo";
$datos3 = ["color" => "azul", "tamaño" => "grande"];
extract($datos3, EXTR_SKIP);
echo "\nEjemplo 3 (EXTR_SKIP - no sobrescribe):\n";
echo "Color: $color\n"; // rojo (no se sobrescribió)
echo "Tamaño: $tamaño\n"; // grande (se creó)

// Ejemplo 4: extract con EXTR_OVERWRITE (sobrescribe - comportamiento por defecto)
$animal = "gato";
$datos4 = ["animal" => "perro", "raza" => "labrador"];
extract($datos4, EXTR_OVERWRITE);
echo "\nEjemplo 4 (EXTR_OVERWRITE):\n";
echo "Animal: $animal\n"; // perro (se sobrescribió)
echo "Raza: $raza\n";

// Ejemplo 5: extract es lo opuesto a compact
$datos5 = ["lenguaje" => "PHP", "version" => "8.3"];
extract($datos5);
$reconstruido = compact("lenguaje", "version");
echo "\nEjemplo 5 (extract + compact = ida y vuelta):\n";
print_r($reconstruido);

// Ejemplo 6: extract devuelve el número de variables importadas
$datos6 = ["x" => 1, "y" => 2, "z" => 3];
$num = extract($datos6);
echo "\nEjemplo 6 (Número de variables extraídas):\n";
echo "Variables extraídas: $num\n"; // 3
?>
