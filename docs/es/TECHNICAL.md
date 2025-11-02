# Documentación Técnica - Filament Tenancy

## Resumen de Arquitectura

El paquete Filament Tenancy está construido con una arquitectura modular que proporciona soporte completo de multi-tenancy con capacidades avanzadas de registro y monitoreo.

### Componentes Principales

#### 1. Gestión de Conexiones
- **ConnectionManager**: Gestiona las conexiones de base de datos para tenants
- **CredentialManager**: Maneja el almacenamiento seguro de credenciales y rotación
- **Connection Pooling**: Optimiza el uso de conexiones de base de datos

#### 2. Sistema de Registro
- **TenancyLogger**: Registro centralizado para todas las operaciones de tenancy
- **Event Tracking**: Trazabilidad completa de auditoría
- **Performance Metrics**: Datos de monitoreo en tiempo real

#### 3. Sistema de Monitoreo
- **Performance Monitoring**: Rastrea tiempos de conexión y uso de recursos
- **Alert System**: Umbrales configurables y notificaciones
- **Health Checks**: Monitoreo automatizado de la salud del sistema

## Estructura de Clases

### Modelos

#### Modelo Tenant
```php
AngelitoSystems\FilamentTenancy\Models\Tenant
```

**Atributos Fillable:**
- `name`: Nombre de visualización del tenant
- `slug`: Identificador amigable para URL
- `domain`: Dominio principal
- `subdomain`: Identificador de subdominio
- `database_name`: Nombre de la base de datos del tenant
- `database_host`: Host de la base de datos
- `database_port`: Puerto de la base de datos
- `database_username`: Usuario de la base de datos
- `database_password`: Contraseña de la base de datos (encriptada)
- `is_active`: Estado del tenant
- `plan`: Plan de suscripción
- `expires_at`: Fecha de expiración
- `data`: Datos JSON adicionales

**Casts:**
- `expires_at`: datetime
- `data`: array
- `is_active`: boolean

### Clases de Soporte

#### ConnectionManager
```php
AngelitoSystems\FilamentTenancy\Support\ConnectionManager
```

**Métodos Clave:**
- `switchToTenant(Tenant $tenant)`: Cambiar a la base de datos del tenant
- `switchToCentral()`: Volver a la base de datos central
- `validateTenantConfiguration(array $config)`: Validar configuración del tenant
- `getTenantDatabaseConfig(Tenant $tenant)`: Obtener configuración de base de datos

#### CredentialManager
```php
AngelitoSystems\FilamentTenancy\Support\CredentialManager
```

**Métodos Clave:**
- `storeCredentials(string $profile, array $credentials)`: Almacenar credenciales encriptadas
- `getCredentials(string $profile)`: Recuperar credenciales desencriptadas
- `rotateEncryptionKeys()`: Rotar claves de encriptación
- `validateCredentials(array $credentials)`: Validar formato de credenciales

#### TenancyLogger
```php
AngelitoSystems\FilamentTenancy\Support\TenancyLogger
```

**Métodos Clave:**
- `logTenantConnection(Tenant $tenant, string $action)`: Registrar eventos de conexión
- `logDatabaseOperation(string $operation, Tenant $tenant, array $context)`: Registrar operaciones DB
- `logCredentialOperation(string $operation, Tenant $tenant, array $context)`: Registrar operaciones de credenciales
- `logSecurityEvent(string $event, array $context)`: Registrar eventos de seguridad
- `logPerformanceMetric(string $metric, float $value, array $context)`: Registrar datos de rendimiento

### Contratos (Interfaces)

#### ConnectionManagerInterface
```php
AngelitoSystems\FilamentTenancy\Support\Contracts\ConnectionManagerInterface
```

#### CredentialManagerInterface
```php
AngelitoSystems\FilamentTenancy\Support\Contracts\CredentialManagerInterface
```

### Excepciones

#### ConnectionException
```php
AngelitoSystems\FilamentTenancy\Support\Exceptions\ConnectionException
```

## Esquema de Base de Datos

### Tabla Tenants

```sql
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    domain VARCHAR(255) NULL,
    subdomain VARCHAR(255) NULL,
    database_name VARCHAR(255) NULL,
    database_host VARCHAR(255) NULL,
    database_port INT NULL,
    database_username VARCHAR(255) NULL,
    database_password TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    plan VARCHAR(255) NULL,
    expires_at TIMESTAMP NULL,
    data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_tenants_slug (slug),
    INDEX idx_tenants_domain (domain),
    INDEX idx_tenants_subdomain (subdomain),
    INDEX idx_tenants_is_active (is_active),
    INDEX idx_tenants_expires_at (expires_at)
);
```

## Configuración

### Variables de Entorno

```env
# Configuración de Base de Datos
TENANCY_AUTO_CREATE_DB=true
TENANCY_AUTO_DELETE_DB=false
TENANCY_CONNECTION_POOL_SIZE=10
TENANCY_DB_DRIVER=mysql
TENANCY_CONNECTION_TIMEOUT=30

# Configuración de Registro
TENANCY_LOGGING_ENABLED=true
TENANCY_LOG_CHANNEL=tenancy
TENANCY_LOG_LEVEL=info
TENANCY_MASK_SENSITIVE_DATA=true

# Configuración de Monitoreo
TENANCY_MONITORING_ENABLED=true
TENANCY_PERFORMANCE_THRESHOLD=1000
TENANCY_MEMORY_THRESHOLD=128

# Configuración de Seguridad
TENANCY_ENCRYPTION_KEY=your-encryption-key
TENANCY_CREDENTIAL_ROTATION_DAYS=90
TENANCY_MAX_LOGIN_ATTEMPTS=3

# Configuración de Caché
TENANCY_CACHE_ENABLED=true
TENANCY_CACHE_STORE=redis
TENANCY_CACHE_TTL=3600
```

## Consideraciones de Rendimiento

### Connection Pooling

El paquete implementa connection pooling para optimizar las conexiones de base de datos:

- **Pool Size**: Tamaño máximo configurable de conexiones por tenant
- **Connection Reuse**: Reutilización automática de conexiones para eficiencia
- **Cleanup**: Limpieza automática de conexiones inactivas
- **Monitoring**: Monitoreo en tiempo real del pool de conexiones

### Estrategia de Caché

- **Tenant Configuration**: Cacheada para acceso rápido
- **Credentials**: Encriptadas y cacheadas con TTL
- **Performance Metrics**: Cacheadas para visualización en dashboard
- **Connection Pool Status**: Actualizaciones de caché en tiempo real

### Gestión de Memoria

- **Connection Limits**: Límites configurables de conexiones por tenant
- **Memory Monitoring**: Seguimiento automático del uso de memoria
- **Garbage Collection**: Limpieza automática de recursos no utilizados
- **Alert Thresholds**: Alertas configurables de uso de memoria

## Características de Seguridad

### Encriptación de Credenciales

Todas las credenciales de base de datos se encriptan usando el sistema de encriptación de Laravel:

```php
// Encriptación automática al almacenar
$credentialManager->storeCredentials('profile', [
    'password' => 'plain-text-password'
]);

// Desencriptación automática al recuperar
$credentials = $credentialManager->getCredentials('profile');
// $credentials['password'] ahora está desencriptado
```

### Enmascaramiento de Datos

Los datos sensibles se enmascaran automáticamente en los logs:

```php
// Datos originales
$credentials = ['password' => 'secret123'];

// Registrados como
$loggedData = ['password' => '***masked***'];
```

### Control de Acceso

- **Tenant Isolation**: Aislamiento completo de base de datos entre tenants
- **Connection Validation**: Validación automática del acceso del tenant
- **Audit Trails**: Registro completo de todas las operaciones
- **Security Events**: Registro automático de eventos relacionados con seguridad

## Testing

### Estructura de Tests

```
tests/
├── Unit/                    # Tests unitarios para componentes individuales
│   ├── TenantTest.php      # Tests del modelo Tenant
│   ├── ConnectionManagerTest.php
│   ├── CredentialManagerTest.php
│   └── TenancyLoggerTest.php
├── Integration/             # Tests de integración
│   ├── DatabaseConnectionTest.php
│   ├── LoggingIntegrationTest.php
│   └── MonitoringIntegrationTest.php
└── Feature/                 # Tests de características
    ├── TenantManagementTest.php
    ├── PerformanceMonitoringTest.php
    └── SecurityTest.php
```

### Ejecutar Tests

```bash
# Ejecutar todos los tests
composer test

# Ejecutar suites específicas
vendor/bin/phpunit tests/Unit/
vendor/bin/phpunit tests/Integration/
vendor/bin/phpunit tests/Feature/

# Ejecutar con cobertura
vendor/bin/phpunit --coverage-html coverage/
```

### Configuración de Tests

El paquete incluye una configuración de tests simplificada:

```php
// tests/TestCase.php
class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FilamentTenancyServiceProvider::class,
        ];
    }
    
    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        // Configuración adicional de tests
    }
}
```

## Monitoreo y Alertas

### Métricas de Rendimiento

El sistema rastrea varias métricas de rendimiento:

- **Connection Time**: Tiempo para establecer conexiones de base de datos
- **Query Execution Time**: Rendimiento de consultas de base de datos
- **Memory Usage**: Consumo de memoria de la aplicación
- **Active Connections**: Número de conexiones activas de base de datos

### Sistema de Alertas

Alertas configurables para:

- **Slow Connections**: Conexiones que exceden el tiempo umbral
- **High Memory Usage**: Uso de memoria por encima de los límites configurados
- **Failed Connections**: Fallos y errores de conexión
- **Security Events**: Intentos de acceso no autorizados

### Comandos de Monitoreo

```bash
# Monitorear conexiones activas
php artisan tenancy:monitor-connections

# Monitorear con parámetros específicos
php artisan tenancy:monitor-connections --interval=60 --duration=600

# Formatos de salida
php artisan tenancy:monitor-connections --format=json
php artisan tenancy:monitor-connections --format=log
```

## Troubleshooting

### Problemas Comunes

1. **Connection Timeouts**
   - Verificar disponibilidad del servidor de base de datos
   - Verificar conectividad de red
   - Ajustar configuraciones de timeout de conexión

2. **Problemas de Memoria**
   - Monitorear tamaños de pool de conexiones
   - Verificar fugas de memoria
   - Ajustar umbrales de memoria

3. **Problemas de Rendimiento**
   - Revisar logs de consultas lentas
   - Optimizar índices de base de datos
   - Ajustar configuraciones de pool de conexiones

### Modo Debug

Habilitar registro de debug para troubleshooting detallado:

```php
'logging' => [
    'level' => 'debug',
    'enabled' => true,
],
```

### Análisis de Logs

Monitorear archivos de log para:

- Patrones de conexión
- Cuellos de botella de rendimiento
- Eventos de seguridad
- Patrones de error

## Puntos de Extensión

### Credential Managers Personalizados

Implementar gestión de credenciales personalizada:

```php
class CustomCredentialManager implements CredentialManagerInterface
{
    // Implementar métodos de la interfaz
}
```

### Loggers Personalizados

Extender funcionalidad de registro:

```php
class CustomTenancyLogger extends TenancyLogger
{
    // Sobrescribir o extender métodos
}
```

### Monitoreo Personalizado

Agregar métricas de monitoreo personalizadas:

```php
class CustomMonitor
{
    public function collectMetrics()
    {
        // Recopilación de métricas personalizadas
    }
}
```

