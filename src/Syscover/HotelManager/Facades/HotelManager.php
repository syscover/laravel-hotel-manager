<?php namespace Syscover\HotelManager\Facades;

use Illuminate\Support\Facades\Facade;

class HotelManager extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'HotelManager'; }

}