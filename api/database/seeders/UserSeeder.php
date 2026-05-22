<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'teste@wallet.com'],
            [
                'name'     => 'Usuário Teste',
                'password' => Hash::make('password'),
            ]
        );

        if (! $user->wallet()->exists()) {
            $user->wallet()->create(['balance' => 0]);
        }
    }
}
