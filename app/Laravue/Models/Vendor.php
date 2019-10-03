<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name','pic_name','phone','address', 'status', 'created_by','updated_by'
    ];
}
