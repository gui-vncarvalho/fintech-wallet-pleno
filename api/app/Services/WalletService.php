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
     * Coloquei um lock só pra garantir que a verificação e o saque são atômicos,
     * evitando race condition onde dois saques simultâneos poderiam passar
     * pelo check com o mesmo saldo desatualizado.
     *
     * @param Wallet $wallet
     * @param float $amount
     * @return Transaction
     * @throws InsufficientBalanceException
     */
    public function withdraw(Wallet $wallet, float $amount): Transaction
    {
        $cents = Money::toCents($amount);

        return DB::transaction(function () use ($wallet, $cents) {
            $locked = Wallet::lockForUpdate()->find($wallet->id);

            if ($locked->balance < $cents) {
                throw new InsufficientBalanceException();
            }

            $locked->decrement('balance', $cents);
            $locked->refresh();

            return $locked->transactions()->create([
                'type'          => TransactionType::Debit,
                'amount'        => $cents,
                'balance_after' => $locked->balance,
            ]);
        });
    }
}
