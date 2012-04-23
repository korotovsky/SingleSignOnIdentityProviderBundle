<?php

namespace FM\SingleSignOnBundle\Firewall;

use FM\SingleSignOnBundle\Authentication\Token\OneTimePasswordToken;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OneTimePasswordListener extends AbstractAuthenticationListener
{
    protected function attemptAuthentication(Request $request)
    {
        // fetch
        $otp = $request->get('_otp');

        try {

            $token = $this->authenticationManager->authenticate(new OneTimePasswordToken($otp));

            if (null !== $this->logger) {
                $this->logger->debug('SecurityContext populated with OneTimePassword token.');
            }

            return $token;

        } catch (AuthenticationException $e) {
            // you might log something here
            if (null !== $this->logger) {
                $this->logger->warn(sprintf('Not authenticated with OneTimePassword: ' . $e->getMessage()));
            }
        }
    }
}