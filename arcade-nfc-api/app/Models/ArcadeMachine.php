<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'api_token',
    'is_online',
    'cost_per_play'
])]
class ArcadeMachine extends Model
{
    use HasFactory;
    use HasUuids;

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'cost_per_play' => 'decimal:2'
        ];
    }
}
