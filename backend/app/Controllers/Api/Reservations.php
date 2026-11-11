<?php

namespace App\Controllers\Api;

use App\Models\ReservationModel;

/** Reservasi meja: tanggal dan jam dicek terhadap jam buka kedai. */
class Reservations extends BaseApi
{
    public function create()
    {
        if (! $this->allowed('reservations', 5)) {
            return $this->tooMany();
        }
        $data = $this->payload();
        if ($this->isBot($data)) {
            return $this->respondCreated(['code' => 'RS-000000-0000', 'message' => 'Reservasi tersimpan.']);
        }
        $cfg    = config('Store');
        $data  += ['name' => '', 'phone' => '', 'date' => '', 'time' => '', 'people' => '', 'area' => '', 'note' => ''];
        $people = array_merge(array_map(static fn ($n) => "{$n} orang", range(1, 12)), ['Lebih dari 12 orang']);
        $rules  = [
            'name'   => 'required|min_length[2]|max_length[80]',
            'phone'  => 'required|max_length[20]',
            'date'   => 'required|valid_date[Y-m-d]',
            'time'   => 'required|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'people' => 'required|in_list[' . implode(',', $people) . ']',
            'area'   => 'required|in_list[' . implode(',', $cfg->areas) . ']',
            'note'   => 'permit_empty|max_length[300]',
        ];
        $messages = [
            'name'   => ['required' => 'Tulis namamu.', 'min_length' => 'Nama terlalu pendek.'],
            'phone'  => ['required' => 'Tulis nomor WhatsApp.'],
            'date'   => ['required' => 'Pilih tanggal.', 'valid_date' => 'Tanggal tidak valid.'],
            'time'   => ['required' => 'Pilih jam.', 'regex_match' => 'Jam tidak valid.'],
            'people' => ['in_list' => 'Pilih jumlah orang.'],
            'area'   => ['in_list' => 'Pilih tempat duduk.'],
        ];
        if (! $this->validateData($data, $rules, $messages)) {
            return $this->invalid();
        }
        $phone = normalize_phone($data['phone']);
        if ($phone === null) {
            return $this->fail422('phone', 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.');
        }

        $today = date('Y-m-d');
        if ($data['date'] < $today || $data['date'] > date('Y-m-d', strtotime("+{$cfg->reservationDays} days"))) {
            return $this->fail422('date', "Reservasi bisa untuk hari ini sampai {$cfg->reservationDays} hari ke depan.");
        }
        $hours = $cfg->hours[(int) date('w', strtotime($data['date']))] ?? null;
        if ($hours === null) {
            return $this->fail422('date', 'Kami tutup di hari itu. Pilih tanggal lain.');
        }
        $min  = static fn (string $t): int => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
        $want = $min($data['time']);
        if ($want < $min($hours[0]) || $want > $min($hours[1]) - 60) {
            return $this->fail422('time', 'Jam reservasi ' . jam($hours[0]) . '–' . date('H.i', mktime(0, $min($hours[1]) - 60)) . ' di hari itu.');
        }
        if ($data['date'] === $today && $want < (int) date('G') * 60 + (int) date('i') + 30) {
            return $this->fail422('time', 'Jam itu sudah lewat atau terlalu dekat. Pilih jam lain.');
        }

        $m   = new ReservationModel();
        $row = [
            'code'   => $m->makeCode('RS'),
            'name'   => strip_tags($data['name']),
            'phone'  => $phone,
            'date'   => $data['date'],
            'time'   => $data['time'],
            'people' => $data['people'],
            'area'   => $data['area'],
            'note'   => $data['note'] !== '' ? strip_tags($data['note']) : null,
            'status' => 'baru',
            'ip'     => $this->request->getIPAddress(),
        ];
        $m->insert($row);

        $text = "Halo {$cfg->name}, saya {$row['name']}. Konfirmasi reservasi {$row['code']}: " . tanggal($row['date']) . ', jam ' . jam($row['time']) . ", {$row['people']}, tempat {$row['area']}.";

        return $this->respondCreated([
            'code'     => $row['code'],
            'whatsapp' => wa_url($cfg->whatsapp, $text),
            'message'  => 'Untuk ' . tanggal($row['date']) . ' jam ' . jam($row['time']) . '. Konfirmasi lewat WhatsApp supaya meja kami siapkan.',
        ]);
    }
}
