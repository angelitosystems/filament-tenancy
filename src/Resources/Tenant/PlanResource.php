<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;

class PlanResource extends Resource
{
    use HasSimpleTranslations;

    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Override translation prefix to use 'plans' namespace
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'plans';
    }

    /**
     * Override translation keys
     */
    public static function getNavigationKey(): string
    {
        return 'plans';
    }

    public static function getModelKey(): string
    {
        return 'plan';
    }

    public static function getPluralModelKey(): string
    {
        return 'plans';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'plans';
    }

    /**
     * Define el grupo de navegación
     */
    public static function getNavigationGroupKey(): ?string
    {
        return 'billing';
    }

    protected static function getNavigationGroupLabel(): ?string
    {
        return 'billing';
    }

    public static function canCreate(): bool
    {
        return false; // Tenants cannot create plans, only view available plans
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(static::__('name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge(function (Plan $record) {
                        $tenant = Tenancy::current();
                        if (!$tenant) {
                            return null;
                        }
                        
                        $activeSubscription = $tenant->subscriptions()
                            ->where('status', Subscription::STATUS_ACTIVE)
                            ->where('plan_id', $record->id)
                            ->where(function ($query) {
                                $query->whereNull('ends_at')
                                    ->orWhere('ends_at', '>', now());
                            })
                            ->first();
                        
                        return $activeSubscription ? static::__('current_plan') : null;
                    })
                    ->color('success'),

                Tables\Columns\TextColumn::make('description')
                    ->label(static::__('description'))
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('price')
                    ->label(static::__('price'))
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label(static::__('billing_cycle'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'monthly' => 'blue',
                        'yearly' => 'green',
                        'quarterly' => 'yellow',
                        'lifetime' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => static::__($state)),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(static::__('available')),

                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->label(static::__('popular'))
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->label(static::__('billing_cycle'))
                    ->options([
                        'monthly' => static::__('monthly'),
                        'yearly' => static::__('yearly'),
                        'quarterly' => static::__('quarterly'),
                        'lifetime' => static::__('lifetime'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(static::__('available'))
                    ->placeholder(static::__('all_plans'))
                    ->trueLabel(static::__('available_plans'))
                    ->falseLabel(static::__('unavailable_plans')),

                Tables\Filters\TernaryFilter::make('is_popular')
                    ->label(static::__('popular'))
                    ->placeholder(static::__('all_plans'))
                    ->trueLabel(static::__('popular_plans'))
                    ->falseLabel(static::__('regular_plans')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(static::__('view_details')),
                Action::make('subscribe')
                    ->label(static::__('subscribe'))
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(function (Plan $record) {
                        // No mostrar si el plan no está activo
                        if (!$record->is_active) {
                            return false;
                        }
                        
                        // No mostrar si es el plan actual del tenant
                        $tenant = Tenancy::current();
                        if (!$tenant) {
                            return true;
                        }
                        
                        $activeSubscription = $tenant->subscriptions()
                            ->where('status', Subscription::STATUS_ACTIVE)
                            ->where('plan_id', $record->id)
                            ->where(function ($query) {
                                $query->whereNull('ends_at')
                                    ->orWhere('ends_at', '>', now());
                            })
                            ->first();
                        
                        // Si tiene suscripción activa para este plan, no mostrar el botón
                        return !$activeSubscription;
                    })
                    ->modalHeading(static::__('completing_payment'))
                    ->modalDescription(static::__('please_complete_payment'))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(static::__('cancel'))
                    ->mountUsing(function (Plan $record, $livewire) {
                        // Create subscription before opening modal
                        static::initializeSubscription($record, $livewire);
                    })
                    ->modalContent(function (Plan $record) {
                        return view('filament-tenancy::components.paypal-checkout-iframe', [
                            'plan' => $record,
                            'subscriptionId' => session()->get('pending_paypal_subscription_id'),
                            'approvalUrl' => session()->get('pending_paypal_approval_url'),
                        ]);
                    }),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages\ListPlans::route('/'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages\ViewPlan::route('/{record}'),
        ];
    }

    /**
     * Initialize subscription for a plan.
     * Creates subscription in DB and PayPal, then opens checkout modal.
     */
    public static function initializeSubscription(Plan $plan, $livewire = null): void
    {
        $tenant = Tenancy::current();

        if (!$tenant) {
            \Filament\Notifications\Notification::make()
                ->title(static::__('error'))
                ->body(__('filament-tenancy::tenancy.errors.tenant_not_found'))
                ->danger()
                ->send();
            return;
        }

        // Check if PayPal is enabled
        $paypalService = app(PayPalService::class);
        if (!$paypalService->isEnabled()) {
            \Filament\Notifications\Notification::make()
                ->title(static::__('error'))
                ->body(__('filament-tenancy::plans.paypal_not_configured'))
                ->danger()
                ->send();
            return;
        }

        try {
            // Calculate ends_at based on billing cycle
            $endsAt = null;
            if ($plan->billing_cycle !== 'lifetime') {
                $endsAt = match ($plan->billing_cycle) {
                    'monthly' => now()->addMonth(),
                    'yearly' => now()->addYear(),
                    'quarterly' => now()->addMonths(3),
                    default => now()->addMonth(),
                };
            }

            // Create subscription in database
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_PENDING,
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'ends_at' => $endsAt,
            ]);

            // Create subscription in PayPal
            $paypalSubscription = $paypalService->createSubscription($subscription);

            if (!$paypalSubscription) {
                $subscription->delete(); // Clean up if PayPal creation failed
                \Filament\Notifications\Notification::make()
                    ->title(static::__('error'))
                    ->body(__('filament-tenancy::plans.paypal_subscription_failed'))
                    ->danger()
                    ->send();
                return;
            }

            // Get approval URL from PayPal response
            $approvalUrl = null;
            if (isset($paypalSubscription['links'])) {
                $approveLink = collect($paypalSubscription['links'])->firstWhere('rel', 'approve');
                if ($approveLink) {
                    $approvalUrl = $approveLink['href'];
                }
            }

            if (!$approvalUrl) {
                \Filament\Notifications\Notification::make()
                    ->title(static::__('error'))
                    ->body(__('filament-tenancy::plans.paypal_approval_url_not_found'))
                    ->danger()
                    ->send();
                return;
            }

            // Store approval URL in subscription for retrieval
            $subscription->update([
                'payment_link' => $approvalUrl,
                'payment_link_expires_at' => now()->addHours(24),
            ]);

            // Store in session for the modal form to pick up
            session()->put('pending_paypal_subscription_id', $subscription->id);
            session()->put('pending_paypal_approval_url', $approvalUrl);
        } catch (\Exception $e) {
            Log::error('Failed to initialize subscription', [
                'plan_id' => $plan->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            \Filament\Notifications\Notification::make()
                ->title(static::__('error'))
                ->body(__('filament-tenancy::plans.subscription_creation_failed'))
                ->danger()
                ->send();
        }
    }
}
