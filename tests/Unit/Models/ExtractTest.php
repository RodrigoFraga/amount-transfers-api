<?php

namespace Tests\Unit\Models;

use App\Models\Extract;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ExtractTest extends TestCase
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
        $fillableTest = ['description', 'value', 'current_value', 'type'];

        $this->assertEqualsCanonicalizing($fillableTest, (new Extract())->getFillable());
    }

    /**
     * Test if the class has a person method
     *
     * @return void
     * @test
     */
    public function test_if_the_class_has_a_person_method ()
    {
        $this->assertTrue(method_exists(new Extract(), 'personable'));
    }

}
