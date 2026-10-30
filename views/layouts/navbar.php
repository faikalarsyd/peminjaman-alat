<header class="topbar">
    <button class="hamb icon-button" data-hamb aria-label="Buka menu" type="button">
        <svg class="icon"><use href="<?=asset_url('icons/icons.svg')?>#menu"></use></svg>
    </button>
    <a class="brand" href="<?=base_url('dashboard')?>" aria-label="AlatQU - Dashboard">
        <span class="brand-mark-text" aria-hidden="true">A</span>
        <span>AlatQU</span>
        <span class="brand-caption">Manajemen alat</span>
    </a>
    <div class="topbar-actions">
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" title="Aktifkan mode gelap">
            <span class="theme-toggle-icon" data-theme-icon aria-hidden="true">☾</span>
            <span data-theme-label>Mode gelap</span>
        </button>
        <a class="profile-btn" href="<?=base_url('profile')?>" title="Profil" aria-label="Buka profil">
            <span class="avatar-fallback js-avatar-fallback" aria-hidden="true"><?=e(strtoupper(substr((string)(current_user()['nama'] ?? 'U'), 0, 1)))?></span>
            <img class="js-profile-avatar" alt="Foto profil" hidden>
        </a>
    </div>
</header>
