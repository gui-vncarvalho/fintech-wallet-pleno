<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Recebe o valor do deposito, converte e salva.
     *
     * @param Wallet $wallet
     * @param float $amount
     * @return Transaction
     */
    public function deposit(Wallet $wallet, float $amount): Transaction
    {
        $cents = Money::toCents($amount);

        return DB::transaction(function () use ($wallet, $cents) {
            $wallet->increment('balance', $cents);
            $wallet->refresh();

            return $wallet->transactions()->create([
                'type'          => TransactionType::Credit,
                'amount'        => $cents,
                'balance_after' => $wallet->balance,
            ]);
        });
    }

    /**
     * Recebe o valor do saque, converte e salva.
     *
     * @param Wallet $wallet
     * @param float $amount
     * @return Transaction
     * @throws InsufficientBalanceException
     */
    public function withdraw(Wallet $wallet, float $amount): Transaction
    {
        $cents = Money::toCents($amount);

        if ($wallet->balance < $cents) {
            throw new InsufficientBalanceException();
        }

        return DB::transaction(function () use ($wallet, $cents) {
            $wallet->decrement('balance', $cents);
            $wallet->refresh();

            return $wallet->transactions()->create([
                'type'          => TransactionType::Debit,
                'amount'        => $cents,
                'balance_after' => $wallet->balance,
            ]);
        });
    }
}
