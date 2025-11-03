# Asset Sharing System

## Overview

The Asset Sharing System allows tenants to share common assets (like Livewire components, Filament assets, CSS, and JavaScript files) from the central application, reducing duplication and improving performance.

## Features

- **Automatic Asset Sharing**: Automatically shares specified directories from central to tenants
- **Fallback System**: Tenants can fallback to central assets if local copies don't exist
- **Livewire Support**: Special handling for Livewire components and scripts
- **Configurable Directories**: Configure which directories to share
- **URL Helper**: Helper function for accessing shared assets

## Configuration

Add to your `config/filament-tenancy.php`:

```php
'assets' => [
    'central_disk' => env('TENANCY_CENTRAL_DISK', 'public'),
    'tenant_disk' => env('TENANCY_TENANT_DISK', 'public'),
    'shared_directories' => [
        'livewire',
        'filament',
        'css',
        'js',
    ],
],
```

## Environment Variables

```env
TENANCY_CENTRAL_DISK=public
TENANCY_TENANT_DISK=public
```

## Usage

### Using the tenant_asset() Helper

The `tenant_asset()` helper function automatically checks for assets in the tenant disk first, then falls back to the central disk:

```blade
<!-- In Blade templates -->
<link href="{{ tenant_asset('css/app.css') }}" rel="stylesheet">
<script src="{{ tenant_asset('js/app.js') }}"></script>
<script src="{{ tenant_asset('livewire/livewire.js') }}" data-csrf="{{ csrf_token() }}" data-update-uri="{{ route('livewire.update') }}" defer></script>
```

### Programmatic Usage

```php
use AngelitoSystems\FilamentTenancy\Support\AssetManager;

// Get asset URL with fallback
$url = AssetManager::assetUrl('livewire/livewire.js');

// Share specific directories
AssetManager::shareAssets(['livewire', 'filament']);

// Share Livewire assets specifically
AssetManager::shareLivewireAssets();

// Copy assets for a specific tenant
AssetManager::copyAssetsForTenant();
```

## How It Works

### 1. Asset Resolution

When you call `tenant_asset('path/to/file')`:

1. **Check Tenant Disk**: Looks for the file in the tenant's storage disk
2. **Fallback to Central**: If not found, checks the central storage disk
3. **Default Fallback**: If still not found, uses Laravel's `asset()` helper

### 2. Automatic Sharing

When a new tenant is created:

1. **Event Listener**: The `ShareAssetsOnTenantCreated` listener is triggered
2. **Copy Assets**: Assets from configured directories are copied to tenant storage
3. **Maintain Structure**: Directory structure is preserved during copying

### 3. Storage Configuration

The system uses Laravel's storage system:

- **Central Disk**: Where shared assets are stored centrally
- **Tenant Disk**: Where tenant-specific assets are stored
- **URL Generation**: Uses storage disk URL methods when available

## Default Shared Directories

### Livewire Assets

- `livewire/livewire.js`
- `livewire/livewire.js.map`
- `livewire/manifest.json`

### Filament Assets

- `filament/` - All Filament panel assets
- `filament/assets/` - Compiled CSS and JS

### Common Assets

- `css/` - Stylesheets
- `js/` - JavaScript files
- `images/` - Image assets
- `fonts/` - Font files

## Advanced Usage

### Custom Asset Directories

Add custom directories to share:

```php
// In config/filament-tenancy.php
'shared_directories' => [
    'livewire',
    'filament',
    'css',
    'js',
    'my-custom-assets', // Your custom directory
],
```

### Manual Asset Sharing

```php
// Share specific files manually
AssetManager::shareAssets([
    'custom-components',
    'shared-styles',
]);

// Create symbolic link (alternative to copying)
if (AssetManager::createAssetSymlink()) {
    // Symlink created successfully
}
```

### Asset URL Generation

```php
// Get tenant-specific URL
$url = AssetManager::assetUrl('css/app.css');

// Direct storage access
$tenantUrl = Storage::disk('tenant')->url('css/app.css');
$centralUrl = Storage::disk('central')->url('css/app.css');
```

## Performance Considerations

### 1. Storage Strategy

- **Symbolic Links**: Faster access, uses central storage directly
- **File Copying**: Isolated storage, better for customization
- **Hybrid Approach**: Use symlinks for read-only assets, copy for customizable ones

### 2. Caching

```php
// Cache asset URLs for better performance
$assetUrl = Cache::remember('asset-url-' . $path, 3600, function () use ($path) {
    return AssetManager::assetUrl($path);
});
```

### 3. CDN Integration

Configure CDN in your `.env`:

```env
ASSET_URL=https://cdn.yourdomain.com
TENANCY_CENTRAL_DISK=s3
TENANCY_TENANT_DISK=s3
```

## Troubleshooting

### Common Issues

1. **Asset Not Found**: Check that the asset exists in central storage
2. **Permission Errors**: Ensure storage directories are writable
3. **URL Generation**: Verify storage disk configuration supports URL generation

### Debug Mode

Enable asset debugging:

```php
// In your AppServiceProvider or similar
if (config('app.debug')) {
    // Log asset resolution attempts
    AssetManager::assetUrl('test.css');
}
```

### Checking Asset Status

```php
// Check if asset exists in tenant
$existsInTenant = Storage::disk('tenant')->exists('css/app.css');

// Check if asset exists in central
$existsInCentral = Storage::disk('central')->exists('css/app.css');

// Get actual URL being used
$url = AssetManager::assetUrl('css/app.css');
```

## Best Practices

1. **Version Your Assets**: Use versioned filenames for cache busting
2. **Optimize Images**: Compress images before sharing
3. **Minify Assets**: Share minified CSS and JavaScript files
4. **Use CDN**: Configure CDN for better performance in production
5. **Monitor Storage**: Regularly check storage usage and clean up unused assets

## Migration Guide

If you're upgrading from an older version:

1. **Update Configuration**: Add the assets configuration to your config file
2. **Run Installer**: The installer will set up the necessary directories
3. **Update Templates**: Replace `asset()` calls with `tenant_asset()` where appropriate
4. **Test Asset Loading**: Verify that all assets load correctly in tenant contexts

## Example Implementation

### Complete Asset Setup

```php
// In a service provider
public function boot()
{
    // Register the asset helper
    AssetManager::registerAssetHelper();
    
    // Share assets when tenant is created
    Event::listen(TenantCreated::class, function ($event) {
        AssetManager::copyAssetsForTenant();
    });
}
```

### Blade Template Example

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name') }}</title>
    
    <!-- Shared CSS -->
    <link href="{{ tenant_asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ tenant_asset('filament/assets/app.css') }}" rel="stylesheet">
</head>
<body>
    @livewire('navigation')
    
    <!-- Shared JavaScript -->
    <script src="{{ tenant_asset('js/app.js') }}"></script>
    <script src="{{ tenant_asset('livewire/livewire.js') }}" defer></script>
</body>
</html>
```

This system ensures that your tenants have access to all necessary assets while maintaining the benefits of centralized asset management and the ability to customize when needed.
