<?php $cfg = config('Store'); $o = $order; ?>
<article class="order card s-border-<?= esc($o['status']) ?>">
    <header>
        <div>
            <b class="code"><?= esc($o['code']) ?></b>
            <span class="muted"><?= esc(waktu($o['created_at'])) ?></span>
        </div>
        <form method="post" action="<?= site_url("admin/pesanan/{$o['id']}/status") ?>"><?= csrf_field() ?>
            <select name="status" class="s-<?= esc($o['status']) ?>" onchange="this.form.submit()" aria-label="Status <?= esc($o['code']) ?>">
                <?php foreach ($cfg->orderStatuses as $k => $l): ?><option value="<?= $k ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?>
            </select>
        </form>
    </header>
    <ul class="lines">
        <?php foreach ($o['lines'] as $l): ?>
            <li><span><b><?= (int) $l['qty'] ?>×</b> <?= esc($l['name']) ?><?php if ($l['level'] !== null): ?> <span class="lvl">pedas <?= (int) $l['level'] ?></span><?php endif ?></span><span><?= rupiah($l['total']) ?></span></li>
        <?php endforeach ?>
        <li class="sum"><span>Subtotal</span><span><?= rupiah($o['subtotal']) ?></span></li>
    </ul>
    <p class="buyer"><b><?= esc($o['name']) ?></b> · <?= esc($o['phone']) ?><br>
        <span class="tag mode-<?= esc($o['mode']) ?>"><?= esc($cfg->modes[$o['mode']] ?? $o['mode']) ?></span> <span class="tag"><?= esc($o['pay']) ?></span>
        <?php if ($o['address']): ?><br><span class="muted"><?= esc($o['address']) ?></span><?php endif ?>
        <?php if ($o['note']): ?><br><em class="note">“<?= esc($o['note']) ?>”</em><?php endif ?></p>
    <div class="actions">
        <a class="btn sm wa" target="_blank" rel="noopener" href="<?= esc(wa_url($o['phone'], "Halo {$o['name']}, kami dari {$cfg->name}. Pesanan {$o['code']} (" . rupiah($o['subtotal']) . ') sudah kami terima dan sedang disiapkan.')) ?>">WhatsApp pembeli</a>
        <?php if (! empty($withDelete)): ?>
        <form method="post" action="<?= site_url("admin/pesanan/{$o['id']}/hapus") ?>" onsubmit="return confirm('Hapus pesanan <?= esc($o['code'], 'js') ?>?')"><?= csrf_field() ?><button class="btn sm danger" type="submit">Hapus</button></form>
        <?php endif ?>
    </div>
</article>
