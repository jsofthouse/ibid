<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route reset password bernama admin.reset-password (bukan konvensi
        // default password.reset) karena diprefix 'admin.' - beritahu
        // notifikasi bawaan Laravel supaya link di email mengarah ke sana.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('admin.reset-password', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
