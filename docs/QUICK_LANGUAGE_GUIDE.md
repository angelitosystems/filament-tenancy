# 🌐 Guía Rápida: Configuración de Idioma según .env

## 🎯 **Configuración Inmediata**

### **1. Configurar tu .env:**
```env
# Para Inglés
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Para Español  
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=en_US
```

### **2. Publicar traducciones:**
```bash
php artisan filament-tenancy:publish --lang
```

### **3. Probar traducciones:**
```bash
php artisan filament-tenancy:test-translations
```

---

## ✅ **Verificación Manual**

### **Con APP_LOCALE=en:**
```bash
php artisan tinker
>>> __('tenancy.plans')     # "Plans"
>>> __('tenancy.name')      # "Name"
>>> __('tenancy.monthly')   # "Monthly"
>>> __('tenancy.view')      # "View"
```

### **Con APP_LOCALE=es:**
```bash
php artisan tinker
>>> __('tenancy.plans')     # "Planes"
>>> __('tenancy.name')      # "Nombre"
>>> __('tenancy.monthly')   # "Mensual"
>>> __('tenancy.view')      # "Ver"
```

---

## 🎉 **Resultado en la Interfaz**

### **Inglés (APP_LOCALE=en):**
- Navigation: Plans, Tenants, Roles, Permissions
- Groups: Billing Management, User Management, Admin Management
- Fields: Name, Description, Price, Color, Active
- Actions: View, Edit, Create, Delete, Save, Cancel

### **Español (APP_LOCALE=es):**
- Navigation: Planes, Inquilinos, Roles, Permisos
- Groups: Gestión de Facturación, Gestión de Usuarios, Administración
- Fields: Nombre, Descripción, Precio, Color, Activo
- Actions: Ver, Editar, Crear, Eliminar, Guardar, Cancelar

---

## 🔧 **Si no funciona:**

### **1. Limpiar caché:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **2. Verificar archivos publicados:**
```bash
ls -la resources/lang/en/tenancy.php
ls -la resources/lang/es/tenancy.php
```

### **3. Verificar configuración:**
```bash
php artisan tinker
>>> app()->getLocale()        # Debe mostrar 'en' o 'es'
>>> config('app.fallback_locale') # Debe mostrar 'en' o 'es'
```

---

## 📋 **Traducciones Disponibles**

| Clave | Español | Inglés |
|-------|---------|--------|
| `tenancy.plans` | Planes | Plans |
| `tenancy.tenants` | Inquilinos | Tenants |
| `tenancy.roles` | Roles | Roles |
| `tenancy.permissions` | Permisos | Permissions |
| `tenancy.billing_management` | Gestión de Facturación | Billing Management |
| `tenancy.user_management` | Gestión de Usuarios | User Management |
| `tenancy.admin_management` | Administración | Admin Management |
| `tenancy.name` | Nombre | Name |
| `tenancy.description` | Descripción | Description |
| `tenancy.price` | Precio | Price |
| `tenancy.monthly` | Mensual | Monthly |
| `tenancy.yearly` | Anual | Yearly |
| `tenancy.view` | Ver | View |
| `tenancy.edit` | Editar | Edit |
| `tenancy.create` | Crear | Create |
| `tenancy.delete` | Eliminar | Delete |

---

## 🎯 **Listo!**

El paquete ahora respeta automáticamente tu configuración del `.env` y mostrará las traducciones en el idioma correspondiente.
