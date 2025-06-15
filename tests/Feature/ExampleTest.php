<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_unauthenticated_redirects(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }
}
