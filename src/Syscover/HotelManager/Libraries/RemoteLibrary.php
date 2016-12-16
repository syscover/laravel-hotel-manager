<?php namespace Syscover\HotelManager\Libraries;

class RemoteLibrary
{
    /**
     * @param $curlParams
     * @param $params
     * @return mixed
     */
    public static function send($curlParams, $params = null)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,                     $curlParams['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,          isset($curlParams['returnTransfer'])? $curlParams['returnTransfer'] : false);
        curl_setopt($curl, CURLOPT_TIMEOUT,                 isset($curlParams['timeout'])? $curlParams['timeout'] : 10);
        
        if(isset($curlParams['followLocation']))
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION,      $curlParams['followLocation']);

        if(isset($curlParams['post']))
            curl_setopt($curl, CURLOPT_POST,                $curlParams['post']);

        if(isset($curlParams['port']))
            curl_setopt($curl, CURLOPT_PORT ,               $curlParams['port']);

        if(isset($curlParams['sslVerifyPeer']))
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,      $curlParams['sslVerifyPeer']);

        if(isset($curlParams['httpAuth']))
        {
            curl_setopt($curl, CURLOPT_HTTPAUTH,            CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD,             $curlParams['httpAuth']);
        }

        if(isset($curlParams['headers']))
            curl_setopt($curl, CURLOPT_HTTPHEADER,          $curlParams['headers']);

        if(isset($params))
            curl_setopt($curl, CURLOPT_POSTFIELDS,          $params);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}