<?php
$events = $pdo->query('SELECT id_event, nama_event FROM event ORDER BY tanggal DESC')->fetchAll();
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM tiket WHERE id_tiket=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query("SELECT t.*, e.nama_event AS event_title, COALESCE(SUM(od.qty),0) AS sold_qty FROM tiket t JOIN event e ON e.id_event=t.id_event LEFT JOIN order_detail od ON od.id_tiket=t.id_tiket LEFT JOIN orders o ON o.id_order=od.id_order AND o.status IN ('pending', 'paid', 'confirmed') GROUP BY t.id_tiket ORDER BY t.id_tiket DESC")->fetchAll();
?>
<div class="card card-modern mb-4"><div class="card-body">
    <h5><?= $edit ? 'Edit Tiket' : 'Tambah Tiket' ?></h5>
    <form method="post" action="index.php?action=save_tiket&page=tiket" class="row g-2">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id_tiket'] ?? 0) ?>">
        <div class="col-md-3"><select class="form-select" name="id_event" required><option value="">Pilih event</option><?php foreach($events as $e): ?><option value="<?= (int)$e['id_event'] ?>" <?= (int)($edit['id_event'] ?? 0)===(int)$e['id_event']?'selected':'' ?>><?= e($e['nama_event']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><input class="form-control" name="nama_tiket" placeholder="Nama tiket" required value="<?= e($edit['nama_tiket'] ?? '') ?>"></div>
        <div class="col-md-2"><input class="form-control" type="number" min="0" name="harga" placeholder="Harga" required value="<?= e((string)($edit['harga'] ?? '')) ?>"></div>
        <div class="col-md-2"><input class="form-control" type="number" min="1" name="kuota" placeholder="Kuota" required value="<?= e((string)($edit['kuota'] ?? '')) ?>"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
    </form>
</div></div>
<div class="card card-modern"><div class="card-body table-responsive">
<table id="table-tiket" class="table table-striped"><thead><tr><th>Event</th><th>Tiket</th><th>Harga</th><th>Kuota</th><th>Terjual</th><th>Progress</th><th width="180">Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr>
<?php
    $kuota = max(1, (int)$r['kuota']);
    $terjual = (int)$r['sold_qty'];
    $persen = min(100, (int)round(($terjual / $kuota) * 100));
?>
<td><?= e($r['event_title']) ?></td><td><?= e($r['nama_tiket']) ?></td><td>Rp <?= number_format((float)$r['harga'],0,',','.') ?></td><td><?= (int)$r['kuota'] ?></td><td><?= (int)$r['sold_qty'] ?></td>
<td>
    <div class="small mb-1"><?= $terjual ?> / <?= (int)$r['kuota'] ?> (<?= $persen ?>%)</div>
    <div class="progress" style="height: 8px;">
        <div class="progress-bar" style="width: <?= $persen ?>%"></div>
    </div>
</td>
<td class="d-flex gap-2">
    <a class="btn btn-sm btn-warning" href="index.php?page=tiket&edit=<?= (int)$r['id_tiket'] ?>">Edit</a>
    <form method="post" action="index.php?action=delete_tiket&page=tiket" data-confirm-title="Hapus Tiket" data-confirm-message="Yakin ingin menghapus tiket ini?" data-confirm-ok-text="Ya, Hapus" data-confirm-ok-class="btn btn-danger"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id_tiket'] ?>"><button class="btn btn-sm btn-danger">Hapus</button></form>
</td>
</tr><?php endforeach; ?></tbody></table>
</div></div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#table-tiket').DataTable({
        "order": []
    });
});
</script>
