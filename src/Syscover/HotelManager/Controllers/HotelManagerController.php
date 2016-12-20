<?php namespace Syscover\FacturaDirecta\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Syscover\HotelManager\Facades\HotelManager;

/**
 * Class HotelManagerController
 * @package Syscover\HotelManager\Controllers
 */

class HotelManagerController extends BaseController
{
    public function checkAvailability(Request $request)
    {
        $response = HotelManager::checkAvailability($request->all());

        return response($response, 200)
            ->header('Content-Type', 'application/json');
    }
}