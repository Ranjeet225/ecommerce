<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestimonialCategory extends Model
{
    protected $fillable = ['name', 'slug'];
    public $timestamps = false;

    public function testimonial()
    {
    	return $this->hasMany('App\Models\Testimonial','category_id');
    }

 

    public function setSlugAttribute($value)
    {
    	$this->attributes['slug'] = str_replace(' ', '-', $value);
    }
}
