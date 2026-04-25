<?php
$stmt = $pdo->query('SELECT * FROM metode_pembayaran ORDER BY id_metode DESC');
$metodes = $stmt->fetchAll();

$editMode = false;
$editData = null;
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];
    $stmtE = $pdo->prepare('SELECT * FROM metode_pembayaran WHERE id_metode = ?');
    $stmtE->execute([$idEdit]);
    $editData = $stmtE->fetch();
    if ($editData) {
        $editMode = true;
    }
}
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card card-modern h-100">
            <div class="card-body">
                <h5 class="mb-4"><?= $editMode ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' ?></h5>
                <form action="index.php?action=save_metode_pembayaran" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id_metode" value="<?= (int)$editData['id_metode'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Pembayaran</label>
                        <select name="jenis" class="form-select" required>
                            <option value="">Pilih Jenis...</option>
                            <option value="Bank" <?= $editMode && $editData['jenis'] === 'Bank' ? 'selected' : '' ?>>Transfer Bank</option>
                            <option value="E-Wallet" <?= $editMode && $editData['jenis'] === 'E-Wallet' ? 'selected' : '' ?>>E-Wallet</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Bank / Penyedia</label>
                        <input type="text" name="nama_penyedia" class="form-control" value="<?= $editMode ? e($editData['nama_penyedia']) : '' ?>" placeholder="Contoh: BCA, GoPay, OVO" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Akun / Rekening</label>
                        <input type="text" name="nomor_akun" class="form-control" value="<?= $editMode ? e($editData['nomor_akun']) : '' ?>" placeholder="Nomor Rekening / No. HP" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Nama Pemilik Akun / Bisnis</label>
                        <input type="text" name="nama_bisnis" class="form-control" value="<?= $editMode ? e((string)$editData['nama_bisnis']) : '' ?>" placeholder="Atas Nama (Opsional)">
                        <div class="form-text">Contoh: PT. Event Kreatif (untuk QRIS/Bank)</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><?= $editMode ? 'Simpan Perubahan' : 'Simpan Data' ?></button>
                        <?php if ($editMode): ?>
                            <a href="index.php?page=metode_pembayaran" class="btn btn-light w-100">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-modern h-100">
            <div class="card-body">
                <h5 class="mb-4">Daftar Metode Pembayaran</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis</th>
                                <th>Penyedia</th>
                                <th>Nomor Akun</th>
                                <th>Atas Nama</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($metodes)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada metode pembayaran.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($metodes as $m): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $m['jenis'] === 'Bank' ? 'primary' : ($m['jenis'] === 'E-Wallet' ? 'info' : 'success') ?> bg-opacity-10 text-<?= $m['jenis'] === 'Bank' ? 'primary' : ($m['jenis'] === 'E-Wallet' ? 'info' : 'success') ?> border border-<?= $m['jenis'] === 'Bank' ? 'primary' : ($m['jenis'] === 'E-Wallet' ? 'info' : 'success') ?> px-2 py-1">
                                                <?= e($m['jenis']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-medium text-dark"><?= e($m['nama_penyedia']) ?></td>
                                        <td><?= e($m['nomor_akun']) ?></td>
                                        <td><?= e((string)$m['nama_bisnis']) ?></td>
                                        <td class="text-end">
                                            <a href="index.php?page=metode_pembayaran&edit=<?= (int)$m['id_metode'] ?>" class="btn btn-sm btn-outline-info">Edit</a>
                                            <form method="post" action="index.php?action=delete_metode_pembayaran&page=metode_pembayaran" class="d-inline" onsubmit="return confirm('Hapus metode pembayaran ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="id_metode" value="<?= (int)$m['id_metode'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
