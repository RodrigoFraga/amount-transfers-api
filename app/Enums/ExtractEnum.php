<?php

namespace App\Enums;

abstract class ExtractEnum
{
    const INCOMING  = 'incoming';
    const OUTCOMING = 'outcoming';


    const TRANSACTION_TEXT = [
        'incoming'  => 'Transferência realizada',
        'outcoming' => 'Transferência recebida',

        'incoming_chargeback'  => 'Transferência estornada',
        'outcoming_chargeback' => 'Transferência estornada',
    ];


}
