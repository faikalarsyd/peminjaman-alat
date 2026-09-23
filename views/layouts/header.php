<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/redirect.php';
$title = isset($title) ? $title : APP_NAME;
$assetVersion = APP_VERSION;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Aplikasi Peminjaman Alat">
    <meta name="theme-color" content="#2563EB">
    <title><?=e($title)?> - <?=e(APP_NAME)?></title>
    <link rel="stylesheet" href="<?=e(asset_url('css/style.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/login.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/dashboard.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/form.css'))?>?v=<?=$assetVersion?>">
    <link rel="stylesheet" href="<?=e(asset_url('css/table.css'))?>?v=<?=$assetVersion?>">
    <script defer src="<?=e(asset_url('js/script.js'))?>?v=<?=$assetVersion?>"></script>
</head>
<body>
