<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TwitchTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTwitchGet()
    {
        $response = $this->json('GET', '/twitch');

        $response
            ->assertStatus(200)
            ->assertJson([
              'streamers'  =>  [],
              'results'  =>  [],
              ]);
    }
}
