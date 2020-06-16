<?php


namespace Assin\PHPAuth\Drivers\JWT;


use Assin\PHPAuth\Contracts\CodeGeneratorInterface;
use Assin\PHPAuth\Contracts\DriverInterface;
use Assin\PHPAuth\Contracts\MiddlewareInterface;
use Assin\PHPAuth\Contracts\UserInterface;
use Assin\PHPAuth\Contracts\UserRepositoryInterface;
use Assin\PHPAuth\ObjectValue\Input;
use Closure;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

/**
 * Class JWTDriver
 * @package Assin\PHPAuth\Drivers\JWT
 */
class JWTDriver implements DriverInterface
{
    protected $tokenPrefix = "Bearer ";
    protected $headerKeyName = 'header.Authorization';

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $middleware = [
        //Group to call by Auth class before the action call.
        'public' => [],
        //Group to call in login
        'login' => [],
        //Group to call in forget password
        'forgotPassword' => [],
    ];
    /** @var CodeGeneratorInterface */
    protected $codeGenerator;

    public $codeLength = 8;

    public function __construct(Config $config = null, UserRepositoryInterface $userRepository = null, CodeGeneratorInterface $codeGenerator = null)
    {
        $this->userRepository = $userRepository;
        $this->config = $config ?: new Config();
        $this->codeGenerator = $codeGenerator ?: new CodeGenerator();
    }

    /**
     * Use this method to create a token for guest or logged in user.
     * Call getJWTData method to getJWTData if you need old jwtData in old token.
     * @param array $userInfo
     * @return Token
     */
    public function createToken(array $userInfo = []): Token
    {
        $time = time();

        $builder = $this->config->getBuilder()
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + $this->config->getExpireAfter());

        return $builder->withClaim('user_info', $userInfo)
            ->getToken($this->config->getSigner(), $this->config->getKey()); // Retrieves the generated token
    }

    /**
     * @param Token $token
     * @return Token
     */
    public function refreshToken(Token $token): Token
    {
        $time = time();
        $builder = $this->config->getBuilder();
        foreach ($token->getClaims() as $name => $value) {
            $builder->withClaim($name, $value);
        }
        $builder->expiresAt($time + $this->config->getExpireAfter());
        return $builder->getToken($this->config->getSigner(), $this->config->getKey());
    }

    /**
     * Authorize the user with UserRepositoryInterface and old token(optional).
     * The new token has user_info payload with id, name and some other fields value.
     * @important if the user was invalid this method return a response with status code(501|502).
     * @param Input $input
     * @return Response
     * @throws JWTUserRepositoryException
     */
    public function login(Input $input): Response
    {
        try {
            list($user, $jwtData) = $this->checkUser($input, function () use ($input) {
                return $this->userRepository->findForLogin($input);
            });
        } catch (JWTInValidUserException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        $token = $this->createToken($this->makeUserInfo($user, $jwtData)->toArray());

        return (new Response())->setData(['token' => $this->plainToken($token, true), 'user' => $user]);
    }

    /**
     * @param Input $input
     * @return Response
     * @throws JWTUserRepositoryException
     */
    public function forgotPassword(Input $input)
    {
        try {
            /** @var UserInterface $user */
            list($user, $jwtData) = $this->checkUser($input, function () use ($input) {
                return $this->userRepository->findForForgetPassword($input);
            });
        } catch (JWTInValidUserException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        $userInfo = $this->makeUserInfo($user, $jwtData)->toArray();
        $userInfo['forget_time'] = time();

        $token = $this->createToken($userInfo);

        return (new Response())->setData(['token' => $this->plainToken($token, true), 'user' => $user, 'code' => $this->generateCode($user)]);
    }

    public function getMiddleware(string $groupName): array
    {
        $middleware = $this->middleware['public'] ?? [];
        if (isset($this->middleware[$groupName])) {
            foreach ($this->middleware[$groupName] as $item) {
                $middleware[] = $item;
            }
        }
        return $middleware;
    }

    public function addMiddleware(string $groupName, MiddlewareInterface $middleware)
    {
        if (!isset($this->middleware[$groupName])) {
            $this->middleware[$groupName] = [];
        }
        $this->middleware[$groupName][] = $middleware;
        return $this;
    }

    public function parseToTokenObject($token): Token
    {
        return (new Parser())->parse($this->plainToken($token)); // Parses from a string
    }


    public function plainToken($token, $reverse = false): string
    {
        $token = str_replace($this->tokenPrefix, "", (string)$token);
        return $reverse ? $this->tokenPrefix . $token : $token;
    }

    /**
     * Extract jwt data from token
     * @param $headerAuthorization
     * @return array
     */
    public function getJWTData($headerAuthorization): array
    {
        $jwtData = [];
        if ($headerAuthorization) {
            $token = $this->parseToTokenObject($headerAuthorization);
            $validationData = new ValidationData();
            $validationData->setId($this->config->getId());
            $jwtData = $this->convertClaimToJWTData($token);
        }
        return $jwtData;
    }

    /**
     * @param Token $token
     * @return mixed
     */
    public function convertClaimToJWTData(Token $token)
    {
        return json_decode(json_encode($token->getClaims()), true); // Retrieves the token claims
    }

    /**
     * @return string
     */
    public function getHeaderKeyName(): string
    {
        return $this->headerKeyName;
    }

    /**
     * @param UserInterface $user
     * @param array $jwtData
     * @return UserInfo
     */
    protected function makeUserInfo(UserInterface $user = null, array $jwtData = []): UserInfo
    {
        return new UserInfo($user, $jwtData);
    }

    /**
     * @return UserRepositoryInterface
     */
    public function getUserRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Input $input
     * @param Closure $userFinder
     * @return array
     * @throws JWTUserRepositoryException
     * @throws JWTInValidUserException
     */
    protected function checkUser(Input $input, Closure $userFinder)
    {
        if (is_null($this->userRepository)) {
            throw new JWTUserRepositoryException('Invalid user repository on jwt driver!');
        }

        $jwtData = $this->getJWTData($input->get($this->headerKeyName));

        if ($jwtData) {
            $input->set('jwt_data', $jwtData);
        }

        $user = $userFinder();

        if (!$user->getUserId()) {
            throw new JWTInValidUserException('Invalid user!', Response::STATUS_INVALID_USER);
        }

        if ((isset($jwtData['user_info']['id']) && $user->getUserId() != $jwtData['user_info']['id'])) {
            throw new JWTInValidUserException('Mismatch user id!', Response::STATUS_MISMATCH_USER_ID);
        }
        return [$user, $jwtData];
    }

    protected function generateCode(UserInterface $user)
    {
        return $this->codeGenerator->generate($user);
    }

}