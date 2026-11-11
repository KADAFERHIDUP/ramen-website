<?php

namespace App\Models;

class ReservationModel extends OrderModel
{
    protected $table         = 'reservations';
    protected $allowedFields = ['code', 'name', 'phone', 'date', 'time', 'people', 'area', 'note', 'status', 'ip'];
}
