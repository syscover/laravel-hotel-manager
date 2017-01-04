<?php namespace Syscover\HotelManager\Services;

class HotelManagerService
{
    public static function checkAvailability($params = [])
    {
        $url = config('hotelManager.url');

        // set paramsLibraries
        $stringParams='';
        foreach($params as $key => $value)
        {
            $stringParams .= $key . '=' .  urlencode($value) . '&';
        }
        $stringParams = rtrim($stringParams,'&');

        $curlParams = [
            'url'               => $url,
            'followLocation'    => false,
            'post'              => true,
            'sslVerifyPeer'     => false,
            'sslVerifyHost'     => false,
            'returnTransfer'    => true,
            'timeout'           => 30
        ];

        $response = RemoteLibrary::send($curlParams, $stringParams);

        return $response;
    }
}