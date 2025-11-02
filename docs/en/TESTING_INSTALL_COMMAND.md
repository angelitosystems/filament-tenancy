# How to Test the `filament-tenancy:install` Command

This document explains how to test the installation command in a real Laravel project.

## Recommended Method: Use Path Repository

This is the easiest and standard way to test the package during local development.

### Step 1: Prepare Your Laravel Project

In your Laravel test project, edit the `composer.json` file and add the local repository in the `repositories` section:

**Example for Windows (absolute path):**
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "C:/Proyectos/Angelito Systems/packages/filament-tenancy"
        }
    ]
}
```

**Example for relative path:**
If your Laravel project is at `C:/Proyectos/MiProyecto` and the package is at `C:/Proyectos/Angelito Systems/packages/filament-tenancy`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../Angelito Systems/packages/filament-tenancy"
        }
    ]
}
```

**⚠️ Important on Windows:** Use forward slashes `/` or double backslashes `\\` in paths, not single `\`.

### Step 2: Install the Package

From your Laravel project directory:

```bash
composer require angelitosystems/filament-tenancy:@dev
```

This will install the package from the local path and create a symlink. You'll see a message like:
```
- Installing angelitosystems/filament-tenancy (dev-main): Symlinking from C:/Proyectos/Angelito Systems/packages/filament-tenancy
```

### Step 3: Verify the Command is Available

**On Windows PowerShell:**
```powershell
php artisan list | Select-String filament-tenancy
```

**On Windows CMD or Git Bash:**
```bash
php artisan list | findstr filament-tenancy
```

You should see the `filament-tenancy:install` command in the list.

### Step 4: Run the Installation Command

```bash
php artisan filament-tenancy:install
```

### Complete composer.json Example

Here's a complete example of what your `composer.json` should look like:

```json
{
    "name": "laravel/laravel",
    "type": "project",
    "repositories": [
        {
            "type": "path",
            "url": "C:/Proyectos/Angelito Systems/packages/filament-tenancy"
        }
    ],
    "require": {
        "php": "^8.1",
        "laravel/framework": "^11.0",
        "angelitosystems/filament-tenancy": "@dev"
    }
}
```

## Alternative Option: Publish to Packagist or Private Repository

If you want to test it as if it were a real installation:

### Step 1: Create Version Tag

```bash
git tag v1.0.0
git push origin v1.0.0
```

### Step 2: In Your Laravel Project

```bash
composer require angelitosystems/filament-tenancy
```

### Step 3: Run the Command

```bash
php artisan filament-tenancy:install
```

## Post-Installation Verification

After running the command, verify:

1. **Configuration File:**
   
   **On Windows PowerShell:**
   ```powershell
   Test-Path config/tenancy.php
   Test-Path config/filament-tenancy.php
   ```
   
   **On Windows CMD:**
   ```cmd
   dir config\tenancy.php
   dir config\filament-tenancy.php
   ```

2. **ServiceProvider Registered:**
   - Laravel 10: Check `config/app.php` in the `providers` array
   - Laravel 11: Check `bootstrap/providers.php`

3. **Migrations Executed:**
   ```bash
   php artisan migrate:status
   ```
   You should see the package migrations executed.

4. **Tenants Table:**
   ```bash
   php artisan tinker
   >>> \DB::table('tenants')->count()
   ```

## Troubleshooting

### Command doesn't appear

1. Clear configuration cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Regenerate autoloader:
   ```bash
   composer dump-autoload
   ```

### Error publishing configuration

Make sure the `filament-tenancy-config` tag is correctly configured in `TenancyServiceProvider`.

### Error registering ServiceProvider

- Verify you have write permissions on `config/app.php` or `bootstrap/providers.php`
- Check that the file format is correct

## Test Specific Scenarios

### Test without running migrations

```bash
php artisan filament-tenancy:install
# Answer "no" when asked about migrations
```

### Test when ServiceProvider is already registered

Run the command twice to verify it correctly detects existing registration.

### Test on Laravel 11

Create a fresh Laravel 11 project and follow the Recommended Method (Path Repository) steps.

## Quick Commands for Windows

If you're on Windows PowerShell, here's a quick command sequence:

```powershell
# 1. Navigate to your Laravel project
cd C:\Proyectos\MiProyectoLaravel

# 2. Add the repository (edit composer.json manually first)

# 3. Install the package
composer require angelitosystems/filament-tenancy:@dev

# 4. Verify the command
php artisan list | Select-String filament-tenancy

# 5. Run the installation
php artisan filament-tenancy:install
```

## Important Notes

- The command is **idempotent**: you can run it multiple times without issues
- It doesn't overwrite existing files without confirmation
- Works on both Laravel 10 and Laravel 11

