<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $cfg = config('Store'); $tabs = ['mendatang' => 'Mendatang', 'hari-ini' => 'Hari ini', 'lewat' => 'Sudah lewat', 'semua' => 'Semua']; ?>
<div class="toolbar">
    <nav class="tabs"><?php foreach ($tabs as $k => $l): ?><a class="<?= $when === $k ? 'active' : '' ?>" href="<?= site_url('admin/reservasi?waktu=' . $k) ?>"><?= $l ?></a><?php endforeach ?></nav>
</div>
<section class="card">
<?php if (! $rows): ?>
    <p class="empty">Tidak ada reservasi di daftar ini.</p>
<?php else: ?>
    <div class="table-wrap"><table>
        <thead><tr><th>Tanggal &amp; jam</th><th>Tamu</th><th>Meja</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr class="<?= $r['date'] === date('Y-m-d') ? 'today' : '' ?>">
                <td><b><?= esc(tanggal($r['date'])) ?></b><br><span class="muted">jam <?= esc(jam($r['time'])) ?> · <?= esc($r['code']) ?></span></td>
                <td><?= esc($r['name']) ?><br><span class="muted"><?= esc($r['phone']) ?></span><?php if ($r['note']): ?><br><em class="note">“<?= esc($r['note']) ?>”</em><?php endif ?></td>
                <td><?= esc($r['people']) ?><br><span class="muted"><?= esc($r['area']) ?></span></td>
                <td>
                    <form method="post" action="<?= site_url("admin/reservasi/{$r['id']}/status") ?>"><?= csrf_field() ?>
                        <select name="status" class="s-<?= esc($r['status']) ?>" onchange="this.form.submit()" aria-label="Status <?= esc($r['code']) ?>">
                            <?php foreach ($cfg->reservationStatuses as $k => $l): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?>
                        </select>
                    </form>
                </td>
                <td><div class="actions">
                    <a class="btn sm wa" target="_blank" rel="noopener" href="<?= esc(wa_url($r['phone'], "Halo {$r['name']}, reservasi {$r['code']} di {$cfg->name} untuk " . tanggal($r['date']) . ' jam ' . jam($r['time']) . " ({$r['people']}) sudah kami konfirmasi. Sampai jumpa!")) ?>">Konfirmasi</a>
                    <form method="post" action="<?= site_url("admin/reservasi/{$r['id']}/hapus") ?>" onsubmit="return confirm('Hapus reservasi <?= esc($r['code'], 'js') ?>?')"><?= csrf_field() ?><button class="btn sm danger" type="submit">Hapus</button></form>
                </div></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table></div>
<?php endif ?>
</section>
<?= $this->endSection() ?>
