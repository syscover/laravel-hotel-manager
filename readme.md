# Hotel Manager to Laravel 5.4

<a href="https://packagist.org/packages/syscover/laravel-hotel-manager"><img src="https://poser.pugx.org/syscover/laravel-hotel-manager/downloads" alt="Total Downloads"></a>

## Installation

**1 - From the command line run**
```
composer require syscover/laravel-hotel-manager
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
HOTELMANAGER_USER=xxxxxxxxx
HOTELMANAGER_PASSWORD=xxxxxxxxx
```