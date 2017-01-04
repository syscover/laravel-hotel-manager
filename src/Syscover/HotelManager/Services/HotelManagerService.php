<?php namespace Syscover\HotelManager\Services;

class HotelManagerService
{
    private static function getToken()
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

        $response = json_decode($response);

        if(isset($response->ID_Token))
        {
            return $response->ID_Token;
        }

        throw new \Exception('Token ID is not valid, please verify your username and password');
    }

    /**
     * @param array $parameters
     * @return mixed
     *
     * keys to array parameters:
     * string       lang
     * array[int]   hotelIds
     * string       checkInDate
     */
    public static function checkAvailability(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'list_disponibilidad';
        $parameters['token']    = HotelManagerService::getToken();
        $stringParameters       = RemoteService::formatParameters($parameters);

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

    /**
     * @param array $parameters
     * @return mixed
     */
    public static function openTransaction(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'abrir_transaccion';
        $parameters['token']    = HotelManagerService::getToken();
        $stringParameters       = RemoteService::formatParameters($parameters);

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

    /**
     * @param array $parameters
     * @return mixed
     */
    public static function closeTransaction(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'realizar_reserva';
        $parameters['token']    = HotelManagerService::getToken();
        $stringParameters       = RemoteService::formatParameters($parameters);

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