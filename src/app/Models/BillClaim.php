<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'payer_name',
        'amount',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function claimItems(): HasMany
    {
        return $this->hasMany(BillClaimItem::class, 'bill_claim_id');
    }
}
