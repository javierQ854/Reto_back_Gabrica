<?php
require_once __DIR__ . '/config.php';

$email = 'admin@email.com';
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO usuarios (email, password) VALUES (:email, :password)");
$stmt->execute([
    ':email' => $email,
    ':password' => $hashedPassword
]);

echo "Usuario creado con éxito";
?>