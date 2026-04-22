<?php
// Pencarian nama event (kosong = tampil semua)
$kataKunci = trim($_GET['q'] ?? '');
$sql = 'SELECT e.id_event, e.nama_event, e.tanggal, v.nama_venue, MIN(t.harga) AS min_price
    FROM event e
    LEFT JOIN venue v ON v.id_venue = e.id_venue
    LEFT JOIN tiket t ON t.id_event = e.id_event
    WHERE e.nama_event LIKE ?
    GROUP BY e.id_event
    ORDER BY e.tanggal DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute(['%' . $kataKunci . '%']);
$daftarEvent = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Daftar Event</h4>
    <form class="d-flex">
        <input type="hidden" name="page" value="user_dashboard">
        <input class="form-control" name="q" placeholder="Cari event" value="<?= e($kataKunci) ?>">
    </form>
</div>
<div class="row g-3">
<?php foreach ($daftarEvent as $ev): ?>
    <div class="col-md-4">
        <div class="card card-modern h-100">
            <div class="card-body">
                <h5 class="card-title"><?= e($ev['nama_event']) ?></h5>
                <p class="text-secondary mb-1"><?= e((string)($ev['nama_venue'] ?? '-')) ?></p>
                <p class="mb-1"><?= e(date('d M Y', strtotime($ev['tanggal']))) ?></p>
                <p class="fw-semibold">Mulai Rp <?= number_format((float)($ev['min_price'] ?? 0), 0, ',', '.') ?></p>
                <a class="btn btn-primary btn-sm" href="index.php?page=event_detail&id=<?= (int)$ev['id_event'] ?>">Lihat Detail</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
