<?php namespace Syscover\HotelManager\Services;

class HotelManagerService
{
    public static function checkAvailability(array $parameters = [])
    {
        $url = config('hotelManager.url');

        $stringParameters = RemoteService::formatParameters($parameters);

        $curlParams = [
            'url'               => $url,
            'followLocation'    => false,
            'post'              => true,
            'sslVerifyPeer'     => false,
            'sslVerifyHost'     => false,
            'returnTransfer'    => true,
            'timeout'           => 30
        ];

        $response = RemoteService::send($curlParams, $stringParameters);

        return $response;
    }

    public static function getToken()
    {
        $url = config('hotelManager.url');

        $stringParameters = RemoteService::formatParameters([
            'user'      => config('hotelManager.user'),
            'pass'      => config('hotelManager.password'),
            'action'    => 'solicitar_token'
        ]);

        $curlParams = [
            'url'               => $url,
            'followLocation'    => false,
            'post'              => true,
            'sslVerifyPeer'     => false,
            'sslVerifyHost'     => false,
            'returnTransfer'    => true,
            'timeout'           => 30
        ];

        $response = RemoteService::send($curlParams, $stringParameters);

        return $response;
    }
}