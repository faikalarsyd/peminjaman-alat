<?php $title='Masuk';require __DIR__.'/../layouts/header.php';?>
<div class="auth-page">
    <section class="auth-card" aria-labelledby="authTitle">
        <div class="auth-brand">
            <div class="auth-mark" aria-hidden="true">A</div>
            <h1 id="authTitle">AlatQU</h1>
            <p>Masuk untuk melanjutkan</p>
        </div>
        <?php if($error):?><div class="alert" role="alert"><?=e($error)?></div><?php endif;?>
        <?php if($m=flash('success')):?><div class="alert success" role="status"><?=e($m)?></div><?php endif;?>
        <form class="auth-form" method="post">
            <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
            <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" placeholder="nama@email.com" autocomplete="username" required></div>
            <div class="field"><label for="password">Password</label><div class="password-wrap"><input class="input" id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required><button class="toggle-pass" type="button" data-toggle-password="password" aria-label="Tampilkan atau sembunyikan password">◉</button></div></div>
            <div class="remember"><label class="check"><input type="checkbox" name="remember"><span>Ingat saya</span></label><a class="link" href="#" onclick="alert('Fitur reset password dapat dikembangkan selanjutnya.');return false">Lupa password?</a></div>
            <button class="btn btn-primary" type="submit" style="width:100%">Masuk</button>
        </form>
        <div class="auth-footer">Belum punya akun? <a class="link" href="<?=base_url('register')?>">Daftar</a></div>
    </section>
</div>
</body>
</html>
