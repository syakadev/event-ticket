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
    <div class="table-responsive">
        <table class="table table-striped align-middle"><thead><tr><th>Tiket</th><th>Harga</th><th>Sisa / Kuota</th><th>Status</th><th style="min-width: 14rem;">Pesan</th></tr></thead><tbody>
        <?php foreach ($tickets as $t): ?>
            <?php
                $soldStmt = $pdo->prepare("SELECT COALESCE(SUM(od.qty),0) FROM order_detail od JOIN orders o ON o.id_order=od.id_order WHERE od.id_tiket=? AND o.status IN ('pending', 'paid', 'confirmed')");
                $soldStmt->execute([(int) $t['id_tiket']]);
                $terjual = (int) $soldStmt->fetchColumn();
                $kuota = (int) $t['kuota'];
                $sisa = $kuota - $terjual;
                $habis = $sisa <= 0;
            ?>
            <tr class="<?= $habis ? 'table-secondary' : '' ?>">
                <td><?= e($t['nama_tiket']) ?></td>
                <td>Rp <?= number_format((float) $t['harga'], 0, ',', '.') ?></td>
                <td><?= max(0, $sisa) ?> / <?= $kuota ?></td>
                <td>
                    <?php if ($habis): ?>
                        <span class="badge text-bg-secondary">Habis</span>
                    <?php else: ?>
                        <span class="badge text-bg-success">Tersedia</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($habis): ?>
                        <span class="text-muted small">Tiket tidak dapat dipesan.</span>
                    <?php else: ?>
                        <form method="post" action="index.php?action=create_order&page=event_detail&id=<?= $eventId ?>" class="row g-2 align-items-center" id="form-tiket-<?= (int) $t['id_tiket'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="tiket_id" value="<?= (int) $t['id_tiket'] ?>">
                            <div class="col-4 col-sm-3 col-md-2">
                                <input type="number" name="qty" class="form-control form-control-sm" min="1" max="<?= $sisa ?>" value="1" required>
                            </div>
                            <div class="col-8 col-sm-5 col-md-4">
                                <input type="text" name="voucher_code" class="form-control form-control-sm" placeholder="Kode voucher">
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-3">
                                <button type="button" class="btn btn-sm btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#orderConfirmationModal" data-form-id="form-tiket-<?= (int) $t['id_tiket'] ?>" data-ticket-name="<?= e($t['nama_tiket']) ?>" data-ticket-price="<?= (float) $t['harga'] ?>">Pesan</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
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
