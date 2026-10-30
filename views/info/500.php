<?php $title = 'Terjadi Kendala'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="auth-page">
    <div class="auth-card" style="text-align:center">
        <div class="modal-icon">!</div>
        <h1>Terjadi kendala</h1>
        <p><?=e($message ?? 'Silakan coba lagi.')?></p>
        <a class="btn btn-primary" href="<?=e(base_url('dashboard'))?>">Kembali</a>
    </div>
</div>
</body></html>
