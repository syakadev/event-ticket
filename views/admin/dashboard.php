<?php
$totalUser = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalOrder = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='paid'")->fetchColumn();

$recentOrders = $pdo->query('SELECT o.id_order, o.status, o.total, u.nama 
    FROM orders o 
    JOIN users u ON u.id_user=o.id_user 
    ORDER BY o.id_order DESC 
    LIMIT 5')->fetchAll();

// Chart Data Query: Penjualan tiket
$salesData = $pdo->query("
    SELECT t.nama_tiket, 
           COALESCE(SUM(CASE WHEN o.status = 'paid' THEN od.qty ELSE 0 END), 0) as total_paid,
           COALESCE(SUM(CASE WHEN o.status = 'pending' THEN od.qty ELSE 0 END), 0) as total_pesan
    FROM tiket t
    LEFT JOIN order_detail od ON t.id_tiket = od.id_tiket
    LEFT JOIN orders o ON od.id_order = o.id_order AND o.status IN ('paid', 'pending')
    GROUP BY t.id_tiket, t.nama_tiket
")->fetchAll();

$chartLabels = [];
$chartDataPaid = [];
$chartDataPesan = [];
foreach ($salesData as $row) {
    $chartLabels[] = $row['nama_tiket'];
    $chartDataPaid[] = (int)$row['total_paid'];
    $chartDataPesan[] = (int)$row['total_pesan'];
}
?>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card card-modern text-center"><div class="card-body"><p class="text-secondary mb-1">Total User</p><h3><?= $totalUser ?></h3></div></div></div>
    <div class="col-md-4"><div class="card card-modern text-center"><div class="card-body"><p class="text-secondary mb-1">Total Order</p><h3><?= $totalOrder ?></h3></div></div></div>
    <div class="col-md-4"><div class="card card-modern text-center"><div class="card-body"><p class="text-secondary mb-1">Total Pendapatan (Diterima)</p><h3>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3></div></div></div>
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
        <h5 class="mb-3">Statistik Penjualan Tiket</h5>
        <canvas id="ticketSalesChart" style="max-height: 350px;"></canvas>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('ticketSalesChart').getContext('2d');
        const labels = <?= json_encode($chartLabels) ?>;
        const dataPaid = <?= json_encode($chartDataPaid) ?>;
        const dataPesan = <?= json_encode($chartDataPesan) ?>;

        const chartConfig = {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tiket Terjual (Paid)',
                        data: dataPaid,
                        backgroundColor: '#3b82f6', // sleek blue
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 32
                    },
                    {
                        label: 'Tiket Dipesan (Pending)',
                        data: dataPesan,
                        backgroundColor: '#cbd5e1', // soft slate
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 32
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        border: { display: false },
                        ticks: { font: { family: "'Inter', sans-serif" } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: '#f1f5f9', 
                            borderDash: [5, 5],
                            drawBorder: false
                        },
                        border: { display: false },
                        ticks: { 
                            stepSize: 1, 
                            padding: 12,
                            font: { family: "'Inter', sans-serif" }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: { family: "'Inter', sans-serif", weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                        bodyFont: { family: "'Inter', sans-serif", size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 6
                    }
                }
            }
        };

        const ticketSalesChart = new Chart(ctx, chartConfig);

        function updateChartColors() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            
            const colorText = isDark ? '#94a3b8' : '#64748b'; // softer text
            const colorGrid = isDark ? '#334155' : '#f1f5f9';
            const colorPendingBg = isDark ? '#475569' : '#cbd5e1';
            const tooltipBg = isDark ? '#1e293b' : '#0f172a';
            const tooltipBorder = isDark ? '#334155' : 'transparent';

            // Update Dataset (Pending Color)
            ticketSalesChart.data.datasets[1].backgroundColor = colorPendingBg;

            // Update Scales
            ticketSalesChart.options.scales.x.ticks.color = colorText;
            ticketSalesChart.options.scales.y.ticks.color = colorText;
            ticketSalesChart.options.scales.y.grid.color = colorGrid;
            
            // Update Legend
            ticketSalesChart.options.plugins.legend.labels.color = colorText;

            // Update Tooltip
            ticketSalesChart.options.plugins.tooltip.backgroundColor = tooltipBg;
            if(isDark) {
                ticketSalesChart.options.plugins.tooltip.borderColor = tooltipBorder;
                ticketSalesChart.options.plugins.tooltip.borderWidth = 1;
            } else {
                ticketSalesChart.options.plugins.tooltip.borderWidth = 0;
            }

            ticketSalesChart.update();
        }

        // initial color update
        
    });
</script>
