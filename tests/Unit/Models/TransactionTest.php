<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionEnum;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Check fillable data.
     *
     * @return void
     * @test
     */
    public function test_same_fillable ()
    {
        $fillableTest = ['payee_id', 'amount', 'scheduling_date', 'description'];

        $fillable = (new Transaction())->getFillable();

        $this->assertEqualsCanonicalizing($fillableTest, $fillable);
    }

    /**
     * Test registration successfully.
     *
     * @return void
     * @test
     */
    public function test_registration_successfully ()
    {
        $payer = User::factory()->create();
        $payee = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'payer_id'        => $payer->id,
            'payee_id'        => $payee->id,
            'amount'          => 15000,
            'scheduling_date' => Carbon::now()
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'               => $transaction->id,
            'payer_id'         => $payer->id,
            'payee_id'         => $payee->id,
            'amount'           => 15000,
            'scheduling_date'  => Carbon::now()->format('Y-m-d'),
            'status'           => TransactionEnum::STATUS['scheduled'],
            'transaction_date' => null
        ]);

    }

    /**
     * Test registration with required data failure.
     *
     * @return void
     * @test
     */
    public function test_registration_with_required_data_failure ()
    {
        $payer = User::factory()->create();

        $this->expectException(QueryException::class);

        Transaction::factory()->create([
            'payer_id'        => $payer->id,
            'payee_id'        => null,
            'amount'          => null,
            'scheduling_date' => Carbon::now()->format('Y-m-d')
        ]);

    }
}
