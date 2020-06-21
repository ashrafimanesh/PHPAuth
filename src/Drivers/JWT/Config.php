<?php


namespace Assin\PHPAuth\Drivers\JWT;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim\Factory as ClaimFactory;
use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class Config
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var int
     */
    protected $expireAfter;

    public $issuedBy;
    public $permittedFor;
    protected $key='test';


    /**
     * Config constructor.
     * @param string $id
     * @param int $expireAfter (seconds)
     */
    public function __construct(string $id = '4f1g23a12aa', int $expireAfter = 3600)
    {
        $this->id = $id;
        $this->expireAfter = $expireAfter;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id ?: '';
    }

    /**
     * @return int
     */
    public function getExpireAfter(): int
    {
        return $this->expireAfter ?: 0;
    }

    public function getIssuedBy()
    {
        return $this->issuedBy;
    }

    public function getPermittedFor()
    {
        return $this->permittedFor;
    }

    /**
     * Create Builder instance and sets issuedBy, permittedFor and identifiedBy attributes.
     * @param Encoder|null $encoder
     * @return Builder
     */
    public function getBuilder(Encoder $encoder = null)
    {
        $builder = (new Builder($encoder));
        if ($this->getIssuedBy()) {
            $builder->issuedBy($this->getIssuedBy());
        }
        if ($this->getPermittedFor()) {
            $builder->permittedFor($this->getPermittedFor());
        }

        return $builder->identifiedBy($this->getId(), true); // Configures the id (jti claim), replicating as a header item
    }

    public function getSigner()
    {
        return new Sha256();
    }

    public function getKey()
    {
        return new Key($this->key);
    }
}