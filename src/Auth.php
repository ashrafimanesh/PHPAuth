<?php

namespace Assin\PHPAuth;

use Assin\PHPAuth\Builder\DriverBuilder;
use Assin\PHPAuth\Middleware\Kernel as MiddlewareKernel;
use Assin\PHPAuth\ObjectValue\Input;

/**
 * Class Auth
 * @package Assin\PHPAuth
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
    public function __construct(DriverBuilder $driverBuilder, MiddlewareKernel $middlewareKernel = null)
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
        $driver = $this->beforeAction($driverId, $input);
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
        $driver = $this->beforeAction($driverId, $input);
        return call_user_func_array([$driver, 'isLogin'], [$input]);
    }

    /**
     * @param $driverId
     * @param Input $input
     * @return Contracts\DriverInterface
     * @throws Exception\InvalidDriverException
     */
    protected function beforeAction($driverId, Input $input): Contracts\DriverInterface
    {
        $driver = $this->driverBuilder->getDriver($driverId);
        if ($this->middlewareKernel) {
            $this->middlewareKernel->run($input);
        }
        return $driver;
    }
}