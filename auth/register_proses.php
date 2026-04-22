<?php
require_once '../proses/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

csrf_validate();

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    flash_set('danger', 'Input tidak valid.');
    header('Location: register.php');
    exit;
}

$check = $pdo->prepare('SELECT id_user FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    flash_set('warning', 'Email sudah terdaftar.');
    header('Location: register.php');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$nama, $email, password_hash($password, PASSWORD_BCRYPT), 'user']);

flash_set('success', 'Registrasi berhasil. Silakan login.');
header('Location: login.php');
exit;
