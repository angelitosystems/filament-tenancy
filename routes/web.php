<?php

use Illuminate\Support\Facades\Route;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

// Alternative language switching route for Laravel 12 compatibility
Route::get('/switch-language/{locale}', function (string $locale) {
    if (in_array($locale, array_keys(LanguageSwitcher::getAvailableLocales()))) {
        LanguageSwitcher::setLocale($locale);
        
        // Flash message for debugging
        session()->flash('language_changed', $locale);
    }
    
    return redirect()->back();
})->name('language.switch.alt')->middleware('web');

// Original route (backup)
Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, array_keys(LanguageSwitcher::getAvailableLocales()))) {
        LanguageSwitcher::setLocale($locale);
    }
    return redirect()->back();
})->name('language.switch');
