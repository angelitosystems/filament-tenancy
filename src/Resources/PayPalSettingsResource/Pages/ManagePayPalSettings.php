<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PayPalSettingsResource\Pages;

use AngelitoSystems\FilamentTenancy\Models\PayPalSettings;
use AngelitoSystems\FilamentTenancy\Resources\PayPalSettingsResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class ManagePayPalSettings extends EditRecord
{
    protected static string $resource = PayPalSettingsResource::class;

    protected static ?string $title = 'Configuración PayPal';

    public function mount(int | string | null $record = null): void
    {
        // For singleton pattern, always use the current settings (ID 1)
        $settings = PayPalSettings::current();
        parent::mount($settings->id);
    }

    protected function resolveRecord(int | string $key): PayPalSettings
    {
        // Always return the current singleton settings
        return PayPalSettings::current();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label('Probar Conexión')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Probar Conexión con PayPal')
                ->modalDescription('Esto verificará que tus credenciales de PayPal sean correctas.')
                ->action(function () {
                    $settings = PayPalSettings::current();

                    if (!$settings->is_enabled) {
                        Notification::make()
                            ->title('PayPal no está habilitado')
                            ->body('Por favor habilita PayPal primero.')
                            ->warning()
                            ->send();
                        return;
                    }

                    if (!$settings->client_id || !$settings->client_secret) {
                        Notification::make()
                            ->title('Credenciales faltantes')
                            ->body('Por favor configura tu Client ID y Client Secret.')
                            ->warning()
                            ->send();
                        return;
                    }

                    try {
                        Cache::forget('paypal_access_token_sandbox');
                        Cache::forget('paypal_access_token_live');

                        $paypalService = app(\AngelitoSystems\FilamentTenancy\Support\PayPalService::class);
                        $token = $paypalService->getAccessToken();

                        if ($token) {
                            Notification::make()
                                ->title('Conexión exitosa')
                                ->body('Las credenciales de PayPal son correctas.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error de conexión')
                                ->body('No se pudo obtener el token de acceso. Verifica tus credenciales.')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Error al probar conexión: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn() => PayPalSettings::current()->is_enabled),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear PayPal cache when settings are updated
        Cache::forget('paypal_access_token_sandbox');
        Cache::forget('paypal_access_token_live');
        Cache::forget('paypal_product_id');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
