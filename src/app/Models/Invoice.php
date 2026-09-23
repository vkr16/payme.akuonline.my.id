<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'edit_token',
        'passcode',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'sender_name',
        'sender_email',
        'sender_phone',
        'sender_address',
        'sender_logo_path',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'client_company',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'qris_image_path',
        'qris_static_payload',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'shipping_fee',
        'total_amount',
        'currency',
        'notes',
        'terms',
        'paid_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'shipping_fee' => 'float',
        'total_amount' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->slug)) {
                $invoice->slug = Str::random(10);
            }
            if (empty($invoice->edit_token)) {
                $invoice->edit_token = Str::random(32);
            }
            if (empty($invoice->passcode)) {
                $invoice->passcode = strtoupper(Str::random(8));
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function banks(): HasMany
    {
        return $this->hasMany(InvoiceBank::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => 'Belum Dibayar',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-success text-white',
            'cancelled' => 'bg-secondary text-white',
            default => 'bg-warning text-dark',
        };
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getWhatsappShareUrlAttribute(): string
    {
        $invoiceUrl = url('/i/' . $this->slug);
        $totalFormatted = $this->formatted_total_amount;
        $dueDateText = $this->due_date ? ' jatuh tempo pada ' . $this->due_date->translatedFormat('d F Y') : '';

        $message = "Halo {$this->client_name},\n\n";
        $message .= "Berikut adalah tagihan *{$this->invoice_number}* dari *{$this->sender_name}* sebesar *{$totalFormatted}*{$dueDateText}.\n\n";
        $message .= "Rincian dan instruksi pembayaran lengkap dapat dilihat di link berikut:\n{$invoiceUrl}\n\n";
        $message .= "Terima kasih!";

        $phone = preg_replace('/[^0-9]/', '', (string) $this->client_phone);
        if ($phone) {
            if (str_starts_with($phone, '08')) {
                $phone = '628' . substr($phone, 2);
            } elseif (str_starts_with($phone, '8')) {
                $phone = '628' . substr($phone, 1);
            }
            return 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . rawurlencode($message);
        }

        return 'https://api.whatsapp.com/send?text=' . rawurlencode($message);
    }
}
