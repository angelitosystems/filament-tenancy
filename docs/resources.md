# Resources Documentation

This document provides a comprehensive overview of all available Filament resources in the Filament Tenancy package.

## Overview

The package provides separate resource sets for:
- **Landlord/Admin Panel** - Full management capabilities
- **Tenant Panel** - Limited, tenant-specific functionality

---

## Landlord Resources

### 🏢 Tenant Management

#### TenantResource
Full tenant management with CRUD operations, domain configuration, and database settings.

**Features:**
- Complete tenant lifecycle management
- Domain/subdomain configuration
- Database connection settings
- Status management
- Bulk operations

**Navigation:** `Tenants` (root level)

---

### 💳 Billing Management

#### PlanResource
Manage subscription plans available to tenants.

**Features:**
- Plan creation and configuration
- Pricing and billing cycle setup
- Feature and limits management
- Popular/featured plan designation
- Color coding for UI elements

**Navigation:** `Billing Management` → `Plans`

#### SubscriptionResource
Manage tenant subscriptions and billing.

**Features:**
- Subscription lifecycle management
- Status tracking (active, cancelled, expired, etc.)
- Auto-renewal settings
- Payment method tracking
- Trial period management
- Expiration monitoring

**Navigation:** `Billing Management` → `Subscriptions`

---

### 👥 User Management

#### RoleResource
Manage roles and their permissions.

**Features:**
- Role creation and assignment
- Permission management
- User assignment tracking
- Color-coded roles
- Bulk role operations

**Navigation:** `User Management` → `Roles`

#### PermissionResource
Manage system permissions.

**Features:**
- Permission creation and grouping
- System permission protection
- Role assignment tracking
- User assignment tracking
- Permission categorization

**Navigation:** `User Management` → `Permissions`

---

## Tenant Resources

### 💳 Billing

#### Tenant\PlanResource
View available subscription plans and manage subscriptions.

**Features:**
- Browse available plans (read-only)
- View plan details and features
- Subscribe to plans
- Current subscription status widget
- Pricing comparison

**Navigation:** `Billing` → `Plans`

**Restrictions:**
- Cannot create/edit plans
- Can only view available plans
- Can subscribe to plans

---

### 👥 User Management

#### Tenant\RoleResource
Manage tenant-specific roles and permissions.

**Features:**
- Role creation and management
- Permission assignment
- User role assignment
- Role-based access control

**Navigation:** `User Management` → `Roles`

**Scope:**
- Only shows roles for current tenant
- Only shows users for current tenant
- Tenant-isolated permissions

---

## Resource Features

### Common Features

All resources include:

#### **Forms**
- Auto-generated slugs from names
- Live validation
- Color pickers for visual elements
- Rich text descriptions
- Key-value metadata fields

#### **Tables**
- Searchable columns
- Sortable fields
- Advanced filtering
- Bulk actions
- Export capabilities

#### **Actions**
- View, Edit, Delete operations
- Custom actions (e.g., Subscribe, Cancel)
- Confirmation dialogs
- Success/error notifications

#### **Infolists**
- Detailed record views
- Badge displays for status
- Color-coded elements
- Timestamp tracking
- Relationship displays

### Advanced Features

#### **Tenant Isolation**
- Automatic tenant context switching
- Tenant-scoped queries
- Secure data separation
- Cross-tenant prevention

#### **Permission Integration**
- Role-based access control
- Permission checking
- Secure operations
- Audit trails

#### **Status Management**
- Visual status indicators
- State-based actions
- Workflow controls
- Status transitions

---

## Navigation Structure

### Landlord Panel
```
📋 Dashboard
├── 🏢 Tenants
├── 💳 Billing Management
│   ├── 💳 Plans
│   └── 📄 Subscriptions
└── 👥 User Management
    ├── 🛡️ Roles
    └── 🔑 Permissions
```

### Tenant Panel
```
📋 Dashboard
├── 💳 Billing
│   └── 💳 Plans
└── 👥 User Management
    └── 🛡️ Roles
```

---

## Usage Examples

### Creating a Plan
```php
// Via Filament Admin Panel
1. Navigate to Billing Management → Plans
2. Click "New Plan"
3. Fill in plan details:
   - Name: "Professional"
   - Price: 29.99
   - Billing Cycle: "monthly"
   - Features: ["API Access", "Priority Support"]
4. Save plan
```

### Managing Subscriptions
```php
// Via Filament Admin Panel
1. Navigate to Billing Management → Subscriptions
2. View active subscriptions
3. Use actions:
   - Cancel subscription
   - Reactivate expired
   - Update payment method
```

### Tenant Role Management
```php
// Via Tenant Panel
1. Navigate to User Management → Roles
2. Create new role: "Content Manager"
3. Assign permissions: ["create_posts", "edit_posts"]
4. Assign to users
```

---

## Customization

### Extending Resources
```php
// Custom tenant resource
class CustomTenantResource extends Resource
{
    protected static ?string $model = CustomModel::class;
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            // Custom form fields
        ]);
    }
}
```

### Adding Custom Actions
```php
// Add custom action to existing resource
public static function table(Table $table): Table
{
    return $table->actions([
        Tables\Actions\Action::make('custom_action')
            ->label('Custom Action')
            ->action(function ($record) {
                // Custom logic
            }),
    ]);
}
```

### Custom Filters
```php
// Add custom filter
public static function table(Table $table): Table
{
    return $table->filters([
        Tables\Actions\Filter::make('custom_filter')
            ->query(fn ($query) => $query->where('field', 'value'))
            ->label('Custom Filter'),
    ]);
}
```

---

## Security Considerations

### Tenant Isolation
- All tenant resources are automatically scoped
- Cross-tenant data access is prevented
- Database connections are tenant-specific

### Permission Checks
- All operations require appropriate permissions
- System permissions are protected
- Role-based access control is enforced

### Data Validation
- Input validation on all forms
- Sanitization of user data
- Protection against injection attacks

---

## Best Practices

### Resource Organization
- Group related resources logically
- Use consistent naming conventions
- Implement proper navigation hierarchy

### Performance Optimization
- Use efficient queries with relationships
- Implement proper indexing
- Cache frequently accessed data

### User Experience
- Provide clear labels and descriptions
- Use appropriate visual indicators
- Implement intuitive workflows

### Security
- Always validate user input
- Implement proper permission checks
- Use tenant-scoped queries

---

## Troubleshooting

### Common Issues

#### **Resources Not Showing**
- Check plugin registration
- Verify resource class imports
- Ensure proper namespace usage

#### **Permission Errors**
- Verify role assignments
- Check permission definitions
- Ensure tenant context is active

#### **Data Not Isolated**
- Check model traits
- Verify tenant middleware
- Ensure proper database connections

### Debug Tips

1. **Check Resource Registration**
   ```php
   // Verify resources are registered
   filament()->getResources()
   ```

2. **Check Permissions**
   ```php
   // Verify user permissions
   auth()->user()->hasPermissionTo('permission_name')
   ```

3. **Check Tenant Context**
   ```php
   // Verify tenant is active
   tenancy()->current()
   ```

---

## Integration Examples

### Custom Widgets
```php
class SubscriptionStatsWidget extends Widget
{
    protected static string $view = 'widgets.subscription-stats';
    
    protected function getData(): array
    {
        return [
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'revenue' => Subscription::sum('price'),
        ];
    }
}
```

### Custom Pages
```php
class BillingOverviewPage extends Page
{
    protected static string $view = 'filament.pages.billing-overview';
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Billing Overview';
    
    public function mount(): void
    {
        // Custom initialization
    }
}
```

### Custom Actions
```php
public static function table(Table $table): Table
{
    return $table->actions([
        Tables\Actions\Action::make('upgrade_plan')
            ->label('Upgrade')
            ->color('success')
            ->action(function ($record) {
                // Upgrade subscription logic
                $record->upgradeToNextPlan();
            })
            ->visible(fn ($record) => $record->canUpgrade()),
    ]);
}
```

---

This documentation provides a comprehensive guide to all available resources and their capabilities. For more specific implementation details, refer to the individual resource files and their method documentation.
