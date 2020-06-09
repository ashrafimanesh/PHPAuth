<?php

namespace Assin\PHPAuthTests;

use Assin\PHPAuth\Auth;
use Assin\PHPAuth\Builder\DriverBuilder;
use Assin\PHPAuth\Contracts\DriverInterface;
use Assin\PHPAuth\Middleware\Kernel as MiddlewareKernel;
use Assin\PHPAuth\ObjectValue\Input;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    /**
     * @param array $supportedDrivers
     * @param array $middleware
     * @return Auth
     */
    protected function makeAuth(array $supportedDrivers, array $middleware = []): Auth
    {
        $driverBuilder = new DriverBuilder($supportedDrivers);

        $middlewareKernel = new MiddlewareKernel($middleware);

        return new Auth($driverBuilder, $middlewareKernel);
    }

    protected function mockSimpleLoginDriver()
    {
        $authorizerDriver = \Mockery::mock(DriverInterface::class);

        $authorizerDriver->shouldReceive('login')
            ->withAnyArgs()
            ->andReturnUsing(function (Input $input) {
                if ($input->get('username') == 'u1' && $input->get('password') == 'p1') {
                    return true;
                }
                return false;
            });

        return $authorizerDriver;
    }
}
