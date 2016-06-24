Single Sign On Identity Provider
================================

[![Build Status](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/badges/build.png?b=0.3.x)](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/build-status/0.3.x)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/badges/quality-score.png?b=0.3.x)](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/?branch=0.3.x)
[![Code Coverage](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/badges/coverage.png?b=0.3.x)](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/?branch=0.3.x)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d68cc257-6cfc-4e66-9c51-28be57b347c4/mini.png?v=1)](https://insight.sensiolabs.com/projects/d68cc257-6cfc-4e66-9c51-28be57b347c4)

Disclaimer
----------
I am by no means a security expert. I'm not bad at it either, but I cannot vouch for the security of this bundle.
You can use this in production if you want, but please do so at your own risk.
That said, if you'd like to contribute to make this bundle better/safer, you can always [create an issue](https://github.com/korotovsky/SingleSignOnIdentityProviderBundle/issues) or send [a pull request](https://github.com/korotovsky/SingleSignOnIdentityProviderBundle/pulls).

Description
-----------
This bundle provides an easy way to integrate a single-sign-on in your website. It uses an existing ('main') firewall for the actual authentication,
and redirects all configured SSO-routes to authenticate via a one-time-password.

Installation
------------
Install using composer:

```
php composer.phar require "korotovsky/sso-idp-bundle:~0.3.0"
```

Enable the bundle in the kernel:

``` php
// app/AppKernel.php
$bundles[] = new \Krtv\Bundle\SingleSignOnIdentityProviderBundle\KrtvSingleSignOnIdentityProviderBundle();
```

Configuration
-------------

Enable sso-routes:

``` yaml
# app/config/routing.yml:
sso:
    resource: .
    type:     sso
```

The bundle relies on an existing firewall to provide the actual authentication.
To do this, you have to configure the single-sign-on login path to be behind that firewall,
and make sure you need to be authenticated to access that route.

``` yaml
# app/config/config.yml:
krtv_single_sign_on_identity_provider:
    host:             idp.example.com
    host_scheme:      http

    login_path:       /sso/login/
    logout_path:      /sso/logout

    services:
        - consumer1
        - consumer2

    otp_parameter:    _otp
    secret_parameter: secret
```

You must create the service providers.
Each ServiceProvider must implement `Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface`.

``` php
<?php
// src/AcmeBundle/ServiceProviders/Consumer1.php

namespace AcmeBundle\ServiceProviders;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;

/**
 * Consumer 1 service provider
 */
class Consumer1 implements ServiceProviderInterface
{
    /**
     * Get name of the service
     *
     * @return string
     */
    public function getName()
    {
        return 'consumer1';
    }

    /**
     * Get service provider index url
     *
     * @param  array  $parameters
     *
     * @return string
     */
    public function getServiceIndexUrl($parameters = [])
    {
        return 'http://consumer1.com/';
    }

    /**
     * Get service provider logout url
     *
     * @param  array  $parameters
     *
     * @return string
     */
    public function getServiceLogoutUrl($parameters = [])
    {
        return 'http://consumer1.com/logout';
    }
}
```

``` php
<?php
// src/AcmeBundle/ServiceProviders/Consumer2.php

namespace AcmeBundle\ServiceProviders;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface;

/**
 * Consumer 2 service provider
 */
class Consumer2 implements ServiceProviderInterface
{
    /**
     * Get name of the service
     *
     * @return string
     */
    public function getName()
    {
        return 'consumer2';
    }

    /**
     * Get service provider index url
     *
     * @param  array  $parameters
     *
     * @return string
     */
    public function getServiceIndexUrl($parameters = [])
    {
        return 'http://consumer2.com/';
    }

    /**
     * Get service provider logout url
     *
     * @param  array  $parameters
     *
     * @return string
     */
    public function getServiceLogoutUrl($parameters = [])
    {
        return 'http://consumer2.com/logout';
    }
}
```

And define them as services.

``` yaml
# app/config/services.yml
services:
    acme_bundle.sso.consumer1:
        class: AcmeBundle\ServiceProviders\Consumer1
        tags:
            - { name: sso.service_provider, service: consumer1 }

    acme_bundle.sso.consumer2:
        class: AcmeBundle\ServiceProviders\Consumer2
        tags:
            - { name: sso.service_provider, service: consumer2 }
```

We need to allow users to access the /sso/login route without being logged in

``` yaml
# app/config/security.yml
security:
    access_control:
        - { path: ^/sso/login, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

You need to create an OTP retrieving route that will be used by the SP bundle.
The route doesn't really matter, but take note of it. It will be used in the SP bundle.

``` php
<?php
// src/AcmeBundle/Controller/OtpController.php

namespace AcmeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OtpController extends Controller
{
    /**
     * Method used for retrieving of the OTP
     *
     * @Route("/internal/v1/sso", name="sso_otp")
     * @Method("GET")
     *
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        /** @var \Krtv\SingleSignOn\Manager\OneTimePasswordManagerInterface */
        $otpManager = $this->get('sso_identity_provider.otp_manager');

        $pass = str_replace(' ', '+', $request->query->get('_otp'));

        /** @var \Krtv\SingleSignOn\Model\OneTimePasswordInterface */
        $otp = $otpManager->get($pass);

        $response = ['data' => []];

        if (!empty($otp)) {
            $response = [
                'data' => [
                    'created_at' => $otp->getCreated()->format('r'),
                    'hash' => $otp->getHash(),
                    'password' => $otp->getPassword(),
                    'is_used' => $otp->getUsed(),
                ],
            ];
        }

        return new JsonResponse($response);
    }
}
```

In your login form, add a hidden input with the name `_target_path` and the value `{{ app.request.query.get('_target_path') }}` like so:

``` twig
<input type="hidden" name="_target_path" value="{{ app.request.query.get('_target_path') }}" />
```
This will be used to redirect the user after login to the OTP validation route.

Doctrine
--------

To be able to store the OTPs, you must run the command:

```
php bin/console doctrine:schema:update --force
```

Public API of this bundle
-------------------------

This bundle registers several services into service container. These services will help you customize SSO flow in the your application:

- [sso_identity_provider.service_manager](https://github.com/korotovsky/SingleSignOnIdentityProviderBundle/blob/0.3.x/src/Krtv/Bundle/SingleSignOnIdentityProviderBundle/Manager/ServiceManager.php) – Manager to work with SP. By given SP-identifier it returns an instance of `\Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface`
- [sso_identity_provider.otp_manager](https://github.com/korotovsky/SingleSignOnLibrary/blob/0.3.x/src/Krtv/SingleSignOn/Manager/OneTimePasswordManagerInterface.php) – Manager to work with OTP-tokens. Validation, invalidation and receiving.
- [sso_identity_provider.uri_signer](https://github.com/symfony/symfony/blob/2.7/src/Symfony/Component/HttpKernel/UriSigner.php) – Service for signing URLs, if you need to redirect users to /sso/login yourself.

That's it for Identity Provider.
Now you can continue configure [ServiceProvider part](https://github.com/korotovsky/SingleSignOnServiceProviderBundle#single-sign-on-service-provider)
