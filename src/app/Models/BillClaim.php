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

    public function getExactPayableAttribute(): float
    {
        $bill = $this->bill;
        if (!$bill) {
            return (float) $this->amount;
        }

        $itemsSubtotal = 0;
        foreach ($this->claimItems as $cItem) {
            if ($cItem->item) {
                $itemsSubtotal += ($cItem->qty * $cItem->item->price);
            }
        }

        $totalBillSubtotal = $bill->subtotal;
        $netExtraFees = $bill->net_extra_fees;

        $feeShare = 0;
        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $feeShare = $proportion * $netExtraFees;
        }

        return (float) max(0, round($itemsSubtotal + $feeShare));
    }

    public function claimItems(): HasMany
    {
        return $this->hasMany(BillClaimItem::class, 'bill_claim_id');
    }

    public function getSurplusAttribute(): float
    {
        return (float) max(0, $this->amount - $this->exact_payable);
    }
}
