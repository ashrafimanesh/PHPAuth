<?php


namespace Assin\PHPAuth\Contracts;
/**
 * An interface to return instance of DriverInterface
 * @package Assin\PHPAuth\Contracts
 */
interface DriverBuilderInterface
{
    public function getDriver($driverId): DriverInterface;
}