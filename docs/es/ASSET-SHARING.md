# Sistema de Compartir Assets

## Resumen

El Sistema de Compartir Assets permite a los tenants compartir assets comunes (como componentes Livewire, assets de Filament, CSS y archivos JavaScript) desde la aplicación central, reduciendo duplicación y mejorando el rendimiento.

## Características

- **Compartir Assets Automático**: Comparte automáticamente directorios especificados desde central a tenants
- **Sistema Fallback**: Los tenants pueden hacer fallback a assets centrales si las copias locales no existen
- **Soporte Livewire**: Manejo especial para componentes y scripts Livewire
- **Directorios Configurables**: Configura qué directorios compartir
- **Helper URL**: Función helper para acceder a assets compartidos

## Configuración

Añade a tu `config/filament-tenancy.php`:

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

## Variables de Entorno

```env
TENANCY_CENTRAL_DISK=public
TENANCY_TENANT_DISK=public
```

## Uso

### Usando el Helper tenant_asset()

La función helper `tenant_asset()` automáticamente verifica assets en el disco del tenant primero, luego hace fallback al disco central:

```blade
<!-- En plantillas Blade -->
<link href="{{ tenant_asset('css/app.css') }}" rel="stylesheet">
<script src="{{ tenant_asset('js/app.js') }}"></script>
<script src="{{ tenant_asset('livewire/livewire.js') }}" data-csrf="{{ csrf_token() }}" data-update-uri="{{ route('livewire.update') }}" defer></script>
```

### Uso Programático

```php
use AngelitoSystems\FilamentTenancy\Support\AssetManager;

// Obtener URL de asset con fallback
$url = AssetManager::assetUrl('livewire/livewire.js');

// Compartir directorios específicos
AssetManager::shareAssets(['livewire', 'filament']);

// Compartir assets de Livewire específicamente
AssetManager::shareLivewireAssets();

// Copiar assets para un tenant específico
AssetManager::copyAssetsForTenant();
```

## Cómo Funciona

### 1. Resolución de Assets

Cuando llamas a `tenant_asset('ruta/al/archivo')`:

1. **Verificar Disco Tenant**: Busca el archivo en el disco de almacenamiento del tenant
2. **Fallback a Central**: Si no lo encuentra, verifica el disco de almacenamiento central
3. **Fallback por Defecto**: Si aún no lo encuentra, usa el helper `asset()` de Laravel

### 2. Compartir Automático

Cuando se crea un nuevo tenant:

1. **Event Listener**: El listener `ShareAssetsOnTenantCreated` se activa
2. **Copiar Assets**: Los assets de directorios configurados se copian al almacenamiento del tenant
3. **Mantener Estructura**: La estructura de directorios se preserva durante la copia

### 3. Configuración de Almacenamiento

El sistema usa el sistema de almacenamiento de Laravel:

- **Disco Central**: Donde se almacenan los assets compartidos centralmente
- **Disco Tenant**: Donde se almacenan los assets específicos del tenant
- **Generación URL**: Usa métodos URL del disco de almacenamiento cuando están disponibles

## Directorios Compartidos por Defecto

### Assets Livewire

- `livewire/livewire.js`
- `livewire/livewire.js.map`
- `livewire/manifest.json`

### Assets Filament

- `filament/` - Todos los assets del panel Filament
- `filament/assets/` - CSS y JS compilados

### Assets Comunes

- `css/` - Hojas de estilo
- `js/` - Archivos JavaScript
- `images/` - Assets de imagen
- `fonts/` - Archivos de fuente

## Uso Avanzado

### Directorios de Assets Personalizados

Añade directorios personalizados para compartir:

```php
// En config/filament-tenancy.php
'shared_directories' => [
    'livewire',
    'filament',
    'css',
    'js',
    'my-custom-assets', // Tu directorio personalizado
],
```

### Compartir Assets Manualmente

```php
// Compartir archivos específicos manualmente
AssetManager::shareAssets([
    'custom-components',
    'shared-styles',
]);

// Crear enlace simbólico (alternativa a copiar)
if (AssetManager::createAssetSymlink()) {
    // Enlace simbólico creado exitosamente
}
```

### Generación de URL de Assets

```php
// Obtener URL específica del tenant
$url = AssetManager::assetUrl('css/app.css');

// Acceso directo al almacenamiento
$tenantUrl = Storage::disk('tenant')->url('css/app.css');
$centralUrl = Storage::disk('central')->url('css/app.css');
```

## Consideraciones de Rendimiento

### 1. Estrategia de Almacenamiento

- **Enlaces Simbólicos**: Acceso más rápido, usa almacenamiento central directamente
- **Copia de Archivos**: Almacenamiento aislado, mejor para personalización
- **Enfoque Híbrido**: Usa enlaces simbólicos para assets de solo lectura, copia para personalizables

### 2. Caché

```php
// Cachear URLs de assets para mejor rendimiento
$assetUrl = Cache::remember('asset-url-' . $path, 3600, function () use ($path) {
    return AssetManager::assetUrl($path);
});
```

### 3. Integración CDN

Configura CDN en tu `.env`:

```env
ASSET_URL=https://cdn.yourdomain.com
TENANCY_CENTRAL_DISK=s3
TENANCY_TENANT_DISK=s3
```

## Solución de Problemas

### Problemas Comunes

1. **Asset No Encontrado**: Verifica que el asset exista en almacenamiento central
2. **Errores de Permiso**: Asegura que los directorios de almacenamiento sean escribibles
3. **Generación URL**: Verifica que la configuración del disco de almacenamiento soporte generación de URL

### Modo Depuración

Habilita depuración de assets:

```php
// En tu AppServiceProvider o similar
if (config('app.debug')) {
    // Registrar intentos de resolución de assets
    AssetManager::assetUrl('test.css');
}
```

### Verificar Estado de Assets

```php
// Verificar si el asset existe en tenant
$existsInTenant = Storage::disk('tenant')->exists('css/app.css');

// Verificar si el asset existe en central
$existsInCentral = Storage::disk('central')->exists('css/app.css');

// Obtener URL real siendo usada
$url = AssetManager::assetUrl('css/app.css');
```

## Mejores Prácticas

1. **Versiona tus Assets**: Usa nombres de archivo versionados para invalidar caché
2. **Optimiza Imágenes**: Comprime imágenes antes de compartirlas
3. **Minifica Assets**: Comparte archivos CSS y JavaScript minificados
4. **Usa CDN**: Configura CDN para mejor rendimiento en producción
5. **Monitorea Almacenamiento**: Verifica regularmente el uso de almacenamiento y limpia assets no usados

## Guía de Migración

Si estás actualizando desde una versión anterior:

1. **Actualiza Configuración**: Añade la configuración de assets a tu archivo config
2. **Ejecuta Installer**: El installer configurará los directorios necesarios
3. **Actualiza Plantillas**: Reemplaza llamadas `asset()` con `tenant_asset()` donde sea apropiado
4. **Prueba Carga de Assets**: Verifica que todos los assets carguen correctamente en contextos de tenant

## Ejemplo de Implementación

### Configuración Completa de Assets

```php
// En un service provider
public function boot()
{
    // Registrar el helper de assets
    AssetManager::registerAssetHelper();
    
    // Compartir assets cuando se crea tenant
    Event::listen(TenantCreated::class, function ($event) {
        AssetManager::copyAssetsForTenant();
    });
}
```

### Ejemplo de Plantilla Blade

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name') }}</title>
    
    <!-- CSS compartido -->
    <link href="{{ tenant_asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ tenant_asset('filament/assets/app.css') }}" rel="stylesheet">
</head>
<body>
    @livewire('navigation')
    
    <!-- JavaScript compartido -->
    <script src="{{ tenant_asset('js/app.js') }}"></script>
    <script src="{{ tenant_asset('livewire/livewire.js') }}" defer></script>
</body>
</html>
```

Este sistema asegura que tus tenants tengan acceso a todos los assets necesarios mientras mantienen los beneficios del gestión centralizada de assets y la capacidad de personalizar cuando sea necesario.
