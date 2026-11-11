<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table         = 'menu_items';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['slug', 'kind', 'name', 'description', 'price', 'categories', 'spicy', 'image', 'model', 'badge', 'signature', 'show_in_menu', 'is_available', 'is_active', 'sort'];

    public function listed(bool $activeOnly = true): array
    {
        if ($activeOnly) {
            $this->where('is_active', 1);
        }

        return $this->orderBy("CASE kind WHEN 'ramen' THEN 0 WHEN 'topping' THEN 1 ELSE 2 END", '', false)->orderBy('sort')->orderBy('id')->findAll();
    }

    /** Bentuk data yang dikirim ke frontend. */
    public function public(array $m): array
    {
        return [
            'id'           => (int) $m['id'],
            'slug'         => $m['slug'],
            'kind'         => $m['kind'],
            'name'         => $m['name'],
            'description'  => (string) $m['description'],
            'price'        => (int) $m['price'],
            'categories'   => array_values(array_filter(explode(',', (string) $m['categories']))),
            'spicy'        => $m['spicy'] === null ? null : (int) $m['spicy'],
            'image'        => self::imageUrl($m['image']),
            'model'        => $m['model'] ?: null,
            'badge'        => $m['badge'] ?: null,
            'signature'    => (bool) $m['signature'],
            'show_in_menu' => (bool) $m['show_in_menu'],
            'available'    => (bool) $m['is_available'],
        ];
    }

    /** Foto upload ditulis lengkap dengan alamat server; foto bawaan (img/...) tetap relatif. */
    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'uploads/') ? base_url($path) : $path;
    }
}
