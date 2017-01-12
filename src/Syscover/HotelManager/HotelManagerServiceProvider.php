<?php namespace Syscover\HotelManager;

use Illuminate\Support\ServiceProvider;
use Syscover\HotelManager\Services\HotelManagerService;

class HotelManagerServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// include route.php file
		if (!$this->app->routesAreCached())
			require __DIR__ . '/../../routes.php';

        // register translations
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'hotelManager');

		// register config files
		$this->publishes([
            __DIR__ . '/../../config/hotelManager.php' => config_path('hotelManager.php')
        ]);
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->bind('HotelManager', function($app)
        {
            return new HotelManagerService($app);
        });
	}
}