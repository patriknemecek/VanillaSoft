<?php

namespace App\Providers;

use App\Services\ElasticsearchService;
use App\Services\RedisService;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use App\Utilities\Contracts\RedisHelperInterface;
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
        $this->app->bind(ElasticsearchHelperInterface::class, function () {
            return new ElasticsearchService();
        });

        $this->app->bind(RedisHelperInterface::class, function () {
            return new RedisService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
