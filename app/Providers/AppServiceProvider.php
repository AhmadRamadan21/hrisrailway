<?php

namespace App\Providers;

use App\Models\AdminNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Deteksi HTTPS via proxy Ngrok secara aman
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        } elseif (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'ngrok')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $unreadNotifications = AdminNotification::where('is_read', false)->count();
                $recentNotifications = AdminNotification::with('user')
                    ->latest()
                    ->take(5)
                    ->get();
            } else {
                $unreadNotifications = 0;
                $recentNotifications = collect();
            }

            $view->with(compact('unreadNotifications', 'recentNotifications'));
        });
    }
}