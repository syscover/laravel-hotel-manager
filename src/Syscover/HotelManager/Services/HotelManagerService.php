<?php namespace Syscover\HotelManager\Services;

use Syscover\HotelManager\Exceptions\ParameterFormatException;
use Syscover\HotelManager\Exceptions\ParameterNotFoundException;

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
     * @param   array $parameters
     * @return  array
     * @throws  ParameterFormatException
     * @throws  ParameterNotFoundException
     */
    public static function checkAvailability(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'list_disponibilidad';
        $parameters['token']    = HotelManagerService::getToken();

        // check parameters
        if(! isset($parameters['lang']))
            throw new ParameterNotFoundException('Lang parameter not found in parameters array, please set lang index');

        if(! isset($parameters['hotelIds']))
            throw new ParameterNotFoundException('HotelIds parameter not found in parameters array, please set hotelIds index');

        if(! is_array($parameters['hotelIds']))
            throw new ParameterFormatException('HotelIds parameter is not array, please set hotelIds like array parameter with hotel ids');

        if(! isset($parameters['checkInDate']))
            throw new ParameterNotFoundException('CheckInDate parameter not found in parameters array, please set checkInDate index');

        if(! isset($parameters['checkOutDate']))
            throw new ParameterNotFoundException('CheckOutDate parameter not found in parameters array, please set checkOutDate index');

        if(! isset($parameters['numberRooms']))
            throw new ParameterNotFoundException('NumberRooms parameter not found in parameters array, please set numberRooms index');

        if(! isset($parameters['numberAdults']))
            throw new ParameterNotFoundException('NumberAdults parameter not found in parameters array, please set numberAdults index');

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

        $auxResponse            = RemoteService::send($curlParams, $stringParameters);
        $auxResponse            = json_decode($auxResponse);
        $response               = [];
        $setCurrency            = false;


        foreach ($auxResponse as $obj)
        {
            $hotel = [
                'id'    => $obj->hotel->id_hotel,
                'rooms' => []
            ];
            $setCurrency = false;

            $i = 1;
            while(isset($obj->hotel->{$i}))
            {
                $hotel['rooms'][] = (object)[
                    'id'                    => $obj->hotel->{$i}->habitacion->idHabitacion,
                    'name'                  => $obj->hotel->{$i}->habitacion->infoHabitacion->nombreHabitacion,
                    'quantity'              => $obj->hotel->{$i}->habitacion->disponibilidad,
                    'rates'                 => (object)[
                        'isNotRefundableRate'       => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_noreembolsable_valida == 1? true : false,
                        'notRefundablePercentage'   => $obj->hotel->{$i}->habitacion->infoHabitacion->valor_noreembolsable_valida,
                        'rate'                      => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa,
                        'rateRound'                 => $obj->hotel->{$i}->habitacion->infoHabitacion->tfa_total_estadia,
                        'rateAvg'                   => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_rack_promedio,
                        'rateAvgRound'              => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_promedio_noche,
                    ]
                ];
                $i++;

                if(! $setCurrency)
                {
                    $response['currency'] = (object)[
                        'id'    => $obj->hotel->{$i}->habitacion->infoHabitacion->id_moneda,
                        'name'  => $obj->hotel->{$i}->habitacion->infoHabitacion->moneda
                    ];
                    $setCurrency = true;
                }
            }

            $response['hotels'][] = (object)$hotel;
        }

        return $response;
    }

    /**
     * @param   array $parameters
     * @return  mixed
     * @throws  ParameterNotFoundException
     */
    public static function openTransaction(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'abrir_transaccion';
        $parameters['token']    = HotelManagerService::getToken();

        // check parameters
        if(! isset($parameters['roomId']))
            throw new ParameterNotFoundException('RoomId parameter not found in parameters array, please set roomId index');

        if(! isset($parameters['checkInDate']))
            throw new ParameterNotFoundException('CheckInDate parameter not found in parameters array, please set checkInDate index');

        if(! isset($parameters['checkOutDate']))
            throw new ParameterNotFoundException('CheckOutDate parameter not found in parameters array, please set checkOutDate index');

        if(! isset($parameters['numberRooms']))
            throw new ParameterNotFoundException('NumberRooms parameter not found in parameters array, please set numberRooms index');

        // change numberRooms by cantidad
        $parameters['cantidad'] = $parameters['numberRooms'];
        unset($parameters['numberRooms']);

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

        $response                       = [];
        $auxResponse                    = RemoteService::send($curlParams, $stringParameters);
        $auxResponse                    = json_decode($auxResponse);
        $response['transaction']        = (object)[
            'id' => $auxResponse->ID_Tansaccion
        ];

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