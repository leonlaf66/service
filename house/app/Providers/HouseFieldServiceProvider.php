<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HouseMlsFieldService;
use App\Services\HouseListhubFieldService;

class HouseFieldServiceProvider extends ServiceProvider
{
    public function boot() {
    }

    public function register()
    {
        $this->app->singleton('mlsHouseField', function () {
            return new HouseMlsFieldService();
        });

        $this->app->singleton('listhubHouseField', function () {
            return new HouseListhubFieldService();
        });

        $this->app->bind('App\Contracts\HouseFieldContract', function () {
            return new HouseMlsFieldService();
        });

        $this->app->bind('App\Contracts\HouseFieldContract', function () {
            return new HouseListhubFieldService();
        });
    }
}