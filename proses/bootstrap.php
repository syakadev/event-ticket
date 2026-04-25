<?php
/**
 * File ini dipanggil pertama di hampir setiap halaman.
 * Isinya: koneksi DB, session login, dan fungsi kecil yang dipakai berulang.
 */
declare(strict_types=1);

require_once __DIR__ . '/../koneksi.php';

// Mulai session kalau belum (untuk simpan data login user)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

/**
 * Batalkan order yang statusnya pending lebih dari 24 jam.
 * Stok tiket (kuota) dan kuota voucher akan dikembalikan.
 * Fungsi ini memiliki throttling untuk tidak berjalan di setiap request.
 *
 * @param PDO $pdo Instance PDO database.
 * @return void
 */
function cancel_expired_orders(PDO $pdo): void
{
    // Throttling: jalankan pengecekan ini maksimal 5 menit sekali per sesi user
    // untuk mengurangi beban di setiap page load.
    $now = time();
    if (isset($_SESSION['last_expired_check']) && ($now - $_SESSION['last_expired_check']) < 300) {
        return;
    }

    try {
        $pdo->beginTransaction();

        // 1. Ambil ID voucher dari order yang akan expired untuk mengembalikan kuotanya.
        $sqlVoucher = "SELECT id_voucher FROM orders
            WHERE status = 'pending'
            AND id_voucher IS NOT NULL
            AND tanggal_order < NOW() - INTERVAL 24 HOUR FOR UPDATE";

        $stmtVoucher = $pdo->query($sqlVoucher);
        $vouchersToRestore = $stmtVoucher->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($vouchersToRestore)) {
            $voucherCounts = array_count_values($vouchersToRestore);
            $stmtRestoreVoucher = $pdo->prepare('UPDATE voucher SET kuota = kuota + ? WHERE id_voucher = ?');
            foreach ($voucherCounts as $idVoucher => $count) {
                $stmtRestoreVoucher->execute([$count, $idVoucher]);
            }
        }

        // 2. Ubah status order menjadi 'cancel'. Kuota tiket akan "kembali" secara otomatis
        // karena query penjualan tidak menghitung order dengan status 'cancel'.
        $sqlCancel = "UPDATE orders SET status = 'cancel' WHERE status = 'pending' AND tanggal_order < NOW() - INTERVAL 24 HOUR";
        $pdo->exec($sqlCancel);

        $pdo->commit();
        $_SESSION['last_expired_check'] = $now; // Update waktu cek jika berhasil
    } catch (Throwable $e) {
        $pdo->rollBack();
        // Gagal, jangan update waktu pengecekan. Bisa ditambahkan logging error di sini.
    }
}

// Jalankan fungsi untuk membatalkan order yang sudah expired.
cancel_expired_orders($pdo);

/** Mengubah teks supaya aman ditampilkan di HTML (mencegah XSS). */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Apakah user sudah login? */
function is_logged_in(): bool
{
    return !empty($_SESSION['login']) && isset($_SESSION['user_id']);
}

/** Role user saat ini: admin, user, atau petugas. */
function current_role(): string
{
    return $_SESSION['role'] ?? '';
}

/**
 * Halaman yang butuh login: kalau belum login, kirim ke halaman login.
 * Kalau $role diisi (misal 'admin'), hanya role itu yang boleh.
 */
function require_login(?string $role = null): void
{
    if (!is_logged_in()) {
        header('Location: auth/login.php');
        exit;
    }

    if ($role !== null && current_role() !== $role) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

/** Sama seperti require_login, tapi boleh beberapa role sekaligus. */
function require_roles(array $roles): void
{
    if (!is_logged_in()) {
        header('Location: auth/login.php');
        exit;
    }
    if (!in_array(current_role(), $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

/** Token anti-CSRF untuk form (mencegah kiriman form palsu dari situs lain). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Cek token POST sama dengan yang di session. */
function csrf_validate(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        exit('Token keamanan tidak valid.');
    }
}

/** Simpan pesan singkat untuk ditampilkan sekali di halaman berikutnya. */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil pesan flash lalu hapus dari session. */
function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Total kuota terpakai untuk satu jenis tiket: reservasi (pending/paid) + tiket yang sudah diterbitkan (accepted).
 * Order baru menyimpan id_tiket+qty di tabel orders; setelah admin menerima, hitung lewat order_detail.
 */
function tiket_terjual(PDO $pdo, int $idTiket): int
{
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(od.qty), 0) FROM order_detail od
         INNER JOIN orders o ON o.id_order = od.id_order
         WHERE od.id_tiket = ? AND o.status IN ('pending', 'paid')"
    );
    $stmt->execute([$idTiket]);
    return (int) $stmt->fetchColumn();
}

/** Kode tiket unik untuk peserta. */
function ticket_code(): string
{
    return 'TIX-' . strtoupper(bin2hex(random_bytes(5)));
}