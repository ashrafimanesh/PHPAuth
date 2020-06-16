<?php


namespace Assin\PHPAuth\Drivers\JWT;


use Assin\PHPAuth\Contracts\CodeGeneratorInterface;
use Assin\PHPAuth\Contracts\UserInterface;

class CodeGenerator implements CodeGeneratorInterface
{
    /** @var string|null */
    protected $repo;
    /** @var int */
    protected $length = 8;

    public function __construct(string $repo = null, int $length = 8)
    {
        $this->length = $length;
        $this->repo = $repo ?: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    }

    public function generate(UserInterface $user): string
    {
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($this->repo) - 1; //put the length -1 in cache
        for ($i = 0; $i < $this->length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $this->repo[$n];
        }
        return implode($pass); //turn the array into a string        return mt_rand();
    }
}