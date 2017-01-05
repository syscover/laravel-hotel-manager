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

        $auxResponse    = RemoteService::send($curlParams, $stringParameters);
        $auxResponse    = json_decode($auxResponse);
        $response       = [];

        foreach ($auxResponse as $obj)
        {
            $hotel = [
                'id'    => $obj->hotel->id_hotel,
                'rooms' => []
            ];
            $hasCurrency = false;

            $i = 1;
            while(isset($obj->hotel->{$i}))
            {
                $hotel['rooms'][] = (object)[
                    'id'        => $obj->hotel->{$i}->habitacion->idHabitacion,
                    'name'      => $obj->hotel->{$i}->habitacion->infoHabitacion->nombreHabitacion,
                    'quantity'  => $obj->hotel->{$i}->habitacion->disponibilidad,
                    'rates'     => (object)[
                        'rate'                      => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa,
                        'rack'                      => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_rack,
                        'rackAvg'                   => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_rack_promedio,
                        'hasNotRefundable'          => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_noreembolsable_valida == 1? true : false,
                        'notRefundablePercentage'   => $obj->hotel->{$i}->habitacion->infoHabitacion->valor_noreembolsable_valida,
                        'notRefundableRate'         => null,

                        'notRefundableRack'         => $obj->hotel->{$i}->habitacion->infoHabitacion->tfa_noreembolsable,
                        'notRefundableRackAvg'      => $obj->hotel->{$i}->habitacion->infoHabitacion->tfa_prom_noche_no_reembolsable,

                        'rateRound'                 => $obj->hotel->{$i}->habitacion->infoHabitacion->tfa_total_estadia,
                        'rateAvgRound'              => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_promedio_noche,
                    ]
                ];
                $i++;
            }

            $response['hotels'][] = (object)$hotel;
        }

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