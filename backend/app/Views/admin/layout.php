<?php
$admin = session('admin');
$db    = db_connect();
$badge = [
    'pesanan'   => $db->table('orders')->where('status', 'baru')->countAllResults(),
    'reservasi' => $db->table('reservations')->where('status', 'baru')->countAllResults(),
];
$path  = uri_string();
$nav   = [
    ['admin', 'Dashboard', 'M3 13h8V3H3zm10 8h8V11h-8zM3 21h8v-6H3zm10-18v6h8V3z', null],
    ['admin/pesanan', 'Pesanan', 'M6 7h12l-1 13H7L6 7zM9 7a3 3 0 0 1 6 0', 'pesanan'],
    ['admin/reservasi', 'Reservasi', 'M4 5h16v15H4zM4 10h16M9 3v4M15 3v4', 'reservasi'],
    ['admin/menu', 'Menu', 'M4 11h16a8 8 0 0 1-16 0zM7 21h10M8 7c0-1.5 1.5-1.5 1.5-3M12 7c0-1.5 1.5-1.5 1.5-3M16 7c0-1.5 1.5-1.5 1.5-3', null],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= esc($title ?? 'Admin') ?> · Admin <?= esc(config('Store')->name) ?></title>
    <link rel="icon" href="<?= base_url('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
</head>
<body>
<div class="admin">
    <aside class="sidebar" id="sidebar">
        <a href="<?= site_url('admin') ?>" class="brand"><span class="mark">雲</span><span>kumo<b>ramen</b></span> <small>admin</small></a>
        <nav>
            <?php foreach ($nav as [$url, $label, $icon, $key]): $on = $url === 'admin' ? $path === 'admin' : str_starts_with($path, $url); ?>
                <a href="<?= site_url($url) ?>" class="<?= $on ? 'active' : '' ?>"><svg viewBox="0 0 24 24"><path d="<?= $icon ?>"/></svg><?= $label ?><?php if ($key && $badge[$key]): ?><span class="pill"><?= $badge[$key] ?></span><?php endif ?></a>
            <?php endforeach ?>
        </nav>
        <div class="sidebar-foot">
            <a href="<?= base_url('/') ?>" target="_blank" rel="noopener">↗ Lihat website</a>
            <a href="<?= site_url('admin/logout') ?>">⎋ Logout</a>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <button type="button" class="menu-toggle" aria-label="Buka menu" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <h1><?= esc($title ?? 'Admin') ?></h1>
            <span class="who"><?= esc($admin['name'] ?? '') ?></span>
        </header>
        <?php if (session('success')): ?><div class="alert ok"><?= session('success') ?></div><?php endif ?>
        <?php if (session('error')): ?><div class="alert err"><?= esc(session('error')) ?></div><?php endif ?>
        <?php if (session('errors')): ?><div class="alert err"><ul><?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul></div><?php endif ?>
        <?= $this->renderSection('content') ?>
    </main>
</div>
</body>
</html>
