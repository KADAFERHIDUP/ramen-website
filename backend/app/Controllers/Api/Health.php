<?php

namespace App\Controllers\Api;

class Health extends BaseApi
{
    public function index()
    {
        return $this->respond([
            'ok'   => true,
            'name' => config('Store')->name,
            'menu' => db_connect()->table('menu_items')->where('is_active', 1)->countAllResults(),
            'time' => date('c'),
        ]);
    }
}
