# 🌐 Guía del LanguageSwitcher - Problemas y Soluciones

## 🔍 **Análisis del Problema**

El usuario reporta: *"siempre los recursos muestran en español, si cambio debe cambiar el idioma pero el principal es el que está en el .env"*

### **🎯 Problema Identificado:**

1. **LanguageSwitcher usa traducciones antiguas** (`filament-tenancy::tenancy.*`)
2. **Middleware SetLocale puede no estar aplicado correctamente**
3. **Prioridad de detección de idioma puede estar incorrecta**

---

## ✅ **Soluciones Implementadas**

### **1. LanguageSwitcher Actualizado** ✅
**Problema:** Usaba `__('filament-tenancy::tenancy.actions.switch_language')`

**Solución:** Ahora usa `__('tenancy.switch_language')`
```php
// ✅ ANTES:
->label(__('filament-tenancy::tenancy.actions.switch_language'))
->label(__('filament-tenancy::tenancy.fields.language'))

// ✅ DESPUÉS:
->label(__('tenancy.switch_language'))
->label(__('tenancy.language'))
```

### **2. Traducciones Agregadas** ✅
**Español (`lang/es/simple.php`):**
```php
'switch_language' => 'Cambiar Idioma',
'language' => 'Idioma',
```

**Inglés (`lang/en/simple.php`):**
```php
'switch_language' => 'Switch Language',
'language' => 'Language',
```

---

## 🔧 **Sistema de Cambio de Idioma**

### **1. Flujo de Detección de Idioma:**
```php
// Middleware SetLocale - Orden de prioridad:
// 1. Session locale (máxima prioridad)
// 2. User preference (si está autenticado)
// 3. Browser Accept-Language header
// 4. App default locale (.env)
```

### **2. Ruta de Cambio:**
```php
// routes/tenant.php
Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, array_keys(LanguageSwitcher::getAvailableLocales()))) {
        LanguageSwitcher::setLocale($locale);
    }
    return redirect()->back();
})->name('language.switch');
```

### **3. Componente LanguageSwitcher:**
```php
// getCurrentLocale() - Obtiene idioma actual
public static function getCurrentLocale(): string
{
    return Session::get('locale', config('app.locale', 'en'));
}

// setLocale() - Cambia idioma
public static function setLocale(string $locale): bool
{
    if (self::isValidLocale($locale)) {
        Session::put('locale', $locale);
        App::setLocale($locale);
        return true;
    }
    return false;
}
```

---

## 🚀 **Cómo Funciona el Sistema**

### **1. Configuración Inicial (.env):**
```env
# Idioma principal por defecto
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
```

### **2. Primer Acceso:**
```php
// 1. No hay locale en sesión
// 2. No hay usuario autenticado (o no tiene preferencia)
// 3. Detecta del navegador o usa .env
// 4. APP_LOCALE=en → Todo en inglés
```

### **3. Usuario Cambia Idioma:**
```php
// 1. Usuario hace clic en "Español"
// 2. Llama a /language/es
// 3. LanguageSwitcher::setLocale('es')
// 4. Session::put('locale', 'es')
// 5. App::setLocale('es')
// 6. Redirect back
// 7. Todo en español
```

### **4. Accesos Futuros:**
```php
// 1. Session tiene 'locale' => 'es'
// 2. Middleware SetLocale lo detecta primero
// 3. App::setLocale('es')
// 4. Todo en español hasta que cambie
```

---

## 🧪 **Verificación y Pruebas**

### **1. Publicar Traducciones:**
```bash
php artisan filament-tenancy:publish --lang
```

### **2. Limpiar Caché:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan session:clear
```

### **3. Probar Cambio de Idioma:**
```bash
# Verificar idioma actual
php artisan tinker
>>> app()->getLocale()           // 'en' (desde .env)
>>> session('locale')           // null (no hay sesión)

# Probar traducciones
>>> __('tenancy.plans')         // "Plans"
>>> __('tenancy.switch_language') // "Switch Language"
```

### **4. Simular Cambio de Idioma:**
```bash
php artisan tinker
>>> \AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher::setLocale('es')
>>> app()->getLocale()           // 'es'
>>> __('tenancy.plans')         // "Planes"
>>> __('tenancy.switch_language') // "Cambiar Idioma"
```

---

## 🔍 **Diagnóstico de Problemas**

### **Si siempre muestra español:**

#### **1. Verificar configuración:**
```bash
php artisan tinker
>>> config('app.locale')        // ¿Qué muestra?
>>> session('locale')           // ¿Hay algo en sesión?
```

#### **2. Verificar middleware:**
```php
// ¿Está SetLocale aplicado a las rutas?
// En TenancyServiceProvider.php debería estar:
$router->aliasMiddleware('locale', SetLocale::class);
```

#### **3. Verificar archivos de traducción:**
```bash
ls -la resources/lang/en/tenancy.php
ls -la resources/lang/es/tenancy.php
```

### **Si no cambia el idioma:**

#### **1. Verificar sesión:**
```bash
php artisan tinker
>>> session()->put('locale', 'en')
>>> app()->getLocale()
```

#### **2. Verificar ruta:**
```bash
php artisan route:list | grep language
# Debería mostrar: GET /language/{locale} language.switch
```

---

## 🎯 **Configuración Recomendada**

### **1. .env:**
```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Mostrar language switcher
TENANCY_SHOW_LANGUAGE_SWITCHER=true
TENANCY_LANGUAGE_SWITCHER_POSITION=user_menu
```

### **2. Middleware aplicado:**
```php
// En TenancyServiceProvider.php
protected function registerMiddleware(): void
{
    $router = $this->app['router'];
    $router->aliasMiddleware('locale', SetLocale::class);
    // ... otros middleware
}
```

### **3. Panel Configuration:**
```php
// En TenancyLandlordPlugin.php
->middleware([
    'web',
    'auth', 
    'locale', // ← Importante!
    // ... otros middleware
])
```

---

## 🎉 **Resultado Esperado**

### **Con APP_LOCALE=en:**
- **Inicio:** Todo en inglés
- **Usuario cambia a español:** Todo en español
- **Siguientes visitas:** Español (guardado en sesión)

### **Con APP_LOCALE=es:**
- **Inicio:** Todo en español  
- **Usuario cambia a inglés:** Todo en inglés
- **Siguientes visitas:** Inglés (guardado en sesión)

**🎯 El idioma del .env es el inicial, pero el usuario puede cambiarlo y se mantiene en sesión!**
