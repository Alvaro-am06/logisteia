<?php
// Script para generar un hash bcrypt de una contraseña
$password = '1234'; // Cambia aquí la contraseña que quieras
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash bcrypt para la contraseña '$password':\n$hash\n";
