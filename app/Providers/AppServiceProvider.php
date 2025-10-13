<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, function ($event) {
            Log::info('EVENT_LOGIN', [
                'email' => $event->user->email ?? null,
                'id' => $event->user->id ?? null,
            ]);
        });

        Event::listen(Failed::class, function ($event) {
            Log::warning('EVENT_LOGIN_FAILED', [
                'email' => $event->credentials['email'] ?? null,
            ]);
        });
    }
}
