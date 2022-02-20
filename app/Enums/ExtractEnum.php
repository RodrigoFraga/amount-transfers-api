<?php

namespace App\Enums;

abstract class ExtractEnum
{
    const INCOMING  = 'incoming';
    const OUTCOMING = 'outcoming';


    const TRANSACTION_TEXT = [
        'incoming'  => 'Transferência recebida - ',
        'outcoming' => 'Transferência envida - ',

        'incoming_chargeback'  => 'Transferência estornada',
        'outcoming_chargeback' => 'Transferência estornada',
    ];


}
