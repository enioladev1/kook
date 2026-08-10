<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_url_redirects_to_login()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }
}
