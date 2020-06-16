<?php


namespace Assin\PHPAuth\Middleware;


use Assin\PHPAuth\Contracts\MiddlewareInterface;
use Assin\PHPAuth\ObjectValue\Input;

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

    public function run(Input $input, array $privateMiddleware = [])
    {
        $pointer = 0;
        $middleware = $this->middleware;
        foreach ($privateMiddleware as $item){
            $middleware[] = $item;
        }
        $next = function ($input) use ($middleware, &$pointer, &$next) {
            if (!isset($middleware[$pointer])) {
                return null;
            }
            $middleware = $this->getMiddleware($middleware, $pointer);
            $pointer++;
            return $middleware->handle($input, $next);
        };
        $next($input);
    }

    /**
     * @param array $middleware
     * @param int $pointer
     * @return MiddlewareInterface
     */
    protected function getMiddleware(array $middleware, int $pointer): MiddlewareInterface
    {
        return $middleware[$pointer];
    }
}