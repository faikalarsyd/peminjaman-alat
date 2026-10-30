<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/redirect.php';
$title = isset($title) ? $title : 'AlatQU';
$assetVersion = APP_VERSION . '-alatqu-ui1';
$layoutUser = current_user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AlatQU — pengelolaan alat, peminjaman, pengembalian, dan inventaris.">
    <meta name="theme-color" content="#f5f7fb">
    <title><?=e($title === 'AlatQU' ? 'AlatQU' : $title . ' - AlatQU')?></title>
    <link rel="stylesheet" href="<?=e(asset_url('css/style.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/login.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/dashboard.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/form.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/table.css'))?>?v=<?=$assetVersion?>">
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('alatqu-theme')||'light';}catch(e){document.documentElement.dataset.theme='light';}</script>
    <link rel="stylesheet" href="<?=e(asset_url('css/alatqu.css'))?>?v=<?=e($assetVersion)?>">
    <script defer src="<?=e(asset_url('js/script.js'))?>?v=<?=$assetVersion?>"></script>
</head>
<body data-user-id="<?=e($layoutUser['id'] ?? 'guest')?>">
