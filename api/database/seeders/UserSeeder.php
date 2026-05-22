<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'     => 'Usuário Teste',
            'email'    => 'teste@wallet.com',
            'password' => Hash::make('password'),
        ]);

        $user->wallet()->create(['balance' => 0]);
    }
}
