# Encriptacion y seguridad

Funciones criptograficas de PHP para hashing, cifrado, generacion segura de tokens y manejo de contrasenas.

## Orden de lectura

1. **`hash_functions.php`** - Funciones hash(), hash_hmac(), hash_file() y comparacion de algoritmos
2. **`random_bytes.php`** - Generacion criptograficamente segura: tokens, UUIDs v4, contrasenas y sistema de tokens
3. **`password_hash.php`** - Hashing de contrasenas con PASSWORD_DEFAULT, BCRYPT y ARGON2ID
4. **`password_verify.php`** - Verificacion y rehash: password_verify(), password_needs_rehash() y migracion legacy
5. **`openssl_encrypt.php`** - Cifrado simetrico AES-256-CBC/GCM, clase reutilizable y cifrado de archivos
6. **`sodium.php`** - Criptografia moderna con libsodium: secretbox, clave publica y firmas digitales
