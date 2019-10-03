<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name','unit_id', 'status', 'created_by','updated_by'
    ];

    public function unit(){
        return $this->belongsTo('App\Laravue\Models\Unit');
    }
}
