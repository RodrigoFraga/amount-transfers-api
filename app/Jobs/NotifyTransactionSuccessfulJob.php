<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Utils\Notifier;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyTransactionSuccessfulJob implements ShouldQueue
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
        try {
            Notifier::send();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
