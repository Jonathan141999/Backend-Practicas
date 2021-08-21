<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Postulation extends Model
{
    protected $fillable = ['languages','type','work_experience','career','status','category_id', 'publication_id'];
    public static function boot()
    {
        parent::boot();
        static::creating(function ($postulation) {
            $postulation->user_id = Auth::id();
        });
    }
    public function users()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function publication()
    {
        return $this->hasMany('App\Models\Detail');
    }
}
