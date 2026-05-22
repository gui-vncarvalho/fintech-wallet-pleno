<?php

namespace Database\Seeders;

use App\Exceptions\InsufficientBalanceException;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function __construct(private readonly WalletService $walletService) {}

    /**
     * @return void
     * @throws InsufficientBalanceException
     */
    public function run(): void
    {
        $wallet = User::where('email', 'teste@wallet.com')->firstOrFail()->wallet;

        if ($wallet->transactions()->exists()) {
            return;
        }

        $this->walletService->deposit($wallet, 500.00);
        $this->walletService->deposit($wallet, 250.50);
        $this->walletService->withdraw($wallet, 100.00);
        $this->walletService->deposit($wallet, 1000.00);
        $this->walletService->withdraw($wallet, 75.25);
        $this->walletService->deposit($wallet, 300.00);
        $this->walletService->withdraw($wallet, 50.00);
    }
}
