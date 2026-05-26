<?php
// Ejemplo 1: Decodificar un string Base64
$codificado = "SG9sYSBtdW5kbyBQSFA=";
$decodificado = base64_decode($codificado);
echo "Ejemplo 1 (Decodificar Base64):\n";
echo "Base64: $codificado\n";
echo "Decodificado: $decodificado\n";

// Ejemplo 2: Decodificación estricta (strict mode)
echo "\nEjemplo 2 (Modo estricto):\n";
$valido = base64_decode("SGVsbG8=", true);
$invalido = base64_decode("SGVsbG8!@#", true);
var_dump($valido);   // string
var_dump($invalido); // false (caracteres inválidos)

// Ejemplo 3: Decodificar datos JSON
$b64json = base64_encode('{"nombre":"Ana","edad":30}');
$json = base64_decode($b64json);
$datos = json_decode($json, true);
echo "\nEjemplo 3 (Decodificar JSON en Base64):\n";
echo "Base64: $b64json\n";
echo "JSON: $json\n";
print_r($datos);

// Ejemplo 4: Verificar si un string es Base64 válido
function esBase64Valido(string $str): bool {
    return base64_decode($str, true) !== false && base64_encode(base64_decode($str)) === $str;
}
echo "\nEjemplo 4 (Verificar Base64 válido):\n";
echo "'SGVsbG8=': " . (esBase64Valido("SGVsbG8=") ? "Válido" : "Inválido") . "\n";
echo "'no-base64!': " . (esBase64Valido("no-base64!") ? "Válido" : "Inválido") . "\n";

// Ejemplo 5: Uso práctico - decodificar token JWT (payload)
$jwt_payload = "eyJ1c2VyIjoiQW5hIiwicm9sIjoiYWRtaW4ifQ";
$payload = json_decode(base64_decode($jwt_payload), true);
echo "\nEjemplo 5 (Decodificar payload JWT):\n";
print_r($payload);
?>
