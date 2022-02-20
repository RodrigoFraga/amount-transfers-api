<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['payer_id', 'payee_id', 'amount', 'scheduling_date', 'description'];

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
        return $this->belongsTo(Wallet::class, 'payer_id');
    }

    public function payee (): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payee_id');
    }

    public function checkBalance ()
    {
        $available_balance = $this->payer->available_balance;

        $transfer_amount = $this->amount + $this->intermediation_amount;

        if ($transfer_amount > $available_balance) {
            abort(406, 'Insufficient balance');
        }
    }

}
