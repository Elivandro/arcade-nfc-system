<?php

namespace App\Enums;

enum TransactionType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEBIT     => 'Débito',
            self::CREDIT   => 'Crédito'
        };
    }
}
