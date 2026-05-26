<?php
// Ejemplo 1: Crear un directorio
$dir = sys_get_temp_dir() . '/test_mkdir';
echo "Ejemplo 1 (Crear directorio):\n";
if (mkdir($dir)) {
    echo "Creado: $dir\n";
    echo "Existe: " . (is_dir($dir) ? "Sí" : "No") . "\n";
    rmdir($dir);
}

// Ejemplo 2: Crear con permisos específicos
echo "\nEjemplo 2 (Con permisos 0755):\n";
mkdir($dir, 0755);
echo "Creado con permisos 0755\n";
rmdir($dir);

// Ejemplo 3: Crear directorios anidados (recursive = true)
$nested = sys_get_temp_dir() . '/a/b/c';
echo "\nEjemplo 3 (Directorios anidados):\n";
if (mkdir($nested, 0777, true)) {
    echo "Creado: $nested\n";
}
// Limpiar
rmdir(sys_get_temp_dir() . '/a/b/c');
rmdir(sys_get_temp_dir() . '/a/b');
rmdir(sys_get_temp_dir() . '/a');

// Ejemplo 4: Uso práctico - patrón seguro
echo "\nEjemplo 4 (Patrón seguro):\n";
$ruta = sys_get_temp_dir() . '/uploads/2026/05';
if (!is_dir($ruta)) {
    mkdir($ruta, 0755, true);
    echo "Directorio creado: $ruta\n";
} else {
    echo "Ya existía: $ruta\n";
}
// Limpiar
rmdir(sys_get_temp_dir() . '/uploads/2026/05');
rmdir(sys_get_temp_dir() . '/uploads/2026');
rmdir(sys_get_temp_dir() . '/uploads');
?>
