<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Util\PremClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('PremClient', function ($app) {
            return new PremClient();
        });

        $this->app->singleton('TmdbClient', function ($app) {
            $tmdbToken = new \Tmdb\ApiToken(config('services.tmdb.api_key'));
            return new \Tmdb\Client($tmdbToken, [
                'cache' => [
                    'path' => config('luntiq.tmdb.cache'),
                ],
            ]);
        });
    }
}
