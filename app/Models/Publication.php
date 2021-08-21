<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    protected $fillable = ['name','location','phone','email','hour','type','details','image','category_id','user_id'];

    //Relacion de muchos a muchos
    public function users()
    {
        return $this->belongsTo('App\Models\User');
    }
    //Un categoria tine varias publicaiones
    public function categories()
    {
        return $this->hasMany('App\Models\Category');
    }
    public function postulation()
    {
        return $this->hasMany('App\Models\Postulation');
    }
}
