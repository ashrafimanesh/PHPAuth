<?php


namespace Assin\PHPAuth\Drivers\JWT;


use Assin\PHPAuth\Contracts\UserInterface;

class UserInfo
{
    /**
     * @var UserInterface
     */
    private $user;
    /**
     * @var array
     */
    private $jwtData = [];

    public function __construct(UserInterface $user = null, array $jwtData = [])
    {
        $this->user = $user;
        $this->jwtData = $jwtData;
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    public function toArray()
    {
        if(!$this->user){
            return [];
        }
        $userId = $this->user->getUserId();
        if (!$userId) {
            return [];
        }
        return ['id' => $userId];
    }

    /**
     * @return UserInterface|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getJwtData(): array
    {
        return $this->jwtData;
    }
}