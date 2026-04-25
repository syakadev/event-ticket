<?php
$idOrder = (int)($_GET['id_order'] ?? 0);

if ($idOrder <= 0) {
    flash_set('danger', 'Pesanan tidak ditemukan.');
    header('Location: index.php?page=my_orders');
    exit;
}

// Cek apakah order milik user ini dan statusnya pending
$stmtOrder = $pdo->prepare('SELECT o.*, v.kode_voucher, v.potongan FROM orders o LEFT JOIN voucher v ON v.id_voucher = o.id_voucher WHERE o.id_order = ? AND o.id_user = ?');
$stmtOrder->execute([$idOrder, $_SESSION['user_id']]);
$order = $stmtOrder->fetch();

if (!$order) {
    flash_set('danger', 'Pesanan tidak valid atau bukan milik Anda.');
    header('Location: index.php?page=my_orders');
    exit;
}

if ($order['status'] !== 'pending') {
    flash_set('warning', 'Pesanan ini sudah dibayar atau dibatalkan.');
    header('Location: index.php?page=my_orders');
    exit;
}

// Get methods
$stmtMetode = $pdo->query('SELECT * FROM metode_pembayaran ORDER BY jenis ASC, nama_penyedia ASC');
$metodes = $stmtMetode->fetchAll();

// Get order details
$stmtDet = $pdo->prepare('SELECT od.*, t.nama_tiket, t.harga, e.nama_event, vn.nama_venue FROM order_detail od JOIN tiket t ON t.id_tiket=od.id_tiket JOIN event e ON e.id_event=t.id_event LEFT JOIN venue vn ON e.id_venue=vn.id_venue WHERE od.id_order=?');
$stmtDet->execute([$idOrder]);
$details = $stmtDet->fetchAll();

$subtotal = 0;
foreach ($details as $d) {
    $subtotal += (int)$d['subtotal'];
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="mb-4 d-flex align-items-center">
            <a href="index.php?page=my_orders" class="btn btn-sm btn-light me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
            </a>
            <h4 class="mb-0 fw-bold">Pilih Pembayaran</h4>
        </div>
        
        <!-- Order Summary Card -->
        <div class="card card-modern mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-3">
                    <span class="text-secondary">Order ID</span>
                    <strong class="text-dark">#<?= $idOrder ?></strong>
                </div>
                
                <div class="mb-3">
                    <?php foreach ($details as $d): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <div class="text-dark fw-medium"><?= e($d['nama_tiket']) ?> &times; <?= $d['qty'] ?></div>
                                <div class="text-secondary small"><?= e($d['nama_event']) ?></div>
                            </div>
                            <div class="text-dark">Rp <?= number_format((float)$d['subtotal'], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($order['kode_voucher'])): ?>
                    <div class="d-flex justify-content-between text-success mb-2 small">
                        <span>Voucher (<?= e($order['kode_voucher']) ?>)</span>
                        <span>-Rp <?= number_format((float)$order['potongan'], 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                    <span class="text-secondary fw-medium">Total Pembayaran</span>
                    <strong class="fs-4 text-primary">Rp <?= number_format((float)$order['total'], 0, ',', '.') ?></strong>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card card-modern">
            <div class="card-body">
                <h5 class="mb-3">Transfer ke:</h5>
                <form action="index.php?action=submit_payment" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id_order" value="<?= $idOrder ?>">
                    
                    <div class="mb-4">
                        <?php if (empty($metodes)): ?>
                            <div class="alert alert-warning">Belum ada metode pembayaran yang tersedia. Hubungi admin.</div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($metodes as $m): ?>
                                    <div class="col-12">
                                        <div class="form-check custom-radio border rounded p-3 d-flex align-items-center mb-0">
                                            <input class="form-check-input mt-0 me-3" type="radio" name="id_metode" id="metode_<?= $m['id_metode'] ?>" value="<?= $m['id_metode'] ?>" required>
                                            <label class="form-check-label w-100 d-flex flex-column" for="metode_<?= $m['id_metode'] ?>" style="cursor: pointer;">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <strong class="text-dark"><?= e($m['nama_penyedia']) ?> (<?= e($m['jenis']) ?>)</strong>
                                                </div>
                                                <div class="text-secondary font-monospace"><?= e($m['nomor_akun']) ?></div>
                                                <?php if (!empty($m['nama_bisnis'])): ?>
                                                    <div class="small text-muted mt-1">a.n. <?= e($m['nama_bisnis']) ?></div>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="bukti_pembayaran" class="form-label fw-medium">Upload Bukti Transfer <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/jpeg, image/png, image/jpg" required>
                        <div class="form-text">Format didukung: JPG, JPEG, PNG. Ukuran maksimal 2MB.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold" <?= empty($metodes) ? 'disabled' : '' ?>>
                        Konfirmasi Pembayaran
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>

<style>
.custom-radio {
    transition: all 0.2s ease;
}
.custom-radio:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1 !important;
}
.custom-radio input:checked + label {
    color: #0f172a;
}
.custom-radio:has(input:checked) {
    border-color: #3b82f6 !important;
    background-color: #eff6ff;
    box-shadow: 0 0 0 1px #3b82f6;
}
html[data-bs-theme="dark"] .custom-radio:hover {
    background-color: #1e293b;
    border-color: #475569 !important;
}
html[data-bs-theme="dark"] .custom-radio:has(input:checked) {
    border-color: #3b82f6 !important;
    background-color: rgba(59, 130, 246, 0.1);
    box-shadow: 0 0 0 1px #3b82f6;
}
</style>
