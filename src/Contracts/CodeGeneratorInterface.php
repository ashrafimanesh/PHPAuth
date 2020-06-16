<?php


namespace Assin\PHPAuth\Contracts;


interface CodeGeneratorInterface
{
    public function generate(UserInterface $user): string;
}