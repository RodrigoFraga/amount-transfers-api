<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray ($request): array
    {
        return [
            'user_id'         => $this->user_id,
            'user_name'       => $this->user->name,
            'scheduling_date' => $this->scheduling_date,
            'wallet_payee_id' => $this->wallet_payee_id,
            'amount'          => $this->amount,
            'status'          => $this->status,
        ];
    }
}
