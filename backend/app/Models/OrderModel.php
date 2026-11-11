<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'phone', 'mode', 'address', 'pay', 'note', 'subtotal', 'items', 'status', 'ip'];

    /** Kode pendek yang mudah dibacakan, misalnya KR-261111-K7Q2. */
    public function makeCode(string $prefix = 'KR'): string
    {
        $abc = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = $prefix . '-' . date('ymd') . '-';
            for ($i = 0; $i < 4; $i++) {
                $code .= $abc[random_int(0, strlen($abc) - 1)];
            }
        } while ($this->db->table($this->table)->where('code', $code)->countAllResults() > 0);

        return $code;
    }

    /** Pesanan beserta isinya, untuk daftar di panel admin. */
    public function withItems(array $orders): array
    {
        if (! $orders) {
            return [];
        }
        $rows = $this->db->table('order_items')->whereIn('order_id', array_column($orders, 'id'))->orderBy('id')->get()->getResultArray();
        $by   = [];
        foreach ($rows as $r) {
            $by[$r['order_id']][] = $r;
        }
        foreach ($orders as &$o) {
            $o['lines'] = $by[$o['id']] ?? [];
        }

        return $orders;
    }
}
