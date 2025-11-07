# Configuración de PayPal

Esta guía te ayudará a configurar correctamente la integración de PayPal en el paquete Filament Tenancy.

## Tabla de Contenidos

1. [Requisitos Previos](#requisitos-previos)
2. [Configuración Inicial](#configuración-inicial)
3. [Migraciones](#migraciones)
4. [Configuración desde Filament](#configuración-desde-filament)
5. [Configuración de Webhooks en PayPal](#configuración-de-webhooks-en-paypal)
6. [Variables de Entorno](#variables-de-entorno)
7. [Uso del Servicio](#uso-del-servicio)
8. [Eventos y Webhooks Soportados](#eventos-y-webhooks-soportados)
9. [Solución de Problemas](#solución-de-problemas)
10. [Ejemplos de Uso](#ejemplos-de-uso)

---

## Requisitos Previos

Antes de comenzar, asegúrate de tener:

- Una cuenta de PayPal Business (no personal)
- Acceso al [PayPal Developer Dashboard](https://developer.paypal.com/)
- Laravel con el paquete `filament-tenancy` instalado
- Base de datos configurada y migraciones ejecutadas

---

## Configuración Inicial

### 1. Crear una Aplicación en PayPal

1. Ve a [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Inicia sesión con tu cuenta de PayPal Business
3. Navega a **My Apps & Credentials**
4. Haz clic en **Create App**
5. Completa el formulario:
   - **App Name**: Nombre de tu aplicación (ej: "Mi Aplicación")
   - **Merchant**: Selecciona tu cuenta de negocio
   - **Features**: Selecciona las características que necesites
6. Haz clic en **Create App**

### 2. Obtener Credenciales

Después de crear la aplicación, verás dos conjuntos de credenciales:

- **Sandbox Credentials**: Para pruebas
- **Live Credentials**: Para producción

Cada conjunto incluye:
- **Client ID**: Identificador público de tu aplicación
- **Client Secret**: Clave secreta (guárdala de forma segura)

---

## Migraciones

Las migraciones necesarias ya están incluidas en el paquete. Ejecuta las migraciones si aún no lo has hecho:

```bash
php artisan migrate
```

Las migraciones relevantes para PayPal son:

- `2024_01_01_000017_create_paypal_settings_table.php` - Crea la tabla de configuración de PayPal
- `2024_01_01_000015_add_paypal_fields_to_subscriptions_table.php` - Añade campos de PayPal a las suscripciones

---

## Configuración desde Filament

### Acceso a la Configuración

1. Accede al panel de administración de Filament (Landlord Panel)
2. En el menú lateral, busca **PayPal Settings** en el grupo **Administration**
3. Haz clic para abrir la página de configuración

### Configuración Paso a Paso

#### 1. Habilitar PayPal

- Activa el toggle **Is Enabled** para habilitar PayPal
- Una vez activado, se mostrarán los demás campos

#### 2. Configurar Credenciales

**Modo (Mode)**:
- **Sandbox**: Para pruebas y desarrollo
- **Live**: Para producción

**Client ID**:
- Ingresa el Client ID obtenido de PayPal Developer Dashboard
- Para pruebas, usa el Client ID de Sandbox
- Para producción, usa el Client ID de Live

**Client Secret**:
- Ingresa el Client Secret correspondiente
- Este campo está oculto por seguridad (tipo password)

**Currency (Moneda)**:
- Código de moneda ISO 4217 (ej: USD, EUR, MXN)
- Por defecto: USD

#### 3. Configurar Webhooks

**Webhook Secret**:
- Se obtiene después de configurar el webhook en PayPal (ver sección siguiente)
- Se usa para verificar la autenticidad de las notificaciones de PayPal

**Return URL**:
- URL a la que PayPal redirige después de un pago exitoso
- Por defecto: `/paypal/success`
- Puedes personalizarlo según tus necesidades

**Cancel URL**:
- URL a la que PayPal redirige si el usuario cancela el pago
- Por defecto: `/paypal/cancel`
- Puedes personalizarlo según tus necesidades

#### 4. Probar Conexión

Después de configurar las credenciales:

1. Haz clic en el botón **Probar Conexión** en la parte superior
2. El sistema intentará obtener un token de acceso de PayPal
3. Si la conexión es exitosa, verás una notificación de éxito
4. Si hay un error, verifica tus credenciales

---

## Configuración de Webhooks en PayPal

Los webhooks permiten que PayPal notifique a tu aplicación sobre eventos importantes (pagos completados, cancelaciones, etc.).

### Exponer URL Local para Desarrollo

Si estás desarrollando en local, necesitas exponer tu aplicación para que PayPal pueda enviar webhooks. Aquí tienes varias opciones:

#### Opción 0: Usar Laravel Herd con ngrok (Para dominios locales)

Si estás usando **Laravel Herd** con dominios locales como `http://test.test`, puedes exponerlos fácilmente:

1. **Verificar que tu aplicación funciona en Herd**:
   - Asegúrate de que tu aplicación está accesible en `http://test.test`
   - Verifica que el endpoint `/paypal/webhook` responde correctamente

2. **Instalar ngrok** (si no lo tienes):
   ```bash
   # macOS (con Homebrew)
   brew install ngrok
   
   # Windows (con Chocolatey)
   choco install ngrok
   
   # O descarga desde https://ngrok.com/download
   ```

3. **Autenticar ngrok** (si es la primera vez):
   ```bash
   ngrok config add-authtoken TU_TOKEN_AQUI
   ```
   - Obtén tu token desde [ngrok.com](https://ngrok.com/)

4. **Exponer tu dominio local de Herd**:
   
   **Opción A: Dominio simple** (si solo tienes un sitio):
   ```bash
   ngrok http test.test:80 --host-header=test.test
   ```
   
   **Opción B: Permitir subdominios wildcard** (si tienes múltiples sitios con subdominios):
   ```bash
   # Permite *.test.test (app.test.test, admin.test.test, etc.)
   ngrok http test.test:80 --host-header="*.test.test"
   ```
   
   **Opción C: Si Herd usa otro puerto** (verifica en la configuración de Herd):
   ```bash
   ngrok http test.test:PUERTO --host-header=test.test
   # O con wildcard:
   ngrok http test.test:PUERTO --host-header="*.test.test"
   ```
   
   **Opción D: Usar el puerto directamente** (si conoces el puerto interno):
   ```bash
   # Herd normalmente usa el puerto 80, pero puedes verificar
   ngrok http 80 --host-header=test.test
   # O con wildcard:
   ngrok http 80 --host-header="*.test.test"
   ```

5. **Copiar la URL HTTPS de ngrok**:
   - ngrok te dará una URL como: `https://abc123.ngrok-free.app`
   - Esta URL apuntará a tu dominio local `test.test`

6. **Configurar el webhook en PayPal**:
   - **Webhook URL**: `https://abc123.ngrok-free.app/paypal/webhook`
   - PayPal enviará los webhooks a esta URL, que ngrok redirigirá a tu `test.test` local

**Ejemplo completo - Dominio simple**:
```bash
# Terminal 1: Verificar que Herd está corriendo
# Tu app debería estar en http://test.test

# Terminal 2: Exponer con ngrok
ngrok http test.test:80 --host-header=test.test

# ngrok mostrará algo como:
# Forwarding  https://abc123.ngrok-free.app -> http://test.test:80
```

**Ejemplo completo - Con subdominios wildcard**:
```bash
# Si tienes múltiples sitios en Herd:
# - http://app.test.test
# - http://admin.test.test
# - http://test.test

# Terminal: Exponer con wildcard para permitir todos los subdominios
ngrok http test.test:80 --host-header="*.test.test"

# ngrok mostrará algo como:
# Forwarding  https://abc123.ngrok-free.app -> http://test.test:80
# Ahora funcionará para cualquier subdominio: app.test.test, admin.test.test, etc.
```

**Nota importante**: 
- El flag `--host-header` es crucial para que Herd reconozca correctamente el dominio
- Usa `--host-header=test.test` para un solo dominio
- Usa `--host-header="*.test.test"` para permitir todos los subdominios (wildcard)
- Con wildcard, cualquier subdominio de `test.test` funcionará a través de ngrok

#### Opción 1: Usar ngrok (Recomendado para php artisan serve)

**ngrok** es la herramienta más popular para exponer aplicaciones locales:

1. **Instalar ngrok**:
   ```bash
   # Windows (con Chocolatey)
   choco install ngrok
   
   # macOS (con Homebrew)
   brew install ngrok
   
   # O descarga desde https://ngrok.com/download
   ```

2. **Registrarse y obtener token**:
   - Ve a [ngrok.com](https://ngrok.com/) y crea una cuenta gratuita
   - Obtén tu token de autenticación desde el dashboard

3. **Autenticar ngrok**:
   ```bash
   ngrok config add-authtoken TU_TOKEN_AQUI
   ```

4. **Iniciar tu servidor Laravel**:
   ```bash
   php artisan serve
   # Tu app estará en http://localhost:8000
   ```

5. **Exponer con ngrok**:
   ```bash
   ngrok http 8000
   ```

6. **Copiar la URL HTTPS**:
   - ngrok te dará una URL como: `https://abc123.ngrok-free.app`
   - **Usa esta URL** para configurar el webhook en PayPal

**Ejemplo de URL del webhook**:
```
https://abc123.ngrok-free.app/paypal/webhook
```

**Nota**: Con la cuenta gratuita de ngrok, la URL cambia cada vez que reinicias ngrok. Para desarrollo, esto está bien. Si necesitas una URL fija, considera la versión de pago.

#### Opción 2: Usar localtunnel

**localtunnel** es una alternativa gratuita y open source:

1. **Instalar localtunnel**:
   ```bash
   npm install -g localtunnel
   ```

2. **Iniciar tu servidor Laravel**:
   ```bash
   php artisan serve
   ```

3. **Exponer con localtunnel**:
   ```bash
   lt --port 8000
   ```

4. **Copiar la URL proporcionada**:
   - localtunnel te dará una URL como: `https://random-name.loca.lt`
   - Usa esta URL para el webhook: `https://random-name.loca.lt/paypal/webhook`

#### Opción 3: Usar Cloudflare Tunnel (cloudflared)

**Cloudflare Tunnel** es otra opción gratuita:

1. **Instalar cloudflared**:
   ```bash
   # Descarga desde https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/
   ```

2. **Exponer tu aplicación**:
   ```bash
   cloudflared tunnel --url http://localhost:8000
   ```

3. **Usar la URL proporcionada** para el webhook

#### Opción 4: Usar Serveo (Sin instalación)

**Serveo** es un servicio SSH que no requiere instalación:

```bash
ssh -R 80:localhost:8000 serveo.net
```

Esto te dará una URL temporal que puedes usar.

### Configurar Webhook en PayPal Dashboard

#### Para Sandbox (Pruebas):

1. Ve a [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Selecciona tu aplicación Sandbox
3. Ve a la sección **Webhooks**
4. Haz clic en **Add Webhook**
5. Completa el formulario:
   - **Webhook URL**: 
     - Para desarrollo local: `https://tu-url-ngrok.ngrok-free.app/paypal/webhook`
     - Para producción: `https://tu-dominio.com/paypal/webhook`
   - **Event Types**: Selecciona los eventos que quieres recibir (ver lista abajo)
6. Haz clic en **Save**
7. Copia el **Webhook ID** que aparece después de crear el webhook
8. Pega este ID en el campo **Webhook Secret** en Filament

#### Para Live (Producción):

1. Repite los mismos pasos pero en la aplicación Live
2. Usa tu dominio de producción para la URL del webhook

### Configurar Return URLs y Cancel URLs para Desarrollo Local

Si estás usando ngrok o similar, también necesitas actualizar las URLs de retorno en Filament:

1. Ve a Filament > PayPal Settings
2. Configura las URLs:
   - **Return URL**: `/paypal/success` (relativa, se construirá automáticamente)
   - **Cancel URL**: `/paypal/cancel` (relativa, se construirá automáticamente)

**Nota**: El sistema construye automáticamente las URLs completas usando `url()`, por lo que funcionarán tanto en local como en producción.

### Verificar que el Webhook Funciona

Después de configurar el webhook:

1. **PayPal enviará un evento de verificación** (`WEBHOOKS.VERIFICATION`)
2. **Revisa los logs de Laravel**:
   ```bash
   tail -f storage/logs/laravel.log
   ```
3. **Busca mensajes que comiencen con "PayPal:"**
4. Si ves errores de verificación, verifica:
   - Que la URL del webhook sea accesible públicamente
   - Que el Webhook Secret coincida con el Webhook ID de PayPal
   - Que tu aplicación esté corriendo y accesible

### Consejos para Desarrollo Local

1. **Mantén ngrok corriendo**: 
   - Deja ngrok corriendo mientras desarrollas
   - Si reinicias ngrok, actualiza la URL del webhook en PayPal

2. **Para Laravel Herd específicamente**:
   - Usa siempre el flag `--host-header` con ngrok para que Herd reconozca el dominio
   - Para un solo sitio: `--host-header=test.test`
   - Para múltiples subdominios: `--host-header="*.test.test"` (permite app.test.test, admin.test.test, etc.)
   - Verifica que tu dominio local funciona antes de exponerlo
   - Si tienes múltiples sitios en Herd, puedes usar wildcard para exponerlos todos a la vez

3. **Usa un subdominio fijo** (ngrok Pro):
   - Con ngrok Pro puedes tener URLs fijas
   - Útil si no quieres actualizar el webhook constantemente
   - Especialmente útil con Herd para evitar reconfigurar webhooks

4. **Prueba con PayPal Sandbox**:
   - Siempre usa Sandbox para desarrollo
   - Crea cuentas de prueba en PayPal Sandbox para simular pagos

5. **Revisa los logs regularmente**:
   - Los webhooks se registran en los logs de Laravel
   - Busca errores que comiencen con "PayPal:"
   - Con Herd, los logs están en `storage/logs/laravel.log`

6. **Desactiva CSRF para webhooks**:
   - El paquete ya desactiva CSRF para `/paypal/webhook` automáticamente
   - No necesitas hacer nada adicional

7. **Verificar que Herd está funcionando**:
   ```bash
   # Verifica que tu sitio está accesible localmente
   curl http://test.test/paypal/webhook
   
   # O visita en el navegador (debería dar error 405 porque es POST, pero confirma que el dominio funciona)
   ```

### Eventos Recomendados

Selecciona estos eventos en PayPal para una funcionalidad completa:

- `PAYMENT.CAPTURE.COMPLETED` - Pago completado
- `PAYMENT.CAPTURE.DENIED` - Pago denegado
- `PAYMENT.CAPTURE.REFUNDED` - Pago reembolsado
- `BILLING.SUBSCRIPTION.CREATED` - Suscripción creada
- `BILLING.SUBSCRIPTION.ACTIVATED` - Suscripción activada
- `BILLING.SUBSCRIPTION.CANCELLED` - Suscripción cancelada
- `BILLING.SUBSCRIPTION.EXPIRED` - Suscripción expirada
- `BILLING.SUBSCRIPTION.PAYMENT.FAILED` - Pago de suscripción fallido
- `BILLING.SUBSCRIPTION.UPDATED` - Suscripción actualizada

### Verificar Webhook

Después de configurar el webhook:

1. PayPal enviará un evento de prueba (`WEBHOOKS.VERIFICATION`)
2. El sistema verificará automáticamente la firma del webhook
3. Revisa los logs de Laravel para confirmar que los webhooks están funcionando

---

## Variables de Entorno

Aunque la configuración se realiza principalmente desde Filament, también puedes usar variables de entorno como respaldo:

```env
# PayPal Configuration
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=tu_client_id_aqui
PAYPAL_CLIENT_SECRET=tu_client_secret_aqui
PAYPAL_CURRENCY=USD
PAYPAL_WEBHOOK_SECRET=tu_webhook_secret_aqui
PAYPAL_RETURN_URL=/paypal/success
PAYPAL_CANCEL_URL=/paypal/cancel
```

**Nota**: La configuración desde Filament tiene prioridad sobre las variables de entorno. Las variables de entorno solo se usan si PayPal no está habilitado en la base de datos o si hay un error al cargar la configuración.

---

## Uso del Servicio

### Crear una Orden de Pago

```php
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use AngelitoSystems\FilamentTenancy\Models\Subscription;

$paypalService = app(PayPalService::class);
$subscription = Subscription::find(1);

// Crear orden de pago único
$order = $paypalService->createOrder($subscription);

if ($order) {
    // Obtener URL de aprobación
    $approveUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'];
    
    // Redirigir al usuario a PayPal
    return redirect($approveUrl);
}
```

### Crear una Suscripción Recurrente

```php
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use AngelitoSystems\FilamentTenancy\Models\Subscription;

$paypalService = app(PayPalService::class);
$subscription = Subscription::find(1);

// Crear suscripción recurrente en PayPal
$paypalSubscription = $paypalService->createSubscription($subscription);

if ($paypalSubscription) {
    // Obtener URL de aprobación
    $approveUrl = collect($paypalSubscription['links'])->firstWhere('rel', 'approve')['href'];
    
    // Redirigir al usuario a PayPal
    return redirect($approveUrl);
}
```

### Capturar una Orden

```php
use AngelitoSystems\FilamentTenancy\Support\PayPalService;

$paypalService = app(PayPalService::class);
$orderId = '5O190127TN364715T'; // ID de orden de PayPal

$order = $paypalService->captureOrder($orderId);

if ($order && $order['status'] === 'COMPLETED') {
    // Pago completado exitosamente
}
```

### Cancelar una Suscripción

```php
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use AngelitoSystems\FilamentTenancy\Models\Subscription;

$paypalService = app(PayPalService::class);
$subscription = Subscription::find(1);

$cancelled = $paypalService->cancelSubscription($subscription, 'Cancelado por el usuario');

if ($cancelled) {
    // Suscripción cancelada exitosamente
}
```

### Generar Enlace de Pago

Para suscripciones expiradas o próximas a expirar:

```php
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use AngelitoSystems\FilamentTenancy\Models\Subscription;

$paypalService = app(PayPalService::class);
$subscription = Subscription::find(1);

// Generar enlace de pago válido por 24 horas
$paymentLink = $paypalService->generatePaymentLink($subscription, 24);

if ($paymentLink) {
    // Enviar enlace al usuario por email o mostrarlo en la interfaz
}
```

### Verificar si PayPal está Habilitado

```php
use AngelitoSystems\FilamentTenancy\Support\PayPalService;

$paypalService = app(PayPalService::class);

if ($paypalService->isEnabled()) {
    // PayPal está configurado y habilitado
}
```

---

## Eventos y Webhooks Soportados

El sistema maneja automáticamente los siguientes eventos de PayPal:

### Eventos de Pago

- **PAYMENT.CAPTURE.COMPLETED**: 
  - Activa la suscripción
  - Crea una factura
  - Calcula comisiones si hay vendedor asociado

- **PAYMENT.CAPTURE.DENIED**: 
  - Marca la suscripción como pendiente
  - Registra el motivo del fallo

- **PAYMENT.CAPTURE.REFUNDED**: 
  - Marca la suscripción como pendiente
  - Registra el reembolso

### Eventos de Suscripción

- **BILLING.SUBSCRIPTION.CREATED**: 
  - Registra la creación de la suscripción en PayPal

- **BILLING.SUBSCRIPTION.ACTIVATED**: 
  - Activa la suscripción en el sistema
  - Crea factura inicial
  - Calcula comisiones

- **BILLING.SUBSCRIPTION.CANCELLED**: 
  - Cancela la suscripción en el sistema
  - Registra el motivo de cancelación

- **BILLING.SUBSCRIPTION.EXPIRED**: 
  - Marca la suscripción como expirada

- **BILLING.SUBSCRIPTION.PAYMENT.FAILED**: 
  - Marca la suscripción como pendiente
  - Registra el fallo de pago

- **BILLING.SUBSCRIPTION.UPDATED**: 
  - Actualiza el estado de la suscripción

---

## Solución de Problemas

### Error: "PayPal: Service is not enabled"

**Causa**: PayPal no está habilitado en la configuración.

**Solución**:
1. Ve a Filament > PayPal Settings
2. Activa el toggle "Is Enabled"
3. Guarda los cambios

### Error: "PayPal: Failed to get access token"

**Causa**: Credenciales incorrectas o problemas de conexión.

**Soluciones**:
1. Verifica que el Client ID y Client Secret sean correctos
2. Asegúrate de usar las credenciales correctas (Sandbox vs Live)
3. Verifica tu conexión a internet
4. Usa el botón "Probar Conexión" en Filament para diagnosticar

### Error: "PayPal: Webhook signature verification failed"

**Causa**: El Webhook Secret no está configurado correctamente.

**Soluciones**:
1. Verifica que el Webhook Secret en Filament coincida con el Webhook ID de PayPal
2. Asegúrate de que el webhook esté configurado en PayPal Dashboard
3. Verifica que la URL del webhook sea accesible públicamente

### Los webhooks no llegan

**Causas posibles**:
1. La URL del webhook no es accesible públicamente
2. El webhook no está configurado en PayPal
3. Problemas de firewall o SSL

**Soluciones**:
1. Usa una herramienta como [ngrok](https://ngrok.com/) para desarrollo local
2. Verifica que tu servidor tenga SSL válido (HTTPS)
3. Revisa los logs de Laravel para ver si llegan los webhooks
4. Verifica la configuración del webhook en PayPal Dashboard

### Error: "Invalid subscription ID" en return URL

**Causa**: El parámetro `subscription_id` no se está pasando correctamente.

**Solución**: 
- El sistema añade automáticamente el `subscription_id` a las URLs de retorno
- No modifiques manualmente las URLs de retorno para incluir este parámetro

### Las suscripciones no se activan automáticamente

**Causas posibles**:
1. Los webhooks no están configurados
2. El webhook no está recibiendo los eventos correctos
3. Hay errores en el procesamiento del webhook

**Soluciones**:
1. Verifica que los webhooks estén configurados en PayPal
2. Revisa los logs de Laravel para ver si hay errores
3. Asegúrate de que los eventos correctos estén seleccionados en PayPal
4. Verifica que la URL del webhook sea correcta

### Problemas con el modo Sandbox vs Live

**Importante**: 
- Las credenciales de Sandbox solo funcionan con el modo Sandbox
- Las credenciales de Live solo funcionan con el modo Live
- No mezcles credenciales de diferentes modos

**Solución**:
1. Asegúrate de que el modo en Filament coincida con las credenciales que estás usando
2. Para pruebas, usa siempre Sandbox
3. Para producción, cambia a Live y usa las credenciales de Live

---

## Ejemplos de Uso

### Ejemplo 1: Proceso Completo de Suscripción

```php
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;

// 1. Obtener el plan y tenant
$plan = Plan::find(1);
$tenant = Tenant::find(1);

// 2. Crear suscripción
$subscription = Subscription::create([
    'tenant_id' => $tenant->id,
    'plan_id' => $plan->id,
    'status' => Subscription::STATUS_PENDING,
]);

// 3. Crear orden de PayPal
$paypalService = app(PayPalService::class);
$order = $paypalService->createOrder($subscription);

if ($order) {
    // 4. Redirigir a PayPal
    $approveUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'];
    return redirect($approveUrl);
}
```

### Ejemplo 2: Verificar Estado de Pago

```php
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;

$subscription = Subscription::find(1);
$paypalService = app(PayPalService::class);

// Obtener detalles de la orden de PayPal
if ($subscription->metadata['paypal_order_id'] ?? null) {
    $orderId = $subscription->metadata['paypal_order_id'];
    $order = $paypalService->getOrder($orderId);
    
    if ($order) {
        $status = $order['status'];
        // CREATED, SAVED, APPROVED, VOIDED, COMPLETED
    }
}
```

### Ejemplo 3: Manejar Renovación de Suscripción

```php
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;

// Para suscripciones que están por expirar
$expiringSubscriptions = Subscription::where('ends_at', '<=', now()->addDays(7))
    ->where('status', Subscription::STATUS_ACTIVE)
    ->get();

$paypalService = app(PayPalService::class);

foreach ($expiringSubscriptions as $subscription) {
    // Generar enlace de pago
    $paymentLink = $paypalService->generatePaymentLink($subscription, 168); // 7 días
    
    if ($paymentLink) {
        // Enviar email al tenant con el enlace de pago
        // Mail::to($subscription->tenant->email)->send(new RenewalReminder($subscription, $paymentLink));
    }
}
```

---

## Rutas Disponibles

El paquete registra automáticamente las siguientes rutas:

- `POST /paypal/webhook` - Endpoint para recibir webhooks de PayPal
- `GET /paypal/success` - URL de retorno después de pago exitoso
- `GET /paypal/cancel` - URL de retorno si el usuario cancela

**Nota**: Estas rutas están registradas automáticamente. No necesitas añadirlas manualmente a tu archivo `routes/web.php`.

---

## Seguridad

### Mejores Prácticas

1. **Nunca expongas tus credenciales**: 
   - No las incluyas en el código fuente
   - Usa variables de entorno o la configuración de Filament

2. **Usa HTTPS en producción**: 
   - PayPal requiere HTTPS para webhooks en producción
   - Asegúrate de tener un certificado SSL válido

3. **Verifica webhooks**: 
   - El sistema verifica automáticamente las firmas de los webhooks
   - Nunca deshabilites esta verificación

4. **Mantén las credenciales actualizadas**: 
   - Si cambias las credenciales en PayPal, actualízalas también en Filament
   - Limpia la caché después de cambiar las credenciales

### Limpiar Caché

Después de cambiar las credenciales de PayPal, limpia la caché:

```bash
php artisan cache:clear
```

O desde Filament, el sistema limpia automáticamente la caché al guardar los cambios.

---

## Soporte Adicional

Si encuentras problemas que no están cubiertos en esta guía:

1. Revisa los logs de Laravel (`storage/logs/laravel.log`)
2. Busca mensajes que comiencen con "PayPal:" en los logs
3. Verifica la documentación oficial de [PayPal REST API](https://developer.paypal.com/docs/api/overview/)
4. Consulta la documentación del paquete en `docs/`

---

## Conclusión

Con esta configuración, tu aplicación debería estar lista para procesar pagos y suscripciones a través de PayPal. Recuerda:

- Usar **Sandbox** para pruebas y desarrollo
- Cambiar a **Live** solo cuando estés listo para producción
- Configurar los **webhooks** correctamente para recibir notificaciones
- Revisar los **logs** regularmente para detectar problemas

¡Buena suerte con tu integración de PayPal!

