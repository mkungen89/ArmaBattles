<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use SocialiteProviders\Steam\Provider;

class SocialiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $socialite = $this->app->make(Factory::class);

        $socialite->extend('steam', function () use ($socialite) {
            $config = config('services.steam');

            return $socialite->buildProvider(Provider::class, $config);
        });
    }
}
