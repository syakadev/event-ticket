<?php
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM venue WHERE id_venue=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query('SELECT * FROM venue ORDER BY id_venue DESC')->fetchAll();
?>
<div class="card card-modern mb-4">
    <div class="card-body">
        <h5><?= $edit ? 'Edit Venue' : 'Tambah Venue' ?></h5>
        <form method="post" action="index.php?action=save_venue&page=venue" class="row g-2">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)($edit['id_venue'] ?? 0) ?>">
            <div class="col-md-4"><input class="form-control" name="nama_venue" placeholder="Nama venue" required value="<?= e($edit['nama_venue'] ?? '') ?>"></div>
            <div class="col-md-4"><input class="form-control" name="alamat" placeholder="Alamat" value="<?= e($edit['alamat'] ?? '') ?>"></div>
            <div class="col-md-2"><input class="form-control" type="number" min="0" name="kapasitas" placeholder="Kapasitas" value="<?= e((string)($edit['kapasitas'] ?? '')) ?>"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
        </form>
    </div>
</div>
<div class="card card-modern">
    <div class="card-body">
        <div class="table-responsive"><table id="table-venue" class="table table-striped">
            <thead><tr><th>Nama</th><th>Alamat</th><th>Kapasitas</th><th width="180">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nama_venue']) ?></td><td><?= e($r['alamat'] ?? '-') ?></td><td><?= e((string)$r['kapasitas']) ?></td>
                    <td class="d-flex gap-2">
                        <a class="btn btn-sm btn-warning" href="index.php?page=venue&edit=<?= (int)$r['id_venue'] ?>">Edit</a>
                        <form method="post"
                              action="index.php?action=delete_venue&page=venue"
                              data-confirm-title="Hapus Venue"
                              data-confirm-message="Yakin ingin menghapus venue ini?"
                              data-confirm-ok-text="Ya, Hapus"
                              data-confirm-ok-class="btn btn-danger">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int)$r['id_venue'] ?>">
                                <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#table-venue').DataTable({
        "order": []
    });
});
</script>
