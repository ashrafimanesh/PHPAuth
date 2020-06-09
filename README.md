PHPAuth
=======
A simple package to handle user authentication on PHP.

Features list:
--------------
* Register the custom driver to handle authentication
* Check request before call authentication method by defining middleware

Installation
------------
Use composer to manage your dependencies and download PHPAuth:
```bash
composer require assin/php-auth
```
Usage
-------
* Create a driver that implements DriverInterface.
```php
<?php

require 'vendor/autoload.php';

class SimpleDriver implements \Assin\PHPAuth\Contracts\DriverInterface
{
    public function login(\Assin\PHPAuth\ObjectValue\Input $input)
    {
        if($input->get('username') === 'test' && $input->get('password') === 'test'){
            return 'Success!';
        }
        return 'Failed!';
    }
}

```
* Register driver to DriverBuilder.
```php
$driver = new SimpleDriver();

$driverId = 1;

$driverBuilder = new \Assin\PHPAuth\Builder\DriverBuilder([
    $driverId=>$driver
]);

```
* Initiate request data.

```php
$input = new \Assin\PHPAuth\ObjectValue\Input([
    'username'=>'test',
    'password'=>'test'
]);

```

* Config auth class.

```php
$auth = new \Assin\PHPAuth\Auth($driverBuilder);

print_r($auth->login($driverId, $input));

```

Full example 1:
---------------
```php

<?php

require 'vendor/autoload.php';

class SimpleDriver implements \Assin\PHPAuth\Contracts\DriverInterface
{
    public function login(\Assin\PHPAuth\ObjectValue\Input $input)
    {
        if($input->get('username') === 'test' && $input->get('password') === 'test'){
            return 'Success!';
        }
        return 'Failed!';
    }
}

$driver = new SimpleDriver();

$driverId = 1;

$driverBuilder = new \Assin\PHPAuth\Builder\DriverBuilder([
    $driverId=>$driver
]);

$input = new \Assin\PHPAuth\ObjectValue\Input([
    'username'=>'test',
    'password'=>'test'
]);

$auth = new \Assin\PHPAuth\Auth($driverBuilder);

print_r($auth->login($driverId, $input));

?>
```

**Notice:** You can register multiple drivers and change authentication behavior base on business request!

Multiple drivers example 2:
---------------------------
```php
<?php

require 'vendor/autoload.php';

class SimpleDriver implements \Assin\PHPAuth\Contracts\DriverInterface
{
    public function login(\Assin\PHPAuth\ObjectValue\Input $input)
    {
        if($input->get('username') === 'test' && $input->get('password') === 'test'){
            return 'Successful!';
        }
        return 'Failed!';
    }
}


class SimpleDriver2 implements \Assin\PHPAuth\Contracts\DriverInterface
{
    public function login(\Assin\PHPAuth\ObjectValue\Input $input)
    {
        if($input->get('username') === 'test' && $input->get('password') === 'test'){
            return ['status'=>true, 'message'=>'Successful!'];
        }
        return ['status'=>false, 'message'=>'Failed!'];
    }
}

$driverBuilder = new \Assin\PHPAuth\Builder\DriverBuilder([
    1=>new SimpleDriver(),
    2=>new SimpleDriver2()
]);



$input = new \Assin\PHPAuth\ObjectValue\Input([
    'username'=>'test',
    'password'=>'test'
]);

$auth = new \Assin\PHPAuth\Auth($driverBuilder);

//Print driver 1 respond
print_r($auth->login(1, $input));
print PHP_EOL;
//Print driver 2 respond
print_r($auth->login(2, $input));

```  

Middleware Example:
-------------------
* Create simple middleware.

```php

Class SimpleMiddleware implements \Assin\PHPAuth\Contracts\MiddlewareInterface{

    public function handle(\Assin\PHPAuth\ObjectValue\Input $input, \Closure $next)
    {
        if($input->get('username') === 'test'){
            throw new Exception('The user is banned');
        }
        $next($input);
    }
}

```

* Register the middleware to middleware/kernel and call login.

```php

$auth = new \Assin\PHPAuth\Auth($driverBuilder, new \Assin\PHPAuth\Middleware\Kernel([
    new SimpleMiddleware()
]));

try {
    print_r($auth->login(1, $input));
}catch (\Exception $exception){
    print $exception->getMessage();
}

```
