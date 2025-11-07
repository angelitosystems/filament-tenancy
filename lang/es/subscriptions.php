<?php

return [
    // Sections
    'section_subscription_info' => 'Información de Suscripción',
    'section_subscription_period' => 'Período de Suscripción',
    'section_status_settings' => 'Estado y Configuración',
    'section_additional_info' => 'Información Adicional',

    // Fields
    'tenant' => 'Inquilino',
    'plan' => 'Plan',
    'price' => 'Precio',
    'billing_cycle' => 'Ciclo de Facturación',
    'starts_at' => 'Fecha de Inicio',
    'ends_at' => 'Fecha de Fin',
    'ends_at_helper' => 'Dejar vacío para suscripciones de por vida',
    'trial_ends_at' => 'Fin del Período de Prueba',
    'trial_ends_at_helper' => 'Dejar vacío si no hay período de prueba',
    'next_billing_at' => 'Próxima Fecha de Facturación',
    'next_billing_at_helper' => 'Calculado automáticamente según el ciclo de facturación',
    'status' => 'Estado',
    'auto_renew' => 'Renovación Automática',
    'auto_renew_helper' => 'Renovar automáticamente la suscripción cuando expire',
    'payment_method' => 'Método de Pago',
    'payment_method_placeholder' => 'stripe, paypal, bank_transfer, etc.',
    'external_id' => 'ID Externo',
    'external_id_placeholder' => 'ID del proveedor de pago',
    'external_id_helper' => 'ID de suscripción externo de la pasarela de pago',
    'seller' => 'Vendedor',
    'seller_helper' => 'Vendedor asociado a esta suscripción para comisiones',
    'payment_link' => 'Link de Pago',
    'notes' => 'Notas',
    'notes_placeholder' => 'Notas internas sobre esta suscripción',
    'metadata' => 'Metadatos',
    'metadata_key' => 'Clave',
    'metadata_value' => 'Valor',
    'metadata_add' => 'Agregar Metadato',

    // Billing Cycles
    'billing_cycle_monthly' => 'Mensual',
    'billing_cycle_yearly' => 'Anual',
    'billing_cycle_quarterly' => 'Trimestral',
    'billing_cycle_lifetime' => 'De por vida',

    // Statuses
    'status_active' => 'Activa',
    'status_inactive' => 'Inactiva',
    'status_cancelled' => 'Cancelada',
    'status_expired' => 'Expirada',
    'status_suspended' => 'Suspendida',
    'status_pending' => 'Pendiente',

    // Table columns
    'auto_renew' => 'Renovación Automática',
    'lifetime' => 'De por vida',
    'created_at' => 'Fecha de Creación',

    // Filters
    'filter_expiring_soon' => 'Por Expirar Pronto (30 días)',
    'filter_expired' => 'Expiradas',
    'filter_in_trial' => 'En Período de Prueba',
    'filter_all_subscriptions' => 'Todas las suscripciones',
    'filter_auto_renew_enabled' => 'Renovación automática habilitada',
    'filter_auto_renew_disabled' => 'Renovación automática deshabilitada',

    // Actions
    'pay_with_paypal' => 'Pagar con PayPal',
    'create_paypal_subscription' => 'Crear Suscripción PayPal',
    'cancel_paypal_subscription' => 'Cancelar Suscripción PayPal',
    'cancel_subscription' => 'Cancelar Suscripción',
    'reactivate' => 'Reactivar',
    'generate_payment_link' => 'Generar Link de Pago',
    'copy_payment_link' => 'Copiar Link de Pago',
    'payment_link_generated' => 'Link de Pago Generado',
    'payment_link_generated_message' => 'El link de pago ha sido generado exitosamente. El inquilino puede usar este link para renovar su suscripción.',
    'payment_link_error' => 'No se pudo generar el link de pago. Por favor intenta nuevamente.',
    'payment_link_copied' => 'Link copiado',
    'payment_link_copied_message' => 'El link de pago ha sido copiado al portapapeles.',
    'paypal_payment_failed' => 'Error al crear pago PayPal. Por favor intenta nuevamente.',
    'paypal_subscription_failed' => 'Error al crear suscripción PayPal. Por favor intenta nuevamente.',
    'paypal_subscription_cancelled' => 'Suscripción PayPal cancelada exitosamente',
    'subscription_cancelled' => 'Suscripción cancelada exitosamente',
    'subscription_reactivated' => 'Suscripción reactivada exitosamente',
    'error' => 'Error',
    
    // Infolist labels
    'subscription_information' => 'Información de Suscripción',
    'subscription_period' => 'Período de Suscripción',
    'status_settings' => 'Estado y Configuración',
    'additional_information' => 'Información Adicional',
    'timestamps' => 'Fechas',
    'tenant_label' => 'Inquilino',
    'plan_label' => 'Plan',
    'no_trial' => 'Sin período de prueba',
    'not_specified' => 'No especificado',
    'no_notes_label' => 'Sin notas',
    'n_a' => 'N/A',
];

