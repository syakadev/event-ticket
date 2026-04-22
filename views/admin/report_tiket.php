<?php
$rows = $pdo->query("SELECT e.nama_event, COALESCE(SUM(od.qty),0) AS total_terjual, COALESCE(SUM(od.subtotal),0) AS total_subtotal FROM event e LEFT JOIN tiket t ON t.id_event=e.id_event LEFT JOIN order_detail od ON od.id_tiket=t.id_tiket LEFT JOIN orders o ON o.id_order=od.id_order AND o.status = 'accepted' GROUP BY e.id_event ORDER BY e.id_event DESC")->fetchAll();
?>
<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Laporan Penjualan Tiket per Event</h5>
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
