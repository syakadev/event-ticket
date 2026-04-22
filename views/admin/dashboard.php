<?php
$totalUser = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalOrder = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='paid'")->fetchColumn();

$recentOrders = $pdo->query('SELECT o.id_order, o.status, o.total, u.nama 
    FROM orders o 
    JOIN users u ON u.id_user=o.id_user 
    ORDER BY o.id_order DESC 
    LIMIT 5')->fetchAll();
?>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card card-modern"><div class="card-body"><p class="text-secondary mb-1">Total User</p><h3><?= $totalUser ?></h3></div></div></div>
    <div class="col-md-4"><div class="card card-modern"><div class="card-body"><p class="text-secondary mb-1">Total Order</p><h3><?= $totalOrder ?></h3></div></div></div>
    <div class="col-md-4"><div class="card card-modern"><div class="card-body"><p class="text-secondary mb-1">Total Pendapatan (Diterima)</p><h3>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3></div></div></div>
</div>
<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Akses Cepat</h5>
        <a class="btn btn-outline-primary btn-sm" href="index.php?page=report_transaksi">Laporan Transaksi</a>
        <a class="btn btn-outline-primary btn-sm" href="index.php?page=report_tiket">Laporan Tiket Terjual</a>
        <a class="btn btn-outline-primary btn-sm" href="index.php?page=checkin">Check-in Tiket</a>
    </div>
</div>

<div class="card card-modern mt-4">
    <div class="card-body">
        <h5 class="mb-3">Riwayat Order Terbaru</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Order</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <?php
                            $badge = 'secondary';
                            if ($order['status'] === 'pending') {
                                $badge = 'warning';
                            } elseif ($order['status'] === 'paid') {
                                $badge = 'info';
                            } elseif ($order['status'] === 'accepted') {
                                $badge = 'success';
                            } elseif ($order['status'] === 'cancel') {
                                $badge = 'danger';
                            }
                        ?>
                        <tr>
                            <td>#<?= (int)$order['id_order'] ?></td>
                            <td><?= e($order['nama']) ?></td>
                            <td>Rp <?= number_format((float)$order['total'], 0, ',', '.') ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= e($order['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
