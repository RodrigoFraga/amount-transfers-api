<?php

namespace App\Services;

use App\Enums\TransactionEnum;
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
     * Display a listing of the resource.
     */
    public function index ()
    {
        return $this->model()->transactions()->get();
    }

    /**
     * @param array $data
     * @return Transaction
     */
    public function store (array $data): Transaction
    {
        $data['scheduling_date'] = $data['scheduling_date'] ?? Carbon::now();

        $data['wallet_payer_id'] = $this->model()->wallet->id;

        TransactionValidator::validate($data);

        $lastTransaction = $this->model()->transactions()
            ->where('wallet_payer_id', $data['wallet_payer_id'])
            ->where('wallet_payee_id', $data['wallet_payee_id'])
            ->where('amount', $data['amount'])
            ->whereIN('status', [TransactionEnum::STATUS['scheduled'], TransactionEnum::STATUS['finalized']])
            ->where('created_at', '>', Carbon::now()->subMinutes(10))
            ->orderBy('created_at', 'DESC')
            ->first();


        if ($lastTransaction)
            abort(400, "Duplicate transaction.");

        return DB::transaction(function () use ($data) {

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
