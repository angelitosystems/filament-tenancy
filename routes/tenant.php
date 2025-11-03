<?php

use Illuminate\Support\Facades\Route;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

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
