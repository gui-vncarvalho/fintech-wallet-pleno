<?php

namespace App\Http\Resources;

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
            'amount'        => $this->amount / 100,
            'balance_after' => $this->balance_after / 100,
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
}
