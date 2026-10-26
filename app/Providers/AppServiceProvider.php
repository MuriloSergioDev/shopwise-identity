<?php

declare(strict_types=1);

namespace App\Providers;

use App\Tokens\DatabaseKeySource;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Shopwise\Kit\Auth\KeySource;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(KeySource::class, DatabaseKeySource::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Requests come from the BFF, so the IP alone would throttle every shopper together; key on the email too.
        RateLimiter::for('credentials', fn (Request $request): Limit => Limit::perMinute(10)
            ->by(mb_strtolower($request->string('email')->toString()).'|'.$request->ip()));
    }
}
