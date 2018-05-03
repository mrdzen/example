<?php
namespace App\Goods;

use Illuminate\Support\ServiceProvider;

class GoodsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TableOptions::class, function($app, $arguments) {
            $options = new TableOptions();

            if (isset($arguments[0]) && $arguments[0]) {
                $options->setProvider($arguments[0]);
            }

            if (isset($arguments[1]) && $arguments[1]) {
                $options->setShopID($arguments[1]);
            }

            if (isset($arguments[2]) && $arguments[2]) {
                $options->setTemporary($arguments[2]);
            }

            return $options;
        });
    }
}