<?php


namespace Assin\PHPAuth\Drivers\JWT;


class Response
{
    const STATUS_INVALID_USER = 501;
    const STATUS_MISMATCH_USER_ID = 502;
    /**
     * @var int
     */
    private $status;
    /**
     * @var string
     */
    private $message;

    private $data;

    public function __construct(int $status = 200, string $message = '')
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $data
     * @return Response
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}