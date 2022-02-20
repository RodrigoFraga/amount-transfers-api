<?php

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
     * @return MorphMany
     */
    public function transactions (): MorphMany;

}
