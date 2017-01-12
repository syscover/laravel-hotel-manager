<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HOTELMANAGER
    |--------------------------------------------------------------------------
    */

    'url'           => env('HOTELMANAGER_URL', 'your url'),
    'user'          => env('HOTELMANAGER_USER', 'your user'),
    'password'      => env('HOTELMANAGER_PASSWORD', 'your password'),


    /*
    |--------------------------------------------------------------------------
    | VALUES
    |--------------------------------------------------------------------------
    */
    'docTypes'          => [
        (object)['id' => 1, 'name' => 'hotelManager::pulsar.dni'],
        (object)['id' => 2, 'name' => 'hotelManager::pulsar.nie'],
        (object)['id' => 3, 'name' => 'hotelManager::pulsar.passport'],
        (object)['id' => 4, 'name' => 'hotelManager::pulsar.cif'],
    ],

    'paymentMethods'    => [
        (object)['id' => 1, 'name' => 'hotelManager::pulsar.visa'],
        (object)['id' => 2, 'name' => 'hotelManager::pulsar.american_express'],
        (object)['id' => 3, 'name' => 'hotelManager::pulsar.master_card'],
        (object)['id' => 4, 'name' => 'hotelManager::pulsar.bank_deposit'],
    ]
];