<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $cfg = config('Store'); ?>
<div class="toolbar">
    <nav class="tabs">
        <a class="<?= $kind === '' ? 'active' : '' ?>" href="<?= site_url('admin/menu') ?>">Semua</a>
        <?php foreach ($cfg->kinds as $k => $l): ?><a class="<?= $kind === $k ? 'active' : '' ?>" href="<?= site_url('admin/menu?jenis=' . $k) ?>"><?= $l ?></a><?php endforeach ?>
    </nav>
    <a class="btn primary" href="<?= site_url('admin/menu/tambah') ?>">+ Tambah menu</a>
</div>
<section class="card">
<?php if (! $rows): ?>
    <p class="empty">Belum ada menu.</p>
<?php else: ?>
    <div class="table-wrap"><table>
        <thead><tr><th>Menu</th><th>Harga</th><th>Tampil di</th><th>Stok hari ini</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $m): $img = \App\Models\MenuModel::imageUrl($m['image']); ?>
            <tr class="<?= $m['is_active'] ? '' : 'off' ?>">
                <td><div class="menu-cell">
                    <?php if ($img): ?><img src="<?= esc(str_starts_with($img, 'http') ? $img : base_url($img)) ?>" alt=""><?php else: ?><span class="ph"><?= esc(mb_substr($m['name'], 0, 1)) ?></span><?php endif ?>
                    <div><b><?= esc($m['name']) ?></b><br><span class="muted"><?= esc($cfg->kinds[$m['kind']] ?? $m['kind']) ?><?= $m['categories'] ? ' · ' . esc(implode(', ', array_map(static fn ($c) => $cfg->categories[$c] ?? $c, explode(',', $m['categories'])))) : '' ?><?= $m['spicy'] !== null ? ' · pedas bawaan ' . (int) $m['spicy'] : '' ?></span></div>
                </div></td>
                <td><?= rupiah($m['price']) ?></td>
                <td>
                    <?php if (! $m['is_active']): ?><span class="tag">Disembunyikan</span>
                    <?php else: ?>
                        <?php if ($m['show_in_menu']): ?><span class="tag">Menu</span><?php endif ?>
                        <?php if ($m['model']): ?><span class="tag d3">3D</span><?php endif ?>
                        <?php if ($m['signature']): ?><span class="tag star">Andalan</span><?php endif ?>
                    <?php endif ?>
                </td>
                <td>
                    <form method="post" action="<?= site_url("admin/menu/{$m['id']}/habis") ?>"><?= csrf_field() ?>
                        <button class="btn sm <?= $m['is_available'] ? 'ok' : 'danger' ?>" type="submit" title="Klik untuk mengubah"><?= $m['is_available'] ? 'Tersedia' : 'Habis' ?></button>
                    </form>
                </td>
                <td><div class="actions">
                    <a class="btn sm" href="<?= site_url("admin/menu/{$m['id']}/edit") ?>">Ubah</a>
                    <form method="post" action="<?= site_url("admin/menu/{$m['id']}/hapus") ?>" onsubmit="return confirm('Hapus <?= esc($m['name'], 'js') ?>? Riwayat pesanan tetap aman.')"><?= csrf_field() ?><button class="btn sm danger" type="submit">Hapus</button></form>
                </div></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table></div>
    <p class="hint">Tombol <b>Tersedia/Habis</b> langsung berlaku di website: menu yang habis tetap tampil tapi tidak bisa dipesan.</p>
<?php endif ?>
</section>
<?= $this->endSection() ?>
