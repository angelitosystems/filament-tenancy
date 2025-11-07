<?php

return [
    // Fields (usado por TenantResource con prefijo 'tenant.fields')
    'fields' => [
        'basic_information' => 'Información Básica',
        'name' => 'Nombre',
        'slug' => 'Slug',
        'is_active' => 'Activo',
        'plan' => 'Plan',
        'basic' => 'Básico',
        'premium' => 'Premium',
        'enterprise' => 'Enterprise',
        'select_plan' => 'Seleccionar Plan',
        'expires_at' => 'Fecha de Expiración',
        'never_expires' => 'Nunca expira',
        'domain_configuration' => 'Configuración de Dominio',
        'domain' => 'Dominio',
        'example_domain' => 'ejemplo.com',
        'full_domain' => 'Dominio completo (ejemplo.com)',
        'subdomain' => 'Subdominio',
        'subdomain_prefix' => 'prefijo',
        'subdomain_helper' => 'Prefijo del subdominio (ej: prefijo.tudominio.com)',
        'additional_data' => 'Datos Adicionales',
        'data' => 'Datos',
        'value' => 'Valor',
        'custom_data' => 'Datos personalizados en formato clave-valor',
        'na' => 'N/A',
        'no_plan' => 'Sin plan',
        'never' => 'Nunca',
        'active_status' => 'Activo',
        'expired' => 'Expirado',
    ],

    // Pages - ViewTenant
    'visit_tenant' => 'Visitar Inquilino',
    'run_migrations' => 'Ejecutar Migraciones',
    'migrations_completed' => 'Migraciones completadas',
    'migrations_completed_message' => 'Las migraciones del inquilino se han ejecutado exitosamente.',
    'migration_failed' => 'Migración fallida',
    'run_migrations_description' => 'Esto ejecutará todas las migraciones pendientes para este inquilino.',
    'basic_information' => 'Información Básica',
    'id' => 'ID',
    'active' => 'Activo',
    'expires' => 'Expira',
    'domain_configuration' => 'Configuración de Dominio',
    'custom_domain' => 'Dominio Personalizado',
    'full_url' => 'URL Completa',
    'not_set' => 'No establecido',
    'timestamps' => 'Fechas',
    'created' => 'Creado',
    'updated' => 'Actualizado',
    'deleted' => 'Eliminado',
    'not_deleted' => 'No eliminado',
    'additional_data' => 'Datos Adicionales',
    'custom_data' => 'Datos Personalizados',
    'no_additional_data' => 'Sin datos adicionales',

    // Pages - CreateTenant
    'tenant_created_successfully' => '🎉 ¡Inquilino creado exitosamente!',
    'tenant_created_message' => 'El inquilino \':name\' ha sido creado exitosamente.',
    'database_created' => '✅ Base de datos \':name\' creada',
    'migrations_executed' => '✅ Migraciones ejecutadas',
    'seeders_executed' => '✅ :count seeders ejecutados',
    'failed_to_create_tenant' => '❌ Error al crear inquilino',
    'error' => 'Error: :message',
];




