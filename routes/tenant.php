<?php

use Illuminate\Support\Facades\Route;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;
use AngelitoSystems\FilamentTenancy\Http\Controllers\InvoicePdfController;

Route::get('/language/{locale}', function (string $locale) {
    \Log::info('Language switch route called', [
        'requested_locale' => $locale,
        'available_locales' => array_keys(LanguageSwitcher::getAvailableLocales()),
        'session_before' => session('locale'),
        'url' => request()->url(),
        'referer' => request()->header('referer')
    ]);
    
    if (in_array($locale, array_keys(LanguageSwitcher::getAvailableLocales()))) {
        $result = LanguageSwitcher::setLocale($locale);
        \Log::info('Language switch result', ['success' => $result, 'session_after' => session('locale')]);
    } else {
        \Log::warning('Invalid locale requested', ['locale' => $locale]);
    }
    
    return redirect()->back();
})->name('language.switch');

// Invoice PDF routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/invoices/{invoice}/pdf/download', [InvoicePdfController::class, 'generate'])
        ->name('tenant.invoices.pdf.download');
    
    Route::get('/invoices/{invoice}/pdf/view', [InvoicePdfController::class, 'view'])
        ->name('tenant.invoices.pdf.view');
});
