<?php

namespace App\Enums;

/**
 * Enum Transaction
 */
abstract class TransactionEnum
{
    /**
     * Transaction status
     */
    const STATUS = [
        'scheduled'       => 0,
        'finalized'       => 1,
        'divergent_info'  => 2,
        'unauthorized'    => 3,
        'canceled'        => 4,
        'fail_processing' => 5,
    ];
}

