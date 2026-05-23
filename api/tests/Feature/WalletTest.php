<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_deposito_falha_com_mais_de_dois_decimais(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/wallet/deposit', ['amount' => 10.001])
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

    public function test_saque_falha_com_valor_negativo(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/wallet/withdraw', ['amount' => -50])
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

    // --- Integridade ---

    public function test_historico_balance_after_e_consistente(): void
    {
        $this->actingAs($this->user)->postJson('/api/wallet/deposit',  ['amount' => 100.00]);
        $this->actingAs($this->user)->postJson('/api/wallet/deposit',  ['amount' => 50.00]);
        $this->actingAs($this->user)->postJson('/api/wallet/withdraw', ['amount' => 30.00]);

        $transactions = $this->user->wallet->transactions()->orderBy('id')->get();

        $this->assertEquals(10000, $transactions[0]->balance_after); // depósito R$100 → saldo R$100
        $this->assertEquals(15000, $transactions[1]->balance_after); // depósito R$50  → saldo R$150
        $this->assertEquals(12000, $transactions[2]->balance_after); // saque   R$30  → saldo R$120
    }

    public function test_rollback_reverte_saldo_se_operacao_falhar(): void
    {
        $wallet = $this->user->wallet;

        // Subclasse que força falha após o incremento, dentro da transaction
        $service = new class extends WalletService {
            public function deposit(Wallet $wallet, float $amount): Transaction
            {
                return DB::transaction(function () use ($wallet, $amount) {
                    $wallet->increment('balance', Money::toCents($amount));
                    throw new \RuntimeException('Falha simulada após incremento');
                });
            }
        };

        try {
            $service->deposit($wallet, 100.00);
        } catch (\RuntimeException) {}

        $this->assertEquals(0, $wallet->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_saque_nao_gera_saldo_negativo_em_requisicoes_rapidas(): void
    {
        $this->user->wallet->update(['balance' => 5000]); // R$50,00

        // Duas requisições com saldo que comporta apenas uma.
        // O lockForUpdate no WalletService garante que a leitura do saldo
        // e o decremento são atômicos, impedindo que ambas passem pelo check.
        $respostas = [
            $this->actingAs($this->user)->postJson('/api/wallet/withdraw', ['amount' => 50.00]),
            $this->actingAs($this->user)->postJson('/api/wallet/withdraw', ['amount' => 50.00]),
        ];

        $statusCodes = array_map(fn($r) => $r->status(), $respostas);

        $this->assertContains(201, $statusCodes);
        $this->assertContains(422, $statusCodes);
        $this->assertDatabaseHas('wallets', ['user_id' => $this->user->id, 'balance' => 0]);
        $this->assertDatabaseCount('transactions', 1);
    }

    // --- Autenticação ---

    public function test_operacoes_exigem_autenticacao(): void
    {
        $this->postJson('/api/wallet/deposit',  ['amount' => 100])->assertStatus(401);
        $this->postJson('/api/wallet/withdraw', ['amount' => 100])->assertStatus(401);
    }
}
