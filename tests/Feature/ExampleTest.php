<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_public_landing_page_for_guests()
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Home'));
    }
}
