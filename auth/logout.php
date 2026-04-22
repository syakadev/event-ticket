<?php
/**
 * Proses logout: hancurkan session dan redirect ke halaman login.
 */

// Selalu mulai session untuk bisa memanipulasinya
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Hapus semua variabel dari array $_SESSION
$_SESSION = [];

// Hapus cookie session dari browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Hancurkan session di server
session_destroy();

// Arahkan kembali ke halaman login
header('Location: login.php?status=logout');
exit;