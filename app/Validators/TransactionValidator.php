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
            'payee_id'        => 'required|numeric|exists:users,id',
            'scheduling_date' => 'required|date|after_or_equal:' . date('Y-m-d'),
            'amount'          => 'required|numeric|min:1'
        ]);

        if ($validator->fails())
            throw new ValidationException($validator);
    }
}