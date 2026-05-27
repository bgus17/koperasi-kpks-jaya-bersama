<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_gateway_page_is_visible_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewIs('gateway');
    }
}
