<?php

namespace FM\SingleSignOnBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OneTimePasswordToken extends AbstractToken
{
    private $credentials;

    public function __construct($credentials, array $roles = array())
    {
        parent::__construct($roles);

        $this->credentials = $credentials;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}