<?php namespace Syscover\HotelManager\Services;

use Syscover\HotelManager\Exceptions\ParameterFormatException;
use Syscover\HotelManager\Exceptions\ParameterNotFoundException;
use Syscover\HotelManager\Exceptions\ParameterValueException;

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

                if(! $setCurrency)
                {
                    $response['currency'] = (object)[
                        'id'    => $obj->hotel->{$i}->habitacion->infoHabitacion->id_moneda,
                        'name'  => $obj->hotel->{$i}->habitacion->infoHabitacion->moneda
                    ];
                    $setCurrency = true;
                }

                $i++;
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

        $auxResponse                    = RemoteService::send($curlParams, $stringParameters);
        $auxResponse                    = json_decode($auxResponse);
        $response                       = [];
        $response['transaction']        = (object)[
            'id' => $auxResponse->ID_Tansaccion
        ];

        return $response;
    }

    /**
     * @param   array $parameters
     * @return  array
     * @throws  ParameterNotFoundException
     * @throws  ParameterValueException
     */
    public static function closeTransaction(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'realizar_reserva';
        $parameters['token']    = HotelManagerService::getToken();

        if(! isset($parameters['lang']))
            throw new ParameterNotFoundException('Lang parameter not found in parameters array, please set lang index');

        if(! isset($parameters['checkInDate']))
            throw new ParameterNotFoundException('CheckInDate parameter not found in parameters array, please set checkInDate index');

        if(! isset($parameters['checkOutDate']))
            throw new ParameterNotFoundException('CheckOutDate parameter not found in parameters array, please set checkOutDate index');

        if(! isset($parameters['numberRooms']))
            throw new ParameterNotFoundException('NumberRooms parameter not found in parameters array, please set numberRooms index');

        if(! isset($parameters['numberAdults']))
            throw new ParameterNotFoundException('NumberAdults parameter not found in parameters array, please set numberAdults index');

        if(isset($parameters['checkInHour']))
        {
            $parameters['horaLlegada'] = $parameters['checkInHour'];
            unset($parameters['checkInHour']);
        }

        if(isset($parameters['checkInMinute']))
        {
            $parameters['minutoLlegada'] = $parameters['checkInMinute'];
            unset($parameters['checkInMinute']);
        }

        if(! isset($parameters['name']))
            throw new ParameterNotFoundException('Name parameter not found in parameters array, please set name index');

        $parameters['nombre'] = $parameters['name'];
        unset($parameters['name']);

        if(! isset($parameters['surname']))
            throw new ParameterNotFoundException('Surname parameter not found in parameters array, please set surname index');

        $parameters['apellido'] = $parameters['surname'];
        unset($parameters['surname']);

        if(! isset($parameters['phone']))
            throw new ParameterNotFoundException('Phone parameter not found in parameters array, please set phone index');

        $parameters['telefono'] = $parameters['phone'];
        unset($parameters['phone']);

        if(! isset($parameters['email']))
            throw new ParameterNotFoundException('Email parameter not found in parameters array, please set email index');

        if(! isset($parameters['docType']))
            throw new ParameterNotFoundException('DocType parameter not found in parameters array, please set docType index');

        if(! in_array($parameters['docType'], array_pluck(config('hotelManager.docTypes'), 'id')))
            throw new ParameterValueException('DocType has value not allowed, please check allow values and set correct value');

        if(! isset($parameters['docNumber']))
            throw new ParameterNotFoundException('DocNumber parameter not found in parameters array, please set docNumber index');

        $parameters['docNum'] = $parameters['docNumber'];
        unset($parameters['docNumber']);

        if(isset($parameters['observations']))
        {
            $parameters['aclaraciones'] = $parameters['observations'];
            unset($parameters['observations']);
        }

        if(isset($parameters['country']))
        {
            $parameters['pais'] = $parameters['country'];
            unset($parameters['country']);
        }

        if(! isset($parameters['paymentMethod']))
            throw new ParameterNotFoundException('PaymentMethod parameter not found in parameters array, please set paymentMethod index');

        $parameters['medioPagoID'] = $parameters['paymentMethod'];
        unset($parameters['paymentMethod']);

        if(! isset($parameters['creditCardHolder']))
            throw new ParameterNotFoundException('CreditCardHolder parameter not found in parameters array, please set creditCardHolder index');

        $parameters['titularTarjeta'] = $parameters['creditCardHolder'];
        unset($parameters['creditCardHolder']);

        if(! isset($parameters['creditCardNumber']))
            throw new ParameterNotFoundException('CreditCardNumber parameter not found in parameters array, please set creditCardNumber index');

        $parameters['numTarjeta'] = $parameters['creditCardNumber'];
        unset($parameters['creditCardNumber']);

        if(! isset($parameters['creditCardDateExpiry']))
            throw new ParameterNotFoundException('CreditCardDateExpiry parameter not found in parameters array, please set creditCardDateExpiry index');

        if(strlen($parameters['creditCardDateExpiry']) != 4)
            throw new ParameterValueException('CreditCardDateExpiry has length value not allowed. Please check allow values and set correct value');

        $parameters['fechaVto'] = $parameters['creditCardDateExpiry'];
        unset($parameters['creditCardDateExpiry']);

        if(! isset($parameters['cvv']))
            throw new ParameterNotFoundException('Cvv parameter not found in parameters array, please set cvv index');

        $parameters['codSeguridad'] = $parameters['cvv'];
        unset($parameters['cvv']);

        if(! isset($parameters['transactionId']))
            throw new ParameterNotFoundException('TransactionId parameter not found in parameters array, please set transactionId index');

        $parameters['IDtransaccion'] = $parameters['transactionId'];
        unset($parameters['transactionId']);

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

        $auxResponse                = RemoteService::send($curlParams, $stringParameters);
        $auxResponse                = json_decode($auxResponse);

        $response                   = [];
        $response['booking']        = (object)[
            'id'    => array_first((array)$auxResponse->IDReserva),
            'key'   => array_first((array)$auxResponse->claveUnicaReserva)
        ];

        return $response;
    }
}