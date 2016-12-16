<?php namespace Syscover\FacturaDirecta\Libraries;

class HotelManagerLibrary
{
    public static function checkAvailability($params = [])
    {
        $url = config('hotelManager.url');

        // set params in url
        $i = 0;
        foreach($params as $key => $value)
        {
            if($i === 0)
                $url .= '?';
            else
                $url .= '&';

            $url .= $key . '=' .  urlencode($value);
            $i++;
        }

        $curlParams = [
            'url'               => $url,
            //'httpAuth'          => config('facturaDirecta.api') . ':x',
            'followLocation'    => false,
            'returnTransfer'    => true,
            'timeout'           => 30
        ];

        $response = RemoteLibrary::send($curlParams);

        return $response;
    }
}