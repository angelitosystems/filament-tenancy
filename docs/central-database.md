# Central Database Setup

This guide covers the setup and management of the central database for Filament Tenancy, which handles landlord/central administration functionality.

## Overview

The central database provides:
- **Separate Permission System**: Independent roles and permissions for central administration
- **Landlord Administration**: Management interface for all tenants
- **User Management**: Central admin user creation and management
- **System Configuration**: Centralized settings and monitoring

## Quick Setup

### Automated Setup (Recommended)

```bash
# Complete setup with admin creation
php artisan filament-tenancy:setup-central --create-admin
```

This command will:
1. Run central database migrations
2. Seed central roles and permissions
3. Create a Super Admin user (interactive or via parameters)

### Step-by-Step Setup

```bash
# 1. Run central migrations
php artisan migrate --path="packages/filament-tenancy/database/migrations"

# 2. Seed central database
php artisan filament-tenancy:seed-central

# 3. Create central admin
php artisan filament-tenancy:create-central-admin
```

**Note**: The installer now automatically publishes seeders during installation. You can also run:

```bash
# Publish all seeders manually
php artisan vendor:publish --provider="AngelitoSystems\FilamentTenancy\TenancyServiceProvider" --tag="filament-tenancy-seeders"

# Publish tenant seeders to database/seeders/tenant/
php artisan vendor:publish --provider="AngelitoSystems\FilamentTenancy\TenancyServiceProvider" --tag="filament-tenancy-tenant-seeders"
```

## Central Roles & Permissions

### Available Roles

#### Super Admin
- **Description**: Complete access to all central features
- **Permissions**: All central permissions
- **Use Case**: System administrators and owners

#### Landlord Admin
- **Description**: Manage tenants, plans, and subscriptions
- **Permissions**: Tenant management, plan management, dashboard access
- **Use Case**: Property managers and business administrators

#### Support
- **Description**: Read-only access for support staff
- **Permissions**: Dashboard access, landlord panel access
- **Use Case**: Customer support and help desk staff

### Central Permissions

| Permission | Description | Role |
|------------|-------------|------|
| `manage tenants` | Create, edit, delete tenants | Super Admin, Landlord Admin |
| `manage plans` | Manage subscription plans | Super Admin, Landlord Admin |
| `manage subscriptions` | Handle tenant subscriptions | Super Admin, Landlord Admin |
| `manage central users` | Manage central admin users | Super Admin |
| `manage central roles` | Manage central roles and permissions | Super Admin |
| `view central dashboard` | Access central dashboard | All roles |
| `manage system settings` | Configure system-wide settings | Super Admin |
| `access landlord panel` | Access landlord administration panel | All roles |
| `manage tenant databases` | Manage tenant database operations | Super Admin |

## Command Reference

### filament-tenancy:setup-central

Complete central database setup with optional admin creation.

```bash
php artisan filament-tenancy:setup-central [options]

Options:
  --create-admin           Create a central admin user after setup
  --admin-name=NAME        Name for the central admin
  --admin-email=EMAIL      Email for the central admin
  --admin-password=PASS    Password for the central admin
  --force                  Force the operation to run when in production
```

**Examples:**
```bash
# Interactive setup
php artisan filament-tenancy:setup-central

# Setup with admin creation (interactive)
php artisan filament-tenancy:setup-central --create-admin

# Setup with admin parameters
php artisan filament-tenancy:setup-central \
  --create-admin \
  --admin-name="John Doe" \
  --admin-email="admin@example.com" \
  --admin-password="secure-password"
```

### filament-tenancy:seed-central

Seed the central database with roles and permissions.

```bash
php artisan filament-tenancy:seed-central [options]

Options:
  --force    Force the operation to run when in production
```

### filament-tenancy:create-central-admin

Create a central admin user with Super Admin role.

```bash
php artisan filament-tenancy:create-central-admin [options]

Options:
  --name=NAME        Name of the admin user
  --email=EMAIL      Email of the admin user
  --password=PASS    Password for the admin user
  --force            Force creation even if user exists
```

**Examples:**
```bash
# Interactive creation
php artisan filament-tenancy:create-central-admin

# Non-interactive creation
php artisan filament-tenancy:create-central-admin \
  --name="Jane Smith" \
  --email="jane@example.com" \
  --password="my-secure-password"

# Update existing user
php artisan filament-tenancy:create-central-admin \
  --email="existing@example.com" \
  --force
```

## Database Schema

### Central Tables

#### roles
```sql
- id (bigint, primary)
- name (string)
- slug (string, unique)
- description (text, nullable)
- guard_name (string)
- is_active (boolean, default true)
- created_at, updated_at (timestamps)
```

#### permissions
```sql
- id (bigint, primary)
- name (string)
- slug (string, unique)
- description (text, nullable)
- guard_name (string)
- is_active (boolean, default true)
- created_at, updated_at (timestamps)
```

#### model_has_permissions
```sql
- permission_id (bigint, foreign key)
- model_type (string)
- model_id (bigint)
- Composite primary key: (permission_id, model_id, model_type)
```

#### role_has_permissions
```sql
- permission_id (bigint, foreign key)
- role_id (bigint, foreign key)
- Composite primary key: (permission_id, role_id)
```

#### model_has_roles
```sql
- role_id (bigint, foreign key)
- model_type (string)
- model_id (bigint)
- Composite primary key: (role_id, model_id, model_type)
```

## Accessing the Central Administration

After setup:

1. **Access URL**: Navigate to `/admin` in your browser
2. **Login**: Use your central admin credentials
3. **Dashboard**: Access the central dashboard with tenant overview
4. **Management**: Manage tenants, plans, and central users

## Troubleshooting

### Common Issues

#### "Table 'roles' doesn't exist"
**Solution**: Run central database migrations:
```bash
php artisan migrate --path="packages/filament-tenancy/database/migrations"
```

#### "Log [tenancy] is not defined"
**Solution**: This is now handled automatically with fallback to default logging channel.

#### "Type error in PlanResource"
**Solution**: This has been fixed with proper type declarations.

### Verification Commands

```bash
# Check if central tables exist
php artisan tinker
>>> Schema::hasTable('roles');
>>> Schema::hasTable('permissions');

# Check if central roles exist
php artisan tinker
>>> use AngelitoSystems\FilamentTenancy\Models\Role;
>>> Role::count();

# Check if admin user exists
php artisan tinker
>>> use App\Models\User;
>>> User::where('email', 'admin@example.com')->first();
```

## Security Considerations

### Password Requirements
- Minimum 8 characters
- Configurable via environment variables

### Permission Isolation
- Central permissions are completely separate from tenant permissions
- Database-level isolation ensures no cross-tenant access

### Admin User Security
- Central admin users have system-wide access
- Use strong passwords and enable 2FA when available
- Regularly review admin user access

## Best Practices

1. **Initial Setup**: Always use `filament-tenancy:setup-central --create-admin` for new installations
2. **User Management**: Create separate admin users for different roles (Super Admin, Landlord Admin, Support)
3. **Permission Assignment**: Follow principle of least privilege
4. **Regular Maintenance**: Periodically review and update central permissions
5. **Backup Strategy**: Include central database in your backup strategy

## Integration with Tenant System

The central database works alongside tenant databases:

- **Central**: Manages tenants, plans, subscriptions, and central users
- **Tenant**: Contains tenant-specific data, users, roles, and permissions
- **Isolation**: Complete separation between central and tenant data
- **Communication**: Central system can access tenant databases for management tasks

This architecture ensures proper multitenancy with centralized management capabilities.
