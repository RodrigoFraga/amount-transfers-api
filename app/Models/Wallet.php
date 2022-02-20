<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 */
class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    public function personable (): morphTo
    {
        return $this->morphTo();
    }

    /**
     * Increase available balance (absolut value)
     *
     * @param Int $amount
     * @return Void
     */
    public function incrementAvailableBalance (int $amount): void
    {
        $this->available_balance += abs($amount);

        $this->save();
    }

    /**
     * Decrease available balance (absolut value)
     *
     * @param Int $amount
     * @return Void
     */
    public function decreaseAvailableBalance (int $amount): void
    {
        $this->available_balance -= abs($amount);

        $this->save();
    }

    /**
     * Increase available balance (absolut value)
     *
     * @param Int $amount
     * @return Void
     */
    public function incrementBlockedBalance (int $amount): void
    {
        $this->blocked_balance += abs($amount);

        $this->save();
    }

    /**
     * Decrease blocked balance (absolut value)
     *
     * @param Int $amount
     * @return Void
     */
    public function decreaseBlockedBalance (int $amount): void
    {
        $this->blocked_balance -= abs($amount);

        $this->save();
    }
}
