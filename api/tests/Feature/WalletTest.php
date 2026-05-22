<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->wallet()->create(['balance' => 0]);
    }

    // --- Depósito ---

    public function test_deposito_adiciona_saldo_e_registra_transacao(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/wallet/deposit', ['amount' => 100.00]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', ['user_id' => $this->user->id, 'balance' => 10000]);
        $this->assertDatabaseHas('transactions', [
            'type'          => 'credit',
            'amount'        => 10000,
            'balance_after' => 10000,
        ]);
    }

    public function test_deposito_falha_com_valor_zero(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/wallet/deposit', ['amount' => 0])
            ->assertStatus(422);
    }

    public function test_deposito_falha_com_valor_negativo(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/wallet/deposit', ['amount' => -50])
            ->assertStatus(422);
    }

    public function test_deposito_falha_com_valor_abaixo_do_minimo(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/wallet/deposit', ['amount' => 0.001])
            ->assertStatus(422);
    }

    // --- Saque ---

    public function test_saque_deduz_saldo_e_registra_transacao(): void
    {
        $this->user->wallet->update(['balance' => 20000]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/wallet/withdraw', ['amount' => 50.00]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', ['user_id' => $this->user->id, 'balance' => 15000]);
        $this->assertDatabaseHas('transactions', [
            'type'          => 'debit',
            'amount'        => 5000,
            'balance_after' => 15000,
        ]);
    }

    public function test_saque_falha_com_saldo_insuficiente(): void
    {
        $this->user->wallet->update(['balance' => 500]);

        $this->actingAs($this->user)
            ->postJson('/api/wallet/withdraw', ['amount' => 10.00])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Saldo insuficiente para realizar o saque.');
    }

    public function test_saque_falha_com_valor_zero(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/wallet/withdraw', ['amount' => 0])
            ->assertStatus(422);
    }

    public function test_saldo_nao_fica_negativo_apos_saque_exato(): void
    {
        $this->user->wallet->update(['balance' => 5000]);

        $this->actingAs($this->user)
            ->postJson('/api/wallet/withdraw', ['amount' => 50.00])
            ->assertStatus(201);

        $this->assertDatabaseHas('wallets', ['user_id' => $this->user->id, 'balance' => 0]);
    }

    // --- Endpoint protegido ---

    public function test_operacoes_exigem_autenticacao(): void
    {
        $this->postJson('/api/wallet/deposit', ['amount' => 100])->assertStatus(401);
        $this->postJson('/api/wallet/withdraw', ['amount' => 100])->assertStatus(401);
    }
}
