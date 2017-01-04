# Hotel Manager to Laravel 5.3

[![Total Downloads](https://poser.pugx.org/syscover/hotel-manager/downloads)](https://packagist.org/packages/syscover/hotel-manager)

## Installation

**1 - After install Laravel framework, insert on file composer.json, inside require object this value**
```
"syscover/laravel-hotel-manage": "~2.0"
```
and execute on console:
```
composer update
```

**2 - Register service provider, on file config/app.php add to providers array**
```
Syscover\HotelManager\HotelManagerServiceProvider::class,
```

**3 - Execute publish command**
```
php artisan vendor:publish --provider="Syscover\HotelManager\HotelManagerServiceProviderr"
```

##Configuration
Set config options on config\hotelManager.php
The best option is set options in environment file, with this example
```
HOTELMANAGER_URL=https://xxxxxx
HOTELMANAGER_TOKEN=xxxxxxxxx
HOTELMANAGER_USER=xxxxxxxxx
HOTELMANAGER_PASSWORD=xxxxxxxxx
```