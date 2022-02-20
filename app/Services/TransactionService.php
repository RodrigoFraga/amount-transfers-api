<?php

namespace App\Services;

use App\Jobs\TransactionProcessingJob;
use App\Models\Transaction;
use App\Validators\TransactionValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TransactionService
{
    use ServiceTrait;


    /**
     * @return mixed
     */
    public function model ()
    {
        return $this->model_type::findOrFail($this->model_id);
    }

    /**
     * @param array $data
     * @return Transaction
     */
    public function store (array $data): Transaction
    {

        return DB::transaction(function () use ($data) {

            $data['scheduling_date'] = $data['scheduling_date'] ?? Carbon::now();

            $data['payer_id'] = $this->model()->wallet->id;

            TransactionValidator::validate($data);

            $transaction = $this->model()->transactions()->make($data);

            $transaction->user_id = Auth::id();

            /* check available balance */
            $transaction->checkBalance();

            $transaction->save();

            $wallet = $transaction->payer;

            $wallet->decreaseAvailableBalance($transaction->amount);
            $wallet->incrementBlockedBalance($transaction->amount);

            dispatch(new TransactionProcessingJob($transaction));

            return $transaction->fresh();
        });
    }
}
