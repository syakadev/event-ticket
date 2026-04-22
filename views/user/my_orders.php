<?php
$popupOrderBaru = (int)($_SESSION['popup_order_baru'] ?? 0);
$popupOrderPaid = (int)($_SESSION['popup_order_paid'] ?? 0);
unset($_SESSION['popup_order_baru'], $_SESSION['popup_order_paid']);

$stmt = $pdo->prepare('SELECT o.*, v.kode_voucher, v.potongan, COALESCE(SUM(od.subtotal),0) AS subtotal_line FROM orders o LEFT JOIN order_detail od ON od.id_order=o.id_order LEFT JOIN voucher v ON v.id_voucher=o.id_voucher WHERE o.id_user=? GROUP BY o.id_order ORDER BY o.id_order DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$stmtDet = $pdo->prepare('SELECT od.*, t.nama_tiket, t.harga, e.nama_event, vn.nama_venue FROM order_detail od JOIN tiket t ON t.id_tiket=od.id_tiket JOIN event e ON e.id_event=t.id_event LEFT JOIN venue vn ON e.id_venue=vn.id_venue JOIN orders o ON o.id_order=od.id_order WHERE o.id_user=?');
$stmtDet->execute([$_SESSION['user_id']]);
$allDetails = $stmtDet->fetchAll();
$detailMap = [];
foreach ($allDetails as $d) {
    if (!isset($detailMap[$d['id_order']])) $detailMap[$d['id_order']] = [];
    $detailMap[$d['id_order']][] = [
        'event' => $d['nama_event'],
        'venue' => $d['nama_venue'] ?? 'Belum ditentukan',
        'tiket' => $d['nama_tiket'],
        'harga' => (int)$d['harga'],
        'qty' => (int)$d['qty'],
        'subtotal' => (int)$d['subtotal']
    ];
}
?>
<div class="mb-4">
    <h4 class="mb-1 fw-bold">Riwayat Pembelian</h4>
    <p class="text-muted small">Kelola dan pantau pesanan tiket Anda di sini.</p>
</div>

<?php if(empty($orders)): ?>
    <div class="alert alert-light text-center border">
        Anda belum memiliki pesanan tiket apapun.
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach($orders as $o): ?>
            <?php
                $warnaStatus = 'secondary';
                if ($o['status'] === 'pending') {
                    $warnaStatus = 'warning';
                } elseif ($o['status'] === 'paid') {
                    $warnaStatus = 'info';
                } elseif ($o['status'] === 'confirmed') {
                    $warnaStatus = 'success';
                } elseif ($o['status'] === 'cancel') {
                    $warnaStatus = 'danger';
                }
                $jsonDetails = e(json_encode($detailMap[$o['id_order']] ?? []));
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold border-bottom pb-1">Order #<?= (int)$o['id_order'] ?></h6>
                            <span class="badge bg-<?= $warnaStatus ?> bg-opacity-10 text-<?= $warnaStatus ?> border border-<?= $warnaStatus ?> rounded-pill px-2 py-1"><?= strtoupper(e($o['status'])) ?></span>
                        </div>
                        <div class="mb-3 text-secondary small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Tanggal Order:</span> 
                                <span class="fw-semibold text-dark"><?= e(date('d M Y, H:i', strtotime($o['tanggal_order']))) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Voucher:</span> 
                                <span class="fw-semibold <?= !empty($o['kode_voucher']) ? 'text-primary' : 'text-dark' ?>"><?= e((string)($o['kode_voucher'] ?? '-')) ?></span>
                            </div>
                        </div>
                        <div class="mb-4 pb-2 border-bottom">
                            <small class="text-muted d-block mb-1">Total Pembayaran</small>
                            <div class="fs-4 fw-bold text-dark">Rp <?= number_format((float)$o['total'],0,',','.') ?></div>
                        </div>
                        
                        <div class="mt-auto d-flex flex-column gap-2">
                            <button type="button" class="btn btn-sm btn-light w-100 fw-medium btn-detail-order" 
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetailOrder"
                                    data-order-id="<?= (int)$o['id_order'] ?>" 
                                    data-details="<?= $jsonDetails ?>"
                                    data-total="<?= number_format((float)$o['total'],0,',','.') ?>"
                                    data-potongan="<?= (int)($o['potongan']??0) ?>"
                                    data-tanggal="<?= e(date('d M Y, H:i', strtotime($o['tanggal_order']))) ?>"
                                    data-status="<?= e($o['status']) ?>">
                                Lihat Detail
                            </button>
                            
                            <div class="w-100 d-flex gap-2">
                                <?php if ($o['status'] === 'pending'): ?>
                                    <form method="post" action="index.php?action=pay_order&page=my_orders" id="form-bayar-<?= (int)$o['id_order'] ?>" class="flex-grow-1">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="order_id" value="<?= (int)$o['id_order'] ?>">
                                        <button type="button" class="btn btn-sm btn-primary w-100 fw-medium" data-bs-toggle="modal" data-bs-target="#paymentConfirmationModal" data-form-id="form-bayar-<?= (int)$o['id_order'] ?>" data-order-id="<?= (int)$o['id_order'] ?>" data-order-total="Rp <?= number_format((float)$o['total'],0,',','.') ?>">Bayar Sekarang</button>
                                    </form>
                                    <form method="post" action="index.php?action=user_cancel_order&page=my_orders" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="order_id" value="<?= (int)$o['id_order'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-medium">Batalkan</button>
                                    </form>
                                <?php elseif ($o['status'] === 'paid'): ?>
                                    <button class="btn btn-sm btn-light text-secondary w-100 fw-medium" disabled>Menunggu verifikasi admin</button>
                                <?php elseif ($o['status'] === 'cancel'): ?>
                                    <button class="btn btn-sm btn-light text-danger w-100 fw-medium" disabled>Pesanan Dibatalkan</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success bg-opacity-10 text-success border-success w-100 fw-medium" disabled>Transaksi Selesai</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Popup Detail Order -->
<div class="modal fade" id="modalDetailOrder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Detail Pesanan #<span id="detailOrderId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3 text-secondary small">
                    <div>Waktu: <span id="detailOrderTanggal" class="fw-bold text-dark"></span></div>
                    <div id="detailOrderStatus"></div>
                </div>
                <div id="detailOrderItemList" class="mb-3"></div>
                <div class="bg-light p-3 rounded">
                    <div class="d-flex justify-content-between align-items-center mb-2" id="detailOrderPotonganContainer" style="display: none !important;">
                        <span class="text-secondary small">Potongan Voucher</span>
                        <strong class="text-success small" id="detailOrderPotongan"></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top border-white pt-2 mt-2">
                        <strong class="text-secondary">Total Akhir</strong>
                        <strong class="fs-5 text-dark">Rp <span id="detailOrderTotal"></span></strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light w-100 fw-medium" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Popup setelah user berhasil memesan tiket -->
<div class="modal fade" id="modalOrderBaru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success">Pesanan Berhasil Dibuat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-secondary">
                Pesanan #<strong id="orderBaruId" class="text-dark"></strong> berhasil dibuat.<br>
                Harap lakukan pembayaran maksimal <strong class="text-warning">24 jam</strong> agar tiket tidak expired.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Bayar Nanti</button>
                <form method="post" action="index.php?action=pay_order&page=my_orders" id="formBayarSekarang">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="order_id" id="orderBaruInput" value="">
                    <button class="btn btn-primary fw-medium">Bayar Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Popup setelah user klik bayar -->
<div class="modal fade" id="modalOrderPaid" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary">Pembayaran Tercatat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-secondary">
                Pembayaran order #<strong id="orderPaidId" class="text-dark"></strong> sudah diterima sistem.
                Saat ini status menjadi <strong class="text-info">paid</strong> dan menuggu konfirmasi admin.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fw-medium w-100" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pembayaran -->
<div class="modal fade" id="paymentConfirmationModal" tabindex="-1" aria-labelledby="paymentConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="paymentConfirmationModalLabel">Konfirmasi Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-secondary">
        <p>Anda akan melakukan pembayaran untuk pesanan:</p>
        <div class="bg-light p-3 rounded mb-3">
            <div class="d-flex justify-content-between mb-2">
                <span>ID Order</span>
                <span class="fw-bold text-dark" id="modal-payment-order-id"></span>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                <span>Total Bayar</span>
                <span class="fw-bold text-primary fs-5" id="modal-payment-total"></span>
            </div>
        </div>
        <p class="mb-0 small text-muted">Pastikan Anda sudah menyiapkan metode pembayaran. Apakah Anda yakin ingin melanjutkan pembayaran sekarang?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary fw-medium" id="modal-payment-confirm-button">Ya, Lanjutkan Pembayaran</button>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var popupOrderBaru = <?= $popupOrderBaru ?>;
        var popupOrderPaid = <?= $popupOrderPaid ?>;

        if (popupOrderBaru > 0) {
            document.getElementById('orderBaruId').textContent = popupOrderBaru;
            document.getElementById('orderBaruInput').value = popupOrderBaru;
            var modalBaru = new bootstrap.Modal(document.getElementById('modalOrderBaru'));
            modalBaru.show();
        }

        if (popupOrderPaid > 0) {
            document.getElementById('orderPaidId').textContent = popupOrderPaid;
            var modalPaid = new bootstrap.Modal(document.getElementById('modalOrderPaid'));
            modalPaid.show();
        }

        var paymentModal = document.getElementById('paymentConfirmationModal');
        if (paymentModal) {
            var confirmPaymentButton = paymentModal.querySelector('#modal-payment-confirm-button');
            var currentFormId = '';

            paymentModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) {
                    currentFormId = '';
                    return;
                }

                var orderId = button.getAttribute('data-order-id') || '';
                var orderTotal = button.getAttribute('data-order-total') || '-';
                currentFormId = button.getAttribute('data-form-id') || '';

                paymentModal.querySelector('#modal-payment-order-id').textContent = orderId ? ('#' + orderId) : '-';
                paymentModal.querySelector('#modal-payment-total').textContent = orderTotal;
            });

            confirmPaymentButton.addEventListener('click', function () {
                if (!currentFormId) {
                    return;
                }
                var formToSubmit = document.getElementById(currentFormId);
                if (formToSubmit) {
                    formToSubmit.submit();
                }
            });
        }

        // Logic Modal Detail Order
        var detailModal = document.getElementById('modalDetailOrder');
        if (detailModal) {
            detailModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;
                
                var orderId = button.getAttribute('data-order-id') || '';
                var orderTotal = button.getAttribute('data-total') || '0';
                var orderPotongan = parseInt(button.getAttribute('data-potongan') || '0');
                var orderTanggal = button.getAttribute('data-tanggal') || '';
                var orderStatus = button.getAttribute('data-status') || '';
                var detailsJSON = button.getAttribute('data-details');
                var details = [];
                try {
                    details = JSON.parse(detailsJSON);
                } catch(e) {}

                document.getElementById('detailOrderId').textContent = orderId;
                document.getElementById('detailOrderTotal').textContent = orderTotal;
                
                var stEl = document.getElementById('detailOrderStatus');
                if(stEl) {
                    var stClass = orderStatus === 'paid' ? 'info' : (orderStatus === 'confirmed' || orderStatus === 'accepted' ? 'success' : (orderStatus === 'cancel' ? 'danger' : 'warning'));
                    stEl.innerHTML = `<span class="badge bg-${stClass} bg-opacity-10 text-${stClass} border border-${stClass} rounded-pill">${orderStatus.toUpperCase()}</span>`;
                }
                
                var tgEl = document.getElementById('detailOrderTanggal');
                if(tgEl) tgEl.textContent = orderTanggal;

                var ptElContainer = document.getElementById('detailOrderPotonganContainer');
                if (ptElContainer) {
                    if (orderPotongan > 0) {
                        var ptFmt = new Intl.NumberFormat('id-ID').format(orderPotongan);
                        ptElContainer.style.display = 'flex';
                        ptElContainer.style.setProperty('display', 'flex', 'important');
                        document.getElementById('detailOrderPotongan').textContent = '-Rp ' + ptFmt;
                    } else {
                        ptElContainer.style.display = 'none';
                        ptElContainer.style.setProperty('display', 'none', 'important');
                    }
                }
                
                var listContainer = document.getElementById('detailOrderItemList');
                listContainer.innerHTML = '';
                
                if (details.length === 0) {
                    listContainer.innerHTML = '<div class="text-muted small border rounded p-3 text-center">Detail riwayat tidak tersedia.</div>';
                    return;
                }
                
                details.forEach(function(item) {
                    var el = document.createElement('div');
                    el.className = 'd-flex justify-content-between mb-3 border-bottom pb-3';
                    
                    var subtotalFmt = new Intl.NumberFormat('id-ID').format(item.subtotal);
                    var hargaFmt = new Intl.NumberFormat('id-ID').format(item.harga);
                    
                    el.innerHTML = `
                        <div>
                            <div class="fw-bold text-dark mb-1">${item.tiket} &times; ${item.qty}</div>
                            <div class="text-secondary small mb-1">Event: <span class="text-dark">${item.event}</span></div>
                            <div class="text-secondary small mb-1">Venue: <span class="text-primary fw-medium">${item.venue}</span></div>
                            <div class="text-secondary small">Harga Satuan: Rp ${hargaFmt}</div>
                        </div>
                        <div class="fw-bold text-dark pt-2">
                            Rp ${subtotalFmt}
                        </div>
                    `;
                    listContainer.appendChild(el);
                });
            });
        }
    });
</script>
