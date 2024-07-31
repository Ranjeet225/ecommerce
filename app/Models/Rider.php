<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Rider extends Authenticatable
{

    protected $fillable = ['name', 'photo', 'zip', 'city_id', 'state_id', 'country', 'address', 'phone', 'fax', 'email', 'password', 'location', 'email_verify', 'email_verified', 'email_token', 'status', 'balance'];

    protected $hidden = [
        'password', 'remember_token'
    ];


    public function orders()
    {
        return $this->hasMany('App\Models\DeliveryRider');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }
}
