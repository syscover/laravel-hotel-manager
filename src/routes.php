<?php

Route::group(['middleware' => ['web', 'pulsar']], function()
{
    /*
    |--------------------------------------------------------------------------
    | CHECK AVAILABILITY
    |--------------------------------------------------------------------------
    */
    Route::post(config('pulsar.name') . 'api/hotel/manager/check/availability', ['as' => 'checkAvailability',    'uses' => 'Syscover\HotelManager\Controllers\HotelManagerController@checkAvailability']);

});