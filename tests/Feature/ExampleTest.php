<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    // public function test_the_application_returns_a_successful_response(): void
    // {
    //     $user = User::factory()->create();
    //     $response = $this->get('/');
    //     $response->assertStatus(200);
    // }
}
