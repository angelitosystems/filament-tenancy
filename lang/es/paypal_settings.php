<?php

return [
    // Sections
    'section_status' => 'Estado',
    'section_credentials' => 'Credenciales de PayPal',
    'section_webhooks' => 'Webhooks',
    'section_info' => 'Información',

    // Fields
    'is_enabled' => 'Habilitar PayPal',
    'is_enabled_helper' => 'Activa o desactiva la integración de PayPal',
    'mode' => 'Modo',
    'mode_helper' => 'Usa Sandbox para pruebas y Producción para pagos reales',
    'mode_sandbox' => 'Sandbox (Pruebas)',
    'mode_live' => 'Producción',
    'client_id' => 'Client ID',
    'client_id_helper' => 'Tu Client ID de PayPal',
    'client_secret' => 'Client Secret',
    'client_secret_helper' => 'Tu Client Secret de PayPal',
    'currency' => 'Moneda',
    'currency_helper' => 'Código de moneda ISO 4217 (ej: USD, EUR, MXN)',
    'webhook_secret' => 'Webhook Secret',
    'webhook_secret_helper' => 'Secret key para verificar webhooks de PayPal. Obtén esto desde tu dashboard de PayPal.',
    'return_url' => 'URL de Retorno',
    'return_url_helper' => 'URL donde redirigir después de un pago exitoso',
    'cancel_url' => 'URL de Cancelación',
    'cancel_url_helper' => 'URL donde redirigir si el usuario cancela el pago',
    'info_content' => 'Configura tus credenciales de PayPal para habilitar los pagos. Puedes obtener tus credenciales desde el PayPal Developer Dashboard.',
];

