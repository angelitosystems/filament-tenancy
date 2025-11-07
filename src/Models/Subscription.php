<?php

namespace AngelitoSystems\FilamentTenancy\Models;

use AngelitoSystems\FilamentTenancy\Concerns\UsesLandlordConnection;
use AngelitoSystems\FilamentTenancy\Events\SubscriptionStatusChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use SoftDeletes, UsesLandlordConnection;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenancy_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'seller_id',
        'status',
        'payment_method',
        'external_id',
        'payment_link',
        'payment_link_expires_at',
        'price',
        'billing_cycle',
        'auto_renew',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'next_billing_at',
        'canceled_at',
        'canceled_reason',
        'notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tenant_id' => 'integer',
        'plan_id' => 'integer',
        'seller_id' => 'integer',
        'price' => 'decimal:2',
        'auto_renew' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'next_billing_at' => 'datetime',
        'canceled_at' => 'datetime',
        'payment_link_expires_at' => 'datetime',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Subscription statuses.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_TRIAL = 'trial';
    const STATUS_PENDING = 'pending';

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the seller for this subscription.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * Get the commission for this subscription.
     */
    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    /**
     * Get all invoices for this subscription.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Check if the subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL 
            && $this->trial_ends_at 
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED || $this->canceled_at !== null;
    }

    /**
     * Check if the subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if the subscription is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(?string $reason = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_CANCELED,
            'canceled_at' => now(),
            'canceled_reason' => $reason,
        ]);

        // Fire event if status changed
        if ($oldStatus !== self::STATUS_CANCELED) {
            event(new SubscriptionStatusChanged($this, $oldStatus, self::STATUS_CANCELED));
        }
    }

    /**
     * Activate the subscription.
     */
    public function activate(?Carbon $endsAt = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'starts_at' => $this->starts_at ?: now(),
            'ends_at' => $endsAt,
            'canceled_at' => null,
        ]);

        // Fire event if status changed
        if ($oldStatus !== self::STATUS_ACTIVE) {
            event(new SubscriptionStatusChanged($this, $oldStatus, self::STATUS_ACTIVE));
        }
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::updating(function ($subscription) {
            // Store old status temporarily if status is being changed
            if ($subscription->isDirty('status') && $subscription->exists) {
                $oldStatus = $subscription->getOriginal('status');
                // Use a static array to store temporarily (won't be saved to DB)
                static::$tempOldStatus[$subscription->id] = $oldStatus;
            }
        });

        static::saved(function ($subscription) {
            // Check if status was changed during this save
            $subscriptionId = $subscription->id;
            if (isset(static::$tempOldStatus[$subscriptionId])) {
                $oldStatus = static::$tempOldStatus[$subscriptionId];
                if ($oldStatus !== $subscription->status) {
                    event(new SubscriptionStatusChanged($subscription, $oldStatus, $subscription->status));
                }
                unset(static::$tempOldStatus[$subscriptionId]);
            }
        });
    }

    /**
     * Temporary storage for old status during updates.
     * This won't be saved to database.
     */
    protected static array $tempOldStatus = [];

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope a query to only include expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now())
            ->where('status', '!=', self::STATUS_CANCELED);
    }
}

