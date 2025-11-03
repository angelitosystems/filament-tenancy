<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Asset Manager for sharing central assets with tenants
 */
class AssetManager
{
    /**
     * Share Livewire assets from central to tenant
     */
    public static function shareLivewireAssets(): void
    {
        $centralDisk = config('filament-tenancy.assets.central_disk', 'public');
        $tenantDisk = config('filament-tenancy.assets.tenant_disk', 'public');
        
        $livewirePath = 'livewire';
        
        if (Storage::disk($centralDisk)->exists($livewirePath)) {
            $files = Storage::disk($centralDisk)->allFiles($livewirePath);
            
            foreach ($files as $file) {
                $content = Storage::disk($centralDisk)->get($file);
                Storage::disk($tenantDisk)->put($file, $content);
            }
        }
    }

    /**
     * Share specific asset directories from central to tenant
     */
    public static function shareAssets(array $directories = []): void
    {
        $defaultDirectories = [
            'livewire',
            'filament',
            'css',
            'js',
        ];

        $directories = array_merge($defaultDirectories, $directories);
        
        $centralDisk = config('filament-tenancy.assets.central_disk', 'public');
        $tenantDisk = config('filament-tenancy.assets.tenant_disk', 'public');

        foreach ($directories as $directory) {
            if (Storage::disk($centralDisk)->exists($directory)) {
                $files = Storage::disk($centralDisk)->allFiles($directory);
                
                foreach ($files as $file) {
                    $content = Storage::disk($centralDisk)->get($file);
                    Storage::disk($tenantDisk)->put($file, $content);
                }
            }
        }
    }

    /**
     * Get asset URL with fallback to central
     */
    public static function assetUrl(string $path): string
    {
        $tenantDisk = config('filament-tenancy.assets.tenant_disk', 'public');
        $centralDisk = config('filament-tenancy.assets.central_disk', 'public');
        
        // Try tenant disk first
        if (Storage::disk($tenantDisk)->exists($path)) {
            return self::getStorageUrl($tenantDisk, $path);
        }
        
        // Fallback to central disk
        if (Storage::disk($centralDisk)->exists($path)) {
            return self::getStorageUrl($centralDisk, $path);
        }
        
        // Return default asset URL
        return asset($path);
    }

    /**
     * Get storage URL safely
     */
    private static function getStorageUrl(string $disk, string $path): string
    {
        try {
            $storage = Storage::disk($disk);
            return call_user_func(function($s, $p) {
                if (method_exists($s, 'url')) {
                    return $s->url($p);
                }
                return asset($p);
            }, $storage, $path);
        } catch (\Exception $e) {
            return asset($path);
        }
    }

    /**
     * Configure asset URL helper
     */
    public static function registerAssetHelper(): void
    {
        if (!function_exists('tenant_asset')) {
            function tenant_asset(string $path): string
            {
                return AssetManager::assetUrl($path);
            }
        }
    }

    /**
     * Create symbolic link for assets (alternative approach)
     */
    public static function createAssetSymlink(): bool
    {
        try {
            $centralPath = storage_path('app/public');
            $tenantPath = storage_path('app/tenant_assets');
            
            if (!is_link($tenantPath) && !file_exists($tenantPath)) {
                symlink($centralPath, $tenantPath);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Copy assets on tenant creation
     */
    public static function copyAssetsForTenant(): void
    {
        $directories = config('filament-tenancy.assets.shared_directories', [
            'livewire',
            'filament',
        ]);

        self::shareAssets($directories);
    }
}
