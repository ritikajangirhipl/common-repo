<?php
namespace Vendor\CommonPackage\Providers;

use Illuminate\Support\Facades\Route;
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
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'common');
        Route::middleware('web')
        ->group(function () {
            Route::get('/vendor/common-repo/{path}', function ($path) {
                $filePath = __DIR__ . '/../../public/assets/' . $path;

                if (file_exists($filePath)) {
                    return response()->file($filePath);
                }

                abort(404);
            })->where('path', '.*');
        });
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
