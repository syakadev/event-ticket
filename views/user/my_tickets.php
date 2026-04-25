<?php
$stmt = $pdo->prepare('SELECT a.kode_tiket, a.status_checkin, a.waktu_checkin, e.nama_event, vn.nama_venue, t.nama_tiket, t.harga, o.status, "accepted" AS status_order FROM attendee a JOIN order_detail od ON od.id_detail=a.id_detail JOIN orders o ON o.id_order=od.id_order JOIN tiket t ON t.id_tiket=od.id_tiket JOIN event e ON e.id_event=t.id_event LEFT JOIN venue vn ON e.id_venue=vn.id_venue WHERE o.id_user=? ORDER BY a.id_attendee DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();
?>
<div class="mb-4">
    <h4 class="mb-1 fw-bold">Tiket Saya</h4>
    <p class="text-muted small">Tiket bisa dipakai check-in jika status order sudah <strong>accepted</strong> (admin sudah menerbitkan tiket).</p>
</div>

<?php if(empty($rows)): ?>
    <div class="alert alert-light text-center border">
        Anda belum memiliki tiket.
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach($rows as $r): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-modern h-100 position-relative">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark"><?= e($r['nama_event']) ?></h6>
                                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill"><?= e($r['nama_tiket']) ?></div>
                            </div>
                            <span class="badge bg-<?= $r['status_checkin']==='sudah'?'success':'secondary' ?> rounded-pill px-2 py-1">
                                <?= strtoupper(e($r['status_checkin'])) ?>
                            </span>
                        </div>
                        
                        <div class="text-center bg-light py-3 mb-3 rounded" style="border: 1px dashed #cbd5e1;">
                            <svg class="ticket-barcode" data-code="<?= e($r['kode_tiket']) ?>" style="max-width: 100%; height: 50px;"></svg>
                            <div class="mt-2 font-monospace small fw-bold text-secondary"><?= e($r['kode_tiket']) ?></div>
                        </div>

                        <div class="mb-3 small text-secondary">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Status Order:</span> 
                                <span class="fw-semibold text-success"><?= strtoupper(e($r['status_order'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Waktu Check-in:</span> 
                                <span class="fw-semibold text-dark"><?= e((string)($r['waktu_checkin'] ?? '-')) ?></span>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <button type="button" class="btn btn-sm btn-light w-100 fw-medium" data-bs-toggle="modal" data-bs-target="#ticketDetailModal"
                                data-kode-tiket="<?= e($r['kode_tiket']) ?>"
                                data-nama-event="<?= e($r['nama_event']) ?>"
                                data-nama-venue="<?= e($r['nama_venue'] ?? 'Belum ditentukan') ?>"
                                data-nama-tiket="<?= e($r['nama_tiket']) ?>"
                                data-harga="<?= number_format((float)$r['harga'],0,',','.') ?>"
                                data-status-order="<?= e($r['status_order']) ?>"
                                data-status-checkin="<?= e($r['status_checkin']) ?>"
                                data-waktu-checkin="<?= e((string)($r['waktu_checkin'] ?? '-')) ?>">
                                Lihat Detail Tiket
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Detail Tiket -->
<div class="modal fade" id="ticketDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="ticketDetailModalLabel">Detail Tiket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="ticket-detail-printable">
        <h5 id="modal-event-name" class="text-center text-primary mb-1 fw-bold"></h5>
        <p id="modal-ticket-category" class="text-center text-secondary small fw-medium"></p>
        <div class="text-center my-4 py-3 bg-light rounded" style="border: 1px dashed #cbd5e1;">
            <svg id="modal-barcode" style="max-width: 100%; height: 60px;"></svg>
            <p class="text-center mt-2 mb-0"><code id="modal-ticket-code" class="text-dark fs-5 fw-bold bg-transparent"></code></p>
        </div>
        <div class="bg-light p-3 rounded text-secondary small">
            <div class="d-flex justify-content-between mb-2">
                <span>Venue Event</span>
                <span id="modal-event-venue" class="fw-bold text-dark text-end"></span>
            </div>
            <div class="d-flex justify-content-between mb-2 border-top pt-2 mt-2 border-white">
                <span>Harga Tiket</span>
                <span id="modal-ticket-harga" class="fw-bold text-primary"></span>
            </div>
            <div class="d-flex justify-content-between mb-2 border-top pt-2 mt-2 border-white">
                <span>Status Order</span>
                <span id="modal-order-status" class="fw-bold"></span>
            </div>
            <div class="d-flex justify-content-between mb-2 border-top pt-2 mt-2 border-white">
                <span>Status Check-in</span>
                <span id="modal-checkin-status" class="fw-bold"></span>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2 border-white">
                <span>Waktu Check-in</span>
                <span id="modal-checkin-time" class="fw-bold text-dark"></span>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light fw-medium w-100" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ubah kode tiket teks menjadi barcode 1D (format CODE128)
        document.querySelectorAll('.ticket-barcode').forEach(function (el) {
            var kode = el.getAttribute('data-code') || '';
            if (!kode) {
                return;
            }

            JsBarcode(el, kode, {
                format: 'CODE128',
                lineColor: '#1e293b',
                width: 1.6,
                height: 42,
                displayValue: false,
                margin: 0,
                background: 'transparent'
            });
        });

        const detailModal = document.getElementById('ticketDetailModal');
        if (detailModal) {
            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const kodeTiket = button.getAttribute('data-kode-tiket');
                const namaEvent = button.getAttribute('data-nama-event');
                const namaVenue = button.getAttribute('data-nama-venue');
                const namaTiket = button.getAttribute('data-nama-tiket');
                const harga = button.getAttribute('data-harga');
                const statusOrder = button.getAttribute('data-status-order');
                const statusCheckin = button.getAttribute('data-status-checkin');
                const waktuCheckin = button.getAttribute('data-waktu-checkin');

                detailModal.querySelector('#modal-event-name').textContent = namaEvent;
                detailModal.querySelector('#modal-event-venue').textContent = namaVenue;
                detailModal.querySelector('#modal-ticket-category').textContent = namaTiket;
                detailModal.querySelector('#modal-ticket-harga').textContent = 'Rp ' + harga;
                detailModal.querySelector('#modal-ticket-code').textContent = kodeTiket;
                detailModal.querySelector('#modal-checkin-time').textContent = waktuCheckin || '-';

                const orderStatusEl = detailModal.querySelector('#modal-order-status');
                orderStatusEl.innerHTML = `<span class="text-success">${statusOrder.toUpperCase()}</span>`;
                
                const checkinStatusEl = detailModal.querySelector('#modal-checkin-status');
                checkinStatusEl.innerHTML = `<span class="text-${statusCheckin === 'sudah' ? 'success' : 'muted'}">${statusCheckin.toUpperCase()}</span>`;

                const barcodeEl = detailModal.querySelector('#modal-barcode');
                JsBarcode(barcodeEl, kodeTiket, {
                    format: 'CODE128', lineColor: '#1e293b', width: 2, height: 60, displayValue: false, background: 'transparent'
                });
            });
        }
    });
</script>
