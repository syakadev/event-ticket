<?php
/**
 * Pusat routing: baca ?page= dan ?action= lalu tampilkan view atau proses form.
 * Urutan: proses POST dulu, baru tampil halaman.
 */
declare(strict_types=1);

require_once __DIR__ . '/proses/bootstrap.php';
require_login();

$halaman = $_GET['page'] ?? (current_role() === 'admin' ? 'admin_dashboard' : 'user_dashboard');
$aksi = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

// ---------- Admin: Venue ----------
if ($aksi === 'save_venue') {
    require_login('admin');
    $idVenue = (int) ($_POST['id'] ?? 0);
    $namaVenue = trim($_POST['nama_venue'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kapasitas = (int) ($_POST['kapasitas'] ?? 0);

    if ($idVenue > 0) {
        $sql = 'UPDATE venue SET nama_venue=?, alamat=?, kapasitas=? WHERE id_venue=?';
        $pdo->prepare($sql)->execute([$namaVenue, $alamat, $kapasitas, $idVenue]);
    } else {
        $sql = 'INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES (?,?,?)';
        $pdo->prepare($sql)->execute([$namaVenue, $alamat, $kapasitas]);
    }
    flash_set('success', 'Data venue tersimpan.');
    header('Location: index.php?page=venue');
    exit;
}

if ($aksi === 'delete_venue') {
    require_login('admin');
    $idVenue = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM venue WHERE id_venue = ?')->execute([$idVenue]);
    flash_set('success', 'Venue dihapus.');
    header('Location: index.php?page=venue');
    exit;
}

// ---------- Admin: Event ----------
if ($aksi === 'save_event') {
    require_login('admin');
    $idEvent = (int) ($_POST['id'] ?? 0);
    $namaEvent = trim($_POST['nama_event'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $idVenue = (int) ($_POST['id_venue'] ?? 0);

    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambarName = uniqid('evt_', true) . '.' . $ext;
        $targetDir = __DIR__ . '/img/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetDir . $gambarName)) {
            $gambar = $gambarName;
        }
    }

    if ($idEvent > 0) {
        if ($gambar) {
            $old = $pdo->prepare('SELECT gambar FROM event WHERE id_event=?');
            $old->execute([$idEvent]);
            $oldGambar = $old->fetchColumn();
            if ($oldGambar && file_exists(__DIR__ . '/img/' . $oldGambar)) {
                unlink(__DIR__ . '/img/' . $oldGambar);
            }
            $sql = 'UPDATE event SET nama_event=?, tanggal=?, id_venue=?, gambar=? WHERE id_event=?';
            $pdo->prepare($sql)->execute([$namaEvent, $tanggal, $idVenue, $gambar, $idEvent]);
        } else {
            $sql = 'UPDATE event SET nama_event=?, tanggal=?, id_venue=? WHERE id_event=?';
            $pdo->prepare($sql)->execute([$namaEvent, $tanggal, $idVenue, $idEvent]);
        }
    } else {
        $sql = 'INSERT INTO event (nama_event, tanggal, id_venue, gambar) VALUES (?,?,?,?)';
        $pdo->prepare($sql)->execute([$namaEvent, $tanggal, $idVenue, $gambar]);
    }
    flash_set('success', 'Data event tersimpan.');
    header('Location: index.php?page=event');
    exit;
}

if ($aksi === 'delete_event') {
    require_login('admin');
    $idEvent = (int) ($_POST['id'] ?? 0);

    $old = $pdo->prepare('SELECT gambar FROM event WHERE id_event=?');
    $old->execute([$idEvent]);
    $oldGambar = $old->fetchColumn();
    if ($oldGambar && file_exists(__DIR__ . '/img/' . $oldGambar)) {
        unlink(__DIR__ . '/img/' . $oldGambar);
    }

    $pdo->prepare('DELETE FROM event WHERE id_event=?')->execute([$idEvent]);
    flash_set('success', 'Event dihapus.');
    header('Location: index.php?page=event');
    exit;
}

// ---------- Admin: Tiket ----------
if ($aksi === 'save_tiket') {
    require_login('admin');
    $idTiket = (int) ($_POST['id'] ?? 0);
    $idEvent = (int) ($_POST['id_event'] ?? 0);
    $namaTiket = trim($_POST['nama_tiket'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $kuota = (int) ($_POST['kuota'] ?? 0);

    if ($idEvent <= 0 || $namaTiket === '' || $harga < 0) {
        flash_set('danger', 'Data tiket tidak valid.');
        header('Location: index.php?page=tiket' . ($idTiket > 0 ? '&edit=' . $idTiket : ''));
        exit;
    }

    if ($idTiket > 0) {
        $terjual = tiket_terjual($pdo, $idTiket);
        if ($kuota < $terjual) {
            flash_set('danger', 'Kuota tidak boleh kurang dari jumlah yang sudah terjual (' . $terjual . ').');
            header('Location: index.php?page=tiket&edit=' . $idTiket);
            exit;
        }
        if ($kuota < 1) {
            flash_set('danger', 'Kuota minimal 1.');
            header('Location: index.php?page=tiket&edit=' . $idTiket);
            exit;
        }
        $sql = 'UPDATE tiket SET id_event=?, nama_tiket=?, harga=?, kuota=? WHERE id_tiket=?';
        $pdo->prepare($sql)->execute([$idEvent, $namaTiket, $harga, $kuota, $idTiket]);
    } else {
        if ($kuota < 1) {
            flash_set('danger', 'Kuota minimal 1.');
            header('Location: index.php?page=tiket');
            exit;
        }
        $sql = 'INSERT INTO tiket (id_event, nama_tiket, harga, kuota) VALUES (?,?,?,?)';
        $pdo->prepare($sql)->execute([$idEvent, $namaTiket, $harga, $kuota]);
    }
    flash_set('success', 'Data tiket tersimpan.');
    header('Location: index.php?page=tiket');
    exit;
}

if ($aksi === 'delete_tiket') {
    require_login('admin');
    $idTiket = (int) ($_POST['id'] ?? 0);
    if ($idTiket <= 0) {
        flash_set('danger', 'Tiket tidak valid.');
        header('Location: index.php?page=tiket');
        exit;
    }
    if (tiket_terjual($pdo, $idTiket) > 0) {
        flash_set('danger', 'Tiket tidak bisa dihapus karena sudah pernah dipesan (ada order yang tercatat).');
        header('Location: index.php?page=tiket');
        exit;
    }
    $pdo->prepare('DELETE FROM tiket WHERE id_tiket=?')->execute([$idTiket]);
    flash_set('success', 'Tiket dihapus.');
    header('Location: index.php?page=tiket');
    exit;
}

// ---------- Admin: Voucher ----------
if ($aksi === 'save_voucher') {
    require_login('admin');
    $idVoucher = (int) ($_POST['id'] ?? 0);
    $kodeVoucher = strtoupper(trim($_POST['kode_voucher'] ?? ''));
    $potongan = (int) ($_POST['potongan'] ?? 0);
    $kuotaVoucher = (int) ($_POST['kuota'] ?? 0);
    $statusVoucher = $_POST['status'] ?? '';

    if ($idVoucher > 0) {
        $sql = 'UPDATE voucher SET kode_voucher=?, potongan=?, kuota=?, status=? WHERE id_voucher=?';
        $pdo->prepare($sql)->execute([$kodeVoucher, $potongan, $kuotaVoucher, $statusVoucher, $idVoucher]);
    } else {
        $sql = 'INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES (?,?,?,?)';
        $pdo->prepare($sql)->execute([$kodeVoucher, $potongan, $kuotaVoucher, $statusVoucher]);
    }
    flash_set('success', 'Voucher tersimpan.');
    header('Location: index.php?page=voucher');
    exit;
}

if ($aksi === 'delete_voucher') {
    require_login('admin');
    $idVoucher = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM voucher WHERE id_voucher=?')->execute([$idVoucher]);
    flash_set('success', 'Voucher dihapus.');
    header('Location: index.php?page=voucher');
    exit;
}

// ---------- User: Buat pesanan (order) ----------
if ($aksi === 'create_order') {
    require_login('user');
    $idTiket = (int) ($_POST['tiket_id'] ?? 0);
    $jumlah = max(1, (int) ($_POST['qty'] ?? 1));
    $kodeVoucherInput = strtoupper(trim($_POST['voucher_code'] ?? ''));
    $idEventHalaman = (int) ($_GET['id'] ?? 0);

    $pdo->beginTransaction();
    try {
        // Kunci baris tiket supaya dua user tidak memesan melebihi kuota bersamaan
        $sqlTiket = 'SELECT t.id_tiket, t.id_event, t.nama_tiket, t.harga, t.kuota, e.nama_event
            FROM tiket t
            JOIN event e ON e.id_event = t.id_event
            WHERE t.id_tiket = ? FOR UPDATE';
        $stmtTiket = $pdo->prepare($sqlTiket);
        $stmtTiket->execute([$idTiket]);
        $barisTiket = $stmtTiket->fetch();

        if (!$barisTiket) {
            throw new RuntimeException('Tiket tidak ditemukan.');
        }
        if ($idEventHalaman <= 0 || (int) $barisTiket['id_event'] !== $idEventHalaman) {
            throw new RuntimeException('Tiket tidak sesuai dengan halaman event.');
        }

        $sqlTerjual = "SELECT COALESCE(SUM(od.qty), 0)
            FROM order_detail od
            JOIN orders o ON o.id_order = od.id_order
            WHERE od.id_tiket = ? AND o.status IN ('pending', 'paid') FOR UPDATE";
        $stmtTerjual = $pdo->prepare($sqlTerjual);
        $stmtTerjual->execute([$idTiket]);
        $terjual = (int) $stmtTerjual->fetchColumn();
        $sisaKuota = (int) $barisTiket['kuota'] - $terjual;

        if ($sisaKuota <= 0) {
            throw new RuntimeException('Tiket sudah habis. Kuota telah terpenuhi.');
        }
        if ($jumlah > $sisaKuota) {
            throw new RuntimeException('Jumlah melebihi kuota tersedia (tersisa ' . $sisaKuota . ').');
        }

        $subtotal = (int) $barisTiket['harga'] * $jumlah;
        $potongan = 0.0;
        $idVoucher = null;

        if ($kodeVoucherInput !== '') {
            $stmtV = $pdo->prepare("SELECT * FROM voucher WHERE kode_voucher = ? AND status = 'aktif' FOR UPDATE");
            $stmtV->execute([$kodeVoucherInput]);
            $barisVoucher = $stmtV->fetch();

            if (!$barisVoucher) {
                throw new RuntimeException('Voucher tidak valid atau tidak aktif.');
            }
            if ((int) $barisVoucher['kuota'] <= 0) {
                throw new RuntimeException('Voucher sudah habis.');
            }

            $idVoucher = (int) $barisVoucher['id_voucher'];
            $potongan = (float) $barisVoucher['potongan'];
            $potongan = min($potongan, (float) $subtotal);
            $pdo->prepare('UPDATE voucher SET kuota = kuota - 1 WHERE id_voucher = ?')->execute([$idVoucher]);
        }

        $totalBayar = (int) round($subtotal - $potongan);
        $pdo->prepare('INSERT INTO orders (id_user, total, status, id_voucher) VALUES (?,?,?,?)')
            ->execute([$_SESSION['user_id'], $totalBayar, 'pending', $idVoucher]);
        $idOrder = (int) $pdo->lastInsertId();

        $pdo->prepare('INSERT INTO order_detail (id_order, id_tiket, qty, subtotal) VALUES (?,?,?,?)')
            ->execute([$idOrder, $idTiket, $jumlah, $subtotal]);
        $idDetail = (int) $pdo->lastInsertId();

        $pdo->commit();
        flash_set('success', 'Order berhasil dibuat. Mohon bayar maksimal 24 jam. Tiket akan terbit setelah dikonfirmasi.');
        $_SESSION['popup_order_baru'] = $idOrder;
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash_set('danger', $e->getMessage());
    }

    header('Location: index.php?page=my_orders');
    exit;
}

// ---------- User: Bayar (ubah status jadi paid) ----------
if ($aksi === 'pay_order') {
    require_login('user');
    $idOrder = (int) ($_POST['order_id'] ?? 0);
    $pdo->prepare("UPDATE orders SET status='paid' WHERE id_order=? AND id_user=? AND status='pending'")
        ->execute([$idOrder, $_SESSION['user_id']]);
    if ($idOrder > 0) {
        $_SESSION['popup_order_paid'] = $idOrder;
    }
    flash_set('success', 'Pembayaran tercatat. Menunggu konfirmasi admin.');
    header('Location: index.php?page=my_orders');
    exit;
}

// ---------- User: Cancel mandiri ----------
if ($aksi === 'user_cancel_order') {
    require_login('user');
    $idOrder = (int) ($_POST['order_id'] ?? 0);
    // User bisa cancel hanya jika pending atau paid (belum generated tiket di logikanya)
    // Untuk amannya, kita cek tiket blm digenerate
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT id_order, status FROM orders WHERE id_order=? AND id_user=? FOR UPDATE");
        $stmt->execute([$idOrder, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        if (!$order) {
            throw new Exception("Order tidak ditemukan.");
        }
        if ($order['status'] === 'cancel') {
            throw new Exception("Order sudah dibatalkan.");
        }
        // Pastikan attendee belum digenerate
        $stmtA = $pdo->prepare("SELECT COUNT(*) FROM attendee a JOIN order_detail od ON od.id_detail=a.id_detail WHERE od.id_order=?");
        $stmtA->execute([$idOrder]);
        if ($stmtA->fetchColumn() > 0) {
            throw new Exception("Order sudah dikonfirmasi admin dan tiket terbit, tidak dapat dibatalkan mandiri.");
        }

        $pdo->prepare("UPDATE orders SET status='cancel' WHERE id_order=?")->execute([$idOrder]);
        $pdo->commit();
        flash_set('success', 'Pesanan berhasil dibatalkan.');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash_set('danger', $e->getMessage());
    }
    header('Location: index.php?page=my_orders');
    exit;
}

// ---------- Admin: Konfirmasi (Accept) pembayaran ----------
if ($aksi === 'accept_order') {
    require_roles(['admin', 'petugas']);
    $idOrder = (int) ($_POST['order_id'] ?? 0);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id_order=? AND status='paid' FOR UPDATE");
        $stmt->execute([$idOrder]);
        $order = $stmt->fetch();
        if ($order) {
            // Generate tiket
            $stmtDet = $pdo->prepare("SELECT id_detail, qty FROM order_detail WHERE id_order=?");
            $stmtDet->execute([$idOrder]);
            $details = $stmtDet->fetchAll();
            foreach ($details as $d) {
                // cek apakah udah ada tiketnya
                $checkA = $pdo->prepare("SELECT COUNT(*) FROM attendee WHERE id_detail=?");
                $checkA->execute([$d['id_detail']]);
                if ($checkA->fetchColumn() == 0) {
                    for ($i = 0; $i < $d['qty']; $i++) {
                        $pdo->prepare('INSERT INTO attendee (id_detail, kode_tiket) VALUES (?,?)')
                            ->execute([$d['id_detail'], ticket_code()]);
                    }
                }
            }
            $pdo->commit();
            flash_set('success', 'Pembayaran diterima dan tiket telah diterbitkan.');
        } else {
            $pdo->rollBack();
            flash_set('danger', 'Order tidak valid atau bukan status paid.');
        }
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash_set('danger', 'Terjadi kesalahan sistem.');
    }
    header('Location: index.php?page=report_transaksi');
    exit;
}

// ---------- Admin: Tolak pembayaran ----------
if ($aksi === 'reject_order') {
    require_roles(['admin', 'petugas']);
    $idOrder = (int) ($_POST['order_id'] ?? 0);
    $pdo->prepare("UPDATE orders SET status='cancel' WHERE id_order=? AND status='paid'")->execute([$idOrder]);
    flash_set('success', 'Pesanan ditolak dan dibatalkan.');
    header('Location: index.php?page=report_transaksi');
    exit;
}

// ---------- Admin / Petugas: Check-in ----------
if ($aksi === 'checkin') {
    require_roles(['admin', 'petugas']);
    $kodeScan = strtoupper(trim($_POST['ticket_code'] ?? ''));

    $stmt = $pdo->prepare("SELECT a.id_attendee, a.status_checkin, o.status AS status_order, e.tanggal AS tanggal_event
        FROM attendee a
        JOIN order_detail od ON od.id_detail = a.id_detail
        JOIN orders o ON o.id_order = od.id_order
        JOIN tiket t ON t.id_tiket = od.id_tiket
        JOIN event e ON e.id_event = t.id_event
        WHERE a.kode_tiket = ?");
    $stmt->execute([$kodeScan]);
    $peserta = $stmt->fetch();

    if (!$peserta) {
        flash_set('danger', 'Kode tiket tidak ditemukan.');
    } elseif ($peserta['status_order'] === 'cancel') {
        flash_set('warning', 'Check-in gagal. Order untuk tiket ini telah dibatalkan.');
    } elseif ($peserta['status_order'] === 'pending') {
        flash_set('warning', 'Check-in gagal. Status order tiket ini masih "pending".');
    } elseif ($peserta['status_checkin'] === 'sudah') {
        flash_set('warning', 'Tiket sudah check-in sebelumnya.');
    } elseif ($peserta['tanggal_event'] !== date('Y-m-d')) {
        flash_set('danger', 'Check-in ditolak! Check-in hanya dapat dilakukan pada Hari H Event (' . date('d/m/Y', strtotime($peserta['tanggal_event'])) . ').');
    } else {
        $pdo->prepare("UPDATE attendee SET status_checkin='sudah', waktu_checkin=NOW() WHERE id_attendee=?")
            ->execute([$peserta['id_attendee']]);
        flash_set('success', 'Check-in berhasil.');
    }
    header('Location: index.php?page=checkin');
    exit;
}

// ---------- Tampil halaman (GET) ----------

// Definisikan peran yang dibutuhkan untuk setiap halaman
$page_roles = [
    'admin_dashboard' => 'admin',
    'venue' => 'admin',
    'event' => 'admin',
    'tiket' => 'admin',
    'voucher' => 'admin',
    'report_transaksi' => ['admin', 'petugas'],
    'report_tiket' => 'admin',
    'checkin' => ['admin', 'petugas'],
    'cetak_tiket' => ['admin', 'petugas'],
    'event_detail' => 'user',
    'my_orders' => 'user',
    'my_tickets' => 'user',
    'user_dashboard' => 'user',
];

// Tentukan peran yang dibutuhkan berdasarkan halaman, default ke 'user'
$required_role = $page_roles[$halaman] ?? 'user';

// Lakukan pengecekan otorisasi sebelum output apapun dikirim
if (is_array($required_role)) {
    require_roles($required_role);
} else {
    require_login($required_role);
}

require_once __DIR__ . '/views/template/header.php';

switch ($halaman) {
    case 'admin_dashboard':
        require __DIR__ . '/views/admin/dashboard.php';
        break;
    case 'venue':
        require __DIR__ . '/views/admin/venue.php';
        break;
    case 'event':
        require __DIR__ . '/views/admin/event.php';
        break;
    case 'tiket':
        require __DIR__ . '/views/admin/tiket.php';
        break;
    case 'voucher':
        require __DIR__ . '/views/admin/voucher.php';
        break;
    case 'report_transaksi':
        require __DIR__ . '/views/admin/report_transaksi.php';
        break;
    case 'report_tiket':
        require __DIR__ . '/views/admin/report_tiket.php';
        break;
    case 'checkin':
        require __DIR__ . '/views/admin/checkin.php';
        break;
    case 'cetak_tiket':
        require __DIR__ . '/views/admin/cetak_tiket.php';
        break;
    case 'event_detail':
        require __DIR__ . '/views/user/event_detail.php';
        break;
    case 'my_orders':
        require __DIR__ . '/views/user/my_orders.php';
        break;
    case 'my_tickets':
        require __DIR__ . '/views/user/my_tickets.php';
        break;
    case 'user_dashboard':
    default:
        require __DIR__ . '/views/user/dashboard.php';
        break;
}

require_once __DIR__ . '/views/template/footer.php';
