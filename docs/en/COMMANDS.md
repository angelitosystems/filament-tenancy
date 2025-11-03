# Commands Reference

This document provides comprehensive reference for all available commands in the Filament Tenancy package.

## Available Commands

### Installation Commands

#### `filament-tenancy:install`

Installs and configures the Filament Tenancy package in your Laravel application.

```bash
php artisan filament-tenancy:install
```

**Features:**
- Automatic Filament installation check and setup
- Database compatibility verification (MySQL/PostgreSQL)
- Interactive database configuration wizard
- Automatic configuration file publishing
- ServiceProvider auto-registration (Laravel 10 & 11)
- Smart migration execution with retry logic
- Installation cleanup on critical errors
- Connection testing after database configuration

**Interactive Prompts:**
- Database configuration (host, port, username, password)
- APP_DOMAIN auto-detection from APP_URL
- Plan seeder publishing
- Admin user creation
- Custom 404 page publishing

---

### Tenant Management Commands

#### `tenancy:create`

Creates a new tenant with interactive prompts.

```bash
php artisan tenancy:create
```

**Interactive Prompts:**
- Tenant name
- Tenant slug (auto-generated from name)
- Identification type (Domain/Subdomain)
- Domain or subdomain value
- Database name (auto-generated)
- Plan selection (loaded from database)
- Tenant activation status
- Expiration date

**Features:**
- Beautiful branded interface
- Step-by-step interactive wizard
- Domain or subdomain selection
- Plan selection with real database values
- Database name auto-generation
- Validation and error handling
- Automatic subscription creation

**Example Output:**
```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║           Filament Tenancy - Multi-Tenancy Package        ║
║                  Angelito Systems                      ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

✓ Tenant 'My Company' created successfully!
┌─────────────────────┬──────────────────────────────────────┐
│ Property            │ Value                                │
├─────────────────────┼──────────────────────────────────────┤
│ ID                  │ 1                                    │
│ Name                │ My Company                           │
│ Slug                │ my-company                           │
│ Domain/Subdomain    │ my-company.example.com               │
│ Database            │ tenant_my_company_1                  │
│ Plan                │ Premium (USD 29.99/monthly)         │
│ Status              │ Active                               │
│ Subscription        │ Active (Starts: 2024-01-01)          │
│ URL                 │ https://my-company.example.com      │
└─────────────────────┴──────────────────────────────────────┘
```

---

#### `tenant:user-create`

Creates a user for a specific tenant with roles and permissions.

```bash
php artisan tenant:user-create
```

**Options:**
- `--tenant=` - Tenant ID or slug (interactive if not provided)
- `--name=` - User name
- `--email=` - User email
- `--password=` - User password (auto-generated if not provided)
- `--role=` - Role slug (default: user)
- `--permissions=` - Comma-separated list of permission slugs
- `--list-tenants` - List all available tenants
- `--list-roles` - List all available roles in tenant
- `--list-permissions` - List all available permissions in tenant

**Interactive Mode:**
```bash
php artisan tenant:user-create
```

**Non-Interactive Mode:**
```bash
php artisan tenant:user-create --tenant=my-tenant --name="John Doe" --email="john@example.com" --role=admin
```

**Listing Options:**
```bash
# List all tenants
php artisan tenant:user-create --list-tenants

# List roles in specific tenant
php artisan tenant:user-create --tenant=my-tenant --list-roles

# List permissions in specific tenant
php artisan tenant:user-create --tenant=my-tenant --list-permissions
```

**Features:**
- Interactive tenant selection with numbered options
- Role and permission assignment
- Automatic password generation
- Email validation
- User information display with access URLs
- Support for multiple permission assignment

**Example Output:**
```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║           Filament Tenancy - User Creator        ║
║                  Angelito Systems                      ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

✓ Usuario 'John Doe' creado exitosamente en el tenant 'My Company'!

┌─────────────────────┬──────────────────────────────────────┐
│ Property            │ Value                                │
├─────────────────────┼──────────────────────────────────────┤
│ Tenant              │ My Company (my-company)              │
│ ID                  │ 1                                    │
│ Nombre              │ John Doe                             │
│ Email               │ john@example.com                     │
│ Rol                 │ Admin                                │
│ Permisos adicionales│ 5 permisos                           │
│ Creado              │ 2024-01-01 12:00:00                  │
│ URL del tenant      │ https://my-company.example.com      │
└─────────────────────┴──────────────────────────────────────┘

🔐 Información de acceso:
  • URL del panel: https://my-company.example.com/admin
  • Email: john@example.com
  • Contraseña: La que proporcionaste o la generada automáticamente
```

---

#### `tenancy:list`

Lists all tenants in the system.

```bash
php artisan tenancy:list
```

**Output:**
```
┌────┬─────────────┬─────────────┬─────────────────────┬────────┬─────────────────────────────────┐
│ ID │ Name        │ Slug        │ Domain/Subdomain    │ Active │ URL                             │
├────┼─────────────┼─────────────┼─────────────────────┼────────┼─────────────────────────────────┤
│ 1  │ My Company  │ my-company  │ my-company.example.com │ Yes    │ https://my-company.example.com │
│ 2  │ Test Tenant │ test-tenant │ test.example.com    │ Yes    │ https://test.example.com       │
└────┴─────────────┴─────────────┴─────────────────────┴────────┴─────────────────────────────────┘
```

---

#### `tenancy:delete`

Deletes a tenant and optionally its database.

```bash
php artisan tenancy:delete {tenant}
```

**Arguments:**
- `tenant` - Tenant ID or slug

**Options:**
- `--force` - Skip confirmation prompt
- `--delete-database` - Also delete the tenant database

**Example:**
```bash
php artisan tenancy:delete my-tenant --delete-database
```

---

### Database Management Commands

#### `tenant:migrate`

Run migrations for a specific tenant.

```bash
php artisan tenant:migrate {tenant}
```

**Arguments:**
- `tenant` - ID or slug of the tenant (interactive if not provided)

**Options:**
- `--path=` - Specific migration path
- `--force` - Force migration in production
- `--seed` - Run database seeders after migration
- `--step` - Force migration to run one step at a time

**Examples:**
```bash
# Interactive mode
php artisan tenant:migrate

# Specific tenant
php artisan tenant:migrate my-tenant

# With seeders
php artisan tenant:migrate my-tenant --seed

# Force in production
php artisan tenant:migrate my-tenant --force
```

**Features:**
- Interactive tenant selection with numbered options
- Automatic database creation if missing
- Tenant-specific migration execution
- Seeder support
- Comprehensive error handling

---

#### `tenant:rollback`

Rollback migrations for a specific tenant.

```bash
php artisan tenant:rollback {tenant}
```

**Arguments:**
- `tenant` - ID or slug of the tenant (interactive if not provided)

**Options:**
- `--step=1` - Number of migrations to rollback
- `--batch` - Rollback to specific batch
- `--force` - Force rollback in production

**Examples:**
```bash
# Interactive mode
php artisan tenant:rollback

# Rollback last migration
php artisan tenant:rollback my-tenant

# Rollback last 3 migrations
php artisan tenant:rollback my-tenant --step=3

# Rollback to specific batch
php artisan tenant:rollback my-tenant --batch=5
```

**Features:**
- Safe rollback with confirmation
- Batch-based rollback support
- Step-by-step rollback control
- Migration file validation

---

#### `tenant:fresh`

Drop all tables and re-run migrations for a specific tenant.

```bash
php artisan tenant:fresh {tenant}
```

**Arguments:**
- `tenant` - ID or slug of the tenant (interactive if not provided)

**Options:**
- `--seed` - Run database seeders after migration
- `--force` - Force operation in production
- `--drop-views` - Drop all views
- `--drop-types` - Drop all custom types (PostgreSQL)

**Examples:**
```bash
# Interactive mode
php artisan tenant:fresh

# Fresh start with confirmation
php artisan tenant:fresh my-tenant

# Fresh start with seeders
php artisan tenant:fresh my-tenant --seed

# Force fresh in production
php artisan tenant:fresh my-tenant --force

# Drop views and types (PostgreSQL)
php artisan tenant:fresh my-tenant --drop-views --drop-types
```

**Features:**
- Complete database reset
- Safety warnings and confirmations
- View and type dropping support
- Automatic database recreation
- Seeder integration

---

#### `tenancy:migrate`

Runs migrations for a specific tenant.

```bash
php artisan tenancy:migrate {tenant}
```

**Arguments:**
- `tenant` - Tenant ID or slug

**Options:**
- `--force` - Force migration in production
- `--path=` - Custom migration path
- `--seed` - Run database seeders after migration

**Example:**
```bash
php artisan tenancy:migrate my-tenant --seed
```

---

### Monitoring Commands

#### `tenancy:monitor-connections`

Monitors active tenant connections and performance metrics.

```bash
php artisan tenancy:monitor-connections
```

**Options:**
- `--format=` - Output format (table, json)
- `--interval=` - Monitoring interval in seconds
- `--continuous` - Continuous monitoring mode

**Example:**
```bash
php artisan tenancy:monitor-connections --format=json --interval=30
```

---

## Command Examples

### Complete Workflow Example

```bash
# 1. Install the package
php artisan filament-tenancy:install

# 2. Create a tenant
php artisan tenancy:create

# 3. Create a user for the tenant
php artisan tenant:user-create --tenant=my-tenant --name="Admin User" --email="admin@my-tenant.com" --role=super-admin

# 4. List all tenants
php artisan tenancy:list

# 5. Run migrations for a tenant
php artisan tenancy:migrate my-tenant

# 6. Monitor connections
php artisan tenancy:monitor-connections
```

### Batch Operations

```bash
# Create multiple users for different tenants
php artisan tenant:user-create --tenant=tenant-1 --name="User 1" --email="user1@tenant1.com" --role=user
php artisan tenant:user-create --tenant=tenant-2 --name="User 2" --email="user2@tenant2.com" --role=admin

# List available options before creating
php artisan tenant:user-create --list-tenants
php artisan tenant:user-create --tenant=tenant-1 --list-roles
php artisan tenant:user-create --tenant=tenant-1 --list-permissions
```

---

## Error Handling

All commands include comprehensive error handling:

- **Validation errors** with helpful messages
- **Connection errors** with retry suggestions
- **Permission errors** with configuration guidance
- **Database errors** with troubleshooting tips

### Common Error Messages

```
❌ No hay tenants disponibles. Crea un tenant primero con:
  php artisan tenancy:create

⚠️ SQLite no soporta multi-database tenancy. Usa MySQL o PostgreSQL.

✗ Tenant 'nonexistent' no encontrado.
  Usa --list-tenants para ver los tenants disponibles.
```

---

## Configuration

Commands can be configured through:

1. **Configuration file** (`config/filament-tenancy.php`)
2. **Environment variables** (`.env`)
3. **Command-line options**

### Relevant Configuration Options

```php
// config/filament-tenancy.php
return [
    'database' => [
        'auto_create' => env('TENANCY_AUTO_CREATE_DB', true),
        'auto_delete' => env('TENANCY_AUTO_DELETE_DB', false),
    ],
    'migrations' => [
        'auto_run' => env('TENANCY_AUTO_MIGRATE', true),
    ],
    'monitoring' => [
        'enabled' => env('TENANCY_MONITORING_ENABLED', true),
    ],
];
```

---

## Troubleshooting

### Common Issues

1. **"Table 'permissions' doesn't exist"**
   - This is now fixed - roles and permissions are created when tenants are created
   - No manual intervention required

2. **"Connection not configured" errors**
   - Ensure database configuration is correct in `.env`
   - Check that MySQL/PostgreSQL is being used (not SQLite)

3. **"Tenant not found" errors**
   - Use `--list-tenants` to see available tenants
   - Check tenant spelling and ID

4. **Permission denied errors**
   - Ensure database user has CREATE DATABASE permissions
   - Check file permissions for Laravel storage

### Debug Mode

Enable debug mode for detailed error information:

```env
APP_ENV=local
APP_DEBUG=true
```

This will enable comprehensive logging through the `DebugHelper` class.

---

## Integration with Other Tools

### Laravel Scheduler

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('tenancy:monitor-connections')->everyFiveMinutes();
    $schedule->command('tenancy:migrate --all')->daily();
}
```

### CI/CD Pipeline

```bash
# In deployment scripts
php artisan tenancy:migrate --all --force
php artisan tenant:user-create --tenant=production --name="Admin" --email="admin@production.com" --role=super-admin
```

---

## Best Practices

1. **Always test in development** before production deployment
2. **Use non-interactive mode** for automated scripts
3. **Monitor connections** regularly for performance issues
4. **Keep roles and permissions** consistent across tenants
5. **Use appropriate database permissions** for security
6. **Regular backups** of tenant databases
7. **Monitor disk space** for multi-database setups

---

## Support

For command-specific issues:

1. Check the error messages carefully
2. Enable debug mode for detailed logs
3. Verify configuration files
4. Test with a fresh tenant
5. Check database permissions

For additional help:
- [GitHub Issues](https://github.com/angelitosystems/filament-tenancy/issues)
- [Documentation](README.md)
- [Technical Documentation](TECHNICAL.md)
