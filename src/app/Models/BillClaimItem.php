<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillClaimItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_claim_id',
        'bill_item_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(BillClaim::class, 'bill_claim_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(BillItem::class, 'bill_item_id');
    }
}
