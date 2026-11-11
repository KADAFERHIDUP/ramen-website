<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="toolbar">
    <nav class="tabs">
        <a class="<?= $status === '' ? 'active' : '' ?>" href="<?= site_url('admin/pesanan') ?>">Semua</a>
        <?php foreach (config('Store')->orderStatuses as $k => $l): ?><a class="<?= $status === $k ? 'active' : '' ?>" href="<?= site_url('admin/pesanan?status=' . $k) ?>"><?= $l ?></a><?php endforeach ?>
    </nav>
    <form class="filters" method="get" action="<?= site_url('admin/pesanan') ?>">
        <?php if ($status): ?><input type="hidden" name="status" value="<?= esc($status) ?>"><?php endif ?>
        <input type="search" name="q" value="<?= esc($q) ?>" placeholder="Cari kode, nama, nomor" aria-label="Cari pesanan">
        <a class="btn" href="<?= site_url('admin/pesanan/csv') ?>">Unduh CSV</a>
    </form>
</div>
<?php if (! $orders): ?>
    <section class="card"><p class="empty"><?= $q !== '' || $status !== '' ? 'Tidak ada pesanan yang cocok.' : 'Belum ada pesanan. Pesanan dari keranjang website muncul di sini.' ?></p></section>
<?php else: ?>
    <div class="orders"><?php foreach ($orders as $order): ?><?= view('admin/_order', ['order' => $order, 'withDelete' => true]) ?><?php endforeach ?></div>
<?php endif ?>
<?= $this->endSection() ?>
