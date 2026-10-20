<?php $title='Profil';require __DIR__.'/../layouts/header.php';require __DIR__.'/../layouts/navbar.php';require __DIR__.'/../layouts/sidebar.php';?>
<main class="content">
    <div class="page-head">
        <div><span class="eyebrow">PENGATURAN AKUN</span><h1>Profil</h1><p>Perbarui informasi akun dan atur foto profil Anda.</p></div>
    </div>
    <?php if($m=flash('success')):?><div class="alert success"><?=e($m)?></div><?php endif;?>
    <?php if($m=flash('error')):?><div class="alert"><?=e($m)?></div><?php endif;?>
    <?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>

    <div class="profile-page-grid">
        <section class="card profile-photo-card" aria-labelledby="photoTitle">
            <div class="profile-card-heading"><span class="section-icon" aria-hidden="true">◎</span><div><h2 id="photoTitle">Foto profil</h2><p>Personalisasi akun AlatQU Anda.</p></div></div>
            <div class="profile-photo-preview">
                <div class="avatar avatar-large" data-profile-avatar>
                    <span class="avatar-fallback js-avatar-fallback" aria-hidden="true"><?=e(strtoupper(substr((string)($user['nama'] ?? 'U'), 0, 1)))?></span>
                    <img class="js-profile-avatar" alt="Foto profil Anda" hidden>
                </div>
                <strong><?=e($user['nama'])?></strong>
                <span class="muted"><?=e(ucfirst($user['role']))?></span>
            </div>
            <div class="profile-photo-actions">
                <label class="btn btn-primary" for="profilePhotoInput">Pilih foto</label>
                <button class="btn" type="button" data-remove-profile-photo hidden>Hapus foto</button>
                <input id="profilePhotoInput" class="visually-hidden" type="file" accept="image/png,image/jpeg,image/webp" data-profile-photo-input>
            </div>
            <p class="helper-text">Format JPG, PNG, atau WebP. Foto disimpan di browser pada perangkat ini.</p>
            <p class="profile-photo-message" data-profile-photo-message role="status" aria-live="polite"></p>
        </section>

        <form class="card form-card profile-fields" method="post">
            <div class="profile-card-heading"><span class="section-icon" aria-hidden="true">✎</span><div><h2>Informasi akun</h2><p>Pastikan data Anda tetap akurat.</p></div></div>
            <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
            <div class="form-grid">
                <div class="field"><label for="profileNama">Nama lengkap</label><input class="input" id="profileNama" name="nama" value="<?=e($user['nama'])?>" autocomplete="name" required></div>
                <div class="field"><label for="profileUsername">Username</label><input class="input" id="profileUsername" name="username" value="<?=e($user['username'])?>" autocomplete="username" required></div>
                <div class="field"><label for="profileEmail">Alamat email</label><input class="input" id="profileEmail" name="email" type="email" value="<?=e($user['email'])?>" autocomplete="email" required></div>
                <div class="field"><label for="profileRole">Peran akun</label><input class="input" id="profileRole" value="<?=e(ucfirst($user['role']))?>" disabled></div>
                <div class="field full"><label for="profilePassword">Password baru <span class="label-optional">Opsional</span></label><input class="input" id="profilePassword" type="password" name="password" placeholder="Biarkan kosong jika tidak diubah" autocomplete="new-password"></div>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan perubahan</button><a class="btn" href="<?=base_url('profile/password')?>">Ubah password</a></div>
        </form>
    </div>
</main>
<?php require __DIR__.'/../layouts/footer.php';?>
