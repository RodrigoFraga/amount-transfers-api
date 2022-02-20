<?php

namespace Tests\Unit\Models;

use App\Models\Wallet;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class WalletTest extends TestCase
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
        $fillableTest = [];

        $fillable = (new Wallet())->getFillable();

        $this->assertEqualsCanonicalizing($fillableTest, $fillable);
    }

    /**
     * Test if all value fields start with zero
     *
     * @return void
     * @test
     */
    public function test_if_all_value_fields_start_with_zero ()
    {
        $data = Wallet::factory()->create();

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 0]));
    }


    /**
     * Test method to increment available balance
     *
     * @return void
     * @test
     */
    public function test_method_to_increment_available_balance ()
    {
        $data   = Wallet::factory()->create();
        $wallet = Wallet::find($data->id);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 0]));

        $wallet->incrementAvailableBalance(100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 100, 'blocked_balance' => 0]));

        $wallet->incrementAvailableBalance(-100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 200, 'blocked_balance' => 0]));

    }


    /**
     * Test method to decrease available balance
     *
     * @return void
     * @test
     */
    public function test_method_to_decrease_available_balance ()
    {
        $data   = Wallet::factory()->create(['available_balance' => 200, 'blocked_balance' => 0]);
        $wallet = Wallet::find($data->id);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 200, 'blocked_balance' => 0]));

        $wallet->decreaseAvailableBalance(50);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 150, 'blocked_balance' => 0]));

        $wallet->decreaseAvailableBalance(-100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 50, 'blocked_balance' => 0]));

    }


    /**
     * Test method to increment blocked balance
     *
     * @return void
     * @test
     */
    public function test_method_to_increment_blocked_balance ()
    {
        $data   = Wallet::factory()->create();
        $wallet = Wallet::find($data->id);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 0]));

        $wallet->incrementBlockedBalance(100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 100]));

        $wallet->incrementBlockedBalance(-100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 200]));

    }


    /**
     * Test method to decrease blocked balance
     *
     * @return void
     * @test
     */
    public function test_method_to_decrease_blocked_balance ()
    {
        $data   = Wallet::factory()->create(['available_balance' => 0, 'blocked_balance' => 200]);
        $wallet = Wallet::find($data->id);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 200]));

        $wallet->decreaseBlockedBalance(100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 100]));

        $wallet->decreaseBlockedBalance(-100);

        $this->assertDatabaseHas('wallets', array_merge($data->toArray(), ['available_balance' => 0, 'blocked_balance' => 0]));

    }

    /**
     * Test if the class has a personable method
     *
     * @return void
     * @test
     */
    public function test_if_the_class_has_a_personable_method ()
    {
        $this->assertTrue(method_exists(new Wallet(), 'personable'));
    }

}
