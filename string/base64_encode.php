<?php
// Ejemplo 1: Codificar un string en Base64
$texto = "Hola mundo PHP";
$codificado = base64_encode($texto);
echo "Ejemplo 1 (Codificar en Base64):\n";
echo "Original: $texto\n";
echo "Base64: $codificado\n";

// Ejemplo 2: Codificar y decodificar (ida y vuelta)
$decodificado = base64_decode($codificado);
echo "\nEjemplo 2 (Codificar + decodificar):\n";
echo "Decodificado: $decodificado\n";

// Ejemplo 3: Codificar datos binarios
$binario = "\x00\x01\x02\xFF\xFE";
echo "\nEjemplo 3 (Datos binarios):\n";
echo "Base64: " . base64_encode($binario) . "\n";

// Ejemplo 4: Uso práctico - Data URI para imágenes
echo "\nEjemplo 4 (Data URI):\n";
$svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle r="10" fill="red"/></svg>';
$dataUri = "data:image/svg+xml;base64," . base64_encode($svg);
echo "Data URI: " . substr($dataUri, 0, 60) . "...\n";

// Ejemplo 5: Codificar JSON para URL-safe transport
$datos = ["usuario" => "Ana", "rol" => "admin"];
$json = json_encode($datos);
$b64 = base64_encode($json);
echo "\nEjemplo 5 (JSON en Base64):\n";
echo "JSON: $json\n";
echo "Base64: $b64\n";

// Ejemplo 6: Base64 aumenta el tamaño ~33%
$original = "1234567890";
$codificado6 = base64_encode($original);
echo "\nEjemplo 6 (Aumento de tamaño):\n";
echo "Original: " . strlen($original) . " bytes\n";
echo "Base64: " . strlen($codificado6) . " bytes\n";
echo "Aumento: " . round((strlen($codificado6) / strlen($original) - 1) * 100) . "%\n";
?>
