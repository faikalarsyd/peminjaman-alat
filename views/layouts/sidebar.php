<?php
$navUser = current_user() ?: [];
$r = strtolower($navUser['role'] ?? '');
$route = isset($_GET['route']) ? strtolower(trim((string)$_GET['route'], '/')) : 'dashboard';
$section = explode('/', $route)[0] ?: 'dashboard';
$active = static function (array $names) use ($section): string { return in_array($section, $names, true) ? 'active' : ''; };
?>
<aside class="sidebar">
    <div class="sidebar-account">
        <div class="sidebar-avatar avatar">
            <span class="avatar-fallback js-avatar-fallback" aria-hidden="true"><?=e(strtoupper(substr((string)($navUser['nama'] ?? 'U'), 0, 1)))?></span>
            <img class="js-profile-avatar" alt="Foto profil" hidden>
        </div>
        <div class="sidebar-account-copy">
            <strong><?=e($navUser['nama'] ?? 'Pengguna')?></strong>
            <span><?=e(ucfirst($navUser['role'] ?? 'akun'))?></span>
        </div>
    </div>
    <nav aria-label="Navigasi utama">
        <a class="nav-item <?=$active(['dashboard'])?>" href="<?=base_url('dashboard')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#home"></use></svg></span>Dashboard</a>
        <div class="nav-group-title">Menu utama</div>
        <a class="nav-item <?=$active(['alat'])?>" href="<?=base_url('alat')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#box"></use></svg></span><?= $r === 'peminjam' ? 'Daftar Alat' : 'Alat' ?></a>
        <?php if ($r === 'admin'): ?>
            <a class="nav-item <?=$active(['users'])?>" href="<?=base_url('users')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#user"></use></svg></span>Pengguna</a>
            <a class="nav-item <?=$active(['kategori'])?>" href="<?=base_url('kategori')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#settings"></use></svg></span>Kategori</a>
        <?php endif; ?>
        <a class="nav-item <?=$active(['peminjaman'])?>" href="<?=base_url('peminjaman')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#table"></use></svg></span><?= $r === 'peminjam' ? 'Peminjaman Saya' : 'Peminjaman' ?></a>
        <a class="nav-item <?=$active(['pengembalian'])?>" href="<?=base_url('pengembalian')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#back"></use></svg></span>Pengembalian</a>
        <?php if (in_array($r, ['admin', 'petugas'], true)): ?>
            <a class="nav-item <?=$active(['laporan'])?>" href="<?=base_url('laporan')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#table"></use></svg></span>Laporan</a>
        <?php endif; ?>
        <?php if ($r === 'admin'): ?>
            <div class="nav-group-title">Administrasi</div>
            <a class="nav-item" href="<?=base_url('alat/create')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#box"></use></svg></span>Tambah Alat</a>
            <a class="nav-item" href="<?=base_url('users/create')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#user"></use></svg></span>Tambah Pengguna</a>
            <a class="nav-item <?=$active(['log'])?>" href="<?=base_url('log')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#menu"></use></svg></span>Log Aktivitas</a>
            <a class="nav-item <?=$active(['pengaturan'])?>" href="<?=base_url('pengaturan')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#settings"></use></svg></span>Pengaturan</a>
        <?php endif; ?>
        <div class="nav-group-title">Akun</div>
        <a class="nav-item <?=$active(['info'])?>" href="<?=base_url('info')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#info"></use></svg></span>Info & Bantuan</a>
        <a class="nav-item <?=$active(['profile'])?>" href="<?=base_url('profile')?>"><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#user"></use></svg></span>Profil</a>
        <a class="nav-item" href="<?=base_url('logout')?>" data-logout><span class="nav-icon"><svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#logout"></use></svg></span>Keluar</a>
    </nav>
</aside>
