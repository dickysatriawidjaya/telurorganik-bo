<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction_Detail extends Model
{
    protected $fillable = [
        'transaction_id','item_id','quantity','discount','subtotal','status','retur'
    ];

    protected $table = "transaction_detail";

    public function item(){
        return $this->belongsTo('App\Laravue\Models\Item')->where('status',1);
    }

}
