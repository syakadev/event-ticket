<?php
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$params = [];
$whereClause = "";
if ($start_date && $end_date) {
    $whereClause = " WHERE DATE(o.tanggal_order) BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date'] = $end_date;
} elseif ($start_date) {
    $whereClause = " WHERE DATE(o.tanggal_order) >= :start_date";
    $params[':start_date'] = $start_date;
} elseif ($end_date) {
    $whereClause = " WHERE DATE(o.tanggal_order) <= :end_date";
    $params[':end_date'] = $end_date;
}

$stmt = $pdo->prepare("
    SELECT o.id_order, o.tanggal_order, o.total, o.status, u.nama, v.kode_voucher,
           (SELECT COUNT(*) FROM attendee a JOIN order_detail od ON od.id_detail=a.id_detail WHERE od.id_order = o.id_order) as ticket_count,
           p.bukti_pembayaran
    FROM orders o 
    JOIN users u ON u.id_user=o.id_user 
    LEFT JOIN voucher v ON v.id_voucher=o.id_voucher 
    LEFT JOIN pembayaran p ON p.id_order=o.id_order
    $whereClause
    ORDER BY o.id_order DESC
");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Laporan Data Transaksi</h5>
        
        <form method="GET" action="index.php" class="row g-3 mb-4" id="filterForm">
            <input type="hidden" name="page" value="report_transaksi">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= e($start_date) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= e($end_date) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="index.php?page=report_transaksi" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <p class="text-muted mb-3 small">Alur: pending (belum bayar) → paid (menunggu admin) → admin terima = tiket terbit &amp; status <strong>accepted</strong>, atau ditolak / dibatalkan = <strong>cancel</strong>.</p>
        <div class="table-responsive">
            <table id="table-transaksi" class="table table-striped">
                <thead><tr><th>ID</th><th>User</th><th>Tanggal</th><th>Voucher</th><th>Status</th><th>Total</th><th>Bukti</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                        $displayStatus = $r['status'];
                        if ($r['status'] === 'paid' && $r['ticket_count'] > 0) {
                            $displayStatus = 'accepted';
                        }
                        $badge = 'secondary';
                        if ($displayStatus === 'pending') {
                            $badge = 'warning';
                        } elseif ($displayStatus === 'paid') {
                            $badge = 'info';
                        } elseif ($displayStatus === 'accepted') {
                            $badge = 'success';
                        } elseif ($displayStatus === 'cancel') {
                            $badge = 'danger';
                        }
                    ?>
                    <tr>
                        <td>#<?= (int)$r['id_order'] ?></td>
                        <td><?= e($r['nama']) ?></td>
                        <td><?= e($r['tanggal_order']) ?></td>
                        <td><?= e((string)($r['kode_voucher'] ?? '-')) ?></td>
                        <td><span class="badge bg-<?= $badge ?>"><?= e($displayStatus) ?></span></td>
                        <td>Rp <?= number_format((float)$r['total'], 0, ',', '.') ?></td>
                        <td>
                            <?php if (!empty($r['bukti_pembayaran'])): ?>
                                <a href="img/bukti-pembayaran/<?= e($r['bukti_pembayaran']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($displayStatus === 'paid'): ?>
                                <div class="d-flex flex-wrap gap-1">
                                <form method="post"
                                      action="index.php?action=accept_order&page=report_transaksi"
                                      data-confirm-title="Terima pembayaran"
                                      data-confirm-message="Terima pembayaran dan terbitkan tiket untuk order ini?"
                                      data-confirm-ok-text="Ya, Terima"
                                      data-confirm-ok-class="btn btn-primary">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="order_id" value="<?= (int)$r['id_order'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Terima</button>
                                </form>
                                <form method="post"
                                      action="index.php?action=reject_order&page=report_transaksi"
                                      data-confirm-title="Tolak pembayaran"
                                      data-confirm-message="Batalkan order ini dan kembalikan kuota voucher (jika ada)?"
                                      data-confirm-ok-text="Ya, Tolak"
                                      data-confirm-ok-class="btn btn-danger">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="order_id" value="<?= (int)$r['id_order'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Tolak</button>
                                </form>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                e.preventDefault();
                alert('Peringatan: Tanggal akhir tidak boleh lebih kecil dari tanggal awal.');
            }
        });
    }

    $('#table-transaksi').DataTable({
        "order": [[ 0, "desc" ]],
        layout: {
            topStart: {
                buttons: [
                    'copy', 
                    {
                        extend: 'excel',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    }, 
                    {
                        extend: 'pdf',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    }, 
                    {
                        extend: 'print',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    }
                ]
            }
        }
    });
});
</script>
