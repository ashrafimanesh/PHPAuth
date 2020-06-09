<?php


namespace Dennis\PHPAuth\Contracts;
/**
 * An interface to return instance of DriverInterface
 * @package Dennis\PHPAuth\Contracts
 */
interface DriverBuilderInterface
{
    public function getDriver($driverId): DriverInterface;
}