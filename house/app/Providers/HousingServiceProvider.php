<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HousingService;

class HousingServiceProvider extends ServiceProvider
{
    public function boot() {
    }

    public function register()
    {
        $this->app->singleton('housing', function () {
            return new HousingService();
        });

        $this->app->bind('App\Contracts\HousingContract', function () {
            return new HousingService();
        });
    }
}