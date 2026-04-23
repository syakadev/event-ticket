<?php
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$params = [];
$dateCondition = "";
if ($start_date && $end_date) {
    $dateCondition = " AND DATE(o.tanggal_order) BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date'] = $end_date;
} elseif ($start_date) {
    $dateCondition = " AND DATE(o.tanggal_order) >= :start_date";
    $params[':start_date'] = $start_date;
} elseif ($end_date) {
    $dateCondition = " AND DATE(o.tanggal_order) <= :end_date";
    $params[':end_date'] = $end_date;
}

$stmt = $pdo->prepare("SELECT e.nama_event, COALESCE(SUM(od.qty),0) AS total_terjual, COALESCE(SUM(od.subtotal),0) AS total_subtotal 
    FROM event e 
    LEFT JOIN tiket t ON t.id_event=e.id_event 
    LEFT JOIN order_detail od ON od.id_tiket=t.id_tiket 
    LEFT JOIN orders o ON o.id_order=od.id_order AND o.status = 'accepted' $dateCondition 
    GROUP BY e.id_event 
    ORDER BY e.id_event DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Laporan Penjualan Tiket per Event</h5>

        <form method="GET" action="index.php" class="row g-3 mb-4" id="filterForm">
            <input type="hidden" name="page" value="report_tiket">
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
                <a href="index.php?page=report_tiket" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        <table id="table-report-tiket" class="table table-striped">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Total Tiket Terjual</th>
                    <th>Subtotal Penjualan</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nama_event']) ?></td>
                    <td><?= (int)$r['total_terjual'] ?> Tiket</td>
                    <td>Rp <?= number_format((float)$r['total_subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
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

    $('#table-report-tiket').DataTable({
        "order": [],
        layout: {
            topStart: {
                buttons: ['copy', 'excel', 'pdf', 'print']
            }
        }
    });
});
</script>
