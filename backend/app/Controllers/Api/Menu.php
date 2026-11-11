<?php

namespace App\Controllers\Api;

use App\Models\MenuModel;

class Menu extends BaseApi
{
    public function index()
    {
        $m = new MenuModel();

        return $this->respond(['data' => array_map([$m, 'public'], $m->listed())]);
    }
}
