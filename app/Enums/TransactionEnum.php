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
        'processing'      => 3,
        'unauthorized'    => 4,
        'canceled'        => 5,
        'fail_processing' => 6,
    ];
}

