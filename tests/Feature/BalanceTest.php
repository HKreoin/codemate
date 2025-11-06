<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_success(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 500.00,
            'comment' => 'Пополнение через карту',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 500.00,
            ]);
    }

    public function test_deposit_creates_balance_if_not_exists(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);
    }

    public function test_withdraw_success(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 500.00,
        ]);

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 200.00,
            'comment' => 'Покупка подписки',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 300.00,
            ]);
    }

    public function test_withdraw_insufficient_funds(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/withdraw', [
            'user_id' => $user->id,
            'amount' => 200.00,
        ]);

        $response->assertStatus(409);
    }

    public function test_transfer_success(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        $this->postJson('/api/deposit', [
            'user_id' => $fromUser->id,
            'amount' => 500.00,
        ]);

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'amount' => 150.00,
            'comment' => 'Перевод другу',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'from_balance' => 350.00,
                'to_balance' => 150.00,
            ]);
    }

    public function test_transfer_insufficient_funds(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'amount' => 150.00,
        ]);

        $response->assertStatus(409);
    }

    public function test_get_balance(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/deposit', [
            'user_id' => $user->id,
            'amount' => 350.00,
        ]);

        $response = $this->getJson("/api/balance/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->id,
                'balance' => 350.00,
            ]);
    }

    public function test_get_balance_user_not_found(): void
    {
        $response = $this->getJson('/api/balance/999');

        $response->assertStatus(404);
    }

    public function test_deposit_validation(): void
    {
        $response = $this->postJson('/api/deposit', []);

        $response->assertStatus(422);
    }

    public function test_withdraw_validation(): void
    {
        $response = $this->postJson('/api/withdraw', []);

        $response->assertStatus(422);
    }

    public function test_transfer_validation(): void
    {
        $response = $this->postJson('/api/transfer', []);

        $response->assertStatus(422);
    }
}
