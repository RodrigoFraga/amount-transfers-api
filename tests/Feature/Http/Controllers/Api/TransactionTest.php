<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\ExtractEnum;
use App\Enums\TransactionEnum;
use App\Enums\UserRoles;
use App\Models\Store;
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
        $user  = User::factory()->hasWallet()->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $user->assignRole(UserRoles::USER);

        Passport::actingAs($user);

        $response = $this->json('POST', '/api/transaction', ['amount' => 100, 'wallet_payee_id' => $payee->wallet->id]);

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

        $response = $this->json('POST', '/api/transaction', ['amount' => 100, 'wallet_payee_id' => -1]);

        $response->assertStatus(422)
            ->assertJson([
                'error'   => true,
                'message' => [['wallet_payee_id' => ['The selected wallet payee is invalid.']]]
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

        $response = $this->json('POST', '/api/transaction', ['amount' => 0, 'wallet_payee_id' => $payee->wallet->id]);

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

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

        $response->assertStatus(200)
            ->assertExactJson(['data' => [
                'scheduling_date' => Carbon::now()->format('Y-m-d'),
                'user_id'         => $user->id,
                'user_name'       => $user->name,
                'wallet_payee_id' => $payee->wallet->id,
                'amount'          => $amount,
                'status'          => TransactionEnum::STATUS['scheduled']
            ]]);

        $this->assertDatabaseHas('transactions', [
            'user_id'         => $user->id,
            'wallet_payer_id' => $user->wallet->id,
            'wallet_payee_id' => $payee->wallet->id,
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

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'user_id'         => $user->id,
            'wallet_payer_id' => $user->wallet->id,
            'wallet_payee_id' => $payee->wallet->id, 'scheduling_date' => Carbon::now()->format('Y-m-d'),
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

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'user_id'         => $user->id,
            'wallet_payer_id' => $user->wallet->id,
            'wallet_payee_id' => $payee->wallet->id, 'scheduling_date' => Carbon::now()->format('Y-m-d'),
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
     * Test authorized transfer processing between users
     *
     * @return void
     */
    public function test_authorized_transfer_processing_between_users ()
    {

        $payer = User::factory()->hasWallet(['available_balance' => 1500])->create();
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $payer->assignRole(UserRoles::USER);

        Passport::actingAs($payer);

        $amount = 300;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

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

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

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
     * Test authorized transfer processing between user and store
     *
     * @return void
     */
    public function test_authorized_transfer_processing_between_user_and_store ()
    {

        $payer = User::factory()->hasWallet(['available_balance' => 1500])->create();
        $payee = Store::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::USER, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $payer->assignRole(UserRoles::USER);

        Passport::actingAs($payer);

        $amount = 300;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

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
            'personable_type'   => Store::class,
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
            'personable_type' => Store::class,
            'personable_id'   => $payee->id,
            'value'           => $amount,
            'type'            => ExtractEnum::INCOMING,
            'current_value'   => $amount,
            'description'     => ExtractEnum::TRANSACTION_TEXT['incoming'] . $payer->name,
        ]);
    }

    /**
     * Test an unauthorized transfer from store to user
     *
     * @return void
     */
    public function test_an_unauthorized_transfer_from_store_to_user ()
    {

        $storeUser = User::factory()->create();
        $payer     = Store::factory()->hasWallet(['available_balance' => 1500])->create(['user_id' => $storeUser->id]);
        $payee     = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::STORE, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $storeUser->assignRole(UserRoles::STORE);

        Passport::actingAs($storeUser);

        $amount = 300;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

        $response->assertStatus(403)
            ->assertJson([
                'error'   => true,
                'message' => [['error' => ['Resource unavailable']]]
            ]);

        $this->assertDatabaseCount('transactions', 0);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => Store::class,
            'personable_id'     => $payer->id,
            'available_balance' => 1500,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => User::class,
            'personable_id'     => $payee->id,
            'available_balance' => 0,
            'blocked_balance'   => 0,
        ]);

        $this->assertDatabaseCount('extracts', 0);
    }

    /**
     * Test authorized transfer from store to user
     *
     * @return void
     */
    /*public function test_authorized_transfer_from_store_to_user ()
    {

        $storeUser = User::factory()->create();
        $payer = Store::factory()->hasWallet(['available_balance' => 1500])->create(['user_id' => $storeUser->id]);
        $payee = User::factory()->hasWallet()->create();

        $role = Role::create(['name' => UserRoles::STORE, 'guard_name' => 'api']);
        $role->givePermissionTo([Permission::create(['name' => 'transfer:store', 'guard_name' => 'api'])]);

        $storeUser->assignRole(UserRoles::STORE);

        Passport::actingAs($storeUser);

        $amount = 300;

        $response = $this->json('POST', '/api/transaction', ['amount' => $amount, 'wallet_payee_id' => $payee->wallet->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'scheduling_date' => Carbon::now()->format('Y-m-d'),
            'amount'          => $amount,
            'status'          => TransactionEnum::STATUS['finalized']
        ]);

        $this->assertDatabaseHas('wallets', [
            'personable_type'   => Store::class,
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
            'personable_type' => Store::class,
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
    }*/
}
