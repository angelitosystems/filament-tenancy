<?php

return [
    // Navigation
    'plans' => 'Plans',
    'tenants' => 'Tenants',
    'roles' => 'Roles',
    'permissions' => 'Permissions',
    'users' => 'Users',
    'subscriptions' => 'Subscriptions',
    
    // Navigation Groups
    'billing_management' => 'Billing Management',
    'user_management' => 'User Management',
    
    // Model Labels
    'plan' => 'Plan',
    'tenant' => 'Tenant',
    'role' => 'Role',
    'permission' => 'Permission',
    'subscription' => 'Subscription',
    
    // Sections
    'plan_information' => 'Plan Information',
    'pricing' => 'Pricing',
    'features_limits' => 'Features & Limits',
    'display_settings' => 'Display Settings',
    'basic_information' => 'Basic Information',
    'domain_configuration' => 'Domain Configuration',
    'additional_data' => 'Additional Data',
    'role_information' => 'Role Information',
    
    // Fields
    'name' => 'Name',
    'slug' => 'Slug',
    'description' => 'Description',
    'color' => 'Color',
    'price' => 'Price',
    'billing_cycle' => 'Billing Cycle',
    'trial_days' => 'Trial Days',
    'is_active' => 'Active',
    'is_popular' => 'Popular',
    'is_featured' => 'Featured',
    'sort_order' => 'Sort Order',
    'features' => 'Features',
    'limits' => 'Limits',
    'domain' => 'Custom Domain',
    'subdomain' => 'Subdomain',
    'data' => 'Custom Data',
    'permissions' => 'Permissions',
    'language' => 'Language',
    'value' => 'Value',
    'expires_at' => 'Expiration Date',
    'plan' => 'Plan',
    
    // Billing Cycles
    'monthly' => 'Monthly',
    'yearly' => 'Yearly',
    'quarterly' => 'Quarterly',
    'lifetime' => 'Lifetime',
    
    // Plans Types
    'basic' => 'Basic',
    'premium' => 'Premium',
    'enterprise' => 'Enterprise',
    
    // Placeholders
    'describe_plan' => 'Describe the plan benefits and features',
    'describe_role' => 'Describe the role and its responsibilities',
    'feature_name' => 'Feature Name',
    'feature_description' => 'Description',
    'limit_name' => 'Limit Name',
    'limit_value' => 'Value',
    'select_plan' => 'Select a plan',
    'never_expires' => 'Never expires',
    'example_domain' => 'example.com',
    'subdomain_prefix' => 'clinic',
    
    // Helper Text
    'color_helper' => 'Color for badges and UI elements',
    'trial_days_helper' => 'Number of free trial days',
    'active_helper' => 'Whether this plan is available for new subscriptions',
    'sort_order_helper' => 'Lower numbers appear first',
    'popular_helper' => 'Highlight this plan as popular',
    'featured_helper' => 'Show this plan in featured section',
    'permissions_helper' => 'Select the permissions that this role should have',
    'full_domain' => 'Full domain name (e.g., clinic.com)',
    'subdomain_helper' => 'Subdomain prefix (e.g., clinic.yourdomain.com)',
    'custom_data' => 'Store additional tenant-specific configuration',
    
    // Filters
    'all_plans' => 'All plans',
    'active_plans' => 'Active plans',
    'inactive_plans' => 'Inactive plans',
    'popular_plans' => 'Popular plans',
    'regular_plans' => 'Regular plans',
    'featured_plans' => 'Featured plans',
    'active_status' => 'Active Status',
    'expired' => 'Expired',
    'has_permissions' => 'Has Permissions',
    'no_permissions' => 'No Permissions',
    'has_users' => 'Has Users',
    'no_users' => 'No Users',
    
    // Actions
    'view' => 'View',
    'edit' => 'Edit',
    'create' => 'Create',
    'delete' => 'Delete',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'restore' => 'Restore',
    'add_feature' => 'Add Feature',
    'add_limit' => 'Add Limit',
    'switch_language' => 'Switch Language',
    
    // Messages
    'plan_created' => 'Plan created successfully.',
    'plan_updated' => 'Plan updated successfully.',
    'plan_deleted' => 'Plan deleted successfully.',
    'tenant_created' => 'Tenant created successfully.',
    'tenant_updated' => 'Tenant updated successfully.',
    'tenant_deleted' => 'Tenant deleted successfully.',
    'tenant_restored' => 'Tenant restored successfully.',
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
    
    // Error Messages
    'cannot_delete_tenant_with_users' => 'Cannot delete tenant with associated users. Please reassign or delete users first.',
    'cannot_delete_role_with_users' => 'Cannot delete role with assigned users. Please reassign users first.',
    'cannot_delete_role_with_users_specific' => 'Cannot delete role ":role" with :count assigned users. Please reassign users first.',
    
    // Validation
    'name_required' => 'The name field is required.',
    'slug_required' => 'The slug field is required.',
    'slug_unique' => 'The slug must be unique.',
    'domain_unique' => 'The domain must be unique.',
    'subdomain_unique' => 'The subdomain must be unique.',
    'price_required' => 'The price field is required.',
    'billing_cycle_required' => 'The billing cycle field is required.',
];
