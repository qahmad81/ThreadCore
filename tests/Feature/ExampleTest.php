<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_displays_seeded_landing_page(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Manage AI threads, providers, and agent memory from one gateway.')
            ->assertSee('One API surface');
    }
}
