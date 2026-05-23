<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Retorna os dados da dashboard:
     * saldo, ultimas transações e totais do mês
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $wallet = Auth::user()->wallet;

        $deposited = $wallet->transactions()
            ->where('type', TransactionType::Credit)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $withdrawn = $wallet->transactions()
            ->where('type', TransactionType::Debit)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $lastTransactions = $wallet->transactions()
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'balance'           => Money::toDecimal($wallet->balance),
            'deposited_month'   => Money::toDecimal($deposited),
            'withdrawn_month'   => Money::toDecimal($withdrawn),
            'last_transactions' => TransactionResource::collection($lastTransactions),
        ]);
    }
}
