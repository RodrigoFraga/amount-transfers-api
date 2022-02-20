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
            'scheduling_date' => $this->scheduling_date,
            'payee_id'        => $this->payee_id,
            'amount'          => $this->amount,
            'status'          => $this->status,
        ];
    }
}
