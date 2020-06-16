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
        $this->middlewareKernel = $middlewareKernel ?: new MiddlewareKernel([]);
    }

    /**
     * @param $method
     * @param $driverId
     * @param Input $input
     * @param array $args to pass more thant Input argument to driver method.
     * @return mixed
     * @throws Exception\InvalidDriverException
     */
    public function callDriverMethod($method, $driverId, Input $input, array $args = [])
    {
        $driver = $this->beforeAction($driverId, $input, $method);
        array_unshift($args, $input);
        return call_user_func_array([$driver, $method], $args);
    }

    /**
     * @param $driverId
     * @param Input $input
     * @return mixed
     * @throws Exception\InvalidDriverException
     */
    public function login($driverId, Input $input)
    {
        $action = 'login';
        $driver = $this->beforeAction($driverId, $input, $action);
        return call_user_func_array([$driver, $action], [$input]);
    }

    /**
     * @param $driverId
     * @param Input $input
     * @return mixed
     * @throws Exception\InvalidDriverException
     */
    public function isLogin($driverId, Input $input)
    {
        $action = 'isLogin';
        $driver = $this->beforeAction($driverId, $input, $action);
        return call_user_func_array([$driver, $action], [$input]);
    }

    /**
     * @param $driverId
     * @param Input $input
     * @param string $action
     * @return Contracts\DriverInterface
     * @throws Exception\InvalidDriverException
     */
    protected function beforeAction($driverId, Input $input, string $action): Contracts\DriverInterface
    {
        $driver = $this->driverBuilder->getDriver($driverId);
        if(method_exists($driver, 'getMiddleware')){
            $privateMiddleware = $driver->getMiddleware($action);
        }
        $this->middlewareKernel->run($input, $privateMiddleware ?? []);

        return $driver;
    }
}