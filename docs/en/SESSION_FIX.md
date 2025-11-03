# Fixing 419 Page Expiration Errors in Tenants

This document explains how to fix 419 page expiration errors that occur when working with tenants in Filament Tenancy.

## Problem

When switching to tenant contexts, users may encounter 419 "Page Expired" errors due to CSRF token validation failures. This happens because:

1. Session configuration is not properly isolated between tenants
2. The sessions table doesn't exist in tenant databases
3. Cookie domains are not correctly configured for tenant subdomains

## Solution

The package now includes several fixes to prevent these issues:

### 1. Tenant Session Middleware

A new `TenantSessionMiddleware` automatically configures session settings for tenant contexts:

- Isolates sessions between tenants
- Configures proper cookie domains for subdomains
- Ensures session table exists in tenant databases
- Handles different session drivers (database, file, redis)

### 2. Session Configuration

New configuration options have been added to `config/filament-tenancy.php`:

```php
'session' => [
    'isolation' => env('TENANCY_SESSION_ISOLATION', true),
    'cross_subdomain' => env('TENANCY_CROSS_SUBDOMAIN_SESSIONS', false),
    'prefix' => env('TENANCY_SESSION_PREFIX', 'tenant'),
    'auto_create_session_table' => env('TENANCY_AUTO_CREATE_SESSION_TABLE', true),
    'cookie' => [
        'domain' => env('TENANCY_SESSION_COOKIE_DOMAIN', null),
        'secure' => env('TENANCY_SESSION_COOKIE_SECURE', null),
        'same_site' => env('TENANCY_SESSION_COOKIE_SAME_SITE', 'lax'),
    ],
],
```

### 3. Automatic Session Table Creation

The package now automatically creates the `sessions` table in tenant databases when:

- Using database session driver
- `auto_create_session_table` is enabled (default: true)
- Running tenant migrations

## Environment Variables

Add these to your `.env` file to configure session behavior:

```env
# Enable session isolation between tenants
TENANCY_SESSION_ISOLATION=true

# Allow sessions to work across subdomains
TENANCY_CROSS_SUBDOMAIN_SESSIONS=false

# Auto-create session table in tenant databases
TENANCY_AUTO_CREATE_SESSION_TABLE=true

# Session cookie configuration
TENANCY_SESSION_COOKIE_DOMAIN=null
TENANCY_SESSION_COOKIE_SECURE=null
TENANCY_SESSION_COOKIE_SAME_SITE=lax
```

## Migration Path

The sessions table migration is included in the package at:
`database/migrations/tenant/2024_01_01_000004_create_sessions_table.php`

This will be automatically run when creating new tenants or running tenant migrations.

## Manual Session Table Creation

If you need to manually create the sessions table in existing tenant databases:

```bash
php artisan tenant:migrate --tenant=1
```

Or use the migrate command for all tenants:

```bash
php artisan tenant:migrate --all
```

## Troubleshooting

### Still Getting 419 Errors?

1. **Clear caches**: `php artisan cache:clear` and `php artisan config:clear`
2. **Check session driver**: Ensure your session driver is properly configured
3. **Verify database connection**: Make sure tenant databases are accessible
4. **Check cookie domain**: Verify cookie domain matches your tenant domains

### Session Table Not Created?

1. Check that `TENANCY_AUTO_CREATE_SESSION_TABLE=true` in your `.env`
2. Ensure the session driver is set to `database`
3. Run tenant migrations manually: `php artisan tenant:migrate --all`

### Cross-Subdomain Issues?

1. Set `TENANCY_CROSS_SUBDOMAIN_SESSIONS=true` in your `.env`
2. Configure appropriate cookie domain: `TENANCY_SESSION_COOKIE_DOMAIN=.example.com`

## Best Practices

1. Always use database session driver for multi-tenancy
2. Enable session isolation to prevent conflicts
3. Configure proper cookie domains for your setup
4. Test session behavior across different tenants
5. Monitor logs for session-related errors

## Security Considerations

- Session isolation prevents session hijacking between tenants
- Proper cookie domain configuration prevents CSRF token leakage
- SameSite cookie settings provide additional CSRF protection
- Consider using HTTPS for production environments
