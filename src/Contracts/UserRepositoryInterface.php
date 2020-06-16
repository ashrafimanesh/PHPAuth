<?php


namespace Assin\PHPAuth\Contracts;


use Assin\PHPAuth\ObjectValue\Input;

interface UserRepositoryInterface
{
    public function findForLogin(Input $input): UserInterface;

    public function findForForgetPassword(Input $input): UserInterface;
}