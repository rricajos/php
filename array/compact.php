<?php
// Ejemplo 1: Crear un array a partir de variables
$nombre = "Ana";
$edad = 30;
$ciudad = "Madrid";
$resultado1 = compact("nombre", "edad", "ciudad");
echo "Ejemplo 1 (Crear array desde variables):\n";
print_r($resultado1);

// Ejemplo 2: compact con un array de nombres de variables
$fruta = "manzana";
$color = "rojo";
$precio = 1.50;
$campos = ["fruta", "color", "precio"];
$resultado2 = compact($campos);
echo "\nEjemplo 2 (Usando un array de nombres):\n";
print_r($resultado2);

// Ejemplo 3: compact ignora variables que no existen
$x = 10;
$y = 20;
$resultado3 = compact("x", "y", "z"); // "z" no existe
echo "\nEjemplo 3 (Variable inexistente 'z' se ignora):\n";
print_r($resultado3);

// Ejemplo 4: compact es lo opuesto a extract
$producto = "laptop";
$marca = "HP";
$stock = 15;
$datos = compact("producto", "marca", "stock");
echo "\nEjemplo 4 (compact crea el array):\n";
print_r($datos);
// extract($datos) haría lo contrario: crearía las variables desde el array

// Ejemplo 5: Uso práctico - pasar datos a una plantilla
$titulo = "Mi Página";
$contenido = "Bienvenido al sitio";
$autor = "Carlos";
$vista = compact("titulo", "contenido", "autor");
echo "\nEjemplo 5 (Datos para una plantilla):\n";
print_r($vista);
?>
