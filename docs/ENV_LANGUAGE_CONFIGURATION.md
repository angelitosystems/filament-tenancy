# 🌐 Configuración de Idiomas según .env

## 🎯 **Objetivo**
El paquete debe respetar la configuración del archivo `.env` y usar el idioma correspondiente:

```env
# Para Español
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=en_US

# Para Inglés  
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
```

---

## ✅ **Sistema Actual Funciona Perfectamente**

### **1. Archivos de Traducción Disponibles:**

```
lang/
├── es/
│   ├── simple.php           # ✅ Traducciones en español
│   ├── tenancy.php          # ✅ Traducciones completas español
│   └── filament-*.php       # ✅ Filament en español
└── en/
    ├── simple.php           # ✅ Traducciones en inglés
    ├── tenancy.php          # ✅ Traducciones completas inglés
    └── filament-*.php       # ✅ Filament en inglés
```

### **2. Uso Automático según Configuración:**

```php
// Laravel automáticamente usa el idioma del .env
__('tenancy.plans')     // APP_LOCALE=es → "Planes"
__('tenancy.plans')     // APP_LOCALE=en → "Plans"

__('tenancy.name')      // APP_LOCALE=es → "Nombre"  
__('tenancy.name')      // APP_LOCALE=en → "Name"

__('tenancy.monthly')   // APP_LOCALE=es → "Mensual"
__('tenancy.monthly')   // APP_LOCALE=en → "Monthly"
```

---

## 🚀 **Pasos para Configurar**

### **1. Configurar .env:**
```env
# Para Inglés
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# O para Español
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=en_US
```

### **2. Publicar Traducciones:**
```bash
php artisan filament-tenancy:publish --lang
```

Esto publica:
- `resources/lang/es/tenancy.php` (desde `lang/es/simple.php`)
- `resources/lang/en/tenancy.php` (desde `lang/en/simple.php`)

### **3. Limpiar Caché:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🧪 **Verificación**

### **Para Inglés (APP_LOCALE=en):**
```bash
php artisan tinker
>>> __('tenancy.plans')     // "Plans"
>>> __('tenancy.name')      // "Name"
>>> __('tenancy.monthly')   // "Monthly"
>>> __('tenancy.view')      // "View"
>>> __('tenancy.edit')      // "Edit"
```

### **Para Español (APP_LOCALE=es):**
```bash
php artisan tinker
>>> __('tenancy.plans')     // "Planes"
>>> __('tenancy.name')      // "Nombre"
>>> __('tenancy.monthly')   // "Mensual"
>>> __('tenancy.view')      // "Ver"
>>> __('tenancy.edit')      // "Editar"
```

---

## 📋 **Traducciones Disponibles**

### **Navegación:**
| Clave | Español | Inglés |
|-------|---------|--------|
| `tenancy.plans` | Planes | Plans |
| `tenancy.tenants` | Inquilinos | Tenants |
| `tenancy.roles` | Roles | Roles |
| `tenancy.permissions` | Permisos | Permissions |

### **Grupos de Navegación:**
| Clave | Español | Inglés |
|-------|---------|--------|
| `tenancy.billing_management` | Gestión de Facturación | Billing Management |
| `tenancy.user_management` | Gestión de Usuarios | User Management |
| `tenancy.admin_management` | Administración | Admin Management |

### **Campos Comunes:**
| Clave | Español | Inglés |
|-------|---------|--------|
| `tenancy.name` | Nombre | Name |
| `tenancy.description` | Descripción | Description |
| `tenancy.price` | Precio | Price |
| `tenancy.color` | Color | Color |
| `tenancy.is_active` | Activo | Active |

### **Ciclos de Facturación:**
| Clave | Español | Inglés |
|-------|---------|--------|
| `tenancy.monthly` | Mensual | Monthly |
| `tenancy.yearly` | Anual | Yearly |
| `tenancy.quarterly` | Trimestral | Quarterly |
| `tenancy.lifetime` | De por vida | Lifetime |

### **Acciones:**
| Clave | Español | Inglés |
|-------|---------|--------|
| `tenancy.view` | Ver | View |
| `tenancy.edit` | Editar | Edit |
| `tenancy.create` | Crear | Create |
| `tenancy.delete` | Eliminar | Delete |
| `tenancy.save` | Guardar | Save |
| `tenancy.cancel` | Cancelar | Cancel |

---

## 🔧 **Cómo Funciona el Sistema**

### **1. Laravel Detecta el Idioma:**
```php
// Laravel lee APP_LOCALE del .env
app()->getLocale();     // 'en' o 'es'
```

### **2. Busca el Archivo Correspondiente:**
```php
// Si APP_LOCALE=en → busca resources/lang/en/tenancy.php
// Si APP_LOCALE=es → busca resources/lang/es/tenancy.php
__('tenancy.plans');
```

### **3. Si no Encuentra, usa Fallback:**
```php
// Si no encuentra en 'en', busca en APP_FALLBACK_LOCALE
// Generalmente también será 'en' o 'es'
```

---

## 🌍 **Soporte Multiidioma**

### **Recursos con Traducciones Dinámicas:**
```php
class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    public static function getNavigationLabel(): string
    {
        return __('tenancy.plans'); // Automático según .env
    }
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('tenancy.plan_information')) // Automático
                ->schema([
                    TextInput::make('name')
                        ->label(__('tenancy.name')) // Automático
                ]);
        });
    }
}
```

---

## 🎉 **Resultado Final**

### **Con APP_LOCALE=en:**
- ✅ Navigation: "Plans", "Tenants", "Roles", "Permissions"
- ✅ Groups: "Billing Management", "User Management", "Admin Management"
- ✅ Fields: "Name", "Description", "Price", "Color"
- ✅ Actions: "View", "Edit", "Create", "Delete"

### **Con APP_LOCALE=es:**
- ✅ Navigation: "Planes", "Inquilinos", "Roles", "Permisos"
- ✅ Groups: "Gestión de Facturación", "Gestión de Usuarios", "Administración"
- ✅ Fields: "Nombre", "Descripción", "Precio", "Color"
- ✅ Actions: "Ver", "Editar", "Crear", "Eliminar"

**🎯 El paquete ahora respeta completamente la configuración del .env!**
