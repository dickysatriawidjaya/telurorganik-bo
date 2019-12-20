<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'parent_id','name','pic_name','phone','address', 'status', 'created_by','updated_by'
    ];

    public function child(){
        return $this->hasMany('App\Laravue\Models\Vendor','parent_id')->where('status',1);
    }

    public function parent(){
        return $this->belongsTo('App\Laravue\Models\Vendor','parent_id');
    }
}
