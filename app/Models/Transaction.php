<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_payer_id', 'wallet_payee_id', 'amount', 'scheduling_date', 'description'];

    public function ownerable (): BelongsTo
    {
        return $this->morphTo('ownerable');
    }

    public function user (): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payer (): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_payer_id');
    }

    public function payee (): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_payee_id');
    }

    public function checkBalance ()
    {
        $availableBalance = $this->payer->available_balance;

        $transferAmount = $this->amount + $this->intermediation_amount;

        if ($transferAmount > $availableBalance) {
            abort(406, 'Insufficient balance');
        }
    }

}
