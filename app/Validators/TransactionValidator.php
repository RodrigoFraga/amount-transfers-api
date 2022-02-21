<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionValidator
{
    /**
     * @throws ValidationException
     */
    public static function validate ($data)
    {
        $validator = Validator::make($data, [
            'wallet_payer_id' => 'required|numeric|exists:wallets,id',
            'wallet_payee_id' => 'required|numeric|exists:wallets,id',
            'scheduling_date' => 'required|date|after_or_equal:' . date('Y-m-d'),
            'amount'          => 'required|numeric|min:1'
        ]);

        if ($validator->fails())
            throw new ValidationException($validator);
    }
}
