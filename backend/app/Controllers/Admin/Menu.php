<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Kelola menu: tambah, ubah harga, upload foto, tandai habis, sembunyikan. */
class Menu extends BaseController
{
    public function index()
    {
        $kind = (string) $this->request->getGet('jenis');
        $m    = new MenuModel();
        if (isset(config('Store')->kinds[$kind])) {
            $m->where('kind', $kind);
        }

        return view('admin/menu/index', ['title' => 'Menu', 'rows' => $m->listed(false), 'kind' => $kind]);
    }

    public function new()
    {
        return view('admin/menu/form', ['title' => 'Tambah menu', 'item' => ['kind' => 'ramen', 'is_available' => 1, 'show_in_menu' => 1, 'is_active' => 1, 'spicy' => null, 'categories' => 'kuah', 'sort' => 100]]);
    }

    public function create()
    {
        $data = $this->collect();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $m            = new MenuModel();
        $data['slug'] = $this->uniqueSlug($data['name']);
        $image        = $this->storeImage();
        if ($image === false) {
            return redirect()->back()->withInput()->with('error', 'Foto harus JPG, PNG, atau WebP dan maksimal 3 MB.');
        }
        if ($image) {
            $data['image'] = $image;
        }
        $m->insert($data);

        return redirect()->to(site_url('admin/menu'))->with('success', esc($data['name']) . ' ditambahkan.');
    }

    public function edit(int $id)
    {
        $item = (new MenuModel())->find($id) ?? throw new PageNotFoundException();

        return view('admin/menu/form', ['title' => 'Ubah menu', 'item' => $item]);
    }

    public function update(int $id)
    {
        $m    = new MenuModel();
        $item = $m->find($id) ?? throw new PageNotFoundException();
        $data = $this->collect();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $image = $this->storeImage();
        if ($image === false) {
            return redirect()->back()->withInput()->with('error', 'Foto harus JPG, PNG, atau WebP dan maksimal 3 MB.');
        }
        if ($image) {
            $this->removeUpload($item['image']);
            $data['image'] = $image;
        } elseif ($this->request->getPost('remove_image')) {
            $this->removeUpload($item['image']);
            $data['image'] = null;
        }
        $m->update($id, $data);

        return redirect()->to(site_url('admin/menu'))->with('success', esc($data['name']) . ' disimpan.');
    }

    public function toggleAvailable(int $id)
    {
        $m    = new MenuModel();
        $item = $m->find($id) ?? throw new PageNotFoundException();
        $m->update($id, ['is_available' => $item['is_available'] ? 0 : 1]);

        return redirect()->back()->with('success', esc($item['name']) . ($item['is_available'] ? ' ditandai habis.' : ' tersedia lagi.'));
    }

    public function delete(int $id)
    {
        $m    = new MenuModel();
        $item = $m->find($id) ?? throw new PageNotFoundException();
        $this->removeUpload($item['image']);
        $m->delete($id); // riwayat pesanan tetap menyimpan nama dan harga menu ini

        return redirect()->to(site_url('admin/menu'))->with('success', esc($item['name']) . ' dihapus.');
    }

    /** Ambil dan cek isi form. null kalau tidak valid. */
    private function collect(): ?array
    {
        $cfg   = config('Store');
        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'kind'        => 'required|in_list[' . implode(',', array_keys($cfg->kinds)) . ']',
            'price'       => 'required|is_natural|less_than_equal_to[10000000]',
            'description' => 'permit_empty|max_length[400]',
            'spicy'       => 'permit_empty|in_list[0,1,2,3,4,5]',
            'model'       => 'permit_empty|in_list[' . implode(',', array_keys($cfg->models)) . ']',
            'badge'       => 'permit_empty|max_length[40]',
            'sort'        => 'permit_empty|integer',
        ];
        $messages = [
            'name'  => ['required' => 'Nama menu wajib diisi.'],
            'price' => ['required' => 'Harga wajib diisi.', 'is_natural' => 'Harga harus angka tanpa titik.'],
        ];
        if (! $this->validate($rules, $messages)) {
            return null;
        }
        $p    = $this->request->getPost();
        $cats = array_values(array_intersect(array_keys($cfg->categories), (array) ($p['categories'] ?? [])));

        return [
            'name'         => trim(strip_tags($p['name'])),
            'kind'         => $p['kind'],
            'price'        => (int) $p['price'],
            'description'  => trim(strip_tags($p['description'] ?? '')) ?: null,
            'categories'   => $p['kind'] === 'ramen' ? implode(',', $cats) : null,
            'spicy'        => ($p['spicy'] ?? '') === '' || $p['kind'] !== 'ramen' ? null : (int) $p['spicy'],
            'model'        => ($p['model'] ?? '') ?: null,
            'badge'        => trim(strip_tags($p['badge'] ?? '')) ?: null,
            'signature'    => empty($p['signature']) ? 0 : 1,
            'show_in_menu' => empty($p['show_in_menu']) ? 0 : 1,
            'is_available' => empty($p['is_available']) ? 0 : 1,
            'is_active'    => empty($p['is_active']) ? 0 : 1,
            'sort'         => (int) ($p['sort'] ?? 0),
        ];
    }

    /** Simpan foto upload. null = tidak ada foto, false = file ditolak. */
    private function storeImage(): string|false|null
    {
        $file = $this->request->getFile('image');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $file->isValid() || $file->getSize() > 3 * 1024 * 1024 || ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true) || @getimagesize($file->getTempName()) === false) {
            return false;
        }
        $name = $file->getRandomName();
        $file->move(FCPATH . 'uploads/menu', $name);

        return 'uploads/menu/' . $name;
    }

    private function removeUpload(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/menu/') && ! str_contains($path, '..') && is_file(FCPATH . $path)) {
            @unlink(FCPATH . $path);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = url_title($name, '-', true) ?: 'menu';
        $slug = $base;
        $m    = new MenuModel();
        for ($i = 2; $m->where('slug', $slug)->countAllResults() > 0; $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
