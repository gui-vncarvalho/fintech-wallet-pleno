<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService) {}

    /**
     * Função de depósito
     *
     * @param DepositRequest $request
     * @return JsonResponse
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        $transaction = $this->walletService->deposit(
            Auth::user()->wallet,
            $request->float('amount'),
        );

        return TransactionResource::make($transaction)->response()->setStatusCode(201);
    }

    /**
     * Função de Saque
     *
     * @param WithdrawRequest $request
     * @return JsonResponse
     * @throws InsufficientBalanceException
     */
    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $transaction = $this->walletService->withdraw(
            Auth::user()->wallet,
            $request->float('amount'),
        );

        return TransactionResource::make($transaction)->response()->setStatusCode(201);
    }
}
