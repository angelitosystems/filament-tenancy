<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use Illuminate\Support\Facades\Log;

class DebugHelper
{
    /**
     * Check if debug logging is enabled.
     */
    public static function isDebugEnabled(): bool
    {
        return config('app.env') === 'local' && config('app.debug', false);
    }

    /**
     * Log debug message only if debug is enabled.
     */
    public static function debug(string $message, array $context = []): void
    {
        if (static::isDebugEnabled()) {
            Log::debug($message, $context);
        }
    }

    /**
     * Log info message only if debug is enabled.
     */
    public static function info(string $message, array $context = []): void
    {
        if (static::isDebugEnabled()) {
            Log::info($message, $context);
        }
    }

    /**
     * Log warning message only if debug is enabled.
     */
    public static function warning(string $message, array $context = []): void
    {
        if (static::isDebugEnabled()) {
            Log::warning($message, $context);
        }
    }

    /**
     * Log error message (always logs, regardless of debug setting).
     */
    public static function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    /**
     * Log critical message (always logs, regardless of debug setting).
     */
    public static function critical(string $message, array $context = []): void
    {
        Log::critical($message, $context);
    }
}
