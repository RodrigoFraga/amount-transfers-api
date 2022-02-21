<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionEnum;
use App\Models\Store;
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
        $fillableTest = ['wallet_payer_id', 'wallet_payee_id', 'amount', 'scheduling_date', 'description'];

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
        $payer = User::factory()->hasWallet()->create();
        $payee = Store::factory()->hasWallet()->create();

        $transaction = Transaction::factory()->create([
            'ownerable_type'  => get_class($payer),
            'ownerable_id'    => $payer->id,
            'user_id'         => $payer->id,
            'wallet_payer_id'        => $payer->wallet->id,
            'wallet_payee_id'        => $payee->wallet->id,
            'amount'          => 15000,
            'scheduling_date' => Carbon::now()
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'               => $transaction->id,
            'ownerable_type'   => get_class($payer),
            'ownerable_id'     => $payer->id,
            'user_id'          => $payer->id,
            'wallet_payer_id'         => $payer->wallet->id,
            'wallet_payee_id'         => $payee->wallet->id,
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
            'wallet_payer_id'        => $payer->id,
            'wallet_payee_id'        => null,
            'amount'          => null,
            'scheduling_date' => Carbon::now()->format('Y-m-d')
        ]);

    }

    /**
     * Test class has user method.
     *
     * @return void
     * @test
     */
    public function test_class_has_extracts_user ()
    {
        $this->assertTrue(method_exists(new Transaction(), 'user'));
    }
}
