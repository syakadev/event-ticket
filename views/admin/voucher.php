<?php
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM voucher WHERE id_voucher=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query("SELECT v.*, COALESCE(COUNT(o.id_order),0) AS dipakai FROM voucher v LEFT JOIN orders o ON o.id_voucher = v.id_voucher AND o.status <> 'cancel' GROUP BY v.id_voucher ORDER BY v.id_voucher DESC")->fetchAll();
?>
<div class="card card-modern mb-4"><div class="card-body">
    <h5><?= $edit ? 'Edit Voucher' : 'Tambah Voucher' ?></h5>
    <form method="post" action="index.php?action=save_voucher&page=voucher" class="row g-2">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id_voucher'] ?? 0) ?>">
        <div class="col-md-4"><input class="form-control" name="kode_voucher" placeholder="Kode voucher" required value="<?= e($edit['kode_voucher'] ?? '') ?>"></div>
        <div class="col-md-2"><input class="form-control" type="number" min="0" name="potongan" placeholder="Potongan" required value="<?= e((string)($edit['potongan'] ?? '')) ?>"></div>
        <div class="col-md-2"><input class="form-control" type="number" min="0" name="kuota" placeholder="Kuota" required value="<?= e((string)($edit['kuota'] ?? '')) ?>"></div>
        <div class="col-md-2"><select class="form-select" name="status"><option value="aktif" <?= ($edit['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= ($edit['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></div>
        <div class="col-md-12"><button class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<div class="card card-modern"><div class="card-body table-responsive">
<table id="table-voucher" class="table table-striped"><thead><tr><th>Kode</th><th>Potongan</th><th>Kuota</th><th>Dipakai</th><th>Progress</th><th>Status</th><th width="180">Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<?php
    $kuotaVoucher = max(1, (int)$r['kuota']);
    $dipakai = (int)$r['dipakai'];
    $persen = min(100, (int)round(($dipakai / $kuotaVoucher) * 100));
?>
<tr>
    <td><?= e($r['kode_voucher']) ?></td>
    <td>Rp <?= number_format((float)$r['potongan'],0,',','.') ?></td>
    <td><?= (int)$r['kuota'] ?></td>
    <td><?= $dipakai ?></td>
    <td>
        <div class="small mb-1"><?= $dipakai ?> / <?= (int)$r['kuota'] ?> (<?= $persen ?>%)</div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-info" style="width: <?= $persen ?>%"></div>
        </div>
    </td>
    <td><?= e($r['status']) ?></td>
    <td class="d-flex gap-2"><a class="btn btn-sm btn-warning" href="index.php?page=voucher&edit=<?= (int)$r['id_voucher'] ?>">Edit</a><form method="post" action="index.php?action=delete_voucher&page=voucher" data-confirm-title="Hapus Voucher" data-confirm-message="Yakin ingin menghapus voucher ini?" data-confirm-ok-text="Ya, Hapus" data-confirm-ok-class="btn btn-danger"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id_voucher'] ?>"><button class="btn btn-sm btn-danger">Hapus</button></form></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap5.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap5.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#table-voucher').DataTable({
        "order": []
    });
});
</script>
