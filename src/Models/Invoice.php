<?php

namespace AngelitoSystems\FilamentTenancy\Models;

use AngelitoSystems\FilamentTenancy\Concerns\UsesLandlordConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes, UsesLandlordConnection;

    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'subscription_id',
        'tenant_id',
        'plan_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'currency',
        'status',
        'issued_at',
        'due_date',
        'paid_at',
        'canceled_at',
        'payment_method',
        'payment_reference',
        'payment_link',
        'notes',
        'cancel_reason',
        'metadata',
    ];

    protected $casts = [
        'subscription_id' => 'integer',
        'tenant_id' => 'integer',
        'plan_id' => 'integer',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'canceled_at' => 'datetime',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Invoice statuses.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELED = 'canceled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
            
            if (empty($invoice->issued_at)) {
                $invoice->issued_at = now();
            }
        });
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = config('filament-tenancy.invoice_prefix', 'INV');
        $year = now()->format('Y');
        
        do {
            $number = sprintf('%s-%s-%06d', $prefix, $year, rand(100000, 999999));
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }

    /**
     * Get the subscription for this invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the tenant for this invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the plan for this invoice.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if invoice is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->isPending() 
            && $this->due_date 
            && $this->due_date->isPast();
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(?string $paymentMethod = null, ?string $paymentReference = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => $paymentMethod ?? $this->payment_method,
            'payment_reference' => $paymentReference ?? $this->payment_reference,
        ]);
    }

    /**
     * Cancel invoice.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELED,
            'canceled_at' => now(),
            'cancel_reason' => $reason ?? $this->cancel_reason,
        ]);
    }

    /**
     * Calculate total amount.
     */
    public function calculateTotal(): float
    {
        return max(0, $this->subtotal + $this->tax - $this->discount);
    }
}




