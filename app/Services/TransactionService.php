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
    public function model ()
    {
        return Auth::user()->transactions();
    }

    public function index (array $data)
    {

    }

    public function store (array $data): Transaction
    {

        return DB::transaction(function () use ($data) {

            $data['scheduling_date'] = $data['scheduling_date'] ?? Carbon::now();

            TransactionValidator::validate($data);

            $transaction = $this->model()->make($data);

            /* check available balance */
            $transaction->checkBalance();

            $transaction->save();

            $wallet = $transaction->payer->wallet;

            $wallet->decreaseAvailableBalance($transaction->amount);
            $wallet->incrementBlockedBalance($transaction->amount);

            dispatch(new TransactionProcessingJob($transaction));

            return $transaction->fresh();
        });
    }
}
