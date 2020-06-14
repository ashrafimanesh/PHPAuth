<?php


namespace Assin\PHPAuth\Drivers\JWT;


use Assin\PHPAuth\Contracts\UserInterface;

class User implements UserInterface
{
    /**
     * @var int
     */
    private $userId;

    public function __construct($userId = 0)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}