<?php

namespace App\Controllers\Api;

use App\Models\MenuModel;
use App\Models\OrderModel;

/**
 * Pesanan dari keranjang website. Harga selalu dihitung ulang dari database,
 * jadi harga yang dikirim browser tidak dipakai. Menu yang sedang habis ditolak.
 */
class Orders extends BaseApi
{
    public function create()
    {
        if (! $this->allowed('orders', 5)) {
            return $this->tooMany();
        }
        $json = $this->request->getJSON(true);
        $data = $this->payload();
        if ($this->isBot($data)) {
            return $this->respondCreated(['code' => 'KR-000000-0000', 'message' => 'Pesanan tersimpan.']); // robot: pura-pura berhasil
        }
        $cfg   = config('Store');
        $data += ['name' => '', 'phone' => '', 'mode' => 'antar', 'address' => '', 'pay' => 'QRIS', 'note' => ''];
        $rules = [
            'name'    => 'required|min_length[2]|max_length[80]',
            'phone'   => 'required|max_length[20]',
            'mode'    => 'required|in_list[' . implode(',', array_keys($cfg->modes)) . ']',
            'pay'     => 'required|in_list[' . implode(',', $cfg->payments) . ']',
            'address' => 'permit_empty|max_length[400]',
            'note'    => 'permit_empty|max_length[300]',
        ];
        $messages = [
            'name'  => ['required' => 'Tulis nama pemesan.', 'min_length' => 'Nama terlalu pendek.'],
            'phone' => ['required' => 'Tulis nomor WhatsApp.'],
            'mode'  => ['in_list' => 'Pilih cara terima pesanan.'],
            'pay'   => ['in_list' => 'Pilih cara bayar.'],
        ];
        if (! $this->validateData($data, $rules, $messages)) {
            return $this->invalid();
        }
        $phone = normalize_phone($data['phone']);
        if ($phone === null) {
            return $this->fail422('phone', 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.');
        }
        if ($data['mode'] === 'antar' && mb_strlen($data['address']) < 6) {
            return $this->fail422('address', 'Tulis alamat lengkap untuk pengantaran.');
        }

        // gabungkan baris yang sama (menu + level pedas)
        $raw = is_array($json['items'] ?? null) ? $json['items'] : [];
        if (! $raw) {
            return $this->fail422('items', 'Keranjang masih kosong.');
        }
        if (count($raw) > $cfg->maxLines) {
            return $this->fail422('items', "Maksimal {$cfg->maxLines} jenis menu per pesanan.");
        }
        $want = [];
        foreach ($raw as $line) {
            $slug = is_array($line) ? (string) ($line['id'] ?? '') : '';
            $qty  = is_array($line) ? filter_var($line['qty'] ?? null, FILTER_VALIDATE_INT) : false;
            if ($slug === '' || $qty === false || $qty < 1) {
                return $this->fail422('items', 'Isi keranjang tidak valid. Muat ulang halaman lalu coba lagi.');
            }
            $level = isset($line['level']) && $line['level'] !== null ? filter_var($line['level'], FILTER_VALIDATE_INT) : null;
            $key   = $slug . '|' . ($level ?? '');
            $want[$key] = ['slug' => $slug, 'qty' => ($want[$key]['qty'] ?? 0) + $qty, 'level' => $level];
            if ($want[$key]['qty'] > $cfg->maxQty) {
                return $this->fail422('items', "Maksimal {$cfg->maxQty} porsi per menu. Untuk pesanan besar, hubungi kami lewat WhatsApp.");
            }
        }

        $menu = [];
        foreach ((new MenuModel())->whereIn('slug', array_unique(array_column($want, 'slug')))->where('is_active', 1)->findAll() as $m) {
            $menu[$m['slug']] = $m;
        }
        $lines = [];
        $total = 0;
        $count = 0;
        foreach ($want as $w) {
            $m = $menu[$w['slug']] ?? null;
            if ($m === null) {
                return $this->respond(['status' => 409, 'message' => 'Ada menu di keranjang yang sudah tidak dijual. Muat ulang halaman.'], 409);
            }
            if (! $m['is_available']) {
                return $this->respond(['status' => 409, 'message' => "Maaf, {$m['name']} sedang habis. Hapus dari keranjang dulu."], 409);
            }
            $level = $m['spicy'] === null ? null : max(0, min(5, (int) ($w['level'] ?? $m['spicy'])));
            $sum   = (int) $m['price'] * $w['qty'];
            $lines[] = ['menu_item_id' => $m['id'], 'name' => $m['name'], 'unit_price' => (int) $m['price'], 'qty' => $w['qty'], 'level' => $level, 'total' => $sum];
            $total += $sum;
            $count += $w['qty'];
        }

        $orders = new OrderModel();
        $db     = db_connect();
        $db->transStart();
        $order = [
            'code'     => $orders->makeCode('KR'),
            'name'     => strip_tags($data['name']),
            'phone'    => $phone,
            'mode'     => $data['mode'],
            'address'  => $data['mode'] === 'antar' ? strip_tags($data['address']) : null,
            'pay'      => $data['pay'],
            'note'     => $data['note'] !== '' ? strip_tags($data['note']) : null,
            'subtotal' => $total,
            'items'    => $count,
            'status'   => 'baru',
            'ip'       => $this->request->getIPAddress(),
        ];
        $id = $orders->insert($order);
        foreach ($lines as $l) {
            $db->table('order_items')->insert($l + ['order_id' => $id]);
        }
        $db->transComplete();
        if (! $db->transStatus()) {
            return $this->respond(['status' => 500, 'message' => 'Pesanan gagal disimpan. Coba lagi.'], 500);
        }

        $text = ["Halo {$cfg->name}, saya {$order['name']}. Konfirmasi pesanan {$order['code']}:", ''];
        foreach ($lines as $i => $l) {
            $text[] = ($i + 1) . ". {$l['name']} ×{$l['qty']}" . ($l['level'] !== null ? " ({$this->levelText($l['level'])})" : '') . ' = ' . rupiah($l['total']);
        }
        $text[] = '';
        $text[] = 'Subtotal: ' . rupiah($total);
        $text[] = 'Cara terima: ' . $cfg->modes[$order['mode']];
        if ($order['address']) {
            $text[] = 'Alamat: ' . $order['address'];
        }
        $text[] = 'Bayar: ' . $order['pay'];

        return $this->respondCreated([
            'code'     => $order['code'],
            'subtotal' => $total,
            'items'    => $count,
            'whatsapp' => wa_url($cfg->whatsapp, implode("\n", $text)),
            'message'  => $order['mode'] === 'antar'
                ? 'Tekan tombol di bawah untuk konfirmasi di WhatsApp. Kami balas dengan total plus ongkir.'
                : 'Tekan tombol di bawah untuk konfirmasi di WhatsApp. Kami kabari saat pesanan siap.',
        ]);
    }

    private function levelText(int $level): string
    {
        return 'pedas level ' . $level;
    }
}
