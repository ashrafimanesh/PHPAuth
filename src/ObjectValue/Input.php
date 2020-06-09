<?php


namespace Assin\PHPAuth\ObjectValue;

/**
 * Class Input keep all data that needs on authentication actions(login, isLogin, etc)
 * @package Assin\PHPAuth\ObjectValue
 */
class Input
{
    /**
     * @var null
     */
    private $data;
    /**
     * @var null
     */
    private $initData;

    public function __construct($data = null)
    {
        $this->initData = $data;
        $this->data = $data;
    }

    public function reset()
    {
        $this->data = $this->initData;
        return $this;
    }

    public function get($key, $default = null)
    {
        return (is_array($this->data) ? $this->getFromArray($key, $default) : $this->getFromObject($key, $default));
    }

    private function getFromArray($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    private function getFromObject($key, $default = null)
    {
        $methodName = 'get' . ucwords($key);
        return method_exists($this->data, $methodName)
            ? call_user_func_array([$this->data, $methodName], [$default])
            : ($this->data->$key ?? $default);
    }

    public function set(string $key, $value)
    {
        $methodName = 'set' . ucwords($key);
        if(is_array($this->data)){
            $this->data[$key] = $value;
        }
        else if(method_exists($this->data, $methodName)){
            call_user_func_array([$this->data, $methodName], [$value]);
        }
        else{
            $this->data->$key = $value;
        }
        return $this;

    }
}