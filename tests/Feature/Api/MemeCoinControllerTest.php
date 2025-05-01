<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\MemeCoin;
use App\Models\MemeCoinAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\MemeCoinAttempted;
use Tests\TestCase;

class MemeCoinControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_a_unique_memecoin_and_logs_successful_attempt()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/memecoin/generate-name', ['full_name' => 'John Michael Doe']);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $coin = MemeCoin::first();

        $this->assertEquals('John Michael Doe', $coin->full_name);
        $this->assertEquals($user->id, $coin->user_id);

        $this->assertDatabaseHas('meme_coin_attempts', [
            'user_id' => $user->id,
            'status' => 'success',
            'full_name' => 'John Michael Doe'
        ]);
    }

    /** @test */
    public function it_appends_number_on_duplicate_and_logs_all_attempts()
    {
        $user = User::factory()->create();

        MemeCoin::create([
            'user_id' => $user->id,
            'full_name' => 'Jane Smith',
            'coin_name' => 'MoonJaSmToken',
            'attempts' => 1
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/memecoin/generate-name', ['full_name' => 'Jane Smith']);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success']);

        $data = $response->json();

        $this->assertStringContainsString('MoonJaSmToken2', $data['coin_name']);

        $this->assertDatabaseHas('meme_coin_attempts', [
            'user_id' => $user->id,
            'full_name' => 'Jane Smith',
            'attempted_name' => 'MoonJaSmToken',
            'attempt_number' => 1,
            'status' => 'duplicate'
        ]);
        $this->assertDatabaseHas('meme_coin_attempts', [
            'attempted_name' => 'MoonJaSmToken2',
            'attempt_number' => 2,
            'status' => 'success'
        ]);
    }

    /** @test */
    public function it_errors_after_3_failed_attempts_and_logs_exhaustion()
    {
        $user = User::factory()->create();

        $base = app(\App\Services\MemeCoinService::class)->buildBaseCoinName('Matt Test');
        MemeCoin::create(['user_id' => $user->id, 'full_name' => 'Matt Test', 'coin_name' => $base, 'attempts' => 1]);
        MemeCoin::create(['user_id' => $user->id, 'full_name' => 'Matt Test', 'coin_name' => $base.'2', 'attempts' => 2]);
        MemeCoin::create(['user_id' => $user->id, 'full_name' => 'Matt Test', 'coin_name' => $base.'3', 'attempts' => 3]);

        $response = $this->actingAs($user)
            ->postJson('/api/memecoin/generate-name', ['full_name' => 'Matt Test']);

        $response->assertStatus(409)
            ->assertJsonFragment(['status' => 'error']);

        $this->assertDatabaseHas('meme_coin_attempts', [
            'user_id' => $user->id,
            'full_name' => 'Matt Test',
            'status' => 'exhausted'
        ]);
        $this->assertCount(4, MemeCoinAttempt::where('user_id', $user->id)->where('full_name', 'Matt Test')->get());
    }


    /** @test */
    public function it_returns_validation_error_for_missing_full_name()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson('/api/memecoin/generate-name', []);
        $response->assertStatus(422)->assertJsonValidationErrors(['full_name']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/memecoin/generate-name', ['full_name' => 'Anon']);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_unicode_full_names()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/memecoin/generate-name', ['full_name' => 'José Álvarez']);

        $response->assertStatus(200)->assertJsonFragment(['status' => 'success']);

        $this->assertDatabaseHas('meme_coin_attempts', [
            'user_id' => $user->id,
            'status' => 'success',
            'full_name' => 'José Álvarez'
        ]);
    }

    /** @test */
    public function it_dispatches_the_memecoin_attempted_event()
    {
        Event::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/memecoin/generate-name', ['full_name' => 'Event Tester']);

        Event::assertDispatched(MemeCoinAttempted::class);
    }
}
