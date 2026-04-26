<?php
declare(strict_types=1);

$listStmt = $pdo->prepare("SELECT a.id_attendee, a.kode_tiket, a.status_checkin, a.waktu_checkin,
        u.nama AS nama_user, u.email AS email_user,
        e.nama_event, t.nama_tiket, t.harga,
        o.id_order, o.tanggal_order, o.status AS status_order
    FROM attendee a
    JOIN order_detail od ON od.id_detail = a.id_detail
    JOIN orders o ON o.id_order = od.id_order
    JOIN tiket t ON t.id_tiket = od.id_tiket
    LEFT JOIN users u ON u.id_user = o.id_user
    LEFT JOIN event e ON e.id_event = t.id_event
    WHERE o.status = 'paid'
    ORDER BY a.id_attendee DESC");
$listStmt->execute();
$rows = $listStmt->fetchAll();
?>
<div class="card card-modern mb-4">
    <div class="card-body">
        <h5 class="mb-3">Check-in Tiket</h5>
        <form method="post" action="index.php?action=checkin&page=checkin" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="col-12 col-lg-8">
                <label for="ticket_code" class="form-label small text-muted mb-1">Kode tiket</label>
                <input id="ticket_code" name="ticket_code" class="form-control" placeholder="Input/scan kode tiket, contoh TIX-XXXX" required autocomplete="off">
            </div>
            <div class="col-6 col-sm-6 col-lg-2 d-grid">
                <button type="button" id="btnStartScan" class="btn btn-outline-secondary">Mulai Scan</button>
            </div>
            <div class="col-6 col-sm-6 col-lg-2 d-grid">
                <button class="btn btn-primary">Check-in</button>
            </div>
        </form>
        <div class="mt-3">
            <div id="reader" class="border rounded p-2 bg-light mx-auto" style="display:none; max-width: min(100%, 520px);"></div>
            <small id="scanInfo" class="text-muted d-block mt-2">Tekan &quot;Mulai Scan&quot; untuk membaca barcode tiket.</small>
        </div>
    </div>
</div>

<div class="card card-modern">
    <div class="card-body">
        <h6 class="mb-3">Data Tiket (pesanan diterima admin)</h6>

        <div class="table-responsive rounded border">
            <table id="table-checkin" class="table table-striped table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th scope="col">Kode tiket</th>
                    <th scope="col" class="d-none d-lg-table-cell">Order</th>
                    <th scope="col">Pemesan</th>
                    <th scope="col" class="d-none d-xl-table-cell">Email</th>
                    <th scope="col">Event</th>
                    <th scope="col" class="d-none d-md-table-cell">Jenis tiket</th>
                    <th scope="col" class="d-none d-lg-table-cell text-end">Harga</th>
                    <th scope="col">Check-in</th>
                    <th scope="col" class="d-none d-xxl-table-cell">Waktu</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><span class="fw-semibold"><?= e($r['kode_tiket']) ?></span></td>
                        <td class="d-none d-lg-table-cell small">#<?= (int) $r['id_order'] ?><br><span class="text-muted"><?= e(date('d/m/Y H:i', strtotime((string) $r['tanggal_order']))) ?></span></td>
                        <td>
                            <?= e($r['nama_user']) ?>
                            <div class="d-xl-none small text-muted"><?= e($r['email_user']) ?></div>
                        </td>
                        <td class="d-none d-xl-table-cell small"><?= e($r['email_user']) ?></td>
                        <td><span class="small"><?= e($r['nama_event']) ?></span></td>
                        <td class="d-none d-md-table-cell small"><?= e($r['nama_tiket']) ?></td>
                        <td class="d-none d-lg-table-cell text-end small">Rp <?= number_format((float) $r['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($r['status_checkin'] === 'sudah'): ?>
                                <span class="badge text-bg-success">Sudah</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning text-dark">Belum</span>
                            <?php endif; ?>
                            <div class="d-xxl-none small text-muted mt-1"><?= $r['waktu_checkin'] ? e(date('d/m/Y H:i', strtotime((string) $r['waktu_checkin']))) : '—' ?></div>
                        </td>
                        <td class="d-none d-xxl-table-cell small"><?= $r['waktu_checkin'] ? e(date('d/m/Y H:i', strtotime((string) $r['waktu_checkin']))) : '—' ?></td>
                        <td class="text-center">
                            <a href="index.php?page=cetak_tiket&id=<?= $r['id_attendee'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Gelang">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                  <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                  <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#table-checkin').DataTable({
            "order": [],
            "language": {
                "emptyTable": "Tidak ada data tiket yang statusnya sudah dikonfirmasi admin."
            }
        });

        const inputKode = document.getElementById('ticket_code');
        var btnStart = document.getElementById('btnStartScan');
        var scanInfo = document.getElementById('scanInfo');
        var readerBox = document.getElementById('reader');
        var scanner = null;
        var scannerAktif = false;

        function hentikanScanner() {
            if (!scanner || !scannerAktif) {
                return Promise.resolve();
            }
            return scanner.stop().then(function () {
                scannerAktif = false;
                readerBox.style.display = 'none';
                btnStart.textContent = 'Mulai Scan';
            });
        }

        btnStart.addEventListener('click', function () {
            if (scannerAktif) {
                hentikanScanner();
                scanInfo.textContent = 'Scan dihentikan.';
                return;
            }

            scanner = new Html5Qrcode('reader');
            readerBox.style.display = 'block';
            btnStart.textContent = 'Stop Scan';
            scanInfo.textContent = 'Arahkan kamera ke barcode tiket.';

            scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: function (viewfinderWidth, viewfinderHeight) {
                        var w = Math.floor(viewfinderWidth * 0.85);
                        var h = Math.floor(viewfinderHeight * 0.35);
                        return { width: Math.max(w, 200), height: Math.max(h, 100) };
                    }
                },
                function (decodedText) {
                    inputKode.value = (decodedText || '').trim().toUpperCase();
                    scanInfo.textContent = 'Kode tiket berhasil dibaca: ' + inputKode.value;
                    hentikanScanner();
                },
            ).then(function () {
                scannerAktif = true;
            }).catch(function () {
                scanInfo.textContent = 'Kamera tidak bisa dibuka. Coba izinkan akses kamera di browser.';
                readerBox.style.display = 'none';
                btnStart.textContent = 'Mulai Scan';
            });
        });
    });
</script>
