<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'name',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'float',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function claimItems(): HasMany
    {
        return $this->hasMany(BillClaimItem::class, 'bill_item_id');
    }

    public function getClaimedQtyAttribute(): int
    {
        return (int) $this->claimItems->sum('qty');
    }

    public function getRemainingQtyAttribute(): int
    {
        return max(0, $this->qty - $this->claimed_qty);
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->qty * $this->price);
    }
}
