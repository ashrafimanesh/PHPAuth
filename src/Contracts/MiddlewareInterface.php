<?php


namespace Dennis\PHPAuth\Contracts;


use Dennis\PHPAuth\ObjectValue\Input;

/**
 * An interface to handle middleware before call authentication method(login, isLogin, etc).
 * @package Dennis\PHPAuth\Contracts
 */
interface MiddlewareInterface
{
    /**
     * This method can break next middleware calling or call $next($input) if every think is ok.
     * @param Input $input
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Input $input, \Closure $next);
}