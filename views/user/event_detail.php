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
// Ambil semua jumlah tiket terjual dalam satu query
$ticketIds = array_map(fn($t) => (int)$t['id_tiket'], $tickets);
$soldData = [];
if (!empty($ticketIds)) {
    $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
    $sqlSold = "SELECT od.id_tiket, SUM(od.qty) as total_sold 
            FROM order_detail od 
            JOIN orders o ON o.id_order=od.id_order 
            WHERE od.id_tiket IN ($placeholders) AND o.status IN ('pending', 'paid') 
            GROUP BY od.id_tiket";
    $stmtSold = $pdo->prepare($sqlSold);
    $stmtSold->execute($ticketIds);
    $soldData = $stmtSold->fetchAll(PDO::FETCH_KEY_PAIR);
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
        ?>
        <div class="card mb-3 <?= $habis ? 'bg-light' : '' ?>">
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
                    </div>
                    <div class="col-md-6 col-lg-5">
                        <?php if ($habis): ?>
                            <p class="text-muted small mb-0 text-center text-md-start">Tiket tidak dapat dipesan.</p>
                        <?php else: ?>
                            <form method="post" action="index.php?action=create_order&page=event_detail&id=<?= $eventId ?>" class="row g-2 align-items-center" id="form-tiket-<?= (int) $t['id_tiket'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="tiket_id" value="<?= (int) $t['id_tiket'] ?>">
                                <div class="col-4">
                                    <input type="number" name="qty" class="form-control form-control-sm" min="1" max="<?= $sisa ?>" value="1" required title="Jumlah">
                                </div>
                                <div class="col-8">
                                    <input type="text" name="voucher_code" class="form-control form-control-sm" placeholder="Kode voucher">
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#orderConfirmationModal" data-form-id="form-tiket-<?= (int) $t['id_tiket'] ?>" data-ticket-name="<?= e($t['nama_tiket']) ?>" data-ticket-price="<?= (float) $t['harga'] ?>">Pesan</button>
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
    const orderModal = document.getElementById('orderConfirmationModal');
    if (!orderModal) {
        return;
    }
    let targetFormId = '';

    orderModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        targetFormId = button.getAttribute('data-form-id') || '';
        const ticketName = button.getAttribute('data-ticket-name') || '';
        const ticketPrice = parseFloat(button.getAttribute('data-ticket-price') || '0');

        const form = document.getElementById(targetFormId);
        if (!form) {
            return;
        }
        const quantity = parseInt(form.querySelector('input[name="qty"]').value, 10) || 1;
        const voucherCode = form.querySelector('input[name="voucher_code"]').value;
        const totalPrice = ticketPrice * quantity;
        const currencyFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });

        orderModal.querySelector('#modal-ticket-name').textContent = ticketName;
        orderModal.querySelector('#modal-quantity').textContent = String(quantity);
        orderModal.querySelector('#modal-voucher-code').textContent = voucherCode || '-';
        orderModal.querySelector('#modal-ticket-price').textContent = currencyFormatter.format(ticketPrice);
        orderModal.querySelector('#modal-total-price').textContent = currencyFormatter.format(totalPrice);
    });

    orderModal.querySelector('#modal-confirm-button').addEventListener('click', function () {
        if (!targetFormId) {
            return;
        }
        const formToSubmit = document.getElementById(targetFormId);
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });
});
</script>
