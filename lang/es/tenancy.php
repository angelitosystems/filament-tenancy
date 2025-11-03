<?php

return [
    // Navigation
    'navigation' => [
        'tenants' => 'Inquilinos',
        'plans' => 'Planes',
        'roles' => 'Roles',
        'permissions' => 'Permisos',
        'users' => 'Usuarios',
        'subscriptions' => 'Suscripciones',
    ],

    // Navigation Groups
    'navigation_groups' => [
        'billing_management' => 'Gestión de Facturación',
        'user_management' => 'Gestión de Usuarios',
    ],

    // Resource Labels
    'resources' => [
        'tenant' => [
            'singular' => 'Inquilino',
            'plural' => 'Inquilinos',
            'breadcrumb' => 'Inquilinos',
        ],
        'plan' => [
            'singular' => 'Plan',
            'plural' => 'Planes',
            'breadcrumb' => 'Planes',
        ],
        'role' => [
            'singular' => 'Rol',
            'plural' => 'Roles',
            'breadcrumb' => 'Roles',
        ],
        'permission' => [
            'singular' => 'Permiso',
            'plural' => 'Permisos',
            'breadcrumb' => 'Permisos',
        ],
        'subscription' => [
            'singular' => 'Suscripción',
            'plural' => 'Suscripciones',
            'breadcrumb' => 'Suscripciones',
        ],
    ],

    // Form Sections
    'sections' => [
        'basic_information' => 'Información Básica',
        'domain_configuration' => 'Configuración de Dominio',
        'additional_data' => 'Datos Adicionales',
        'plan_information' => 'Información del Plan',
        'pricing' => 'Precios',
        'features_limits' => 'Características y Límites',
        'display_settings' => 'Configuración de Visualización',
        'role_information' => 'Información del Rol',
        'permissions' => 'Permisos',
    ],

    // Form Fields
    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Slug',
        'is_active' => 'Activo',
        'plan' => 'Plan',
        'expires_at' => 'Fecha de Vencimiento',
        'domain' => 'Dominio Personalizado',
        'subdomain' => 'Subdominio',
        'data' => 'Datos Personalizados',
        'description' => 'Descripción',
        'color' => 'Color',
        'price' => 'Precio',
        'billing_cycle' => 'Ciclo de Facturación',
        'trial_days' => 'Días de Prueba',
        'features' => 'Características',
        'limits' => 'Límites',
        'sort_order' => 'Orden de Clasificación',
        'is_popular' => 'Popular',
        'is_featured' => 'Destacado',
        'permissions' => 'Permisos',
        'language' => 'Idioma',
        'value' => 'Valor',
    ],

    // Billing Cycles
    'billing_cycles' => [
        'monthly' => 'Mensual',
        'yearly' => 'Anual',
        'quarterly' => 'Trimestral',
        'lifetime' => 'De por vida',
    ],

    // Plans
    'plans' => [
        'basic' => 'Básico',
        'premium' => 'Premium',
        'enterprise' => 'Empresarial',
    ],

    // Table Columns
    'table' => [
        'id' => 'ID',
        'name' => 'Nombre',
        'slug' => 'Slug',
        'domain' => 'Dominio',
        'subdomain' => 'Subdominio',
        'active' => 'Activo',
        'plan' => 'Plan',
        'expires' => 'Vence',
        'created' => 'Creado',
        'updated' => 'Actualizado',
        'price' => 'Precio',
        'billing_cycle' => 'Ciclo de Facturación',
        'popular' => 'Popular',
        'featured' => 'Destacado',
        'permissions_count' => 'Permisos',
        'users_count' => 'Usuarios',
        'description' => 'Descripción',
    ],

    // Filters
    'filters' => [
        'active_status' => 'Estado Activo',
        'expired' => 'Vencido',
        'has_permissions' => 'Tiene Permisos',
        'no_permissions' => 'Sin Permisos',
        'has_users' => 'Tiene Usuarios',
        'no_users' => 'Sin Usuarios',
        'all_plans' => 'Todos los planes',
        'active_plans' => 'Planes activos',
        'inactive_plans' => 'Planes inactivos',
        'popular_plans' => 'Planes populares',
        'regular_plans' => 'Planes regulares',
        'featured_plans' => 'Planes destacados',
    ],

    // Actions
    'actions' => [
        'create' => 'Crear',
        'edit' => 'Editar',
        'view' => 'Ver',
        'delete' => 'Eliminar',
        'restore' => 'Restaurar',
        'force_delete' => 'Eliminar Forzosamente',
        'add_feature' => 'Agregar Característica',
        'add_limit' => 'Agregar Límite',
        'switch_language' => 'Cambiar Idioma',
        'crear' => 'Crear',
        'editar' => 'Editar',
        'ver' => 'Ver',
        'borrar' => 'Eliminar',
    ],

    // Buttons and UI Elements
    'buttons' => [
        'create' => 'Crear',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
        'delete' => 'Eliminar',
        'edit' => 'Editar',
        'view' => 'Ver',
        'back' => 'Atrás',
        'next' => 'Siguiente',
        'previous' => 'Anterior',
        'search' => 'Buscar',
        'filter' => 'Filtrar',
        'reset' => 'Restablecer',
        'export' => 'Exportar',
        'import' => 'Importar',
        'refresh' => 'Actualizar',
    ],

    // Status and States
    'status' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'enabled' => 'Habilitado',
        'disabled' => 'Deshabilitado',
        'published' => 'Publicado',
        'draft' => 'Borrador',
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'expired' => 'Vencido',
        'valid' => 'Válido',
    ],

    // Placeholders
    'placeholders' => [
        'select_plan' => 'Seleccionar un plan',
        'never_expires' => 'Nunca vence',
        'example_domain' => 'ejemplo.com',
        'subdomain_prefix' => 'clinica',
        'describe_plan' => 'Describe los beneficios y características del plan',
        'describe_role' => 'Describe el rol y sus responsabilidades',
        'feature_name' => 'Nombre de la Característica',
        'feature_description' => 'Descripción',
        'limit_name' => 'Nombre del Límite',
        'limit_value' => 'Valor',
    ],

    // Helper Text
    'helpers' => [
        'full_domain' => 'Nombre de dominio completo (ej., clinica.com)',
        'subdomain_helper' => 'Prefijo de subdominio (ej., clinica.tudominio.com)',
        'custom_data' => 'Almacenar configuración específica del inquilino',
        'color_helper' => 'Color para insignias y elementos de la interfaz',
        'trial_days_helper' => 'Número de días de prueba gratuita',
        'active_helper' => 'Si este plan está disponible para nuevas suscripciones',
        'sort_order_helper' => 'Los números más bajos aparecen primero',
        'popular_helper' => 'Resaltar este plan como popular',
        'featured_helper' => 'Mostrar este plan en la sección destacada',
        'permissions_helper' => 'Selecciona los permisos que este rol debería tener',
    ],

    // Messages
    'messages' => [
        'tenant_created' => 'Inquilino creado exitosamente.',
        'tenant_updated' => 'Inquilino actualizado exitosamente.',
        'tenant_deleted' => 'Inquilino eliminado exitosamente.',
        'tenant_restored' => 'Inquilino restaurado exitosamente.',
        'cannot_delete_tenant_with_users' => 'No se puede eliminar un inquilino con usuarios asociados. Por favor reasigna o elimina los usuarios primero.',
        'cannot_delete_role_with_users' => 'No se puede eliminar un rol con usuarios asignados. Por favor reasigna los usuarios primero.',
        'cannot_delete_role_with_users_specific' => 'No se puede eliminar el rol ":role" con :count usuarios asignados. Por favor reasigna los usuarios primero.',
        'plan_created' => 'Plan creado exitosamente.',
        'plan_updated' => 'Plan actualizado exitosamente.',
        'plan_deleted' => 'Plan eliminado exitosamente.',
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
    ],

    // Validation
    'validation' => [
        'name_required' => 'El campo nombre es obligatorio.',
        'slug_required' => 'El campo slug es obligatorio.',
        'slug_unique' => 'El slug debe ser único.',
        'domain_unique' => 'El dominio debe ser único.',
        'subdomain_unique' => 'El subdominio debe ser único.',
        'price_required' => 'El campo precio es obligatorio.',
        'billing_cycle_required' => 'El campo ciclo de facturación es obligatorio.',
    ],

    // Filament specific translations
    'filament' => [
        'actions' => [
            'view' => 'Ver',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
            'create' => 'Crear',
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
            'restore' => 'Restaurar',
            'force_delete' => 'Eliminar Forzosamente',
        ],
        'table' => [
            'search' => 'Buscar',
            'filter' => 'Filtrar',
            'reset' => 'Restablecer',
            'per_page' => 'por página',
            'showing' => 'Mostrando',
            'to' => 'a',
            'of' => 'de',
            'results' => 'resultados',
        ],
    ],
];
