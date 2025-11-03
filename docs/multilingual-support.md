# Multilingual Support

The Filament Tenancy package now supports multilingual functionality for Spanish and English languages, following Laravel 12 localization best practices.

## Overview

This implementation provides:
- Complete translation of all UI elements
- Language switching functionality
- Automatic locale detection from browser preferences
- Session-based language persistence
- User preference storage (optional)
- Configurable language switcher placement

## Supported Languages

- **English (en)** - Default language
- **Spanish (es)** - Español

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Enable multilingual support
TENANCY_LOCALIZATION_ENABLED=true

# Default locale
TENANCY_DEFAULT_LOCALE=en

# Auto-detect locale from browser
TENANCY_AUTO_DETECT_LOCALE=true

# Show language switcher in Filament panels
TENANCY_SHOW_LANGUAGE_SWITCHER=true

# Language switcher position: header, sidebar, user_menu
TENANCY_LANGUAGE_SWITCHER_POSITION=user_menu

# Store user locale preference in database
TENANCY_STORE_USER_LOCALE=true
```

### Configuration File

The multilingual configuration is located in `config/filament-tenancy.php` under the `localization` section:

```php
'localization' => [
    'enabled' => env('TENANCY_LOCALIZATION_ENABLED', true),
    'supported_locales' => [
        'en' => 'English',
        'es' => 'Español',
    ],
    'default_locale' => env('TENANCY_DEFAULT_LOCALE', 'en'),
    'auto_detect' => env('TENANCY_AUTO_DETECT_LOCALE', true),
    'store_user_preference' => env('TENANCY_STORE_USER_LOCALE', true),
    'show_language_switcher' => env('TENANCY_SHOW_LANGUAGE_SWITCHER', true),
    'language_switcher_position' => env('TENANCY_LANGUAGE_SWITCHER_POSITION', 'user_menu'),
],
```

## Language Files

### Structure

Language files are located in the `lang/` directory:

```
lang/
├── en/
│   └── tenancy.php
└── es/
    └── tenancy.php
```

### Translation Keys

The translation files are organized into logical sections:

- **navigation** - Navigation menu items
- **navigation_groups** - Navigation group labels
- **resources** - Resource labels (singular, plural, breadcrumb)
- **sections** - Form section titles
- **fields** - Form field labels
- **billing_cycles** - Billing cycle options
- **plans** - Plan types
- **table** - Table column headers
- **filters** - Filter labels
- **actions** - Action buttons
- **placeholders** - Input placeholders
- **helpers** - Helper text
- **messages** - Success/error messages
- **validation** - Validation messages

### Adding New Languages

To add a new language:

1. Create a new directory in `lang/` with the locale code (e.g., `fr/`)
2. Copy `lang/en/tenancy.php` to the new directory
3. Translate all strings in the new file
4. Add the locale to the configuration:

```php
'supported_locales' => [
    'en' => 'English',
    'es' => 'Español',
    'fr' => 'Français',
],
```

5. Run the installer to ensure routes are published:
```bash
php artisan filament-tenancy:install
```

### Publishing Routes

The installer automatically publishes the language switching routes:

```bash
# Manual route publishing (if needed)
php artisan vendor:publish --provider="AngelitoSystems\FilamentTenancy\TenancyServiceProvider" --tag="filament-tenancy-routes"
```

This publishes the route file to `routes/tenant.php` which includes:
- `/language/{locale}` - Switch language route

## Usage

### Using Translations in Code

Use the `__()` helper function with the `filament-tenancy::` namespace:

```php
// In Blade templates
{{ __('filament-tenancy::tenancy.navigation.tenants') }}

// In PHP classes
__('filament-tenancy::tenancy.actions.create')

// With parameters
__('filament-tenancy::tenancy.messages.cannot_delete_role_with_users_specific', [
    'role' => $role->name
])
```

### Language Switcher Component

The package includes a `LanguageSwitcher` component that provides:

- **Action**: Modal-based language switcher for Filament panels
- **Select**: Dropdown select component for forms
- **Locale detection**: Automatic browser language detection
- **Session management**: Persistent language selection

#### Using the Language Switcher

```php
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

// Create an action for Filament panels
$action = LanguageSwitcher::makeAction();

// Create a select component for forms
$select = LanguageSwitcher::makeSelect();

// Get available locales
$locales = LanguageSwitcher::getAvailableLocales();

// Get current locale
$current = LanguageSwitcher::getCurrentLocale();

// Set locale programmatically
LanguageSwitcher::setLocale('es');
```

### Middleware

The `SetLocale` middleware handles:

1. **Session locale** - Uses stored session preference
2. **User preference** - Uses authenticated user's saved locale
3. **Browser detection** - Detects from Accept-Language header
4. **Fallback** - Uses application default locale

Register the middleware in your routes:

```php
// Global middleware (already registered by the package)
Route::middleware('locale')->group(function () {
    // Your routes here
});
```

## Customization

### Adding Custom Translations

You can override package translations by publishing the language files:

```bash
php artisan vendor:publish --tag=filament-tenancy-lang --force
```

This will copy the language files to `resources/lang/vendor/filament-tenancy/`.

### Custom Language Switcher

Create your own language switcher by extending the component:

```php
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

class CustomLanguageSwitcher extends LanguageSwitcher
{
    public static function makeAction(): Action
    {
        return parent::makeAction()
            ->label('Custom Language')
            ->icon('heroicon-o-globe-alt');
    }
}
```

### Middleware Customization

Create custom locale detection logic:

```php
use AngelitoSystems\FilamentTenancy\Middleware\SetLocale;

class CustomSetLocale extends SetLocale
{
    protected function detectBrowserLocale(Request $request): ?string
    {
        // Custom detection logic
        return parent::detectBrowserLocale($request);
    }
}
```

## Best Practices

1. **Always use translation keys** - Avoid hardcoded strings in your code
2. **Follow naming conventions** - Use dot notation for nested keys
3. **Provide context** - Use descriptive key names that indicate usage
4. **Test all languages** - Ensure UI works properly in all supported languages
5. **Handle pluralization** - Use Laravel's pluralization features when needed
6. **Consider text expansion** - Design layouts that accommodate longer text in other languages

## Troubleshooting

### Common Issues

1. **Translations not loading**
   - Ensure language files are published correctly
   - Check that the locale is supported in configuration
   - Verify the `SetLocale` middleware is registered

2. **Language switcher not appearing**
   - Check `TENANCY_SHOW_LANGUAGE_SWITCHER` is set to `true`
   - Verify the position configuration is valid
   - Ensure the panel is using the TenancyLandlordPlugin

3. **Locale not persisting**
   - Ensure sessions are configured correctly
   - Check that the `SetLocale` middleware is running
   - Verify browser detection is enabled if needed

### Debug Mode

Enable debug mode to see locale detection:

```php
// In config/filament-tenancy.php
'localization' => [
    'debug' => env('TENANCY_LOCALIZATION_DEBUG', false),
],
```

## Contributing

When contributing translations:

1. Maintain consistency with existing translations
2. Follow the same file structure and naming
3. Test translations in context
4. Consider cultural nuances and localization best practices
5. Update documentation for new languages

## Additional Resources

- [Laravel 12 Localization Documentation](https://laravel.com/docs/12.x/localization)
- [Filament Translations Guide](https://filamentphp.com/docs/3.x/panels/localization)
- [Laravel Language Packages](https://laravel.com/docs/12.x/packages#localization)
