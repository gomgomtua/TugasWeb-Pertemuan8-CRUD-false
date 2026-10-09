<?php
/**
 * includes/header.php
 * Variabel dari halaman pemanggil: $pageTitle (judul), $activeNav (menu aktif).
 * Pastikan includes/helpers.php sudah di-require sebelum file ini.
 */
$pageTitle = $pageTitle ?? 'Inventaris';
$activeNav = $activeNav ?? '';
$flash     = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — Inventaris Barang</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="index.php">📦 Inventaris Barang</a>
        <nav>
            <a href="index.php" class="<?= $activeNav === 'produk' ? 'active' : '' ?>">Produk</a>
            <a href="create.php" class="<?= $activeNav === 'tambah' ? 'active' : '' ?>">Tambah</a>
            <a href="logs.php" class="<?= $activeNav === 'riwayat' ? 'active' : '' ?>">Riwayat</a>
        </nav>
    </div>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endif; ?>
