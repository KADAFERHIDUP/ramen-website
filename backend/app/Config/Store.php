<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Pengaturan kedai Kumo Ramen yang dipakai API dan panel admin. */
class Store extends BaseConfig
{
    public string $name     = 'Kumo Ramen';
    public string $whatsapp = '6282276078880';
    public string $city     = 'Lhokseumawe';

    /** Batas jumlah per menu dalam satu pesanan. */
    public int $maxQty = 20;

    /** Batas jenis menu dalam satu pesanan. */
    public int $maxLines = 30;

    /** Jam buka per hari (0 = Minggu). Sama dengan js/data.js di frontend. */
    public array $hours = [
        0 => ['11:00', '22:00'], 1 => ['11:00', '22:00'], 2 => ['11:00', '22:00'], 3 => ['11:00', '22:00'],
        4 => ['11:00', '22:00'], 5 => ['14:00', '22:00'], 6 => ['11:00', '22:00'],
    ];

    /** Reservasi paling lambat sejam sebelum tutup, paling jauh 60 hari ke depan. */
    public int $reservationDays = 60;

    public array $orderStatuses = [
        'baru'     => 'Baru',
        'dimasak'  => 'Dimasak',
        'siap'     => 'Siap / diantar',
        'selesai'  => 'Selesai',
        'batal'    => 'Batal',
    ];

    public array $reservationStatuses = [
        'baru'         => 'Baru',
        'dikonfirmasi' => 'Dikonfirmasi',
        'datang'       => 'Sudah datang',
        'batal'        => 'Batal',
    ];

    public array $modes = [
        'antar' => 'Diantar',
        'ambil' => 'Ambil sendiri',
        'makan' => 'Makan di tempat',
    ];

    public array $payments = ['QRIS', 'Transfer', 'Tunai'];

    public array $areas = ['Dalam (ber-AC)', 'Teras', 'Lesehan'];

    public array $kinds = [
        'ramen'   => 'Ramen',
        'topping' => 'Topping',
        'minuman' => 'Minuman',
    ];

    public array $categories = [
        'kuah'   => 'Berkuah',
        'pedas'  => 'Pedas',
        'dingin' => 'Dingin',
        'sayur'  => 'Tanpa daging',
        'instan' => 'Instan Korea',
    ];

    /** Model 3D yang ada di frontend (js/models.js). */
    public array $models = [
        'ramen-spesial' => 'Ramen Spesial (mie melayang)',
        'tori-paitan'   => 'Ramen ayam krim',
        'shoyu-ajitama' => 'Ramen shoyu',
        'shin-bowl'     => 'Shin Ramyun Big Bowl',
        'shin-cup'      => 'Shin Ramyun Cup',
    ];

    public array $spicyLabels = ['Tidak pedas', 'Sedikit', 'Sedang', 'Pedas', 'Pedas banget', 'Level neraka'];
}
