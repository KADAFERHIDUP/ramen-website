<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class Orders extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');
        $q      = trim((string) $this->request->getGet('q'));
        $m      = new OrderModel();
        if (isset(config('Store')->orderStatuses[$status])) {
            $m->where('status', $status);
        }
        if ($q !== '') {
            $m->groupStart()->like('code', $q)->orLike('name', $q)->orLike('phone', $q)->groupEnd();
        }
        $orders = $m->withItems($m->orderBy('id', 'DESC')->findAll(200));

        return view('admin/orders/index', ['title' => 'Pesanan', 'orders' => $orders, 'status' => $status, 'q' => $q]);
    }

    public function updateStatus(int $id)
    {
        $m      = new OrderModel();
        $order  = $m->find($id) ?? throw new \CodeIgniter\Exceptions\PageNotFoundException();
        $status = (string) $this->request->getPost('status');
        $labels = config('Store')->orderStatuses;
        if (! isset($labels[$status]) || $status === $order['status']) {
            return redirect()->back();
        }
        $m->update($id, ['status' => $status]);

        return redirect()->back()->with('success', esc($order['code']) . ' sekarang ' . $labels[$status] . '.');
    }

    public function delete(int $id)
    {
        $m     = new OrderModel();
        $order = $m->find($id) ?? throw new \CodeIgniter\Exceptions\PageNotFoundException();
        db_connect()->table('order_items')->where('order_id', $id)->delete();
        $m->delete($id);

        return redirect()->back()->with('success', 'Pesanan ' . esc($order['code']) . ' dihapus.');
    }

    public function export()
    {
        $m    = new OrderModel();
        $rows = $m->withItems($m->orderBy('id', 'DESC')->findAll());
        $cfg  = config('Store');
        $out  = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF"); // supaya Excel membaca huruf dengan benar
        fputcsv($out, ['Kode', 'Tanggal', 'Isi pesanan', 'Porsi', 'Subtotal', 'Nama', 'WhatsApp', 'Cara terima', 'Alamat', 'Bayar', 'Catatan', 'Status'], ';');
        foreach ($rows as $o) {
            $isi = implode(', ', array_map(static fn ($l) => $l['name'] . ' x' . $l['qty'] . ($l['level'] !== null ? ' (lv ' . $l['level'] . ')' : ''), $o['lines']));
            fputcsv($out, [$o['code'], $o['created_at'], $isi, $o['items'], $o['subtotal'], $o['name'], $o['phone'], $cfg->modes[$o['mode']] ?? $o['mode'], $o['address'], $o['pay'], $o['note'], $cfg->orderStatuses[$o['status']] ?? $o['status']], ';');
        }
        rewind($out);

        return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="pesanan-kumo-' . date('Ymd') . '.csv"')
            ->setBody(stream_get_contents($out));
    }
}
