<?php

namespace App\Models;

use App\Enums\CardType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'uid',
    'secure_key',
    'balance',
    'status'
])]
class Card extends Model
{
    use HasUuids;
    use HasFactory;

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'status' => CardType::class
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
