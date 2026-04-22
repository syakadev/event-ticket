<?php
$rows = $pdo->query('
    SELECT o.id_order, o.tanggal_order, o.total, o.status, u.nama, v.kode_voucher,
           (SELECT COUNT(*) FROM attendee a JOIN order_detail od ON od.id_detail=a.id_detail WHERE od.id_order = o.id_order) as ticket_count 
    FROM orders o 
    JOIN users u ON u.id_user=o.id_user 
    LEFT JOIN voucher v ON v.id_voucher=o.id_voucher 
    ORDER BY o.id_order DESC
')->fetchAll();
?>
<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Laporan Data Transaksi</h5>
        <p class="text-muted mb-3 small">Alur: pending (belum bayar) → paid (menunggu admin) → admin terima = tiket terbit &amp; status <strong>accepted</strong>, atau ditolak / dibatalkan = <strong>cancel</strong>.</p>
        <div class="table-responsive">
            <table id="table-transaksi" class="table table-striped">
                <thead><tr><th>ID</th><th>User</th><th>Tanggal</th><th>Voucher</th><th>Status</th><th>Total</th><th>Aksi</th></tr></thead>
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
