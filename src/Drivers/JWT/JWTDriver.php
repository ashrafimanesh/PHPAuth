<?php


namespace Assin\PHPAuth\Drivers\JWT;


use Assin\PHPAuth\Contracts\DriverInterface;
use Assin\PHPAuth\Contracts\UserInterface;
use Assin\PHPAuth\Contracts\UserRepositoryInterface;
use Assin\PHPAuth\ObjectValue\Input;
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
    private $userRepository;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config = null, UserRepositoryInterface $userRepository = null)
    {
        $this->userRepository = $userRepository;
        $this->config = $config ?: new Config();
    }

    /**
     * Use this method to create a token for guest or logged in user.
     * Call getJWTData method to getJWTData if you need old jwtData in old token.
     * @param UserInterface $user
     * @param array $jwtData
     * @return Token
     */
    public function createToken(UserInterface $user = null, array $jwtData = []): Token
    {
        $time = time();

        $builder = $this->config->getBuilder()
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + $this->config->getExpireAfter());

        $userInfo = $this->makeUserInfo($user, $jwtData)->toArray();

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
        foreach($token->getClaims() as $name=>$value){
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
        if(is_null($this->userRepository)){
            throw new JWTUserRepositoryException('Invalid user repository on jwt driver!');
        }

        $jwtData = $this->getJWTData($input->get($this->headerKeyName));

        if ($jwtData) {
            $input->set('jwt_data', $jwtData);
        }
        $user = $this->userRepository->findByInput($input);
        if (!$user->getUserId()) {
            return new Response(Response::STATUS_INVALID_USER, 'Invalid user!');
        }

        if((isset($jwtData['user_info']['id']) && $user->getUserId() != $jwtData['user_info']['id'])){
            return new Response(Response::STATUS_MISMATCH_USER_ID, 'Mismatch user id!');
        }

        $token = $this->createToken($user, $jwtData);

        return (new Response())->setData(['token' => $this->plainToken($token, true)]);
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

}