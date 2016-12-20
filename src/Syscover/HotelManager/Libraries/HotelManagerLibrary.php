<?php namespace Syscover\HotelManager\Libraries;

class HotelManagerLibrary
{
    public static function checkAvailability($params = [])
    {
        $url = config('hotelManager.url');

        // set params
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