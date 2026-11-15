<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'document',
    'phone',
    'email'
])]
class Customer extends Model
{
    use HasUuids;
    use HasFactory;

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
