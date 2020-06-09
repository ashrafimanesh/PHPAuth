<?php


namespace Assin\PHPAuthTests\Unit;

use Assin\PHPAuth\Config\Config;
use Assin\PHPAuth\Contracts\DriverInterface;
use Assin\PHPAuth\Exception\InvalidDriverException;
use Assin\PHPAuth\ObjectValue\Input;
use Assin\PHPAuthTests\TestCase;
use \Mockery;

class LoginTest extends TestCase
{
    protected $username = 'u1';
    protected $password = 'p1';

    public function testSimple()
    {
        $input = $this->getInput();

        $driverId = Config::DRIVER_USERNAME_PASSWORD;
        $authorizer = $this->mockSimpleLoginDriver();

        try {
            $auth = $this->makeAuth([$driverId => $authorizer]);
            $response = $auth->login($driverId, $input);
            $this->assertTrue($response);
        } catch (InvalidDriverException $e) {
            $this->assertTrue(false, $e->getMessage());
        }

    }

    public function testSimpleTwoStep()
    {
        $input = $this->getInput();

        $driverId = Config::DRIVER_USERNAME_PASSWORD;
        $authorizer = $this->mockSimpleTwoStepAuthorizerDriver();

        try {
            $auth = $this->makeAuth([$driverId => $authorizer]);
            $response = $auth->login($driverId, $input);
            $this->assertEquals('Enter verification code:', $response);
            $input->set('verification_code', '1234');
            $this->assertTrue($auth->login($driverId, $input));
        } catch (InvalidDriverException $e) {
            $this->assertTrue(false, $e->getMessage());
        }
    }

    protected function mockSimpleTwoStepAuthorizerDriver()
    {
        $authorizerDriver = Mockery::mock(DriverInterface::class);

        $authorizerDriver->shouldReceive('login')
            ->withAnyArgs()
            ->andReturnUsing(function (Input $input) {
                if ($input->get('username') == 'u1' && $input->get('password') == 'p1') {
                    $input->set('username', null);
                    $input->set('password', null);
                    //set username and password is correct on session and then return:
                    return 'Enter verification code:';
                } elseif ($input->get('verification_code') == '1234') {
                    return true;
                }
                return false;
            });

        return $authorizerDriver;
    }

    /**
     * @return Input
     */
    protected function getInput(): Input
    {
        return new Input(['username' => $this->username, 'password' => $this->password]);
    }
}