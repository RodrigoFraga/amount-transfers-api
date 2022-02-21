<?php

namespace App\Jobs;

use App\Enums\ExtractEnum;
use App\Enums\TransactionEnum;
use App\Models\Transaction;
use App\Utils\RequestAuthorizeTransaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TransactionProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;

    /**
     * @param Transaction $transaction
     */
    public function __construct (Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle ()
    {

        // Check if the transfer has already been processed
        if (!is_null($this->transaction->transaction_date) and TransactionEnum::STATUS['scheduled']) return;

        DB::transaction(function () {
            $this->transaction->transaction_date = Carbon::now();

            $walletPayer = $this->transaction->payer;
            $walletPayee = $this->transaction->payee;


            if (RequestAuthorizeTransaction::check()) {
                $this->transaction->status = TransactionEnum::STATUS['finalized'];

                $walletPayee->incrementAvailableBalance($this->transaction->amount);
                $walletPayer->decreaseBlockedBalance($this->transaction->amount);

                // Create extracts
                $this->transaction->payer->personable->extracts()->create([
                    'type'          => ExtractEnum::OUTCOMING,
                    'description'   => ExtractEnum::TRANSACTION_TEXT['outcoming'] . $this->transaction->payee->personable->name,
                    'value'         => $this->transaction->amount,
                    'current_value' => $walletPayer->available_balance
                ]);

                $this->transaction->payee->personable->extracts()->create([
                    'type'          => ExtractEnum::INCOMING,
                    'description'   => ExtractEnum::TRANSACTION_TEXT['incoming'] . $this->transaction->payer->personable->name,
                    'value'         => $this->transaction->amount,
                    'current_value' => $walletPayee->available_balance
                ]);

                $this->transaction->save();

                // Notify receipt of transaction
                dispatch(new NotifyTransactionSuccessfulJob($this->transaction));

                return;
            }

            $walletPayer->incrementAvailableBalance($this->transaction->amount);
            $walletPayer->decreaseBlockedBalance($this->transaction->amount);

            $this->transaction->status = TransactionEnum::STATUS['unauthorized'];

            // Notify payer about unauthorized transaction


            $this->transaction->save();

        });
    }
}
