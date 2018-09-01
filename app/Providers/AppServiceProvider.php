<?php

namespace App\Providers;

use App\Util\PremClient;
use App\Util\DownloadManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Downloader', function ($app) {
            return new DownloadManager();
        });

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
