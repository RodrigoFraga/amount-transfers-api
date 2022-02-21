<?php

namespace App\Models\Traits;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait WalletTrait
{
    /**
     * @return MorphOne
     */
    public function wallet (): morphOne
    {
        return $this->morphOne(Wallet::class, 'personable');
    }
}
