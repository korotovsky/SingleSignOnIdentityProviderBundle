Single Sign On Identity Provider
================================

[![Build Status](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/badges/build.png?b=0.1.x)](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/build-status/0.1.x)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/badges/quality-score.png?b=0.1.x)](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/?branch=0.1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/badges/coverage.png?b=0.1.x)](https://scrutinizer-ci.com/g/korotovsky/SingleSignOnIdentityProviderBundle/?branch=0.1.x) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/d68cc257-6cfc-4e66-9c51-28be57b347c4/mini.png)](https://insight.sensiolabs.com/projects/d68cc257-6cfc-4e66-9c51-28be57b347c4)

Disclaimer
--------
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
php composer.phar require "korotovsky/sso-idp-bundle"
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

    otp_parameter:    _otp
    secret_parameter: secret

```

``` yaml
# app/config/security.yml
security:
    access_control:
        - { path: ^/sso/login$, roles: [ROLE_USER, IS_AUTHENTICATED_FULLY] }
```

That's it for Identity Provider. Now you can continue configure [ServiceProvider part](https://github.com/korotovsky/SingleSignOnServiceProviderBundle#single-sign-on-service-provider)
