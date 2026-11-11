<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReservationModel;

class Reservations extends BaseController
{
    public function index()
    {
        $when = (string) $this->request->getGet('waktu') ?: 'mendatang';
        $m    = new ReservationModel();
        $today = date('Y-m-d');
        if ($when === 'hari-ini') {
            $m->where('date', $today)->orderBy('time');
        } elseif ($when === 'lewat') {
            $m->where('date <', $today)->orderBy('date', 'DESC')->orderBy('time', 'DESC');
        } elseif ($when === 'semua') {
            $m->orderBy('date', 'DESC')->orderBy('time');
        } else {
            $when = 'mendatang';
            $m->where('date >=', $today)->orderBy('date')->orderBy('time');
        }

        return view('admin/reservations/index', ['title' => 'Reservasi', 'rows' => $m->findAll(300), 'when' => $when]);
    }

    public function updateStatus(int $id)
    {
        $m      = new ReservationModel();
        $row    = $m->find($id) ?? throw new \CodeIgniter\Exceptions\PageNotFoundException();
        $status = (string) $this->request->getPost('status');
        $labels = config('Store')->reservationStatuses;
        if (isset($labels[$status]) && $status !== $row['status']) {
            $m->update($id, ['status' => $status]);

            return redirect()->back()->with('success', esc($row['code']) . ' sekarang ' . $labels[$status] . '.');
        }

        return redirect()->back();
    }

    public function delete(int $id)
    {
        $m   = new ReservationModel();
        $row = $m->find($id) ?? throw new \CodeIgniter\Exceptions\PageNotFoundException();
        $m->delete($id);

        return redirect()->back()->with('success', 'Reservasi ' . esc($row['code']) . ' dihapus.');
    }
}
