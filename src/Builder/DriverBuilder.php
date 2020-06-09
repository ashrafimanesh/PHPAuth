<?php


namespace Assin\PHPAuth\Builder;

use Assin\PHPAuth\Contracts\DriverBuilderInterface;
use Assin\PHPAuth\Contracts\DriverInterface;
use Assin\PHPAuth\Exception\InvalidDriverException;

class DriverBuilder implements DriverBuilderInterface
{

    /**
     * @var array
     */
    protected $supportedDrivers = [];

    public function __construct(array $supportedDrivers)
    {
        $this->supportedDrivers = $supportedDrivers;
    }

    /**
     * @param $driverId
     * @return DriverInterface
     * @throws InvalidDriverException
     */
    public function getDriver($driverId): DriverInterface
    {
        if (!isset($this->supportedDrivers[$driverId])) {
            throw new InvalidDriverException('Invalid driver ID');
        }

        return $this->supportedDrivers[$driverId];
    }
}