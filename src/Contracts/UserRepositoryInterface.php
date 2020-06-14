<?php


namespace Assin\PHPAuth\Contracts;


use Assin\PHPAuth\ObjectValue\Input;

interface UserRepositoryInterface
{
    public function findByInput(Input $input): UserInterface;
}