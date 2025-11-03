<?php

namespace AngelitoSystems\FilamentTenancy\Traits;

trait HasSimpleTranslations
{
    /**
     * Get a simple translation key.
     */
    protected static function trans(string $key): string
    {
        return "tenancy.{$key}";
    }

    /**
     * Get a simple translation with the __ helper.
     */
    protected static function __(string $key): string
    {
        return __("tenancy.{$key}");
    }

    /**
     * Get a navigation label translation.
     */
    public static function getNavigationLabel(): string
    {
        return static::__(static::getNavigationKey());
    }

    /**
     * Get a navigation group translation.
     */
    public static function getNavigationGroup(): ?string
    {
        $group = static::getNavigationGroupKey();
        return $group ? static::__(static::getNavigationGroupKey()) : null;
    }

    /**
     * Get a model label translation.
     */
    public static function getModelLabel(): string
    {
        return static::__(static::getModelKey());
    }

    /**
     * Get a plural model label translation.
     */
    public static function getPluralModelLabel(): string
    {
        return static::__(static::getPluralModelKey());
    }

    /**
     * Get a breadcrumb translation.
     */
    public static function getBreadcrumb(): string
    {
        return static::__(static::getBreadcrumbKey());
    }

    /**
     * Override these methods in your resource classes
     */
    public static function getNavigationKey(): string
    {
        return 'plans'; // Default, override in resource
    }

    public static function getModelKey(): string
    {
        return 'plan'; // Default, override in resource
    }

    public static function getPluralModelKey(): string
    {
        return 'plans'; // Default, override in resource
    }

    public static function getBreadcrumbKey(): string
    {
        return 'plans'; // Default, override in resource
    }

    public static function getNavigationGroupKey(): ?string
    {
        return 'billing_management'; // Default, override in resource
    }
}
