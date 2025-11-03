<?php

return [
    // Navigation
    'navigation' => [
        'tenants' => 'Tenants',
        'plans' => 'Plans',
        'roles' => 'Roles',
        'permissions' => 'Permissions',
        'users' => 'Users',
        'subscriptions' => 'Subscriptions',
    ],

    // Navigation Groups
    'navigation_groups' => [
        'billing_management' => 'Billing Management',
        'user_management' => 'User Management',
        'admin_management' => 'Admin Management',
    ],

    // Resource Labels
    'resources' => [
        'tenant' => [
            'singular' => 'Tenant',
            'plural' => 'Tenants',
            'breadcrumb' => 'Tenants',
        ],
        'plan' => [
            'singular' => 'Plan',
            'plural' => 'Plans',
            'breadcrumb' => 'Plans',
        ],
        'role' => [
            'singular' => 'Role',
            'plural' => 'Roles',
            'breadcrumb' => 'Roles',
        ],
        'permission' => [
            'singular' => 'Permission',
            'plural' => 'Permissions',
            'breadcrumb' => 'Permissions',
        ],
        'subscription' => [
            'singular' => 'Subscription',
            'plural' => 'Subscriptions',
            'breadcrumb' => 'Subscriptions',
        ],
    ],

    // Form Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'domain_configuration' => 'Domain Configuration',
        'additional_data' => 'Additional Data',
        'plan_information' => 'Plan Information',
        'pricing' => 'Pricing',
        'features_limits' => 'Features & Limits',
        'display_settings' => 'Display Settings',
        'role_information' => 'Role Information',
        'permissions' => 'Permissions',
    ],

    // Form Fields
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'is_active' => 'Active',
        'plan' => 'Plan',
        'expires_at' => 'Expiration Date',
        'domain' => 'Custom Domain',
        'subdomain' => 'Subdomain',
        'data' => 'Custom Data',
        'description' => 'Description',
        'color' => 'Color',
        'price' => 'Price',
        'billing_cycle' => 'Billing Cycle',
        'trial_days' => 'Trial Days',
        'features' => 'Features',
        'limits' => 'Limits',
        'sort_order' => 'Sort Order',
        'is_popular' => 'Popular',
        'is_featured' => 'Featured',
        'permissions' => 'Permissions',
        'language' => 'Language',
        'value' => 'Value',
    ],

    // Billing Cycles
    'billing_cycles' => [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'quarterly' => 'Quarterly',
        'lifetime' => 'Lifetime',
    ],

    // Plans
    'plans' => [
        'basic' => 'Basic',
        'premium' => 'Premium',
        'enterprise' => 'Enterprise',
    ],

    // Table Columns
    'table' => [
        'id' => 'ID',
        'name' => 'Name',
        'slug' => 'Slug',
        'domain' => 'Domain',
        'subdomain' => 'Subdomain',
        'active' => 'Active',
        'plan' => 'Plan',
        'expires' => 'Expires',
        'created' => 'Created',
        'updated' => 'Updated',
        'price' => 'Price',
        'billing_cycle' => 'Billing Cycle',
        'popular' => 'Popular',
        'featured' => 'Featured',
        'permissions_count' => 'Permissions',
        'users_count' => 'Users',
        'description' => 'Description',
    ],

    // Filters
    'filters' => [
        'active_status' => 'Active Status',
        'expired' => 'Expired',
        'has_permissions' => 'Has Permissions',
        'no_permissions' => 'No Permissions',
        'has_users' => 'Has Users',
        'no_users' => 'No Users',
        'all_plans' => 'All plans',
        'active_plans' => 'Active plans',
        'inactive_plans' => 'Inactive plans',
        'popular_plans' => 'Popular plans',
        'regular_plans' => 'Regular plans',
        'featured_plans' => 'Featured plans',
    ],

    // Actions
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'view' => 'View',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
        'add_feature' => 'Add Feature',
        'add_limit' => 'Add Limit',
        'switch_language' => 'Switch Language',
        'crear' => 'Create',
        'editar' => 'Edit',
        'ver' => 'View',
        'borrar' => 'Delete',
    ],

    // Buttons and UI Elements
    'buttons' => [
        'create' => 'Create',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'view' => 'View',
        'back' => 'Back',
        'next' => 'Next',
        'previous' => 'Previous',
        'search' => 'Search',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'export' => 'Export',
        'import' => 'Import',
        'refresh' => 'Refresh',
    ],

    // Status and States
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'published' => 'Published',
        'draft' => 'Draft',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
        'valid' => 'Valid',
    ],

    // Placeholders
    'placeholders' => [
        'select_plan' => 'Select a plan',
        'never_expires' => 'Never expires',
        'example_domain' => 'example.com',
        'subdomain_prefix' => 'clinic',
        'describe_plan' => 'Describe the plan benefits and features',
        'describe_role' => 'Describe the role and its responsibilities',
        'feature_name' => 'Feature Name',
        'feature_description' => 'Description',
        'limit_name' => 'Limit Name',
        'limit_value' => 'Value',
    ],

    // Helper Text
    'helpers' => [
        'full_domain' => 'Full domain name (e.g., clinic.com)',
        'subdomain_helper' => 'Subdomain prefix (e.g., clinic.yourdomain.com)',
        'custom_data' => 'Store additional tenant-specific configuration',
        'color_helper' => 'Color for badges and UI elements',
        'trial_days_helper' => 'Number of free trial days',
        'active_helper' => 'Whether this plan is available for new subscriptions',
        'sort_order_helper' => 'Lower numbers appear first',
        'popular_helper' => 'Highlight this plan as popular',
        'featured_helper' => 'Show this plan in featured section',
        'permissions_helper' => 'Select the permissions that this role should have',
    ],

    // Messages
    'messages' => [
        'tenant_created' => 'Tenant created successfully.',
        'tenant_updated' => 'Tenant updated successfully.',
        'tenant_deleted' => 'Tenant deleted successfully.',
        'tenant_restored' => 'Tenant restored successfully.',
        'cannot_delete_tenant_with_users' => 'Cannot delete tenant with associated users. Please reassign or delete users first.',
        'cannot_delete_role_with_users' => 'Cannot delete role with assigned users. Please reassign users first.',
        'cannot_delete_role_with_users_specific' => 'Cannot delete role ":role" with :count assigned users. Please reassign users first.',
        'plan_created' => 'Plan created successfully.',
        'plan_updated' => 'Plan updated successfully.',
        'plan_deleted' => 'Plan deleted successfully.',
        'role_created' => 'Role created successfully.',
        'role_updated' => 'Role updated successfully.',
        'role_deleted' => 'Role deleted successfully.',
        'permission_created' => 'Permission created successfully.',
        'permission_updated' => 'Permission updated successfully.',
        'permission_deleted' => 'Permission deleted successfully.',
        'subscription_created' => 'Subscription created successfully.',
        'subscription_updated' => 'Subscription updated successfully.',
        'subscription_cancelled' => 'Subscription cancelled successfully.',
        'language_changed' => 'Language changed to :language successfully.',
        'no_plan' => 'No plan',
        'never' => 'Never',
        'na' => 'N/A',
    ],

    // Validation
    'validation' => [
        'name_required' => 'The name field is required.',
        'slug_required' => 'The slug field is required.',
        'slug_unique' => 'The slug must be unique.',
        'domain_unique' => 'The domain must be unique.',
        'subdomain_unique' => 'The subdomain must be unique.',
        'price_required' => 'The price field is required.',
        'billing_cycle_required' => 'The billing cycle field is required.',
    ],

    // Filament specific translations
    'filament' => [
        'actions' => [
            'view' => 'View',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'create' => 'Create',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'restore' => 'Restore',
            'force_delete' => 'Force Delete',
        ],
        'table' => [
            'search' => 'Search',
            'filter' => 'Filter',
            'reset' => 'Reset',
            'per_page' => 'per page',
            'showing' => 'Showing',
            'to' => 'to',
            'of' => 'of',
            'results' => 'results',
        ],
    ],
];
