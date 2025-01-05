# Config Value Component

### Pull config from .env values or from Laminas config

## Install
```bash
composer require iampersistent/config-value-component
```

## Usage
```php
  $config = (new GatherConfigValues)($container, 'print');
```

In the .env file, the first part of the environment name must match the config name
(case-insensitive). Each subsequent underscore creates a key in an array of the value.
```
PRINT_PRINTER=Epson TX-80
```
would result in
```php 
$printConfig = [
    'printer' => 'Epson TX-80',  
];
```

If there is a default key, the case of that key will be used.
```php
// print.config.php

return [
    'print' => [
        'printerType' => null,
    ]
];
```
``` 
.env

PRINT_PRINTER=Epson TX-80
PRINT_PRINTERTYPE=dot-matrix
```

would result in
```php
$printConfig = [
    'print' => [
        'printer'     => 'Epson TX-80',  
        'printerType' => 'dot-matrix',
    ]
];
```

If there are conflicting values between the config files and the .env file, the .env
value will be used
```php
// print.config.php

return [
    'print' => [
        'printerType' => null,
        'speed'       => 'fast',
   ]
];
```
``` 
.env

PRINT_PRINTER=Epson TX-80
PRINT_PRINTERTYPE=dot-matrix
PRINT_SPEED=slow
```

would result in
```php
$printConfig = [
    'print' => [
        'printer'     => 'Epson TX-80',  
        'printerType' => 'dot-matrix',
        'speed'       => 'slow',
    ]
];
```

Any values in the config that don't have a value in the .env remains as is
```php
// print.config.php

return [
    'print' => [
        'location'    => 'Room 1',
        'printerType' => null,
        'speed'       => 'fast',
    ]
];
```
``` 
.env

PRINT_PRINTER=Epson TX-80
PRINT_PRINTERTYPE=dot-matrix
PRINT_SPEED=slow
```

would result in
```php
$printConfig = [
    'print' => [
        'location'    => 'Room 1',
        'printer'     => 'Epson TX-80',  
        'printerType' => 'dot-matrix',
        'speed'       => 'slow',
    ]
];
```
