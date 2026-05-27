# Arrays

Funciones nativas de PHP para crear, acceder, manipular, transformar, ordenar y comparar arrays.

## Orden de lectura

1. **`range.php`** - Crear arrays con rangos numéricos o alfabéticos
2. **`compact.php`** - Crear un array a partir de variables y sus valores
3. **`array_fill.php`** - Rellenar un array con un valor dado
4. **`array_fill_keys.php`** - Crear un array con claves específicas y un valor de relleno
5. **`array_combine.php`** - Combinar un array de claves con un array de valores
6. **`array_keys.php`** - Obtener todas las claves de un array
7. **`array_values.php`** - Obtener todos los valores de un array
8. **`array_key_exists.php`** - Verificar si una clave existe en un array
9. **`array_key_first.php`** - Obtener la primera clave de un array
10. **`array_key_last.php`** - Obtener la última clave de un array
11. **`in_array.php`** - Comprobar si un valor existe en un array
12. **`array_search.php`** - Buscar un valor y devolver su clave
13. **`array_push.php`** - Agregar elementos al final de un array
14. **`array_pop.php`** - Extraer el último elemento de un array
15. **`array_unshift.php`** - Agregar elementos al inicio de un array
16. **`array_shift.php`** - Extraer el primer elemento de un array
17. **`array_splice.php`** - Eliminar o reemplazar porciones de un array
18. **`array_slice.php`** - Extraer una porción de un array
19. **`array_pad.php`** - Rellenar un array hasta un tamaño determinado
20. **`current.php`** - Obtener el elemento actual del puntero interno
21. **`pos.php`** - Alias de current para obtener el elemento actual
22. **`next.php`** - Avanzar el puntero interno al siguiente elemento
23. **`prev.php`** - Retroceder el puntero interno al elemento anterior
24. **`end.php`** - Mover el puntero interno al último elemento
25. **`reset.php`** - Mover el puntero interno al primer elemento
26. **`key.php`** - Obtener la clave del elemento actual del puntero
27. **`array_map.php`** - Aplicar una función a cada elemento del array
28. **`array_filter.php`** - Filtrar elementos usando una función callback
29. **`array_reduce.php`** - Reducir un array a un solo valor con una función
30. **`array_walk.php`** - Aplicar una función a cada elemento modificándolo in situ
31. **`array_walk_recursive.php`** - Aplicar una función recursivamente a cada elemento
32. **`sort.php`** - Ordenar un array de menor a mayor
33. **`rsort.php`** - Ordenar un array de mayor a menor
34. **`asort.php`** - Ordenar por valores manteniendo las claves
35. **`arsort.php`** - Ordenar por valores en orden inverso manteniendo las claves
36. **`ksort.php`** - Ordenar un array por sus claves
37. **`krsort.php`** - Ordenar un array por sus claves en orden inverso
38. **`usort.php`** - Ordenar con una función de comparación personalizada
39. **`uasort.php`** - Ordenar por valores con función personalizada manteniendo claves
40. **`uksort.php`** - Ordenar por claves con una función de comparación personalizada
41. **`natsort.php`** - Ordenar usando un algoritmo de orden natural
42. **`natcasesort.php`** - Ordenar con orden natural sin distinguir mayúsculas
43. **`array_multisort.php`** - Ordenar múltiples arrays o un array multidimensional
44. **`array_merge.php`** - Fusionar dos o más arrays
45. **`array_merge_recursive.php`** - Fusionar arrays recursivamente
46. **`array_replace.php`** - Reemplazar valores de un array con los de otro
47. **`array_replace_recursive.php`** - Reemplazar valores recursivamente
48. **`array_diff.php`** - Calcular la diferencia entre arrays por valores
49. **`array_diff_assoc.php`** - Diferencia de arrays comparando claves y valores
50. **`array_diff_key.php`** - Diferencia de arrays comparando solo claves
51. **`array_diff_uassoc.php`** - Diferencia con función de comparación de claves personalizada
52. **`array_diff_ukey.php`** - Diferencia por claves con función personalizada
53. **`array_udiff.php`** - Diferencia por valores con función personalizada
54. **`array_udiff_assoc.php`** - Diferencia con comparación personalizada de valores y normal de claves
55. **`array_udiff_uassoc.php`** - Diferencia con funciones personalizadas para claves y valores
56. **`array_intersect.php`** - Calcular la intersección de arrays por valores
57. **`array_intersect_assoc.php`** - Intersección comparando claves y valores
58. **`array_intersect_key.php`** - Intersección comparando solo claves
59. **`array_intersect_uassoc.php`** - Intersección con función personalizada de comparación de claves
60. **`array_intersect_ukey.php`** - Intersección por claves con función personalizada
61. **`array_uintersect.php`** - Intersección por valores con función personalizada
62. **`array_uintersect_assoc.php`** - Intersección con comparación personalizada de valores y normal de claves
63. **`array_uintersect_uassoc.php`** - Intersección con funciones personalizadas para claves y valores
64. **`count.php`** - Contar el número de elementos de un array
65. **`sizeof.php`** - Alias de count para contar elementos
66. **`list.php`** - Asignar variables desde un array en una sola operación
67. **`extract.php`** - Importar variables a la tabla de símbolos desde un array
68. **`array_is_list.php`** - Verificar si un array es una lista con índices consecutivos
69. **`array_unique.php`** - Eliminar valores duplicados de un array
70. **`array_flip.php`** - Intercambiar claves y valores de un array
71. **`array_reverse.php`** - Devolver un array con los elementos en orden inverso
72. **`array_chunk.php`** - Dividir un array en fragmentos de tamaño fijo
73. **`array_column.php`** - Extraer una columna de un array multidimensional
74. **`array_change_key_case.php`** - Cambiar las claves a mayúsculas o minúsculas
75. **`array_count_values.php`** - Contar las ocurrencias de cada valor
76. **`array_product.php`** - Calcular el producto de los valores de un array
77. **`array_sum.php`** - Calcular la suma de los valores de un array
78. **`array_rand.php`** - Seleccionar claves aleatorias de un array
79. **`shuffle.php`** - Mezclar aleatoriamente los elementos de un array
