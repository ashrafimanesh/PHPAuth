<?php


namespace Assin\PHPAuthTests\Unit;


use Assin\PHPAuth\Config\Config;
use Assin\PHPAuth\Contracts\DriverInterface;
use Assin\PHPAuth\Exception\InvalidDriverException;
use Assin\PHPAuth\ObjectValue\Input;
use Assin\PHPAuthTests\TestCase;

class AuthorizationTest extends TestCase
{
    public function testSimple()
    {
        $driverId = Config::DRIVER_USERNAME_PASSWORD;
        $input = new Input(['token' => '1234']);
        $authorizerDriver = \Mockery::mock(DriverInterface::class);
        $authorizerDriver->shouldReceive('getMiddleware')
            ->withNoArgs()->andReturn([]);

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