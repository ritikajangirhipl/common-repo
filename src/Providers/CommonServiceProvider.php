<?php
namespace Common\CommonPackage\Providers;

use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'common');
        $this->publishes([
            __DIR__.'/../public/assets' => public_path('common/common-repo'),
        ], 'public');
        $this->mergeConfigFrom(__DIR__ . '/../config/common.php', 'common');
        $this->commands([
            \Common\CommonPackage\Commands\Send2FACode ::class,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // You can register additional services here, if needed
    }
}
