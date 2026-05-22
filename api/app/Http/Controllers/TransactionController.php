<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionIndexRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Retorna o historico de transações com os filtros encadeados com when()
     * e paginado com 15 items por página.
     *
     * @param TransactionIndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(TransactionIndexRequest $request): AnonymousResourceCollection
    {
        $transactions = Auth::user()
            ->wallet
            ->transactions()
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->start_date, fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->latest()
            ->paginate(15);

        return TransactionResource::collection($transactions);
    }
}
