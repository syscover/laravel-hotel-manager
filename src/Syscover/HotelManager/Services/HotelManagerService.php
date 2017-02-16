<?php namespace Syscover\HotelManager\Services;

use Syscover\HotelManager\Exceptions\CloseTransactionException;
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

    public static function getConditions()
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'obtenerPoliticaHotelDeDetalles';
        $parameters['token']    = HotelManagerService::getToken();

        // check parameters
        if(! isset($parameters['lang']))
            throw new ParameterNotFoundException('Lang parameter not found in parameters array, please set lang index');

        if(! isset($parameters['hotelId']))
            throw new ParameterNotFoundException('HotelId parameter not found in parameters array, please set hotelId index');

        if(! isset($parameters['isRefundableRate']))
            throw new ParameterNotFoundException('IsRefundableRate parameter not found in parameters array, please set isRefundableRate index');

        if($parameters['isRefundableRate'] !== 0 || $parameters['isRefundableRate'] !== "0" || $parameters['isRefundableRate'] !== 1 || $parameters['isRefundableRate'] !== "1")
            throw new ParameterValueException('IsRefundableRate parameter has a incorrect value, must to be 1 or 0');

        $parameters['non_refundable'] = ($parameters['isRefundableRate'] === 0 || $parameters['isRefundableRate'] === "0")? 0 : 1;

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
                // get additions to room
                $additions = [];
                if(isset($obj->hotel->{$i}->habitacion->Pensiones))
                {
                    foreach ($obj->hotel->{$i}->habitacion->Pensiones as $addition)
                    {
                        $additions[] = (object)[
                            'id'            => $addition->idPension,
                            'name'          => $addition->infoPension->nombre,
                            'adultRate'     => $addition->infoPension->tarifa_adulto,
                            'childrenRate'  => $addition->infoPension->tarifa_nino,
                        ];
                    }
                }

                // create room
                $hotel['rooms'][] = (object)[
                    'id'                    => $obj->hotel->{$i}->habitacion->idHabitacion,
                    'name'                  => $obj->hotel->{$i}->habitacion->infoHabitacion->nombreHabitacion,
                    'quantity'              => $obj->hotel->{$i}->habitacion->disponibilidad,
                    'rates'                 => (object)[
                        'hasNonRefundableRate'              => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_noreembolsable_valida == 1? true : false, // has non refundable rate
                        'nonRefundablePercentageDiscount'   => $obj->hotel->{$i}->habitacion->infoHabitacion->valor_noreembolsable_valida,

                        // Standard rate
                        'rate'                      => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_rack,                          // tarifa base
                        'rateAvg'                   => $obj->hotel->{$i}->habitacion->infoHabitacion->tarifa_rack_promedio,                 // tarifa promedio por noche

                        // Non refundable rate
                        'rateNonRefundable'         => $obj->hotel->{$i}->habitacion->infoHabitacion->tfa_noreembolsable,                   // tarifa base no reembolsable
                        'rateAvgNonRefundable'      => $obj->hotel->{$i}->habitacion->infoHabitacion->tfa_prom_noche_no_reembolsable,       // tarifa promedio por noche no reembolsable
                    ],
                    'additions'             => $additions
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
     * @throws  CloseTransactionException
     * @throws  ParameterNotFoundException
     * @throws  ParameterValueException
     */
    public static function closeTransaction(array $parameters = [])
    {
        $url                    = config('hotelManager.url');
        $parameters['action']   = 'realizar_reserva';
        $parameters['token']    = HotelManagerService::getToken();


        // lang
        if(! isset($parameters['lang']))
            throw new ParameterNotFoundException('Lang parameter not found in parameters array, please set lang index');


        // checkInDate
        if(! isset($parameters['checkInDate']))
            throw new ParameterNotFoundException('CheckInDate parameter not found in parameters array, please set checkInDate index');


        // checkOutDate
        if(! isset($parameters['checkOutDate']))
            throw new ParameterNotFoundException('CheckOutDate parameter not found in parameters array, please set checkOutDate index');


        // numberRooms
        if(! isset($parameters['numberRooms']))
            throw new ParameterNotFoundException('NumberRooms parameter not found in parameters array, please set numberRooms index');


        // numberAdults
        if(! isset($parameters['numberAdults']))
            throw new ParameterNotFoundException('NumberAdults parameter not found in parameters array, please set numberAdults index');


        // checkInHour
        if(isset($parameters['checkInHour']))
        {
            $parameters['horaLlegada'] = $parameters['checkInHour'];
            unset($parameters['checkInHour']);
        }


        // checkInMinute
        if(isset($parameters['checkInMinute']))
        {
            $parameters['minutoLlegada'] = $parameters['checkInMinute'];
            unset($parameters['checkInMinute']);
        }


        // name
        if(! isset($parameters['name']))
            throw new ParameterNotFoundException('Name parameter not found in parameters array, please set name index');

        $parameters['nombre'] = $parameters['name'];
        unset($parameters['name']);


        // surname
        if(! isset($parameters['surname']))
            throw new ParameterNotFoundException('Surname parameter not found in parameters array, please set surname index');

        $parameters['apellido'] = $parameters['surname'];
        unset($parameters['surname']);


        // phone
        if(! isset($parameters['phone']))
            throw new ParameterNotFoundException('Phone parameter not found in parameters array, please set phone index');

        $parameters['telefono'] = $parameters['phone'];
        unset($parameters['phone']);


        // email
        if(! isset($parameters['email']))
            throw new ParameterNotFoundException('Email parameter not found in parameters array, please set email index');


        // docType
        if(! isset($parameters['docType']))
            throw new ParameterNotFoundException('DocType parameter not found in parameters array, please set docType index');

        if(! in_array($parameters['docType'], array_pluck(config('hotelManager.docTypes'), 'id')))
            throw new ParameterValueException('DocType has value not allowed, please check allow values and set correct value');


        // docNumber
        if(! isset($parameters['docNumber']))
            throw new ParameterNotFoundException('DocNumber parameter not found in parameters array, please set docNumber index');

        $parameters['docNum'] = $parameters['docNumber'];
        unset($parameters['docNumber']);


        // observations
        if(isset($parameters['observations']))
        {
            $parameters['aclaraciones'] = $parameters['observations'];
            unset($parameters['observations']);
        }


        // country
        if(isset($parameters['country']))
        {
            $parameters['pais'] = $parameters['country'];
            unset($parameters['country']);
        }


        // paymentMethod
        if(! isset($parameters['paymentMethod']))
            throw new ParameterNotFoundException('PaymentMethod parameter not found in parameters array, please set paymentMethod index');

        $parameters['medioPagoID'] = $parameters['paymentMethod'];
        unset($parameters['paymentMethod']);


        // creditCardHolder
        if(! isset($parameters['creditCardHolder']))
            throw new ParameterNotFoundException('CreditCardHolder parameter not found in parameters array, please set creditCardHolder index');

        $parameters['titularTarjeta'] = $parameters['creditCardHolder'];
        unset($parameters['creditCardHolder']);


        // creditCardNumber
        if(! isset($parameters['creditCardNumber']))
            throw new ParameterNotFoundException('CreditCardNumber parameter not found in parameters array, please set creditCardNumber index');

        $parameters['numTarjeta'] = $parameters['creditCardNumber'];
        unset($parameters['creditCardNumber']);


        // creditCardDateExpiry
        if(! isset($parameters['creditCardDateExpiry']))
            throw new ParameterNotFoundException('CreditCardDateExpiry parameter not found in parameters array, please set creditCardDateExpiry index');

        if(strlen($parameters['creditCardDateExpiry']) != 4)
            throw new ParameterValueException('CreditCardDateExpiry has length value not allowed. Please check allow values and set correct value');


        // fechaVto
        $parameters['fechaVto'] = $parameters['creditCardDateExpiry'];
        unset($parameters['creditCardDateExpiry']);


        // cvv
        if(! isset($parameters['cvv']))
            throw new ParameterNotFoundException('Cvv parameter not found in parameters array, please set cvv index');

        $parameters['codSeguridad'] = $parameters['cvv'];
        unset($parameters['cvv']);


        // isRefundableRate
        if(! isset($parameters['isRefundableRate']))
            throw new ParameterNotFoundException('IsRefundableRate parameter not found in parameters array, please set isRefundableRate index');

        if(! in_array($parameters['isRefundableRate'], [0,'0',1,'1']))
            throw new ParameterValueException('IsRefundableRate parameter has a incorrect value, must to be 1 or 0');

        $parameters['reembolsable'] = $parameters['isRefundableRate'];


        // additionId
        if(isset($parameters['additionId']))
            $parameters['idPension'] = $parameters['additionId'];
        else
            $parameters['idPension'] = null;


        // transactionId
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

        $auxResponse            = RemoteService::send($curlParams, $stringParameters);
        $auxResponse            = json_decode($auxResponse);

        if(isset($auxResponse->IDReserva) && isset($auxResponse->claveUnicaReserva))
        {
            $response                   = [];
            $response['booking']        = (object)[
                'id'    => array_first((array)$auxResponse->IDReserva),
                'key'   => array_first((array)$auxResponse->claveUnicaReserva)
            ];
        }
        elseif(isset($auxResponse->codigo) && isset($auxResponse->desc_error))
        {
            throw new CloseTransactionException('Error code: ' . $auxResponse->codigo . ' to close transaction, Error: ' . $auxResponse->desc_error);
        }
        else
        {
            throw new CloseTransactionException('Unknown error to close transaction');
        }

        return $response;
    }
}