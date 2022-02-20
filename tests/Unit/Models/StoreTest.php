<?php

namespace Tests\Unit\Models;

use App\Models\Store;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StoreTest extends TestCase
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
        $fillableTest = ['name','legal_name', 'email', 'document',];

        $fillable = (new Store())->getFillable();

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
        $data = Store::factory()->create();

        $this->assertDatabaseHas('stores', $data->toArray());
    }

    /**
     * Test registration with duplicate email.
     *
     * @return void
     * @test
     */
    public function test_registration_with_duplicate_email ()
    {
        $data = Store::factory()->create();

        $this->expectException(QueryException::class);

        Store::factory()->create(['email' => $data->email]);
    }

    /**
     * Test registration with duplicate document.
     *
     * @return void
     * @test
     */
    public function test_registration_with_duplicate_document ()
    {
        $data = Store::factory()->create();

        $this->expectException(QueryException::class);

        Store::factory()->create(['document' => $data->document]);
    }

    /**
     * Test class has wallet method.
     *
     * @return void
     * @test
     */
    public function test_class_has_wallet_method ()
    {
        $this->assertTrue(method_exists(new Store(), 'wallet'));
    }

    /**
     * Test if the class does not have the transaction method.
     *
     * @return void
     * @test
     */
    public function test_if_the_class_does_not_have_the_transaction_method ()
    {
        $this->assertFalse(method_exists(new Store(), 'transactions'));
    }

    /**
     * Test class has extracts method.
     *
     * @return void
     * @test
     */
    public function test_class_has_extracts_method ()
    {
        $this->assertTrue(method_exists(new Store(), 'extracts'));
    }
}
