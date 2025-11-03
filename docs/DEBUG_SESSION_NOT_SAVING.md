# 🔍 Debug: Sesión No Se Guarda

## 🎯 **Problema Identificado**

El log muestra `"session_before":null`, lo que significa que **la sesión no se está guardando** cuando haces clic en el switcher.

## ✅ **Debug Agregado**

He agregado logs completos para rastrear todo el proceso:

### **1. En la Ruta (`/language/{locale}`):**
- ✅ Cuándo se llama la ruta
- ✅ Qué locale se solicita
- ✅ Estado de la sesión antes y después

### **2. En `LanguageSwitcher::setLocale()`:**
- ✅ Cuándo se llama el método
- ✅ Si la sesión se guarda correctamente
- ✅ ID de sesión y driver usado

### **3. En el Middleware `SetLocale`:**
- ✅ Si encuentra locale en sesión
- ✅ Decisión final de locale

## 🚀 **Pasos para Diagnosticar**

### **1. Limpiar logs actuales:**
```bash
# En tu proyecto Laravel
echo "" > storage/logs/laravel.log
```

### **2. Hacer clic en el switcher:**
1. Ve a tu panel Filament
2. Haz clic en el language switcher
3. Observa si hay redirect

### **3. Revisar logs inmediatamente:**
```bash
tail -f storage/logs/laravel.log
```

### **4. Buscar estas secuencias:**

#### **Secuencia Exitosa:**
```
Language switch route called {"requested_locale":"en",...}
LanguageSwitcher::setLocale called {"requested_locale":"en",...}
LanguageSwitcher::setLocale completed {"session_after":"en",...}
Language switch result {"success":true,"session_after":"en"}
SetLocale: Using session locale {"session_locale":"en"}
```

#### **Secuencia Fallida:**
```
Language switch route called {"requested_locale":"en",...}
LanguageSwitcher::setLocale called {"requested_locale":"en",...}
LanguageSwitcher::setLocale completed {"session_after":null,...}  ← PROBLEMA
Language switch result {"success":true,"session_after":null}      ← PROBLEMA
SetLocale: Final locale decision {"session_before":null,...}      ← PROBLEMA
```

## 🔍 **Posibles Problemas**

### **1. Sesión no configurada correctamente:**
Verifica tu `config/session.php`:
```php
'driver' => env('SESSION_DRIVER', 'file'),
'lifetime' => env('SESSION_LIFETIME', 120),
'path' => env('SESSION_PATH', '/'),
'domain' => env('SESSION_DOMAIN', null),
```

### **2. Middleware de sesión no activo:**
Verifica que `StartSession` esté en `web` middleware group.

### **3. Permisos de escritura:**
```bash
# Verificar permisos de storage/framework/sessions
ls -la storage/framework/sessions/
```

### **4. Rutas no registradas correctamente:**
```bash
php artisan route:list | grep language
```

## 🚨 **Soluciones Posibles**

### **1. Forzar inicio de sesión:**
Agrega al inicio de la ruta:
```php
if (!session()->isStarted()) {
    session()->start();
}
```

### **2. Verificar driver de sesión:**
```bash
php artisan config:show session.driver
```

### **3. Usar cookie como backup:**
Si la sesión falla, usar cookie:
```php
// En setLocale()
Session::put('locale', $locale);
cookie()->queue('locale', $locale, 525600); // 1 año
```

## 📋 **Información Necesaria**

Comparte estos datos del log:

1. **¿Se llama la ruta?** → `Language switch route called`
2. **¿Se ejecuta setLocale?** → `LanguageSwitcher::setLocale called`
3. **¿Se guarda en sesión?** → `"session_after":"en"` vs `"session_after":null`
4. **¿Qué driver de sesión?** → `"session_driver":"file"`
5. **¿Hay ID de sesión?** → `"session_id":"xyz123"`

Con esta información podremos identificar exactamente dónde falla el proceso.

## 🎯 **Resultado Esperado**

Después del debug deberías ver:
- ✅ Ruta se ejecuta correctamente
- ✅ Sesión se guarda: `"session_after":"en"`
- ✅ Middleware lee sesión: `"session_locale":"en"`
- ✅ Cambio de idioma funciona
