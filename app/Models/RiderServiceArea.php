<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderServiceArea extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id');
    }
}
