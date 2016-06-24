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
Installation is a quick 9 steps process:

1. Download SingleSignOnIdentityProviderBundle using composer
2. Enable the bundle
3. Create service provider(s)
4. Configure SingleSignOnIdentityProviderBundle
5. Enable the route to validate OTP
6. Modify security settings
7. Create OTP route
8. Add redirect path to login form
9. Update database schema

### Step 1: Download SingleSignOnIdentityProviderBundle using composer

Tell composer to require the package:

``` bash
composer require korotovsky/sso-idp-bundle
```

Composer will install the bundle to your project's `vendor/korotovsky` directory.

### Step 2: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Krtv\Bundle\SingleSignOnIdentityProviderBundle\KrtvSingleSignOnIdentityProviderBundle(),
    ];
}
?>
```

### Step 3: Create service provider(s)

You have to create a ServiceProvider for each application that uses the SSO SP bundle.

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
?>
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
?>
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

### Step 4: Configure SingleSignOnIdentityProviderBundle

The bundle relies on an existing firewall to provide the actual authentication.
To do this, you have to configure the single-sign-on login path to be behind that firewall,
and make sure you need to be authenticated to access that route.

Add the following settings to your **config.yml**.

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


### Step 5: Enable route to validate OTP

``` yaml
# app/config/routing.yml
sso:
    resource: .
    type:     sso
```

### Step 6: Modify security settings

``` yaml
# app/config/security.yml
security:
    firewalls:
        main:
            pattern: ^/
            sso:
                require_previous_session: false
                provider:                 main
                check_path:               /otp/validate/     # Same as in app/config/routing.yml

                sso_scheme:               http               # Required
                sso_host:                 idp.example.com    # Required

                sso_otp_scheme:           http               # Optional
                sso_otp_host:             consumer1.com      # Optional

                sso_failure_path:         /login             # Can also be as an absolute path to service provider
                sso_path:                 /sso/login/        # SSO endpoint on IdP.

                sso_service_extra:           null            # Default service extra parameters. Optional.
                sso_service_extra_parameter: service_extra   # Parameter name. Optional

                sso_login_required:           1              # Optional
                sso_login_required_parameter: login_required # Optional

                sso_service:                  consumer1      # Consumer name

            logout:
                invalidate_session: true
                path:               /logout
                target:             http://idp.example.com/sso/logout?service=consumer1

    access_control:
        # We need to allow users to access the /sso/login route 
        # without being logged in
        - { path: ^/sso/login, role: IS_AUTHENTICATED_ANONYMOUSLY }
```

### Step 7: Create OTP route

In order to validate the OTP and authenticate the user, you must create a route that can retrieve the OTP details
from the database and that can verify if it is valid.

The route path doesn't really matter, but take note of it. It will be used in the SP bundle.
In our example, the route is `/internal/v1/sso`.

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

        if (!($otp instanceof OneTimePassword) || $otp->getUsed() === true) {
            throw new BadRequestHttpException('Invalid OTP password');
        }

        $response = [
            'data' => [
                'created_at' => $otp->getCreated()->format('r'),
                'hash' => $otp->getHash(),
                'password' => $otp->getPassword(),
                'is_used' => $otp->getUsed(),
            ],
        ];

        $otpManager->invalidate($otp);

        return new JsonResponse($response);
    }
}
?>
```

### Step 8: Add redirect path to login form

In your login form, add a hidden input with the name `_target_path` and the value
`{{ app.session.get('_security.main.target_path') }}` like so:

``` twig
<input type="hidden" name="_target_path" value="{{ app.session.get('_security.main.target_path') }}" />
```

This will be used to redirect the user after login to the OTP validation route.

### Step 9: Update database schema

To be able to store the OTPs, you must run the command:

``` bash
php bin/console doctrine:schema:update --force
```

Public API of this bundle
-------------------------

This bundle registers several services into service container. These services will help you customize SSO flow in the your application:

- [sso_identity_provider.service_manager](/src/Krtv/Bundle/SingleSignOnIdentityProviderBundle/Manager/ServiceManager.php) – Manager to work with SP. By given SP-identifier it returns an instance of `\Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceProviderInterface`
- [sso_identity_provider.otp_manager](https://github.com/korotovsky/SingleSignOnLibrary/blob/0.2.x/src/Krtv/SingleSignOn/Manager/OneTimePasswordManagerInterface.php) – Manager to work with OTP-tokens. Validation, invalidation and receiving.
- [sso_identity_provider.uri_signer](https://github.com/symfony/symfony/blob/3.1/src/Symfony/Component/HttpKernel/UriSigner.php) – Service for signing URLs, if you need to redirect users to /sso/login yourself.

That's it for Identity Provider.
Now you can continue configure [ServiceProvider part](https://github.com/korotovsky/SingleSignOnServiceProviderBundle#single-sign-on-service-provider)
