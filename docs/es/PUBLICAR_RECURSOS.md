# Publicar Recursos del Paquete

El comando `filament-tenancy:publish` te permite publicar los recursos del paquete como archivos de idioma y documentación en tu proyecto Laravel.

## Comando Disponible

```bash
php artisan filament-tenancy:publish --[opcion]
```

### Opciones

- `--lang` : Publicar archivos de idioma
- `--docs` : Publicar documentación  
- `--all`  : Publicar todos los recursos (idioma y documentación)

## Uso

### Publicar Archivos de Idioma

```bash
php artisan filament-tenancy:publish --lang
```

Este comando publicará los archivos de idioma del paquete en:
```
resources/lang/vendor/filament-tenancy/
├── en/
│   └── tenancy.php
└── es/
    └── tenancy.php
```

**¿Qué puedes hacer con los archivos de idioma?**
- Personalizar traducciones existentes
- Agregar nuevos idiomas
- Modificar textos según tus necesidades
- Sobrescribir traducciones del paquete

### Publicar Documentación

```bash
php artisan filament-tenancy:publish --docs
```

Este comando publicará toda la documentación del paquete en:
```
docs/filament-tenancy/
├── README.md
├── INSTALLATION.md
├── CONFIGURATION.md
├── USING_PLUGINS.md
├── COMMANDS.md
├── TESTING_INSTALL_COMMAND.md
├── multilingual-support.md
└── es/
    ├── README.md
    ├── INSTALACION.md
    ├── CONFIGURACION.md
    ├── USO_DE_PLUGINS.md
    ├── COMANDOS.md
    ├── PROBAR_COMANDO_INSTALACION.md
    └── soporte-multilingue.md
```

**Contenido de la documentación:**
- Guías de instalación y configuración
- Documentación de comandos
- Ejemplos de uso
- Guías de multilingüismo
- Solución de problemas

### Publicar Todos los Recursos

```bash
php artisan filament-tenancy:publish --all
```

Publicará tanto los archivos de idioma como la documentación.

## Ejemplos de Uso

### 1. Personalizar Traducciones

```bash
# Publicar archivos de idioma
php artisan filament-tenancy:publish --lang

# Editar traducciones
# resources/lang/vendor/filament-tenancy/es/tenancy.php
```

### 2. Consultar Documentación Local

```bash
# Publicar documentación
php artisan filament-tenancy:publish --docs

# Consultar documentación
# docs/filament-tenancy/README.md
```

### 3. Configurar Nuevo Idioma

```bash
# Publicar archivos de idioma
php artisan filament-tenancy:publish --lang

# Copiar archivo existente
cp resources/lang/vendor/filament-tenancy/en/tenancy.php \
   resources/lang/vendor/filament-tenancy/fr/tenancy.php

# Editar traducciones al francés
```

## Archivos Publicados

### Idiomas

Los archivos de idioma siguen la estructura estándar de Laravel:

```php
// resources/lang/vendor/filament-tenancy/es/tenancy.php
return [
    'navigation' => [
        'tenants' => 'Tenants',
        'plans' => 'Planes',
        // ...
    ],
    'resources' => [
        'tenant' => [
            'label' => 'Tenant',
            // ...
        ],
        // ...
    ],
    // ...
];
```

### Documentación

La documentación se publica en formato Markdown y está organizada por temas:

- **README.md** - Visión general y inicio rápido
- **INSTALACION.md** - Guía completa de instalación
- **CONFIGURACION.md** - Opciones de configuración
- **USO_DE_PLUGINS.md** - Uso de plugins de Filament
- **COMANDOS.md** - Referencia de comandos
- **soporte-multilingue.md** - Soporte multilingüe

## Actualizaciones

Cuando actualices el paquete a una nueva versión, es posible que desees actualizar los recursos publicados:

```bash
# Actualizar archivos de idioma (sobrescribe cambios)
php artisan filament-tenancy:publish --lang

# Actualizar documentación
php artisan filament-tenancy:publish --docs
```

⚠️ **Advertencia:** Publicar recursos sobrescribirá los archivos existentes. Haz una copia de seguridad de tus personalizaciones antes de actualizar.

## Solución de Problemas

### Archivos no publicados

Si los archivos no se publican correctamente:

1. Verifica que tienes permisos de escritura en los directorios
2. Limpia el caché de Laravel: `php artisan config:clear`
3. Verifica que el ServiceProvider esté registrado correctamente

### Conflictos con versiones anteriores

Si tienes conflictos después de actualizar:

1. Compara tus archivos personalizados con las nuevas versiones
2. Migra manualmente tus cambios
3. Vuelve a publicar los recursos

## Integración con Flujo de Trabajo

### Para Desarrolladores

```bash
# Durante el desarrollo
php artisan filament-tenancy:publish --all

# Para personalizar traducciones
php artisan filament-tenancy:publish --lang
# Editar archivos en resources/lang/vendor/filament-tenancy/

# Para consultar documentación offline
php artisan filament-tenancy:publish --docs
# Revisar docs/filament-tenancy/
```

### Para Producción

```bash
# Publicar recursos antes del despliegue
php artisan filament-tenancy:publish --lang

# Opcional: Documentación si la necesitas disponible
php artisan filament-tenancy:publish --docs
```

## Véase También

- [Comandos del Paquete](COMANDOS.md) - Lista completa de comandos disponibles
- [Soporte Multilingüe](soporte-multilingue.md) - Guía detallada de idiomas
- [Configuración](CONFIGURACION.md) - Opciones de configuración del paquete
