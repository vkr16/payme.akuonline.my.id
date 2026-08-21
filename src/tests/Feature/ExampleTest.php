<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test root route redirects to create bill.
     */
    public function test_the_application_redirects_to_create_page()
    {
        $response = $this->get('/');
        $response->assertRedirect('/create');
    }

    /**
     * Test create page returns 200 OK.
     */
    public function test_create_page_loads_successfully()
    {
        $response = $this->get('/create');
        $response->assertStatus(200);
    }
}
