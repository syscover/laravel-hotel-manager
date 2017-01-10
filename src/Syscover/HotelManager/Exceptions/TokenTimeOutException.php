<?php namespace Syscover\HotelManager\Exceptions;

class TokenTimeOutException extends \Exception {
    protected $message = 'Token time out, you need get other token authentication';
}