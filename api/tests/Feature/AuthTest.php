<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_cria_usuario_com_carteira_zerada(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Teste',
            'email'                 => 'teste@wallet.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user' => ['wallet'], 'token']);

        $this->assertDatabaseHas('wallets', ['balance' => 0]);
    }

    public function test_registro_falha_com_email_duplicado(): void
    {
        User::factory()->create(['email' => 'teste@wallet.com']);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Outro',
            'email'                 => 'teste@wallet.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', fn ($v) => str_contains($v, 'already been taken'));
    }

    public function test_registro_falha_sem_confirmacao_de_senha(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Teste',
            'email'                 => 'teste@wallet.com',
            'password'              => 'password',
            'password_confirmation' => 'outra_senha',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.password.0', fn ($v) => str_contains($v, 'confirmation'));
    }

    public function test_login_retorna_token(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 0]);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_falha_com_credenciais_invalidas(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'naoexiste@wallet.com',
            'password' => 'senhaerrada',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciais inválidas.');
    }
}
