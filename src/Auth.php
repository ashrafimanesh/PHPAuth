<?php

namespace Dennis\PHPAuth;

use Dennis\PHPAuth\Builder\DriverBuilder;
use Dennis\PHPAuth\Middleware\Kernel as MiddlewareKernel;
use Dennis\PHPAuth\ObjectValue\Input;

/**
 * Class Auth
 * @package Dennis\PHPAuth
 */
class Auth
{
    /**
     * @var DriverBuilder
     */
    protected $driverBuilder;
    /**
     * @var MiddlewareKernel
     */
    protected $middlewareKernel;

    /**
     * Auth constructor.
     * @param DriverBuilder $driverBuilder
     * @param MiddlewareKernel $middlewareKernel
     */
    public function __construct(DriverBuilder $driverBuilder, MiddlewareKernel $middlewareKernel)
    {
        $this->driverBuilder = $driverBuilder;
        $this->middlewareKernel = $middlewareKernel;
    }

    /**
     * @param $driverId
     * @param Input $input
     * @return mixed
     * @throws Exception\InvalidDriverException
     */
    public function login($driverId, Input $input)
    {
        $driver = $this->driverBuilder->getDriver($driverId);
        $this->middlewareKernel->run($input);
        return call_user_func_array([$driver, 'login'], [$input]);
    }

    /**
     * @param $driverId
     * @param Input $input
     * @return mixed
     * @throws Exception\InvalidDriverException
     */
    public function isLogin($driverId, Input $input)
    {
        $driver = $this->driverBuilder->getDriver($driverId);
        $this->middlewareKernel->run($input);
        return call_user_func_array([$driver, 'isLogin'], [$input]);
    }
}