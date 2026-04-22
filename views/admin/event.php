<?php
$venues = $pdo->query('SELECT id_venue, nama_venue FROM venue ORDER BY nama_venue')->fetchAll();
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM event WHERE id_event=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query('SELECT e.*, v.nama_venue FROM event e LEFT JOIN venue v ON v.id_venue = e.id_venue ORDER BY e.tanggal DESC')->fetchAll();
?>
<div class="card card-modern mb-4"><div class="card-body">
<h5><?= $edit ? 'Edit Event' : 'Tambah Event' ?></h5>
<form class="row g-2" method="post" action="index.php?action=save_event&page=event" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id_event'] ?? 0) ?>">
    <div class="col-md-2"><select name="id_venue" class="form-select"><option value="">Pilih venue</option><?php foreach ($venues as $v): ?><option value="<?= (int)$v['id_venue'] ?>" <?= (int)($edit['id_venue'] ?? 0)===(int)$v['id_venue']?'selected':'' ?>><?= e($v['nama_venue']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><input class="form-control" name="nama_event" placeholder="Nama event" required value="<?= e($edit['nama_event'] ?? '') ?>"></div>
    <div class="col-md-2"><input class="form-control" type="date" name="tanggal" required value="<?= e($edit['tanggal'] ?? '') ?>"></div>
    <div class="col-md-3">
        <input class="form-control" type="file" name="gambar" accept="image/*">
        <?php if(!empty($edit['gambar'])): ?>
            <small class="text-muted">Current: <a href="img/<?= e($edit['gambar']) ?>" target="_blank">Lihat Gambar</a></small>
        <?php endif; ?>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
</form></div></div>

<div class="card card-modern"><div class="card-body table-responsive">
<table id="table-event" class="table table-striped align-middle"><thead><tr><th>Gambar</th><th>Event</th><th>Venue</th><th>Tanggal</th><th width="180">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
    <td>
        <?php if(!empty($r['gambar'])): ?>
            <img src="img/<?= e($r['gambar']) ?>" alt="Img" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
        <?php else: ?>
            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 8px; font-size: 0.7rem;">No Img</div>
        <?php endif; ?>
    </td>
    <td><?= e($r['nama_event']) ?></td><td><?= e((string)($r['nama_venue'] ?? '-')) ?></td><td><?= e(date('d M Y', strtotime($r['tanggal']))) ?></td>
    <td class="d-flex gap-2 p-4"><a class="btn btn-sm btn-warning" href="index.php?page=event&edit=<?= (int)$r['id_event'] ?>">Edit</a>
    <form method="post"
          action="index.php?action=delete_event&page=event"
          data-confirm-title="Hapus Event"
          data-confirm-message="Yakin ingin menghapus event ini?"
          data-confirm-ok-text="Ya, Hapus"
          data-confirm-ok-class="btn btn-danger">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id_event'] ?>">
        <button class="btn btn-sm btn-danger">Hapus</button></form>
    </td>
</tr><?php endforeach; ?></tbody></table>
</div></div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#table-event').DataTable({
        "order": [[ 2, "desc" ]]
    });
});
</script>
