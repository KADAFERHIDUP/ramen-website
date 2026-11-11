<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $db    = db_connect();
        $today = date('Y-m-d');
        $stats = [
            'new'      => $db->table('orders')->where('status', 'baru')->countAllResults(),
            'cooking'  => $db->table('orders')->whereIn('status', ['dimasak', 'siap'])->countAllResults(),
            'today'    => (int) ($db->table('orders')->selectSum('subtotal')->where('status !=', 'batal')->where('created_at >=', $today . ' 00:00:00')->get()->getRow()->subtotal ?? 0),
            'bowls'    => (int) ($db->table('orders')->selectSum('items')->where('status !=', 'batal')->where('created_at >=', $today . ' 00:00:00')->get()->getRow()->items ?? 0),
            'resToday' => $db->table('reservations')->where('date', $today)->whereIn('status', ['baru', 'dikonfirmasi'])->countAllResults(),
            'resNew'   => $db->table('reservations')->where('status', 'baru')->countAllResults(),
            'soldOut'  => $db->table('menu_items')->where('is_active', 1)->where('is_available', 0)->countAllResults(),
        ];
        $recent = (new OrderModel())->withItems($db->table('orders')->orderBy('id', 'DESC')->limit(6)->get()->getResultArray());
        $resv   = $db->table('reservations')->where('date >=', $today)->whereIn('status', ['baru', 'dikonfirmasi'])->orderBy('date')->orderBy('time')->limit(6)->get()->getResultArray();
        $top    = $db->table('order_items oi')->select('oi.name, SUM(oi.qty) AS qty')->join('orders o', 'o.id = oi.order_id')
            ->where('o.status !=', 'batal')->groupBy('oi.name')->orderBy('qty', 'DESC')->limit(5)->get()->getResultArray();

        return view('admin/dashboard', ['title' => 'Dashboard', 'stats' => $stats, 'recent' => $recent, 'resv' => $resv, 'top' => $top]);
    }
}
