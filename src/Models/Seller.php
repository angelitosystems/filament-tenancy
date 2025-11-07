<?php

namespace AngelitoSystems\FilamentTenancy\Models;

use AngelitoSystems\FilamentTenancy\Concerns\UsesLandlordConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Seller extends Model
{
    use SoftDeletes, UsesLandlordConnection;

    protected $table = 'sellers';

    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'is_active',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($seller) {
            if (empty($seller->code)) {
                $seller->code = static::generateUniqueCode();
            }
        });
    }

    /**
     * Get the user that is the seller.
     */
    public function user(): BelongsTo
    {
        $userModel = config('filament-tenancy.user_model', config('auth.providers.users.model', 'App\\Models\\User'));
        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Get seller name from user.
     */
    public function getNameAttribute(): string
    {
        return $this->user ? $this->user->name : '';
    }

    /**
     * Get seller email from user.
     */
    public function getEmailAttribute(): string
    {
        return $this->user ? $this->user->email : '';
    }

    /**
     * Generate a unique seller code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the subscriptions for this seller.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the commissions for this seller.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * Get pending commissions.
     */
    public function pendingCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'pending');
    }

    /**
     * Get paid commissions.
     */
    public function paidCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'paid');
    }

    /**
     * Calculate total commission amount.
     */
    public function getTotalCommissionsAttribute(): float
    {
        return $this->commissions()->sum('amount');
    }

    /**
     * Calculate total pending commissions.
     */
    public function getTotalPendingCommissionsAttribute(): float
    {
        return $this->pendingCommissions()->sum('amount');
    }

    /**
     * Calculate total paid commissions.
     */
    public function getTotalPaidCommissionsAttribute(): float
    {
        return $this->paidCommissions()->sum('amount');
    }

    /**
     * Scope a query to only include active sellers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

