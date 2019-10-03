<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name','unit_code','description','status', 'created_by','updated_by'
    ];
}
