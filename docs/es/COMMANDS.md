# Referencia de Comandos

Este documento proporciona una referencia completa para todos los comandos disponibles en el paquete Filament Tenancy.

## Comandos Disponibles

### Comandos de Instalación

#### `filament-tenancy:install`

Instala y configura el paquete Filament Tenancy en tu aplicación Laravel.

```bash
php artisan filament-tenancy:install
```

**Características:**
- Verificación e instalación automática de Filament
- Verificación de compatibilidad de base de datos (MySQL/PostgreSQL)
- Asistente interactivo de configuración de base de datos
- Publicación automática de archivos de configuración
- Auto-registro de ServiceProvider (Laravel 10 y 11)
- Ejecución inteligente de migraciones con lógica de reintentos
- Limpieza de instalación en errores críticos
- Prueba de conexión después de la configuración de base de datos

**Prompts Interactivos:**
- Configuración de base de datos (host, puerto, usuario, contraseña)
- Detección automática de APP_DOMAIN desde APP_URL
- Publicación de seeders de planes
- Creación de usuario administrador
- Publicación de página 404 personalizada

---

### Comandos de Gestión de Tenants

#### `tenancy:create`

Crea un nuevo tenant con prompts interactivos.

```bash
php artisan tenancy:create
```

**Prompts Interactivos:**
- Nombre del tenant
- Slug del tenant (auto-generado desde el nombre)
- Tipo de identificación (Dominio/Subdominio)
- Valor de dominio o subdominio
- Nombre de base de datos (auto-generado)
- Selección de plan (cargado desde base de datos)
- Estado de activación del tenant
- Fecha de expiración

**Características:**
- Interfaz atractiva con marca
- Asistente paso a paso interactivo
- Selección de dominio o subdominio
- Selección de plan con valores reales de base de datos
- Auto-generación de nombre de base de datos
- Validación y manejo de errores
- Creación automática de suscripción

**Ejemplo de Salida:**
```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║           Filament Tenancy - Multi-Tenancy Package        ║
║                  Angelito Systems                      ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

✓ Tenant 'Mi Empresa' creado exitosamente!
┌─────────────────────┬──────────────────────────────────────┐
│ Propiedad           │ Valor                                │
├─────────────────────┼──────────────────────────────────────┤
│ ID                  │ 1                                    │
│ Nombre              │ Mi Empresa                           │
│ Slug                │ mi-empresa                           │
│ Dominio/Subdominio  │ mi-empresa.ejemplo.com               │
│ Base de datos       │ tenant_mi_empresa_1                  │
│ Plan                │ Premium (USD 29.99/mensual)         │
│ Estado              │ Activo                               │
│ Suscripción         │ Activa (Inicio: 2024-01-01)          │
│ URL                 │ https://mi-empresa.ejemplo.com      │
└─────────────────────┴──────────────────────────────────────┘
```

---

#### `tenant:user-create`

Crea un usuario para un tenant específico con roles y permisos.

```bash
php artisan tenant:user-create
```

**Opciones:**
- `--tenant=` - ID o slug del tenant (interactivo si no se proporciona)
- `--name=` - Nombre del usuario
- `--email=` - Email del usuario
- `--password=` - Contraseña del usuario (auto-generada si no se proporciona)
- `--role=` - Slug del rol (default: user)
- `--permissions=` - Lista separada por comas de slugs de permisos
- `--list-tenants` - Listar todos los tenants disponibles
- `--list-roles` - Listar todos los roles disponibles en el tenant
- `--list-permissions` - Listar todos los permisos disponibles en el tenant

**Modo Interactivo:**
```bash
php artisan tenant:user-create
```

**Modo No Interactivo:**
```bash
php artisan tenant:user-create --tenant=mi-tenant --name="Juan Pérez" --email="juan@ejemplo.com" --role=admin
```

**Opciones de Listado:**
```bash
# Listar todos los tenants
php artisan tenant:user-create --list-tenants

# Listar roles en tenant específico
php artisan tenant:user-create --tenant=mi-tenant --list-roles

# Listar permisos en tenant específico
php artisan tenant:user-create --tenant=mi-tenant --list-permissions
```

**Características:**
- Selección interactiva de tenant con opciones numeradas
- Asignación de roles y permisos
- Generación automática de contraseñas
- Validación de email
- Visualización de información del usuario con URLs de acceso
- Soporte para asignación múltiple de permisos

**Ejemplo de Salida:**
```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║           Filament Tenancy - Creador de Usuarios        ║
║                  Angelito Systems                      ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

✓ Usuario 'Juan Pérez' creado exitosamente en el tenant 'Mi Empresa'!

┌─────────────────────┬──────────────────────────────────────┐
│ Propiedad           │ Valor                                │
├─────────────────────┼──────────────────────────────────────┤
│ Tenant              │ Mi Empresa (mi-empresa)              │
│ ID                  │ 1                                    │
│ Nombre              │ Juan Pérez                           │
│ Email               │ juan@ejemplo.com                     │
│ Rol                 │ Admin                                │
│ Permisos adicionales│ 5 permisos                           │
│ Creado              │ 2024-01-01 12:00:00                  │
│ URL del tenant      │ https://mi-empresa.ejemplo.com      │
└─────────────────────┴──────────────────────────────────────┘

🔐 Información de acceso:
  • URL del panel: https://mi-empresa.ejemplo.com/admin
  • Email: juan@ejemplo.com
  • Contraseña: La que proporcionaste o la generada automáticamente
```

---

#### `tenancy:list`

Lista todos los tenants en el sistema.

```bash
php artisan tenancy:list
```

**Salida:**
```
┌────┬─────────────┬─────────────┬─────────────────────┬────────┬─────────────────────────────────┐
│ ID │ Nombre      │ Slug        │ Dominio/Subdominio  │ Activo │ URL                             │
├────┼─────────────┼─────────────┼─────────────────────┼────────┼─────────────────────────────────┤
│ 1  │ Mi Empresa  │ mi-empresa  │ mi-empresa.ejemplo.com │ Sí    │ https://mi-empresa.ejemplo.com │
│ 2  │ Tenant Test │ test-tenant │ test.ejemplo.com    │ Sí    │ https://test.ejemplo.com       │
└────┴─────────────┴─────────────┴─────────────────────┴────────┴─────────────────────────────────┘
```

---

#### `tenancy:delete`

Elimina un tenant y toda su información incluyendo su base de datos.

```bash
php artisan tenancy:delete {tenant}
```

**Argumentos:**
- `tenant` - ID o slug del tenant

**Opciones:**
- `--force` - Omitir prompt de confirmación
- `--delete-database` - También eliminar la base de datos del tenant

**Ejemplo:**
```bash
php artisan tenancy:delete mi-tenant --delete-database
```

---

### Comandos de Gestión de Base de Datos

#### `tenant:migrate`

Ejecuta migraciones para un tenant específico.

```bash
php artisan tenant:migrate {tenant}
```

**Argumentos:**
- `tenant` - ID o slug del tenant (interactivo si no se proporciona)

**Opciones:**
- `--path=` - Ruta de migración específica
- `--force` - Forzar migración en producción
- `--seed` - Ejecutar seeders después de la migración
- `--step` - Forzar migración para ejecutar un paso a la vez

**Ejemplos:**
```bash
# Modo interactivo
php artisan tenant:migrate

# Tenant específico
php artisan tenant:migrate mi-tenant

# Con seeders
php artisan tenant:migrate mi-tenant --seed

# Forzar en producción
php artisan tenant:migrate mi-tenant --force
```

**Características:**
- Selección interactiva de tenant con opciones numeradas
- Creación automática de base de datos si falta
- Ejecución de migraciones específicas del tenant
- Soporte para seeders
- Manejo comprehensivo de errores

---

#### `tenant:rollback`

Revierte migraciones para un tenant específico.

```bash
php artisan tenant:rollback {tenant}
```

**Argumentos:**
- `tenant` - ID o slug del tenant (interactivo si no se proporciona)

**Opciones:**
- `--step=1` - Número de migraciones a revertir
- `--batch` - Revertir a un batch específico
- `--force` - Forzar rollback en producción

**Ejemplos:**
```bash
# Modo interactivo
php artisan tenant:rollback

# Revertir última migración
php artisan tenant:rollback mi-tenant

# Revertir últimas 3 migraciones
php artisan tenant:rollback mi-tenant --step=3

# Revertir a batch específico
php artisan tenant:rollback mi-tenant --batch=5
```

**Características:**
- Rollback seguro con confirmación
- Soporte para rollback basado en batch
- Control paso a paso del rollback
- Validación de archivos de migración

---

#### `tenant:fresh`

Elimina todas las tablas y vuelve a ejecutar migraciones para un tenant específico.

```bash
php artisan tenant:fresh {tenant}
```

**Argumentos:**
- `tenant` - ID o slug del tenant (interactivo si no se proporciona)

**Opciones:**
- `--seed` - Ejecutar seeders después de la migración
- `--force` - Forzar operación en producción
- `--drop-views` - Eliminar todas las vistas
- `--drop-types` - Eliminar todos los tipos personalizados (PostgreSQL)

**Ejemplos:**
```bash
# Modo interactivo
php artisan tenant:fresh

# Reinicio completo con confirmación
php artisan tenant:fresh mi-tenant

# Reinicio completo con seeders
php artisan tenant:fresh mi-tenant --seed

# Forzar en producción
php artisan tenant:fresh mi-tenant --force

# Eliminar vistas y tipos (PostgreSQL)
php artisan tenant:fresh mi-tenant --drop-views --drop-types
```

**Características:**
- Reset completo de base de datos
- Advertencias de seguridad y confirmaciones
- Soporte para eliminación de vistas y tipos
- Recreación automática de base de datos
- Integración con seeders

---

#### `tenancy:migrate`

Ejecuta migraciones para un tenant específico.

```bash
php artisan tenancy:migrate {tenant}
```

**Argumentos:**
- `tenant` - ID o slug del tenant

**Opciones:**
- `--force` - Forzar migración en producción
- `--path=` - Ruta de migración personalizada
- `--seed` - Ejecutar seeders de base de datos después de la migración

**Ejemplo:**
```bash
php artisan tenancy:migrate mi-tenant --seed
```

---

### Comandos de Monitoreo

#### `tenancy:monitor-connections`

Monitorea las conexiones activas de tenants y métricas de rendimiento.

```bash
php artisan tenancy:monitor-connections
```

**Opciones:**
- `--format=` - Formato de salida (table, json)
- `--interval=` - Intervalo de monitoreo en segundos
- `--continuous` - Modo de monitoreo continuo

**Ejemplo:**
```bash
php artisan tenancy:monitor-connections --format=json --interval=30
```

---

## Ejemplos de Comandos

### Ejemplo de Flujo Completo

```bash
# 1. Instalar el paquete
php artisan filament-tenancy:install

# 2. Crear un tenant
php artisan tenancy:create

# 3. Crear un usuario para el tenant
php artisan tenant:user-create --tenant=mi-tenant --name="Usuario Admin" --email="admin@mi-tenant.com" --role=super-admin

# 4. Listar todos los tenants
php artisan tenancy:list

# 5. Ejecutar migraciones para un tenant
php artisan tenancy:migrate mi-tenant

# 6. Monitorear conexiones
php artisan tenancy:monitor-connections
```

### Operaciones por Lotes

```bash
# Crear múltiples usuarios para diferentes tenants
php artisan tenant:user-create --tenant=tenant-1 --name="Usuario 1" --email="usuario1@tenant1.com" --role=user
php artisan tenant:user-create --tenant=tenant-2 --name="Usuario 2" --email="usuario2@tenant2.com" --role=admin

# Ver opciones disponibles antes de crear
php artisan tenant:user-create --list-tenants
php artisan tenant:user-create --tenant=tenant-1 --list-roles
php artisan tenant:user-create --tenant=tenant-1 --list-permissions
```

---

## Manejo de Errores

Todos los comandos incluyen manejo comprehensivo de errores:

- **Errores de validación** con mensajes útiles
- **Errores de conexión** con sugerencias de reintento
- **Errores de permisos** con guía de configuración
- **Errores de base de datos** con consejos de solución de problemas

### Mensajes de Error Comunes

```
❌ No hay tenants disponibles. Crea un tenant primero con:
  php artisan tenancy:create

⚠️ SQLite no soporta multi-database tenancy. Usa MySQL o PostgreSQL.

✗ Tenant 'inexistente' no encontrado.
  Usa --list-tenants para ver los tenants disponibles.
```

---

## Configuración

Los comandos pueden ser configurados a través de:

1. **Archivo de configuración** (`config/filament-tenancy.php`)
2. **Variables de entorno** (`.env`)
3. **Opciones de línea de comandos**

### Opciones de Configuración Relevantes

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

## Solución de Problemas

### Problemas Comunes

1. **"Table 'permissions' doesn't exist"**
   - Esto está ahora solucionado - los roles y permisos se crean cuando se crean los tenants
   - No se requiere intervención manual

2. **Errores de "Connection not configured"**
   - Asegúrate que la configuración de base de datos es correcta en `.env`
   - Verifica que se esté usando MySQL/PostgreSQL (no SQLite)

3. **Errores de "Tenant not found"**
   - Usa `--list-tenants` para ver los tenants disponibles
   - Verifica la ortografía del tenant y el ID

4. **Errores de permiso denegado**
   - Asegúrate que el usuario de base de datos tiene permisos CREATE DATABASE
   - Verifica los permisos de archivos para el storage de Laravel

### Modo Debug

Habilita el modo debug para información detallada de errores:

```env
APP_ENV=local
APP_DEBUG=true
```

Esto habilitará logging comprehensivo a través de la clase `DebugHelper`.

---

## Integración con Otras Herramientas

### Laravel Scheduler

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('tenancy:monitor-connections')->everyFiveMinutes();
    $schedule->command('tenancy:migrate --all')->daily();
}
```

### Pipeline CI/CD

```bash
# En scripts de despliegue
php artisan tenancy:migrate --all --force
php artisan tenant:user-create --tenant=production --name="Admin" --email="admin@production.com" --role=super-admin
```

---

## Mejores Prácticas

1. **Siempre prueba en desarrollo** antes del despliegue a producción
2. **Usa modo no interactivo** para scripts automatizados
3. **Monitorea conexiones** regularmente para problemas de rendimiento
4. **Mantén roles y permisos** consistentes entre tenants
5. **Usa permisos de base de datos apropiados** para seguridad
6. **Backups regulares** de bases de datos de tenants
7. **Monitorea espacio en disco** para configuraciones multi-base de datos

---

## Soporte

Para problemas específicos de comandos:

1. Revisa los mensajes de error cuidadosamente
2. Habilita modo debug para logs detallados
3. Verifica archivos de configuración
4. Prueba con un tenant fresco
5. Revisa permisos de base de datos

Para ayuda adicional:
- [GitHub Issues](https://github.com/angelitosystems/filament-tenancy/issues)
- [Documentación](README.md)
- [Documentación Técnica](TECHNICAL.md)
