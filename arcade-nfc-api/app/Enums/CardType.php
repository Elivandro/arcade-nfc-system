<?php

declare(strict_types=1);

namespace App\Enums;

enum CardType: string
{
    case ACTIVE     = 'active';
    case BLOCKED    = 'blocked';
    case SUSPENDED  = 'suspended';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE     => 'Ativo',
            self::BLOCKED    => 'Bloqueado',
            self::SUSPENDED  => 'Suspenso'
        };
    }
}
