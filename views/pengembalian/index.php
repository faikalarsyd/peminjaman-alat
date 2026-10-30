<?php
$title = 'AlatQU';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/navbar.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<main class="content">
    <div class="page-head">
        <h1>Pengembalian</h1>
        <p>Memantau, mencatat, dan menyelesaikan pengembalian alat</p>
    </div>

    <?php if ($m = flash('success')): ?>
        <div class="alert success"><?= e($m) ?></div>
    <?php endif; ?>
    <?php if ($m = flash('error')): ?>
        <div class="alert"><?= e($m) ?></div>
    <?php endif; ?>

    <?php if (has_role('peminjam')): ?>
        <a class="btn btn-primary no-print" href="<?= base_url('pengembalian/create') ?>">+ Ajukan Pengembalian</a>
    <?php elseif (has_role('admin')): ?>
        <a class="btn btn-primary no-print" href="<?= base_url('pengembalian/create') ?>">+ Catat Pengembalian</a>
    <?php endif; ?>

    <div class="card" style="margin-top:18px">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>KODE</th>
                        <th>PEMINJAM</th>
                        <th>JAM RENCANA KEMBALI</th>
                        <th>JAM PENGEMBALIAN</th>
                        <th>TERLAMBAT</th>
                        <th>DENDA</th>
                        <th>STATUS</th>
                        <?php if (has_role(array('admin', 'petugas'))): ?><th>AKSI</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($returns)): ?>
                        <tr><td colspan="<?= has_role(array('admin', 'petugas')) ? 9 : 8 ?>">Belum ada data pengembalian.</td></tr>
                    <?php else: ?>
                        <?php foreach ($returns as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= e($r['kode_peminjaman']) ?></td>
                                <td><?= e($r['nama_peminjam']) ?></td>
                                <td><?= e(date('H:i', strtotime($r['tanggal_kembali_rencana']))) ?></td>
                                <td><?= e(date('H:i', strtotime($r['tanggal_pengembalian']))) ?></td>
                                <td><?= e($r['terlambat_jam']) ?> jam</td>
                                <td>Rp <?= number_format((float)$r['denda'], 0, ',', '.') ?></td>
                                <td><span class="badge badge-info"><?= e($r['status']) ?></span></td>
                                <?php if (has_role(array('admin', 'petugas'))): ?>
                                    <td class="actions">
                                        <?php if ($r['status'] === 'MENUNGGU'): ?>
                                            <form method="post" action="<?= e(base_url('pengembalian/process/' . $r['id'])) ?>" class="inline-action" onsubmit="return confirm('Selesaikan pengembalian ini dan perbarui stok alat?')">
                                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                                <button class="btn btn-sm btn-success" type="submit">Selesaikan</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (has_role('admin') && $r['status'] === 'MENUNGGU'): ?>
                                            <a class="btn btn-sm" href="<?= base_url('pengembalian/edit/' . $r['id']) ?>">✎</a>
                                            <form method="post" action="<?= e(base_url('pengembalian/delete/' . $r['id'])) ?>" class="inline-action" onsubmit="return confirm('Hapus data pengembalian?')">
                                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                                <button class="btn btn-sm btn-danger" type="submit">▥</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
