<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
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
            'balance'          => $wallet->balance / 100,
            'deposited_month'  => $deposited / 100,
            'withdrawn_month'  => $withdrawn / 100,
            'last_transactions' => TransactionResource::collection($lastTransactions),
        ]);
    }
}
