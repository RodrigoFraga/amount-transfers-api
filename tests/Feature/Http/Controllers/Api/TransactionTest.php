<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\ExtractEnum;
use App\Enums\TransactionEnum;
use App\Enums\UserRoles;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test if unauthenticated users cannot access the following endpoints for the transaction API
     *
     * @return void
     */
    public function test_if_unauthenticated_users_cannot_access_the_following_endpoints_for_the_transaction_api ()
    {
        $index = $this->json('GET', '/api/transaction');
        $index->assertStatus(401);


        $index = $this->json('POST', '/api/transaction');
        $index->assertStatus(401);
    }


    /**
     * Test if unauthenticated users cannot access the following endpoints for the transaction API
     *
     * @return void
     */
    public function test_if_permission_users_cannot_access_the_following_endpoints_for_the_transaction_api ()
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $index = $this->json('GET', '/api/transaction');
        $index->assertStatus(403);


        $index = $this->json('POST', '/api/transaction');
        $index->assertStatus(403);
    }

    /**
     * Test return when payer has no available balance
     *
     * @return void
     */
    public function test_return_when_payer_has_no_available_balance ()
    {
        $user = User::factory()->hasWallet()->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $response = $this->json('POST', '/api/transaction', ['amount' => 100, 'payee_id' => $payee->id]);

        $response->assertStatus(406)
            ->assertJson(['message' => 'Insufficient balance']);
    }

    /**
     * Test with a payee that does not exist
     *
     * @return void
     */
    public function test_with_a_payee_that_does_not_exist ()
    {
        $user = User::factory()->hasWallet(['available_balance' => 1000])->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $response = $this->json('POST', '/api/transaction', ['amount' => 100, 'payee_id' => 5]);

        $response->assertStatus(422)
            ->assertJson([
                'error'   => true,
                'message' => [['payee_id' => ['The selected payee is invalid.']]]
            ]);
    }

    /**
     * Test with payment amount lower than allowed
     *
     * @return void
     */
    public function test_with_payment_amount_lower_than_allowed ()
    {
        $user  = User::factory()->hasWallet(['available_balance' => 1000])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $response = $this->json('POST', '/api/transaction', ['amount' => 0, 'payee_id' => $payee->id]);

        $response->assertStatus(422)
            ->assertJson([
                'error'   => true,
                'message' => [['amount' => ['The amount must be at least 1.']]]
            ]);
    }

    /**
     * Test scheduled transfer
     *
     * @return void
     */
    public function test_scheduled_transfer ()
    {
        Config::set('queue.default', 'database');

        $user  = User::factory()->hasWallet(['available_balance' => 100])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $amount = 60;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'payee_id' => $payee->id]);


        $response->assertStatus(200)
            ->assertExactJson(['data' => [
                'scheduling_date' => Carbon::now()->format('Y-m-d'),
                'payee_id'        => $payee->id,
                'amount'          => $amount,
                'status'          => TransactionEnum::STATUS['scheduled']
            ]]);

        $this->assertDatabaseHas('transactions', [
            'scheduling_date' => Carbon::now()->format('Y-m-d'),
            'amount'          => $amount,
            'status'          => TransactionEnum::STATUS['scheduled']
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $user->id,
            'available_balance' => 40,
            'blocked_balance'   => $amount,
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payee->id,
            'available_balance' => 0,
            'blocked_balance'   => 0,
        ]);
    }


    /**
     * Test transfer processing with unauthorized
     *
     * @return void
     */
    public function test_transfer_processing_with_unauthorized ()
    {
        Config::set('appconfig.request_authorize_transaction.url', 'https://run.mocky.io/v3/860e9adf-6d6a-41cc-94e5-5786df5ac5b4');
        Config::set('queue.default', 'sync');

        $user  = User::factory()->hasWallet(['available_balance' => 1000])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $amount = 150;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'payee_id' => $payee->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'scheduling_date' => Carbon::now()->format('Y-m-d'),
            'amount'          => $amount,
            'status'          => TransactionEnum::STATUS['unauthorized']
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $user->id,
            'available_balance' => 1000,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payee->id,
            'available_balance' => 0,
            'blocked_balance'   => 0,
        ]);
    }

    /**
     * Test failed transfer processing in authorization api
     *
     * @return void
     */
    public function test_failed_transfer_processing_in_authorization_api ()
    {
        Config::set('appconfig.request_authorize_transaction.url', 'https://run.mocky.io/v3/860e9adf-6d6a-41cc-94e5-TESTE');
        Config::set('queue.default', 'sync');

        $user  = User::factory()->hasWallet(['available_balance' => 1000])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $amount = 150;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'payee_id' => $payee->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'scheduling_date' => Carbon::now()->format('Y-m-d'),
            'amount'          => $amount,
            'status'          => TransactionEnum::STATUS['unauthorized']
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $user->id,
            'available_balance' => 1000,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payee->id,
            'available_balance' => 0,
            'blocked_balance'   => 0,
        ]);
    }

    /**
     * Test authorized transfer processing
     *
     * @return void
     */
    public function test_authorized_transfer_processing ()
    {

        $payer = User::factory()->hasWallet(['available_balance' => 1500])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $payer->assignRole(UserRoles::USER);

        Passport::actingAs($payer);

        $amount = 300;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'payee_id' => $payee->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'scheduling_date' => Carbon::now()->format('Y-m-d'),
            'amount'          => $amount,
            'status'          => TransactionEnum::STATUS['finalized']
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payer->id,
            'available_balance' => 1200,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payee->id,
            'available_balance' => $amount,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('extracts', [
            'personable_type' => User::class,
            'personable_id'   => $payer->id,
            'value'           => $amount,
            'type'            => ExtractEnum::OUTCOMING,
            'current_value'   => 1200,
            'description'     => ExtractEnum::TRANSACTION_TEXT['outcoming'] . $payee->name,
        ]);

        $this->assertDatabaseHas('extracts', [
            'personable_type' => User::class,
            'personable_id'   => $payee->id,
            'value'           => $amount,
            'type'            => ExtractEnum::INCOMING,
            'current_value'   => $amount,
            'description'     => ExtractEnum::TRANSACTION_TEXT['incoming'] . $payer->name,
        ]);
    }

    /**
     * Test failed transfer processing in notification api
     *
     * @return void
     */
    public function test_failed_transfer_processing_in_notification_api ()
    {
        Config::set('appconfig.notifier.url', 'http://o4d9z.mocklab.io/notify324');
        Config::set('queue.default', 'sync');

        $payer = User::factory()->hasWallet(['available_balance' => 1500])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $payer->assignRole(UserRoles::USER);

        Passport::actingAs($payer);

        $amount = 300;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'payee_id' => $payee->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'scheduling_date' => Carbon::now()->format('Y-m-d'),
            'amount'          => $amount,
            'status'          => TransactionEnum::STATUS['finalized']
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payer->id,
            'available_balance' => 1200,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payee->id,
            'available_balance' => $amount,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('extracts', [
            'personable_type' => User::class,
            'personable_id'   => $payer->id,
            'value'           => $amount,
            'type'            => ExtractEnum::OUTCOMING,
            'current_value'   => 1200,
            'description'     => ExtractEnum::TRANSACTION_TEXT['outcoming'] . $payee->name,
        ]);

        $this->assertDatabaseHas('extracts', [
            'personable_type' => User::class,
            'personable_id'   => $payee->id,
            'value'           => $amount,
            'type'            => ExtractEnum::INCOMING,
            'current_value'   => $amount,
            'description'     => ExtractEnum::TRANSACTION_TEXT['incoming'] . $payer->name,
        ]);
    }
}
