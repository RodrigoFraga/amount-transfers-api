<?php

use App\Enums\TransactionEnum;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->nullable()->constrained('users');
            $table->foreignId('payee_id')->nullable()->constrained('users');
            $table->timestamp('scheduling_date');
            $table->integer('amount');
            $table->integer('status')->default(TransactionEnum::STATUS['scheduled']);
            $table->timestamp('transaction_date')->nullable();

            $table->text('description')->nullable();
            $table->string('url_receipt')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
