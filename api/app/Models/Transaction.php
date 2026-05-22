<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wallet_id', 'type', 'amount', 'balance_after'])]
class Transaction extends Model
{
    /**
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'type'          => TransactionType::class,
            'amount'        => 'integer',
            'balance_after' => 'integer',
        ];
    }
}
