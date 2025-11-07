<?php

return [
    // Fields (used by TenantResource with prefix 'tenant.fields')
    'fields' => [
        'basic_information' => 'Basic Information',
        'name' => 'Name',
        'slug' => 'Slug',
        'is_active' => 'Active',
        'plan' => 'Plan',
        'basic' => 'Basic',
        'premium' => 'Premium',
        'enterprise' => 'Enterprise',
        'select_plan' => 'Select Plan',
        'expires_at' => 'Expires At',
        'never_expires' => 'Never expires',
        'domain_configuration' => 'Domain Configuration',
        'domain' => 'Domain',
        'example_domain' => 'example.com',
        'full_domain' => 'Full domain (example.com)',
        'subdomain' => 'Subdomain',
        'subdomain_prefix' => 'prefix',
        'subdomain_helper' => 'Subdomain prefix (e.g. prefix.yourdomain.com)',
        'additional_data' => 'Additional Data',
        'data' => 'Data',
        'value' => 'Value',
        'custom_data' => 'Custom data in key-value format',
        'na' => 'N/A',
        'no_plan' => 'No plan',
        'never' => 'Never',
        'active_status' => 'Active',
        'expired' => 'Expired',
    ],

    // Pages - ViewTenant
    'visit_tenant' => 'Visit Tenant',
    'run_migrations' => 'Run Migrations',
    'migrations_completed' => 'Migrations completed',
    'migrations_completed_message' => 'Tenant migrations have been run successfully.',
    'migration_failed' => 'Migration failed',
    'run_migrations_description' => 'This will run all pending migrations for this tenant.',
    'basic_information' => 'Basic Information',
    'id' => 'ID',
    'active' => 'Active',
    'expires' => 'Expires',
    'domain_configuration' => 'Domain Configuration',
    'custom_domain' => 'Custom Domain',
    'full_url' => 'Full URL',
    'not_set' => 'Not set',
    'timestamps' => 'Timestamps',
    'created' => 'Created',
    'updated' => 'Updated',
    'deleted' => 'Deleted',
    'not_deleted' => 'Not deleted',
    'additional_data' => 'Additional Data',
    'custom_data' => 'Custom Data',
    'no_additional_data' => 'No additional data',

    // Pages - CreateTenant
    'tenant_created_successfully' => '🎉 Tenant created successfully!',
    'tenant_created_message' => 'Tenant \':name\' has been created successfully.',
    'database_created' => '✅ Database \':name\' created',
    'migrations_executed' => '✅ Migrations executed',
    'seeders_executed' => '✅ :count seeders executed',
    'failed_to_create_tenant' => '❌ Failed to create tenant',
    'error' => 'Error: :message',
];




