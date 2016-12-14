<?php namespace Syscover\HotelManager;

use Illuminate\Support\ServiceProvider;

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

		// register config files
		$this->publishes([
            __DIR__ . '/../../config/hotelManager.php' 			=> config_path('hotelManager.php')
        ]);
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
        //
	}
}