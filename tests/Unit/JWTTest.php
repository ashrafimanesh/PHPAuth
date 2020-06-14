<?php


namespace Assin\PHPAuthTests\Unit;


use Assin\PHPAuth\Auth;
use Assin\PHPAuth\Builder\DriverBuilder;
use Assin\PHPAuth\Config\Config;
use Assin\PHPAuth\Contracts\UserInterface;
use Assin\PHPAuth\Contracts\UserRepositoryInterface;
use Assin\PHPAuth\Drivers\JWT\JWTDriver;
use Assin\PHPAuth\Drivers\JWT\Response;
use Assin\PHPAuth\ObjectValue\Input;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class JWTTest extends \Assin\PHPAuthTests\TestCase
{
    private $userId = 1;

    public function testCreateGuestToken()
    {
        $JWTDriver = new JWTDriver();
        $token = $JWTDriver->createToken();
        $this->assertTrue($JWTDriver->plainToken($token, true) ? true : false);
    }

    public function testLoginWithGuestToken()
    {
        $input = new Input();
        $input->set('header.Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjRmMWcyM2ExMmFhIn0.eyJqdGkiOiI0ZjFnMjNhMTJhYSIsImlhdCI6MTU5MjAzNzkzOCwiZXhwIjoxNTkyMDM3OTM5LCJ1c2VyX2luZm8iOltdfQ.yyJWz61cee6XsbCHfat5MbUVsfpU5t8CY2LixUMnu0s');
        $input->set('username', '1234')->set('password', '1234');
        $driverId = Config::DRIVER_JWT;
        $userRepository = $this->mockUserRepository();

        $JWTDriver = new JWTDriver(new \Assin\PHPAuth\Drivers\JWT\Config(), $userRepository);

        $driverBuilder = new DriverBuilder([$driverId => $JWTDriver]);

        /** @var Response $response */
        $response = (new Auth($driverBuilder))->login($driverId, $input);

        $this->assertTrue($response->getStatus() == 200, $response->getMessage());
        $this->assertTrue($response->getData()['token'] ? true : false);
        $token = $JWTDriver->parseToTokenObject($response->getData()['token']);
        $tokenUserId = $JWTDriver->convertClaimToJWTData($token)['user_info']['id'];
        $this->assertTrue($tokenUserId == $this->userId);
    }

    public function testRefreshToken()
    {
        $oldToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjRmMWcyM2ExMmFhIn0.eyJqdGkiOiI0ZjFnMjNhMTJhYSIsImlhdCI6MTU5MjExNDczMywiZXhwIjoxNTkyMTE4MzMzLCJ1c2VyX2luZm8iOnsiaWQiOjF9fQ.gLhkKVtSjx5pDLX973grZ94CHKv5gsYw99sfeX5XzQs';
        $JWTDriver = new JWTDriver();
        $token = $JWTDriver->parseToTokenObject($oldToken);
        $newToken = $JWTDriver->refreshToken($token);
        $this->assertTrue(($JWTDriver->plainToken($newToken, true) != $oldToken)
            && ((string)$token->getClaim('exp') < (string)$newToken->getClaim('exp')));
    }

    /**
     * @return UserRepositoryInterface|LegacyMockInterface|MockInterface
     */
    protected function mockUserRepository()
    {
        $userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $guestUser = \Mockery::mock(UserInterface::class);
        $user = \Mockery::mock(UserInterface::class);

        $guestUser->shouldReceive('getUserId')->withNoArgs()->andReturn(0);

        $user->shouldReceive('getUserId')->withNoArgs()->andReturn($this->userId);

        $userRepository->shouldReceive('findByInput')
            ->withAnyArgs()->andReturnUsing(function (Input $input) use ($user, $guestUser) {
                if (!$input->get('username') || !$input->get('password')) {
                    return $guestUser;
                }
                return $user;
            });
        return $userRepository;
    }
}