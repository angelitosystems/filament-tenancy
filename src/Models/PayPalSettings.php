<?php

namespace AngelitoSystems\FilamentTenancy\Models;

use AngelitoSystems\FilamentTenancy\Concerns\UsesLandlordConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class PayPalSettings extends Model
{
    use UsesLandlordConnection;

    protected $table = 'paypal_settings';

    protected $fillable = [
        'mode',
        'client_id',
        'client_secret',
        'currency',
        'webhook_secret',
        'return_url',
        'cancel_url',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Encrypt client_id when setting it.
     */
    public function setClientIdAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['client_id'] = null;
            return;
        }

        // Only encrypt if not already encrypted
        if ($this->isEncrypted($value)) {
            $this->attributes['client_id'] = $value;
        } else {
            $this->attributes['client_id'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt client_id when getting it.
     */
    public function getClientIdAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $this->decryptValue($value);
    }

    /**
     * Encrypt client_secret when setting it.
     */
    public function setClientSecretAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['client_secret'] = null;
            return;
        }

        // Only encrypt if not already encrypted
        if ($this->isEncrypted($value)) {
            $this->attributes['client_secret'] = $value;
        } else {
            $this->attributes['client_secret'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt client_secret when getting it.
     */
    public function getClientSecretAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $this->decryptValue($value);
    }

    /**
     * Encrypt webhook_secret when setting it.
     */
    public function setWebhookSecretAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['webhook_secret'] = null;
            return;
        }

        // Only encrypt if not already encrypted
        if ($this->isEncrypted($value)) {
            $this->attributes['webhook_secret'] = $value;
        } else {
            $this->attributes['webhook_secret'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt webhook_secret when getting it.
     */
    public function getWebhookSecretAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $this->decryptValue($value);
    }

    /**
     * Check if a value is already encrypted.
     * Laravel encrypted strings are base64 encoded JSON that start with "eyJ"
     */
    protected function isEncrypted($value): bool
    {
        if (empty($value) || !is_string($value)) {
            return false;
        }

        // Laravel encrypted strings are base64 encoded JSON that start with "eyJ"
        // They are typically longer than 100 characters
        if (strlen($value) < 50 || !str_starts_with($value, 'eyJ')) {
            return false;
        }

        // Try to decrypt, if it succeeds, it's encrypted
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException $e) {
            return false;
        }
    }

    /**
     * Decrypt a value, handling both encrypted and plain text values (backward compatibility).
     */
    protected function decryptValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // If decryption fails, assume it's plain text (backward compatibility)
            // This handles existing records that weren't encrypted
            return $value;
        }
    }

    /**
     * Get the current PayPal settings instance (singleton pattern).
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'mode' => 'sandbox',
                'currency' => 'USD',
                'return_url' => '/paypal/success',
                'cancel_url' => '/paypal/cancel',
                'is_enabled' => false,
            ]
        );
    }

    /**
     * Get settings as array for PayPalService.
     */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode ?? 'sandbox',
            'client_id' => $this->client_id ?? '',
            'client_secret' => $this->client_secret ?? '',
            'currency' => $this->currency ?? 'USD',
            'webhook_secret' => $this->webhook_secret ?? '',
            'return_url' => $this->return_url ?? '/paypal/success',
            'cancel_url' => $this->cancel_url ?? '/paypal/cancel',
            'api_urls' => [
                'sandbox' => 'https://api-m.sandbox.paypal.com',
                'live' => 'https://api-m.paypal.com',
            ],
        ];
    }
}




