<<<<<<< HEAD
<?php
$title = 'Kelola Pengembalian';
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/navbar.php';
require __DIR__ . '/../layouts/sidebar.php';
?>
<main class="content">
    <div class="page-head">
        <h1><?= isset($item) ? 'Edit' : 'Input' ?> Pengembalian</h1>
        <p>Catat pengembalian alat dari transaksi yang masih aktif. Saat disimpan, transaksi langsung diselesaikan dan stok alat diperbarui.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (empty($loans)): ?>
        <div class="alert">Tidak ada transaksi aktif yang bisa dikembalikan. Transaksi yang sudah memiliki pengajuan pengembalian tidak ditampilkan.</div>
        <div class="form-actions">
            <a class="btn" href="<?= base_url('pengembalian') ?>">Kembali ke Pengembalian</a>
        </div>
    <?php else: ?>
        <form class="card form-card" method="post">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <div class="form-grid">
                <div class="field full">
                    <label for="peminjaman_id">Transaksi Peminjaman</label>
                    <select class="select" id="peminjaman_id" name="peminjaman_id" required>
                        <option value="">Pilih transaksi</option>
                        <?php foreach ($loans as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= ((int)$selectedLoanId === (int)$p['id']) ? 'selected' : '' ?>>
                                <?= e($p['kode_peminjaman']) ?> — <?= e($p['nama_peminjam']) ?> — <?= e($p['status']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="tanggal_pengembalian">Jam Pengembalian</label>
                    <input class="input" id="tanggal_pengembalian" type="time" name="tanggal_pengembalian" value="<?= e($returnTime) ?>" required>
                </div>

                <div class="field">
                    <label for="kondisi_alat">Kondisi Alat</label>
                    <input class="input" id="kondisi_alat" name="kondisi_alat" value="<?= e($item['kondisi_alat'] ?? 'Baik') ?>" required>
                </div>

                <div class="field full">
                    <label for="catatan">Catatan</label>
                    <textarea class="textarea" id="catatan" name="catatan"><?= e($item['catatan'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn" href="<?= base_url('pengembalian') ?>">Kembali</a>
                <button class="btn btn-primary" type="submit">Simpan Pengembalian</button>
            </div>
        </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
=======
<?php $title='Kelola Pengembalian';require __DIR__.'/../layouts/header.php';require __DIR__.'/../layouts/navbar.php';require __DIR__.'/../layouts/sidebar.php';?>
<main class="content"><div class="page-head"><h1><?=isset($item)?'Edit':'Input'?> Pengembalian</h1><p>Masukkan jam pengembalian alat.</p></div><?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?><form class="card form-card" method="post"><input type="hidden" name="_csrf" value="<?=csrf_token()?>"><div class="form-grid"><div class="field full"><label>Transaksi Peminjaman</label><select class="select" name="peminjaman_id" required><?php foreach($loans as $p):?><option value="<?=$p['id']?>" <?=((int)($item['peminjaman_id']??0)===$p['id'])?'selected':''?>><?=e($p['kode_peminjaman'])?> — <?=e($p['nama_peminjam'])?> — <?=e($p['status'])?></option><?php endforeach;?></select></div><div class="field"><label>Jam Pengembalian</label><input class="input" type="time" name="tanggal_pengembalian" value="<?=e($returnTime)?>" required></div><div class="field"><label>Kondisi Alat</label><input class="input" name="kondisi_alat" value="<?=e($item['kondisi_alat']??'Baik')?>" required></div><div class="field full"><label>Catatan</label><textarea class="textarea" name="catatan"><?=e($item['catatan']??'')?></textarea></div></div><div class="form-actions"><a class="btn" href="<?=base_url('pengembalian')?>">Kembali</a><button class="btn btn-primary">Simpan</button></div></form></main><?php require __DIR__.'/../layouts/footer.php';?>
>>>>>>> 05c5d7a4318d906e8f5f87021cd60d448560638f
