<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserTest extends TestCase
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
        $fillableTest = ['name', 'email', 'document', 'password'];

        $fillable = (new User())->getFillable();

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
        $data = User::factory()->create();

        $this->assertDatabaseHas('users', $data->makeHidden('role')->toArray());
    }

    /**
     * Test registration with duplicate email.
     *
     * @return void
     * @test
     */
    public function test_registration_with_duplicate_email ()
    {
        $data = User::factory()->create();

        $this->expectException(QueryException::class);

        User::factory()->create(['email' => $data->email]);
    }

    /**
     * Test registration with duplicate document.
     *
     * @return void
     * @test
     */
    public function test_registration_with_duplicate_document ()
    {
        $data = User::factory()->create();

        $this->expectException(QueryException::class);

        User::factory()->create(['document' => $data->document]);
    }

    /**
     * Test class has wallet method.
     *
     * @return void
     * @test
     */
    public function test_class_has_wallet_method ()
    {
        $this->assertTrue(method_exists(new User(), 'wallet'));
    }

    /**
     * Test class has transactions method.
     *
     * @return void
     * @test
     */
    public function test_class_has_transactions_method ()
    {
        $this->assertTrue(method_exists(new User(), 'transactions'));
    }

    /**
     * Test that sensitive data is not being returned.
     *
     * @return void
     * @test
     */
    public function test_that_sensitive_data_is_not_being_returned ()
    {
        $hidden = ["password", "remember_token"];

        $data   = User::factory()->create();

        $this->assertEquals($data->getHidden(), $hidden);

    }
}
