<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $s = $stats; ?>
<div class="stats">
    <a class="stat hot" href="<?= site_url('admin/pesanan?status=baru') ?>"><span>Pesanan baru</span><b><?= $s['new'] ?></b></a>
    <a class="stat" href="<?= site_url('admin/pesanan?status=dimasak') ?>"><span>Sedang dimasak / diantar</span><b><?= $s['cooking'] ?></b></a>
    <div class="stat"><span>Omzet hari ini</span><b><?= rupiah($s['today']) ?></b></div>
    <div class="stat"><span>Porsi terjual hari ini</span><b><?= $s['bowls'] ?></b></div>
    <a class="stat" href="<?= site_url('admin/reservasi?waktu=hari-ini') ?>"><span>Reservasi hari ini</span><b><?= $s['resToday'] ?></b></a>
    <a class="stat <?= $s['soldOut'] ? 'warn' : '' ?>" href="<?= site_url('admin/menu') ?>"><span>Menu ditandai habis</span><b><?= $s['soldOut'] ?></b></a>
</div>
<div class="dash">
    <section>
        <div class="card-head"><h2>Pesanan terbaru</h2><a href="<?= site_url('admin/pesanan') ?>">Semua →</a></div>
        <?php if (! $recent): ?><section class="card"><p class="empty">Belum ada pesanan.</p></section>
        <?php else: ?><div class="orders"><?php foreach ($recent as $order): ?><?= view('admin/_order', ['order' => $order]) ?><?php endforeach ?></div><?php endif ?>
    </section>
    <aside class="dash-side">
        <section class="card">
            <div class="card-head"><h2>Reservasi berikutnya</h2><a href="<?= site_url('admin/reservasi') ?>">Semua →</a></div>
            <?php if (! $resv): ?><p class="empty">Belum ada reservasi.</p><?php else: ?>
            <ul class="mini">
                <?php foreach ($resv as $r): ?>
                <li><div><b><?= esc($r['name']) ?></b> · <?= esc($r['people']) ?><br><span class="muted"><?= esc(tanggal($r['date'], false)) ?>, jam <?= esc(jam($r['time'])) ?> · <?= esc($r['area']) ?></span></div><span class="tag s-<?= esc($r['status']) ?>"><?= esc(config('Store')->reservationStatuses[$r['status']]) ?></span></li>
                <?php endforeach ?>
            </ul>
            <?php endif ?>
        </section>
        <section class="card">
            <div class="card-head"><h2>Menu terlaris</h2></div>
            <?php if (! $top): ?><p class="empty">Belum ada data.</p><?php else: ?>
            <ol class="mini rank"><?php foreach ($top as $t): ?><li><span><?= esc($t['name']) ?></span><b><?= (int) $t['qty'] ?> porsi</b></li><?php endforeach ?></ol>
            <?php endif ?>
        </section>
    </aside>
</div>
<?= $this->endSection() ?>
