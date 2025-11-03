<?php

return [
    // Navigation
    'plans' => 'Planes',
    'tenants' => 'Inquilinos',
    'roles' => 'Roles',
    'permissions' => 'Permisos',
    'users' => 'Usuarios',
    'subscriptions' => 'Suscripciones',
    
    // Navigation Groups
    'billing_management' => 'Gestión de Facturación',
    'user_management' => 'Gestión de Usuarios',
    'admin_management' => 'Administración',
    
    // Model Labels
    'plan' => 'Plan',
    'tenant' => 'Inquilino',
    'role' => 'Rol',
    'permission' => 'Permiso',
    'subscription' => 'Suscripción',
    
    // Sections
    'plan_information' => 'Información del Plan',
    'pricing' => 'Precios',
    'features_limits' => 'Características y Límites',
    'display_settings' => 'Configuración de Visualización',
    'basic_information' => 'Información Básica',
    'domain_configuration' => 'Configuración de Dominio',
    'additional_data' => 'Datos Adicionales',
    'role_information' => 'Información del Rol',
    
    // Fields
    'name' => 'Nombre',
    'slug' => 'Slug',
    'description' => 'Descripción',
    'color' => 'Color',
    'price' => 'Precio',
    'billing_cycle' => 'Ciclo de Facturación',
    'trial_days' => 'Días de Prueba',
    'is_active' => 'Activo',
    'is_popular' => 'Popular',
    'is_featured' => 'Destacado',
    'sort_order' => 'Orden de Clasificación',
    'features' => 'Características',
    'limits' => 'Límites',
    'domain' => 'Dominio Personalizado',
    'subdomain' => 'Subdominio',
    'data' => 'Datos Personalizados',
    'permissions' => 'Permisos',
    'language' => 'Idioma',
    'value' => 'Valor',
    'expires_at' => 'Fecha de Vencimiento',
    'plan' => 'Plan',
    
    // Billing Cycles
    'monthly' => 'Mensual',
    'yearly' => 'Anual',
    'quarterly' => 'Trimestral',
    'lifetime' => 'De por vida',
    
    // Plans Types
    'basic' => 'Básico',
    'premium' => 'Premium',
    'enterprise' => 'Empresarial',
    
    // Placeholders
    'describe_plan' => 'Describe los beneficios y características del plan',
    'describe_role' => 'Describe el rol y sus responsabilidades',
    'feature_name' => 'Nombre de la Característica',
    'feature_description' => 'Descripción',
    'limit_name' => 'Nombre del Límite',
    'limit_value' => 'Valor',
    'select_plan' => 'Seleccionar un plan',
    'never_expires' => 'Nunca vence',
    'example_domain' => 'ejemplo.com',
    'subdomain_prefix' => 'clinica',
    
    // Helper Text
    'color_helper' => 'Color para insignias y elementos de la interfaz',
    'trial_days_helper' => 'Número de días de prueba gratuita',
    'active_helper' => 'Si este plan está disponible para nuevas suscripciones',
    'sort_order_helper' => 'Los números más bajos aparecen primero',
    'popular_helper' => 'Resaltar este plan como popular',
    'featured_helper' => 'Mostrar este plan en la sección destacada',
    'permissions_helper' => 'Selecciona los permisos que este rol debería tener',
    'full_domain' => 'Nombre de dominio completo (ej., clinica.com)',
    'subdomain_helper' => 'Prefijo de subdominio (ej., clinica.tudominio.com)',
    'custom_data' => 'Almacenar configuración específica del inquilino',
    
    // Filters
    'all_plans' => 'Todos los planes',
    'active_plans' => 'Planes activos',
    'inactive_plans' => 'Planes inactivos',
    'popular_plans' => 'Planes populares',
    'regular_plans' => 'Planes regulares',
    'featured_plans' => 'Planes destacados',
    'active_status' => 'Estado Activo',
    'expired' => 'Vencido',
    'has_permissions' => 'Tiene Permisos',
    'no_permissions' => 'Sin Permisos',
    'has_users' => 'Tiene Usuarios',
    'no_users' => 'Sin Usuarios',
    
    // Actions
    'view' => 'Ver',
    'edit' => 'Editar',
    'create' => 'Crear',
    'delete' => 'Eliminar',
    'save' => 'Guardar',
    'cancel' => 'Cancelar',
    'restore' => 'Restaurar',
    'add_feature' => 'Agregar Característica',
    'add_limit' => 'Agregar Límite',
    'switch_language' => 'Cambiar Idioma',
    
    // Messages
    'plan_created' => 'Plan creado exitosamente.',
    'plan_updated' => 'Plan actualizado exitosamente.',
    'plan_deleted' => 'Plan eliminado exitosamente.',
    'tenant_created' => 'Inquilino creado exitosamente.',
    'tenant_updated' => 'Inquilino actualizado exitosamente.',
    'tenant_deleted' => 'Inquilino eliminado exitosamente.',
    'tenant_restored' => 'Inquilino restaurado exitosamente.',
    'role_created' => 'Rol creado exitosamente.',
    'role_updated' => 'Rol actualizado exitosamente.',
    'role_deleted' => 'Rol eliminado exitosamente.',
    'permission_created' => 'Permiso creado exitosamente.',
    'permission_updated' => 'Permiso actualizado exitosamente.',
    'permission_deleted' => 'Permiso eliminado exitosamente.',
    'subscription_created' => 'Suscripción creada exitosamente.',
    'subscription_updated' => 'Suscripción actualizada exitosamente.',
    'subscription_cancelled' => 'Suscripción cancelada exitosamente.',
    'language_changed' => 'Idioma cambiado a :language exitosamente.',
    'no_plan' => 'Sin plan',
    'never' => 'Nunca',
    'na' => 'N/D',
    
    // Error Messages
    'cannot_delete_tenant_with_users' => 'No se puede eliminar un inquilino con usuarios asociados. Por favor reasigna o elimina los usuarios primero.',
    'cannot_delete_role_with_users' => 'No se puede eliminar un rol con usuarios asignados. Por favor reasigna los usuarios primero.',
    'cannot_delete_role_with_users_specific' => 'No se puede eliminar el rol ":role" con :count usuarios asignados. Por favor reasigna los usuarios primero.',
    
    // Validation
    'name_required' => 'El campo nombre es obligatorio.',
    'slug_required' => 'El campo slug es obligatorio.',
    'slug_unique' => 'El slug debe ser único.',
    'domain_unique' => 'El dominio debe ser único.',
    'subdomain_unique' => 'El subdominio debe ser único.',
    'price_required' => 'El campo precio es obligatorio.',
    'billing_cycle_required' => 'El campo ciclo de facturación es obligatorio.',
];
