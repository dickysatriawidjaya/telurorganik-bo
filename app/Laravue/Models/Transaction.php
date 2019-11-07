<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_no','vendor_id','total','status','created_at'
    ];

    public function vendor(){
        return $this->belongsTo('App\Laravue\Models\Vendor');
    }

    public function detail_transaction(){
        return $this->hasMany('App\Laravue\Models\Transaction_Detail')->where('status',1);
    }
}
