<?php

namespace App\Support;

class Money
{
    /**
     * Converte dinheiro para centavos
     *
     * @param float $amount
     * @return int
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Converte dinheiro para decimal
     *
     * @param int $cents
     * @return float
     */
    public static function toDecimal(int $cents): float
    {
        return $cents / 100;
    }
}
