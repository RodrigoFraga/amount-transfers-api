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
        'scheduled'           => 0,
        'paid'                => 1,
        'divergent_info'      => 2,
        'processing'          => 3,
        'insuficient_balance' => 4,
        'waiting_payment'     => 5,
        'canceled'            => 6,
        'reversed'            => 7,
        'finalized'           => 8,
        'fail_processing'     => 9,
    ];
}

