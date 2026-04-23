<?php
declare(strict_types=1);

$eventId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT e.*, v.nama_venue, v.alamat FROM event e LEFT JOIN venue v ON v.id_venue=e.id_venue WHERE e.id_event=?');
$stmt->execute([$eventId]);
$event = $stmt->fetch();
if (!$event) {
    echo '<div class="alert alert-danger">Event tidak ditemukan.</div>';

    return;
}
$tickets = $pdo->prepare('SELECT * FROM tiket WHERE id_event=? ORDER BY harga ASC');
$tickets->execute([$eventId]);
$tickets = $tickets->fetchAll();

// --- Optimasi N+1 Query ---
// Ambil semua jumlah tiket terjual (global) dalam satu query
$ticketIds = array_map(fn($t) => (int)$t['id_tiket'], $tickets);
$soldData = [];
$userOrderedData = [];
if (!empty($ticketIds)) {
    $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));

    // Kuota global terjual
    $sqlSold = "SELECT od.id_tiket, SUM(od.qty) as total_sold 
            FROM order_detail od 
            JOIN orders o ON o.id_order=od.id_order 
            WHERE od.id_tiket IN ($placeholders) AND o.status IN ('pending', 'paid') 
            GROUP BY od.id_tiket";
    $stmtSold = $pdo->prepare($sqlSold);
    $stmtSold->execute($ticketIds);
    $soldData = $stmtSold->fetchAll(PDO::FETCH_KEY_PAIR);

    // Kuota yang sudah dipesan user ini per tiket
    $sqlUserOrdered = "SELECT od.id_tiket, SUM(od.qty) as user_ordered 
            FROM order_detail od 
            JOIN orders o ON o.id_order=od.id_order 
            WHERE od.id_tiket IN ($placeholders) AND o.id_user = ? AND o.status IN ('pending', 'paid') 
            GROUP BY od.id_tiket";
    $stmtUserOrdered = $pdo->prepare($sqlUserOrdered);
    $paramsUser = array_merge($ticketIds, [$_SESSION['user_id']]);
    $stmtUserOrdered->execute($paramsUser);
    $userOrderedData = $stmtUserOrdered->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>
<div class="card card-modern mb-4">
    <?php if(!empty($event['gambar'])): ?>
        <img src="img/<?= e($event['gambar']) ?>" class="card-img-top" alt="Event Cover" style="max-height: 400px; object-fit: cover;">
    <?php endif; ?>
    <div class="card-body">
        <h4><?= e($event['nama_event']) ?></h4>
        <p class="mb-1 text-secondary"><?= e((string) ($event['nama_venue'] ?? '-')) ?> — <?= e((string) ($event['alamat'] ?? '-')) ?></p>
        <p class="mb-0"><?= e(date('d M Y', strtotime($event['tanggal']))) ?></p>
    </div>
</div>
<div class="card card-modern"><div class="card-body">
    <h5 class="mb-3">Pilih Tiket</h5>
    <?php if (empty($tickets)): ?>
        <div class="alert alert-light text-center border">Tidak ada tiket yang tersedia untuk event ini.</div>
    <?php endif; ?>

    <?php foreach ($tickets as $t): ?>
        <?php
            $terjual = (int) ($soldData[$t['id_tiket']] ?? 0);
            $kuota = (int) $t['kuota'];
            $sisa = $kuota - $terjual;
            $habis = $sisa <= 0;

            // Per-user limit
            $maksPerUser = (int) ($t['maks_per_user'] ?? 5);
            $sudahDipesanUser = (int) ($userOrderedData[$t['id_tiket']] ?? 0);
            $sisaKuotaUser = max(0, $maksPerUser - $sudahDipesanUser);
            $userHabis = $sisaKuotaUser <= 0;

            // Max yang bisa dipesan: minimum dari sisa kuota global dan sisa kuota user
            $maxPesan = min($sisa, $sisaKuotaUser);
            $tidakBisaPesan = $habis || $userHabis || $maxPesan <= 0;
        ?>
        <div class="card mb-3 <?= $tidakBisaPesan ? 'bg-light' : '' ?>">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-6 col-lg-7">
                        <h6 class="mb-1 fw-bold"><?= e($t['nama_tiket']) ?></h6>
                        <p class="mb-1 fs-5 fw-bold text-primary">Rp <?= number_format((float) $t['harga'], 0, ',', '.') ?></p>
                        <div class="small text-muted">
                            Sisa: <span class="fw-medium"><?= max(0, $sisa) ?></span> / Kuota: <?= $kuota ?>
                            <?php if ($habis): ?>
                                <span class="badge text-bg-secondary ms-2">Habis</span>
                            <?php else: ?>
                                <span class="badge text-bg-success ms-2">Tersedia</span>
                            <?php endif; ?>
                        </div>
                        <!-- Info batas per user -->
                        <div class="small mt-1">
                            <span class="text-muted">Batas per user:</span>
                            <span class="fw-semibold"><?= $maksPerUser ?> tiket</span>
                            <?php if ($sudahDipesanUser > 0): ?>
                                <span class="text-muted mx-1">•</span>
                                <span class="text-muted">Sudah dipesan:</span>
                                <span class="fw-semibold text-info"><?= $sudahDipesanUser ?></span>
                                <span class="text-muted mx-1">•</span>
                                <span class="text-muted">Sisa kuota Anda:</span>
                                <?php if ($sisaKuotaUser > 0): ?>
                                    <span class="fw-bold text-success"><?= $sisaKuotaUser ?></span>
                                <?php else: ?>
                                    <span class="fw-bold text-danger">0 (batas tercapai)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5">
                        <?php if ($tidakBisaPesan): ?>
                            <div class="text-center text-md-start">
                                <?php if ($habis): ?>
                                    <p class="text-muted small mb-0">Tiket sudah habis.</p>
                                <?php elseif ($userHabis): ?>
                                    <div class="alert alert-warning small mb-0 py-2 px-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Anda sudah mencapai batas maksimal pemesanan (<strong><?= $maksPerUser ?> tiket</strong>) untuk tiket ini.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <form method="post" action="index.php?action=create_order&page=event_detail&id=<?= $eventId ?>" class="row g-2 align-items-center" id="form-tiket-<?= (int) $t['id_tiket'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="tiket_id" value="<?= (int) $t['id_tiket'] ?>">
                                <div class="col-4">
                                    <label class="form-label small text-muted mb-1">Jumlah <span class="text-danger">(max <?= $maxPesan ?>)</span></label>
                                    <input type="number" name="qty" class="form-control form-control-sm qty-input" min="1" max="<?= $maxPesan ?>" value="1" required title="Jumlah (maks. <?= $maxPesan ?>)" data-price="<?= (float) $t['harga'] ?>" data-ticket-id="<?= (int) $t['id_tiket'] ?>" data-max-allowed="<?= $maxPesan ?>">
                                </div>
                                <div class="col-8">
                                    <label class="form-label small text-muted mb-1">Kode Voucher</label>
                                    <input type="text" name="voucher_code" class="form-control form-control-sm" placeholder="Kode voucher (opsional)">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center bg-light rounded px-3 py-2 mb-2 price-summary" id="price-summary-<?= (int) $t['id_tiket'] ?>">
                                        <div>
                                            <span class="small text-muted">Harga satuan:</span>
                                            <span class="fw-semibold">Rp <?= number_format((float) $t['harga'], 0, ',', '.') ?></span>
                                        </div>
                                        <div>
                                            <span class="small text-muted">×</span>
                                            <span class="fw-bold qty-display" id="qty-display-<?= (int) $t['id_tiket'] ?>">1</span>
                                            <span class="small text-muted">=</span>
                                            <span class="fw-bold text-primary fs-6 total-display" id="total-display-<?= (int) $t['id_tiket'] ?>">Rp <?= number_format((float) $t['harga'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#orderConfirmationModal" data-form-id="form-tiket-<?= (int) $t['id_tiket'] ?>" data-ticket-name="<?= e($t['nama_tiket']) ?>" data-ticket-price="<?= (float) $t['harga'] ?>" data-max-allowed="<?= $maxPesan ?>">Pesan</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div></div>

<!-- Modal Konfirmasi Pesanan -->
<div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderConfirmationModalLabel">Konfirmasi Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda akan memesan tiket berikut:</p>
        <dl class="row">
          <dt class="col-sm-4">Tiket</dt>
          <dd class="col-sm-8" id="modal-ticket-name"></dd>

          <dt class="col-sm-4">Jumlah</dt>
          <dd class="col-sm-8" id="modal-quantity"></dd>

          <dt class="col-sm-4">Kode Voucher</dt>
          <dd class="col-sm-8" id="modal-voucher-code">-</dd>

          <dt class="col-sm-4">Harga Satuan</dt>
          <dd class="col-sm-8" id="modal-ticket-price"></dd>

          <dt class="col-sm-4 fw-bold">Total Harga</dt>
          <dd class="col-sm-8 fw-bold" id="modal-total-price"></dd>
        </dl>
        <p class="mt-3 mb-0">Apakah Anda yakin ingin melanjutkan?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="modal-confirm-button">Ya, Lanjutkan Pesanan</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const currencyFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });

    // ===== Live Price Calculation =====
    document.querySelectorAll('.qty-input').forEach(function (input) {
        function updatePrice() {
            const price = parseFloat(input.getAttribute('data-price') || '0');
            const ticketId = input.getAttribute('data-ticket-id');
            const maxAllowed = parseInt(input.getAttribute('data-max-allowed'), 10) || 1;
            let qty = parseInt(input.value, 10) || 1;

            // Enforce limits
            if (qty < 1) qty = 1;
            if (qty > maxAllowed) qty = maxAllowed;
            input.value = qty;

            const total = price * qty;
            const qtyDisplay = document.getElementById('qty-display-' + ticketId);
            const totalDisplay = document.getElementById('total-display-' + ticketId);

            if (qtyDisplay) qtyDisplay.textContent = String(qty);
            if (totalDisplay) totalDisplay.textContent = currencyFormatter.format(total);

            // Animate the total price change
            if (totalDisplay) {
                totalDisplay.style.transition = 'transform 0.15s ease, color 0.15s ease';
                totalDisplay.style.transform = 'scale(1.15)';
                totalDisplay.style.color = '#0d6efd';
                setTimeout(function() {
                    totalDisplay.style.transform = 'scale(1)';
                }, 200);
            }
        }

        input.addEventListener('input', updatePrice);
        input.addEventListener('change', updatePrice);
    });

    // ===== Modal Konfirmasi =====
    const orderModal = document.getElementById('orderConfirmationModal');
    if (!orderModal) return;
    let targetFormId = '';

    orderModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        targetFormId = button.getAttribute('data-form-id') || '';
        const ticketName = button.getAttribute('data-ticket-name') || '';
        const ticketPrice = parseFloat(button.getAttribute('data-ticket-price') || '0');
        const maxAllowed = parseInt(button.getAttribute('data-max-allowed'), 10) || 1;

        const form = document.getElementById(targetFormId);
        if (!form) return;

        let quantity = parseInt(form.querySelector('input[name="qty"]').value, 10) || 1;
        if (quantity > maxAllowed) quantity = maxAllowed;
        const voucherCode = form.querySelector('input[name="voucher_code"]').value;
        const totalPrice = ticketPrice * quantity;

        orderModal.querySelector('#modal-ticket-name').textContent = ticketName;
        orderModal.querySelector('#modal-quantity').textContent = String(quantity) + ' tiket';
        orderModal.querySelector('#modal-voucher-code').textContent = voucherCode || '-';
        orderModal.querySelector('#modal-ticket-price').textContent = currencyFormatter.format(ticketPrice);
        orderModal.querySelector('#modal-total-price').textContent = currencyFormatter.format(totalPrice);
    });

    orderModal.querySelector('#modal-confirm-button').addEventListener('click', function () {
        if (!targetFormId) return;
        const formToSubmit = document.getElementById(targetFormId);
        if (formToSubmit) formToSubmit.submit();
    });
});
</script>
