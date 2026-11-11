<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php
$cfg  = config('Store');
$it   = $item;
$v    = static fn (string $k, $d = '') => old($k, $it[$k] ?? $d);
$cats = (array) old('categories', array_filter(explode(',', (string) ($it['categories'] ?? ''))));
$img  = \App\Models\MenuModel::imageUrl($it['image'] ?? null);
$isNew = empty($it['id']);
$chk  = static fn (string $k) => old($k, $it[$k] ?? 0) ? 'checked' : '';
?>
<form class="card form" method="post" enctype="multipart/form-data" action="<?= $isNew ? site_url('admin/menu') : site_url('admin/menu/' . $it['id']) ?>">
    <?= csrf_field() ?>
    <div class="grid">
        <label class="span2">Nama menu<input name="name" value="<?= esc($v('name')) ?>" required maxlength="100"></label>
        <label>Jenis<select name="kind" id="kind"><?php foreach ($cfg->kinds as $k => $l): ?><option value="<?= $k ?>" <?= $v('kind') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select></label>
        <label>Harga (Rp)<input name="price" type="number" min="0" step="500" value="<?= esc($v('price')) ?>" required></label>
        <label>Label kecil (opsional)<input name="badge" value="<?= esc($v('badge')) ?>" maxlength="40" placeholder="Contoh: Paling laris"></label>
        <label>Urutan<input name="sort" type="number" value="<?= esc($v('sort', 0)) ?>"></label>
        <label class="full">Deskripsi<textarea name="description" rows="2" maxlength="400"><?= esc($v('description')) ?></textarea></label>
        <fieldset class="full ramen-only"><legend>Kategori</legend>
            <?php foreach ($cfg->categories as $k => $l): ?><label class="check"><input type="checkbox" name="categories[]" value="<?= $k ?>" <?= in_array($k, $cats, true) ? 'checked' : '' ?>> <?= $l ?></label><?php endforeach ?>
        </fieldset>
        <label class="ramen-only">Level pedas
            <select name="spicy">
                <option value="" <?= $v('spicy') === null || $v('spicy') === '' ? 'selected' : '' ?>>Tanpa pilihan pedas</option>
                <?php foreach ($cfg->spicyLabels as $k => $l): ?><option value="<?= $k ?>" <?= (string) $v('spicy') === (string) $k ? 'selected' : '' ?>>Bawaan level <?= $k ?> (<?= $l ?>)</option><?php endforeach ?>
            </select>
        </label>
        <label class="ramen-only">Model 3D
            <select name="model">
                <option value="">Tidak ada</option>
                <?php foreach ($cfg->models as $k => $l): ?><option value="<?= $k ?>" <?= $v('model') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?>
            </select>
        </label>
        <div class="full checks">
            <label class="check"><input type="checkbox" name="is_available" value="1" <?= $chk('is_available') ?>> Tersedia hari ini</label>
            <label class="check ramen-only"><input type="checkbox" name="show_in_menu" value="1" <?= $chk('show_in_menu') ?>> Tampil di Menu lengkap</label>
            <label class="check ramen-only"><input type="checkbox" name="signature" value="1" <?= $chk('signature') ?>> Ramen andalan (3 teratas)</label>
            <label class="check"><input type="checkbox" name="is_active" value="1" <?= $chk('is_active') ?>> Aktif di website</label>
        </div>
        <div class="full photo">
            <?php if ($img): ?><img src="<?= esc(str_starts_with($img, 'http') ? $img : base_url($img)) ?>" alt="Foto <?= esc($v('name')) ?>"><?php endif ?>
            <label>Foto menu (JPG, PNG, WebP, maks 3 MB). Paling bagus: foto mangkuk dari atas dengan background transparan.
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
            <?php if ($img && str_starts_with((string) $it['image'], 'uploads/')): ?><label class="check"><input type="checkbox" name="remove_image" value="1"> Hapus foto ini</label><?php endif ?>
        </div>
    </div>
    <p class="hint">Menu yang punya model 3D muncul di bagian "Putar dulu sebelum pesan". Hilangkan centang "Tampil di Menu lengkap" kalau menu itu hanya mau tampil di bagian 3D.</p>
    <div class="form-foot">
        <a class="btn" href="<?= site_url('admin/menu') ?>">Batal</a>
        <button class="btn primary" type="submit"><?= $isNew ? 'Tambah menu' : 'Simpan' ?></button>
    </div>
</form>
<script>
(() => {
  const kind = document.getElementById('kind');
  const sync = () => document.querySelectorAll('.ramen-only').forEach(el => { el.hidden = kind.value !== 'ramen'; });
  kind.addEventListener('change', sync); sync();
})();
</script>
<?= $this->endSection() ?>
