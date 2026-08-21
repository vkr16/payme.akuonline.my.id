<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'host_name',
        'qris_static_payload',
        'qris_merchant_name',
        'qris_merchant_city',
        'qris_image_path',
        'receipt_image_path',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'delivery_fee',
        'service_fee',
        'discount',
    ];

    protected $casts = [
        'delivery_fee' => 'float',
        'service_fee' => 'float',
        'discount' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (empty($bill->slug)) {
                $bill->slug = Str::random(8);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(BillClaim::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(BillBank::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->qty * $item->price);
    }

    public function getNetExtraFeesAttribute(): float
    {
        return (float) ($this->delivery_fee + $this->service_fee - $this->discount);
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) max(0, $this->subtotal + $this->net_extra_fees);
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->claims->sum('amount');
    }

    public function getUnpaidAmountAttribute(): float
    {
        return (float) max(0, $this->total_amount - $this->total_paid);
    }

    public function getPaymentProgressPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 100;
        }

        return min(100, round(($this->total_paid / $this->total_amount) * 100, 1));
    }
}
