<?php

namespace App\Providers;

use App\Models\Bapb;
use App\Models\Belanja;
use App\Models\Kwitansi;
use App\Models\NotaPesanan;
use App\Models\SchoolProfile;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        Belanja::observe(ActivityObserver::class);
        NotaPesanan::observe(ActivityObserver::class);
        Kwitansi::observe(ActivityObserver::class);
        Bapb::observe(ActivityObserver::class);
        SchoolProfile::observe(ActivityObserver::class);
        User::observe(ActivityObserver::class);

        Event::listen(Login::class, function (Login $event): void {
            ActivityLogger::record(
                (int) $event->user->getAuthIdentifier(),
                'login',
                'Masuk ke SILPJ'
            );
        });

        Event::listen(Failed::class, function (Failed $event): void {
            if ($event->user) {
                ActivityLogger::record(
                    (int) $event->user->getAuthIdentifier(),
                    'login_failed',
                    'Percobaan login gagal'
                );
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user) {
                ActivityLogger::record(
                    (int) $event->user->getAuthIdentifier(),
                    'logout',
                    'Keluar dari SILPJ'
                );
            }
        });
    }
}
