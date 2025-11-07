<?php

namespace AngelitoSystems\FilamentTenancy\Traits;

trait HasSimpleTranslations
{
    /**
     * Get translation prefix (can be overridden in child classes)
     * 
     * @return string|null
     */
    protected static function getTranslationPrefix(): ?string
    {
        return null;
    }

    /**
     * Helper method to translate keys with fallback
     * 
     * @param string $key Translation key
     * @param array $replace Replacements
     * @param string|null $locale Locale
     * @return string
     */
    protected static function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        return static::__($key, $replace, $locale);
    }

    /**
     * Short alias for translate method
     * 
     * @param string $key Translation key
     * @param array $replace Replacements
     * @param string|null $locale Locale
     * @return string
     */
    protected static function t(string $key, array $replace = [], ?string $locale = null): string
    {
        return static::translate($key, $replace, $locale);
    }

    /**
     * Check if translations are published in the project
     * 
     * @param string|null $prefix Translation prefix
     * @param string|null $locale Locale
     * @return bool
     */
    protected static function areTranslationsPublished(?string $prefix = null, ?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();
        $prefix = $prefix ?? 'tenancy';

        // Check if published in lang/{locale}/{prefix}.php
        $publishedPath = lang_path("{$locale}/{$prefix}.php");
        if (file_exists($publishedPath)) {
            return true;
        }

        // Check if published in lang/vendor/filament-tenancy/{locale}/{prefix}.php
        $vendorPath = lang_path("vendor/filament-tenancy/{$locale}/{$prefix}.php");
        if (file_exists($vendorPath)) {
            return true;
        }

        return false;
    }

    /**
     * Get translation with automatic path resolution
     * 
     * - If published: Use project translations '{prefix}.{key}' or 'tenancy.{key}'
     * - If NOT published: Use package translations 'filament-tenancy::{prefix}.{key}' or 'filament-tenancy::tenancy.{key}'
     * 
     * @param string $key Translation key
     * @param array $replace Replacements
     * @param string|null $locale Locale
     * @return string
     */
    public static function __(string $key, array $replace = [], ?string $locale = null): string
    {
        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix, $locale);

        if ($isPublished) {
            // Translations published in project - load from lang/ folder
            if ($prefix) {
                $customKey = "{$prefix}.{$key}";
                $translation = __($customKey, $replace, $locale);
                if ($translation !== $customKey) {
                    return $translation;
                }
            }

            // Try default tenancy namespace
            $tenancyKey = "tenancy.{$key}";
            return __($tenancyKey, $replace, $locale);
        } else {
            // Translations NOT published - load from package ./lang/ folder
            if ($prefix) {
                $packageCustomKey = "filament-tenancy::{$prefix}.{$key}";
                $translation = __($packageCustomKey, $replace, $locale);
                if ($translation !== $packageCustomKey) {
                    return $translation;
                }
            }

            // Use package tenancy namespace
            return __("filament-tenancy::tenancy.{$key}", $replace, $locale);
        }
    }

    /**
     * Get navigation label from translation
     * 
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        $key = static::getNavigationKey();
        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix);

        if ($isPublished) {
            // Published: Try project translations
            if ($prefix) {
                $translation = __("{$prefix}.navigation.{$key}");
                if ($translation !== "{$prefix}.navigation.{$key}") {
                    return $translation;
                }
            }

            return __("tenancy.navigation.{$key}");
        } else {
            // Not published: Use package translations
            if ($prefix) {
                $translation = __("filament-tenancy::{$prefix}.navigation.{$key}");
                if ($translation !== "filament-tenancy::{$prefix}.navigation.{$key}") {
                    return $translation;
                }
            }

            return __("filament-tenancy::tenancy.navigation.{$key}");
        }
    }

    /**
     * Get custom navigation group label key (can be overridden)
     * If null, uses getNavigationGroupKey() for translation lookup
     * 
     * @return string|null
     */
    protected static function getNavigationGroupLabel(): ?string
    {
        return null;
    }

    /**
     * Get navigation group from translation
     * 
     * Permite personalizar el título del grupo usando traducciones:
     * - Si getNavigationGroupLabel() retorna una clave, usa esa clave para buscar la traducción
     * - Si retorna null, usa getNavigationGroupKey() como antes
     * 
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        $customLabel = static::getNavigationGroupLabel();
        
        // Si hay un label personalizado, usarlo directamente como traducción
        if ($customLabel !== null) {
            $prefix = static::getTranslationPrefix();
            $isPublished = static::areTranslationsPublished($prefix);

            if ($isPublished) {
                // Published: Try project translations
                if ($prefix) {
                    $translation = __("{$prefix}.navigation_groups.{$customLabel}");
                    if ($translation !== "{$prefix}.navigation_groups.{$customLabel}") {
                        return $translation;
                    }
                }

                return __("tenancy.navigation_groups.{$customLabel}");
            } else {
                // Not published: Use package translations
                if ($prefix) {
                    $translation = __("filament-tenancy::{$prefix}.navigation_groups.{$customLabel}");
                    if ($translation !== "filament-tenancy::{$prefix}.navigation_groups.{$customLabel}") {
                        return $translation;
                    }
                }

                return __("filament-tenancy::tenancy.navigation_groups.{$customLabel}");
            }
        }

        // Comportamiento por defecto: usar getNavigationGroupKey()
        $key = static::getNavigationGroupKey();

        if (!$key) {
            return null;
        }

        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix);

        if ($isPublished) {
            // Published: Try project translations
            if ($prefix) {
                $translation = __("{$prefix}.navigation_groups.{$key}");
                if ($translation !== "{$prefix}.navigation_groups.{$key}") {
                    return $translation;
                }
            }

            return __("tenancy.navigation_groups.{$key}");
        } else {
            // Not published: Use package translations
            if ($prefix) {
                $translation = __("filament-tenancy::{$prefix}.navigation_groups.{$key}");
                if ($translation !== "filament-tenancy::{$prefix}.navigation_groups.{$key}") {
                    return $translation;
                }
            }

            return __("filament-tenancy::tenancy.navigation_groups.{$key}");
        }
    }

    /**
     * Get model label from translation
     * 
     * @return string
     */
    public static function getModelLabel(): string
    {
        $key = static::getModelKey();
        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix);

        if ($isPublished) {
            // Try resources.{key}.singular format
            $translation = __("tenancy.resources.{$key}.singular");
            if ($translation !== "tenancy.resources.{$key}.singular") {
                return $translation;
            }

            return __("tenancy.{$key}");
        } else {
            // Not published: Use package translations
            $translation = __("filament-tenancy::tenancy.resources.{$key}.singular");
            if ($translation !== "filament-tenancy::tenancy.resources.{$key}.singular") {
                return $translation;
            }

            return __("filament-tenancy::tenancy.{$key}");
        }
    }

    /**
     * Get plural model label from translation
     * 
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        $key = static::getModelKey();
        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix);

        if ($isPublished) {
            // Try resources.{key}.plural format
            $translation = __("tenancy.resources.{$key}.plural");
            if ($translation !== "tenancy.resources.{$key}.plural") {
                return $translation;
            }

            return __("tenancy." . static::getPluralModelKey());
        } else {
            // Not published: Use package translations
            $translation = __("filament-tenancy::tenancy.resources.{$key}.plural");
            if ($translation !== "filament-tenancy::tenancy.resources.{$key}.plural") {
                return $translation;
            }

            return __("filament-tenancy::tenancy." . static::getPluralModelKey());
        }
    }

    /**
     * Get breadcrumb from translation
     * 
     * @return string
     */
    public static function getBreadcrumb(): string
    {
        $key = static::getModelKey();
        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix);

        if ($isPublished) {
            // Try resources.{key}.breadcrumb format
            $translation = __("tenancy.resources.{$key}.breadcrumb");
            if ($translation !== "tenancy.resources.{$key}.breadcrumb" && !is_array($translation)) {
                return (string) $translation;
            }

            $breadcrumb = __("tenancy." . static::getBreadcrumbKey());
            return is_array($breadcrumb) ? static::getPluralModelLabel() : (string) $breadcrumb;
        } else {
            // Not published: Use package translations
            $translation = __("filament-tenancy::tenancy.resources.{$key}.breadcrumb");
            if ($translation !== "filament-tenancy::tenancy.resources.{$key}.breadcrumb" && !is_array($translation)) {
                return (string) $translation;
            }

            $breadcrumb = __("filament-tenancy::tenancy." . static::getBreadcrumbKey());
            return is_array($breadcrumb) ? static::getPluralModelLabel() : (string) $breadcrumb;
        }
    }

    /**
     * Override these methods in your resource to specify translation keys
     */

    /**
     * Get navigation key for translations
     * 
     * @return string
     */
    public static function getNavigationKey(): string
    {
        return static::getPluralModelKey();
    }

    /**
     * Get model key for translations
     * 
     * @return string
     */
    public static function getModelKey(): string
    {
        $model = static::getModel();
        $basename = class_basename($model);
        return \Illuminate\Support\Str::lower(\Illuminate\Support\Str::singular($basename));
    }

    /**
     * Get plural model key for translations
     * 
     * @return string
     */
    public static function getPluralModelKey(): string
    {
        return \Illuminate\Support\Str::plural(static::getModelKey());
    }

    /**
     * Get breadcrumb key for translations
     * 
     * @return string
     */
    public static function getBreadcrumbKey(): string
    {
        return static::getPluralModelKey();
    }

    /**
     * Get navigation group key for translations
     * 
     * @return string|null
     */
    public static function getNavigationGroupKey(): ?string
    {
        return null;
    }
}
