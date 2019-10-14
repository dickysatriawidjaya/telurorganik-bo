<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'transaction_no' => $this->transaction_no,
            'vendor_id' => $this->vendor_id,
            'total' => $this->total,
            'status' => $this->status,
            'vendor' => $this->vendor,
            'created_at' => $this->created_at,
            'detail_transaction' => $this->detail_transaction,
        ];
    }
}
