<?php

namespace App\Providers;

use App\Enums\BuiltInRole;
use App\Models\DB1\SysUser;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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
        // Register custom FCM notification channel
        Notification::resolved(function ($service) {
            $service->extend('fcm', function ($app) {
                return $app->make(FcmChannel::class);
            });
        });

        Gate::before(function (SysUser $user, $ability) {
            if ($user->hasRole(BuiltInRole::SUPER_ADMIN->value)) {
                return true;
            }

            return null;
        });

        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Paginator::useTailwind();
    }
}
