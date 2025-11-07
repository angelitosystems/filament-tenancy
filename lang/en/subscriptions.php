<?php

return [
    // Sections
    'section_subscription_info' => 'Subscription Information',
    'section_subscription_period' => 'Subscription Period',
    'section_status_settings' => 'Status & Settings',
    'section_additional_info' => 'Additional Information',

    // Fields
    'tenant' => 'Tenant',
    'plan' => 'Plan',
    'price' => 'Price',
    'billing_cycle' => 'Billing Cycle',
    'starts_at' => 'Start Date',
    'ends_at' => 'End Date',
    'ends_at_helper' => 'Leave empty for lifetime subscriptions',
    'trial_ends_at' => 'Trial Ends At',
    'trial_ends_at_helper' => 'Leave empty for no trial',
    'next_billing_at' => 'Next Billing Date',
    'next_billing_at_helper' => 'Calculated automatically based on billing cycle',
    'status' => 'Status',
    'auto_renew' => 'Auto Renew',
    'auto_renew_helper' => 'Automatically renew subscription when it expires',
    'payment_method' => 'Payment Method',
    'payment_method_placeholder' => 'stripe, paypal, bank_transfer, etc.',
    'external_id' => 'External ID',
    'external_id_placeholder' => 'ID from payment provider',
    'external_id_helper' => 'External subscription ID from payment gateway',
    'seller' => 'Seller',
    'seller_helper' => 'Seller associated with this subscription for commissions',
    'payment_link' => 'Payment Link',
    'notes' => 'Notes',
    'notes_placeholder' => 'Internal notes about this subscription',
    'metadata' => 'Metadata',
    'metadata_key' => 'Key',
    'metadata_value' => 'Value',
    'metadata_add' => 'Add Metadata',

    // Billing Cycles
    'billing_cycle_monthly' => 'Monthly',
    'billing_cycle_yearly' => 'Yearly',
    'billing_cycle_quarterly' => 'Quarterly',
    'billing_cycle_lifetime' => 'Lifetime',

    // Statuses
    'status_active' => 'Active',
    'status_inactive' => 'Inactive',
    'status_cancelled' => 'Cancelled',
    'status_expired' => 'Expired',
    'status_suspended' => 'Suspended',
    'status_pending' => 'Pending',

    // Table columns
    'auto_renew' => 'Auto Renew',
    'lifetime' => 'Lifetime',
    'created_at' => 'Created At',

    // Filters
    'filter_expiring_soon' => 'Expiring Soon (30 days)',
    'filter_expired' => 'Expired',
    'filter_in_trial' => 'In Trial',
    'filter_all_subscriptions' => 'All subscriptions',
    'filter_auto_renew_enabled' => 'Auto renew enabled',
    'filter_auto_renew_disabled' => 'Auto renew disabled',

    // Actions
    'pay_with_paypal' => 'Pay with PayPal',
    'create_paypal_subscription' => 'Create PayPal Subscription',
    'cancel_paypal_subscription' => 'Cancel PayPal Subscription',
    'cancel_subscription' => 'Cancel Subscription',
    'reactivate' => 'Reactivate',
    'generate_payment_link' => 'Generate Payment Link',
    'copy_payment_link' => 'Copy Payment Link',
    'payment_link_generated' => 'Payment Link Generated',
    'payment_link_generated_message' => 'Payment link has been generated successfully. The tenant can use this link to renew their subscription.',
    'payment_link_error' => 'Could not generate payment link. Please try again.',
    'payment_link_copied' => 'Link Copied',
    'payment_link_copied_message' => 'Payment link has been copied to clipboard.',
    'paypal_payment_failed' => 'Failed to create PayPal payment. Please try again.',
    'paypal_subscription_failed' => 'Failed to create PayPal subscription. Please try again.',
    'paypal_subscription_cancelled' => 'PayPal subscription cancelled successfully',
    'subscription_cancelled' => 'Subscription cancelled successfully',
    'subscription_reactivated' => 'Subscription reactivated successfully',
    'error' => 'Error',
    
    // Infolist labels
    'subscription_information' => 'Subscription Information',
    'subscription_period' => 'Subscription Period',
    'status_settings' => 'Status & Settings',
    'additional_information' => 'Additional Information',
    'timestamps' => 'Timestamps',
    'tenant_label' => 'Tenant',
    'plan_label' => 'Plan',
    'no_trial' => 'No trial',
    'not_specified' => 'Not specified',
    'no_notes_label' => 'No notes',
    'n_a' => 'N/A',
];

