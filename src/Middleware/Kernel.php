<?php


namespace Dennis\PHPAuth\Middleware;


use Dennis\PHPAuth\Contracts\MiddlewareInterface;
use Dennis\PHPAuth\ObjectValue\Input;

class Kernel
{

    /**
     * @var array
     */
    private $middleware = [];

    public function __construct($middleware = [])
    {
        $this->middleware = $middleware;
    }

    public function run(Input $input)
    {
        $pointer = 0;
        $next = function ($input) use (&$pointer, &$next) {
            if (!isset($this->middleware[$pointer])) {
                return null;
            }
            $middleware = $this->getMiddleware($pointer);
            $pointer++;
            return $middleware->handle($input, $next);
        };
        $next($input);
    }

    /**
     * @param int $pointer
     * @return MiddlewareInterface
     */
    protected function getMiddleware(int $pointer): MiddlewareInterface
    {
        return $this->middleware[$pointer];
    }
}