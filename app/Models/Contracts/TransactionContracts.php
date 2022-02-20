<?php

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface TransactionContracts
{

    /**
     * wallet.
     *
     */
    public function wallet () : morphOne;

    /**
     * transactions.
     *
     * @return HasMany
     */
    public function transactions (): HasMany;

}
