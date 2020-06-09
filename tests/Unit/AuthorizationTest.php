<?php


namespace Dennis\PHPAuthTests\Unit;


use Dennis\PHPAuth\Config\Config;
use Dennis\PHPAuth\Contracts\DriverInterface;
use Dennis\PHPAuth\Exception\InvalidDriverException;
use Dennis\PHPAuth\ObjectValue\Input;
use Dennis\PHPAuthTests\TestCase;

class AuthorizationTest extends TestCase
{
    public function testSimple()
    {
        $driverId = Config::DRIVER_USERNAME_PASSWORD;
        $input = new Input(['token' => '1234']);
        $authorizerDriver = \Mockery::mock(DriverInterface::class);

        $authorizerDriver->shouldReceive('isLogin')
            ->withAnyArgs()
            ->andReturnUsing(function (Input $input) {
                return $input->get('token') == '1234';
            });

        try {
            $auth = $this->makeAuth([$driverId => $authorizerDriver]);
            $this->assertTrue($auth->isLogin($driverId, $input));
        } catch (InvalidDriverException $e) {
            $this->assertTrue(false, $e->getMessage());
        }
    }
}