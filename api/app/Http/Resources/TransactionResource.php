<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Formata a resposta e devolve pro front já em reais.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'type'          => $this->type->value,
            'amount'        => Money::toDecimal($this->amount),
            'balance_after' => Money::toDecimal($this->balance_after),
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
}
