<?php
/**
 * Proses form login: cek email/password, lalu isi session dan redirect ke halaman utama.
 */
require_once '../proses/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

csrf_validate();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id_user, nama, email, password, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    flash_set('danger', 'Login gagal. Email atau password salah.');
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['login'] = true;
$_SESSION['user_id'] = (int) $user['id_user'];
$_SESSION['name'] = $user['nama'];
$_SESSION['role'] = $user['role'];

$target = 'user_dashboard';
if ($user['role'] === 'admin') {
    $target = 'admin_dashboard';
} elseif ($user['role'] === 'petugas') {
    $target = 'checkin';
}
header('Location: ../index.php?page=' . $target);
exit;