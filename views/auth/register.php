<?php $title='Daftar';require __DIR__.'/../layouts/header.php';?>
<div class="auth-page">
    <section class="auth-card" aria-labelledby="authTitle">
        <div class="auth-brand">
            <div class="auth-mark" aria-hidden="true">A</div>
            <h1 id="authTitle">AlatQU</h1>
            <p>Buat akun baru</p>
        </div>
        <?php if($error):?><div class="alert" role="alert"><?=e($error)?></div><?php endif;?>
        <form class="auth-form" method="post">
            <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
            <div class="field"><label for="nama">Nama lengkap</label><input class="input" id="nama" name="nama" placeholder="Nama lengkap" value="<?=old('nama')?>" autocomplete="name" required></div>
            <div class="field"><label for="username">Username</label><input class="input" id="username" name="username" placeholder="Username" value="<?=old('username')?>" autocomplete="username" required></div>
            <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" placeholder="nama@email.com" value="<?=old('email')?>" autocomplete="email" required></div>
            <div class="field"><label for="regpass">Password</label><div class="password-wrap"><input class="input" id="regpass" type="password" name="password" placeholder="Buat password" autocomplete="new-password" required><button class="toggle-pass" type="button" data-toggle-password="regpass" aria-label="Tampilkan atau sembunyikan password">◉</button></div></div>
            <div class="field"><label for="confirm">Konfirmasi password</label><div class="password-wrap"><input class="input" id="confirm" type="password" name="confirm" placeholder="Ulangi password" autocomplete="new-password" required><button class="toggle-pass" type="button" data-toggle-password="confirm" aria-label="Tampilkan atau sembunyikan konfirmasi password">◉</button></div></div>
            <button class="btn btn-primary" type="submit" style="width:100%">Buat akun</button>
        </form>
        <div class="auth-footer">Sudah punya akun? <a class="link" href="<?=base_url('login')?>">Masuk</a></div>
    </section>
</div>
</body>
</html>
